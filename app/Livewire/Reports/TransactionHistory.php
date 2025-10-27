<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class TransactionHistory extends Component
{
    use WithPagination;

    public $selectedDate;
    public $search = '';

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $transactions = Transaction::with(['customer', 'user'])
            ->whereDate('created_at', $this->selectedDate)
            ->where(function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(20);

        return view('livewire.reports.transaction-history', [
            'transactions' => $transactions,
        ]);
    }

    public function cancelTransaction($transactionId)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($transactionId) {
                $transaction = Transaction::with('details.product', 'customer')->findOrFail($transactionId);

                // 1. Hanya batalkan transaksi yang sudah selesai
                if ($transaction->status !== 'completed') {
                    throw new \Exception("Hanya transaksi yang sudah selesai yang bisa dibatalkan.");
                }

                // 2. Kembalikan Stok & Kurangi Popularitas
                foreach ($transaction->details as $detail) {
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

                // 3. Kembalikan Hutang & Poin Pelanggan
                if ($customer = $transaction->customer) {
                    // Logika pembalikan hutang: tambahkan kembali selisih antara total dan yang dibayar
                    $debtChange = $transaction->total_amount - $transaction->paid_amount;
                    $customer->debt += $debtChange;
                    
                    // Logika pembalikan poin
                    $pointsEarned = floor($transaction->total_amount / 10000);
                    if ($pointsEarned > 0) {
                        $customer->decrement('points', $pointsEarned);
                    }
                    
                    $customer->save();
                }

                // 4. Hapus Catatan Keuangan Terkait
                \App\Models\FinancialTransaction::where('transaction_id', $transaction->id)->delete();

                // 5. Ubah Status Transaksi
                $transaction->status = 'cancelled';
                $transaction->save();
            });

            session()->flash('success', 'Transaksi berhasil dibatalkan.');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}