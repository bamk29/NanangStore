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
        $pending_transactions = Transaction::with('customer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $total_pending_transactions = $pending_transactions->count();
        $total_pending_amount = $pending_transactions->sum('total_amount');

        $pending_transaction_ids = $pending_transactions->pluck('id');

        $product_2_quantity = TransactionDetail::whereIn('transaction_id', $pending_transaction_ids)
            ->where('product_id', 2)
            ->sum('quantity');

        $product_2 = Product::find(2);
        $product_2_name = $product_2 ? $product_2->name : 'Produk ID 2';


        return view('livewire.transactions.pending-list', [
            'transactions' => $pending_transactions,
            'total_pending_transactions' => $total_pending_transactions,
            'total_pending_amount' => $total_pending_amount,
            'product_2_quantity' => $product_2_quantity,
            'product_2_name' => $product_2_name,
        ]);
    }
}