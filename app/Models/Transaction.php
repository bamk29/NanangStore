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
                $product->increment('stock', $detail->quantity);

                // Hitung ulang stok boks jika ada
                if ($product->units_in_box > 0) {
                    $product->box_stock = floor($product->stock / $product->units_in_box);
                    $product->save();
                }
                
                // Kurangi usage count
                \App\Models\ProductUsage::where('product_id', $product->id)->decrement('usage_count');
            }
        }

        // 2. Kembalikan Hutang & Poin Pelanggan
        if ($customer = $this->customer) {
            $transactionValue = (float) $this->total_amount; // Actual amount charged (after reductions)
            $goodsValue = (float) $this->details->sum('subtotal'); // Gross value for points

            // Previous Debt = Current Debt - Transaction Value + Amount Paid
            $customer->debt = max(0, (float) $customer->debt - $transactionValue + (float) $this->paid_amount);
            $customer->save();

            // Revert points if they were earned on this transaction.
            // Points are earned if debt did NOT increase (i.e. paid in full or more)
            $debtIncrease = $transactionValue - (float) $this->paid_amount;
            
            if ($debtIncrease <= 0 && $this->payment_method !== 'debt') {
                $pointsEarned = floor($goodsValue / 10000); // Points based on gross value
                if ($pointsEarned > 0) {
                    $customer->decrement('points', $pointsEarned);
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
