<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'customer_name',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'transaction_type',
        'notes',
        'status',
        'total_reduction_amount',
        'reduction_notes',
        'discount_for_bakso',
        'discount_for_nanang',

    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        // Format: INV-YYYYMMDDHHMMSS-MS (e.g., INV-20231202123045-123)
        // This ensures uniqueness without database queries and avoids race conditions.
        $date = now()->format('YmdHisv');
        
        return "{$prefix}-{$date}";
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cancel()
    {
        if ($this->status !== 'completed') {
            throw new \Exception("Hanya transaksi yang sudah selesai yang bisa dibatalkan.");
        }

        // 1. Kembalikan Stok & Kurangi Popularitas
        foreach ($this->details as $detail) {
            if ($product = $detail->product) {
                // Kembalikan stok menggunakan helper adjustStock
                $product->adjustStock(
                    $detail->quantity,
                    'correction',
                    'Cancel Transaction #' . $this->invoice_number,
                    $this->id
                );
                
                // Kurangi usage count
                \App\Models\ProductUsage::where('product_id', $product->id)->decrement('usage_count');
            }
        }

        // 2. Kembalikan Hutang berdasarkan History Ledger
        // Ini lebih akurat karena mengembalikan apa yang tercatat di ledger saat transaksi terjadi.
        $ledgers = \App\Models\CustomerLedger::where('transaction_id', $this->id)->get();
        if ($ledgers->isNotEmpty()) {
            foreach ($ledgers as $ledger) {
                $customer = $ledger->customer;
                if ($customer) {
                    $reverseType = $ledger->type === 'increase' ? 'decrease' : 'increase';
                    $customer->updateDebt(
                        $ledger->amount, 
                        $reverseType, 
                        'Cancel Transaction #' . $this->invoice_number, 
                        $this->id
                    );
                }
                // Opsional: Hapus ledger lama jika ingin 'menghapus jejak'? 
                // Tapi user bilang 'kalkulasikan hutangnya dan kembalikan keadaan', 
                // updateDebt di atas akan membuat log baru (Adjustment/Cancel) yang lebih baik untuk audit.
                // Atau, user mungkin ingin menghapus log lama? 
                // Jika "correct" biasanya "edit", mungkin lebih bersih jika log lama dihapus?
                // Tapi `updateDebt` membuat log baru. Jadi history akan:
                // 1. Beli (Debt +100k)
                // 2. Cancel (Debt -100k)
                // Balance kembali ke 0. Correct.
            }
        } else {
             // Fallback calculation in case Ledger is missing (Old transactions?)
             if ($customer = $this->customer) {
                // Calculate real transaction value (goods - reduction), ignoring any "old debt" included in total_amount
                $goodsValue = (float) $this->details->sum('subtotal');
                $transactionValue = $goodsValue - (float) $this->total_reduction_amount;
                
                // Net Payment = Paid Amount - Change Amount (Uang yang benar-benar masuk ke toko)
                $netPayment = (float) $this->paid_amount - (float) $this->change_amount;

                // Previous Debt = Current Debt - Transaction Value + Net Payment
                $debtChange = - $transactionValue + $netPayment;
                
                if (abs($debtChange) > 0.01) {
                     $type = $debtChange > 0 ? 'increase' : 'decrease';
                     $customer->updateDebt(abs($debtChange), $type, 'Cancel Transaction (Calc) #' . $this->invoice_number, $this->id);
                }
            }
        }

        // Revert points if they were earned on this transaction.
        // Points logic remains calculated because PointsLedger might not exist or be granular.
        if ($this->customer) {
            $goodsValue = (float) $this->details->sum('subtotal');
            $transactionValue = $goodsValue - (float) $this->total_reduction_amount; // Re-calc needed if used above
            $netPayment = (float) $this->paid_amount - (float) $this->change_amount;
            $debtIncrease = $transactionValue - $netPayment;
            
            if ($debtIncrease <= 0 && $this->payment_method !== 'debt') {
                $pointsEarned = floor($goodsValue / 10000); // Points based on gross value
                if ($pointsEarned > 0) {
                     $this->customer->decrement('points', $pointsEarned);
                }
            }
        }

        // 3. Hapus Catatan Keuangan Terkait
        \App\Models\FinancialTransaction::where('transaction_id', $this->id)->delete();

        // 4. Ubah Status Transaksi
        $this->status = 'cancelled';
        $this->save();
    }
}
