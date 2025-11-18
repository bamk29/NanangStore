<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\TransactionDetail;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PendingList extends Component
{
    public $transaction_to_cancel;

    public function confirmCancel($transactionId)
    {
        $this->transaction_to_cancel = $transactionId;
    }

    public function cancelTransaction()
    {
        $transaction = Transaction::find($this->transaction_to_cancel);

        if ($transaction && $transaction->status === 'pending') {
            // Untuk transaksi tunda, stok tidak dikurangi, jadi saat dibatalkan,
            // kita hanya perlu mengubah status tanpa mengembalikan stok.
            $transaction->status = 'cancelled';
            $transaction->save();

            session()->flash('message', 'Transaksi tunda berhasil dibatalkan.');
        }

        $this->transaction_to_cancel = null;
    }

    public function render()
    {
        // Get all pending transactions for the list
        $pending_transactions = Transaction::with('customer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate summaries for all pending transactions
        $total_pending_transactions = $pending_transactions->count();
        $total_pending_amount = $pending_transactions->sum('total_amount');

        // Specifically calculate pending stock for product 2 for TODAY
        $product_2_quantity_today = TransactionDetail::whereHas('transaction', function ($query) {
            $query->where('status', 'pending')
                  ->whereDate('created_at', \Carbon\Carbon::today());
        })
        ->where('product_id', 2)
        ->sum('quantity');

        $product_2 = Product::find(2);
        $product_2_name = $product_2 ? $product_2->name : 'Ayam';


        return view('livewire.transactions.pending-list', [
            'transactions' => $pending_transactions,
            'total_pending_transactions' => $total_pending_transactions,
            'total_pending_amount' => $total_pending_amount,
            'product_2_quantity' => $product_2_quantity_today,
            'product_2_name' => $product_2_name,
        ]);
    }
}