<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Models\Product;
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

        return view('livewire.transactions.pending-list', [
            'transactions' => $pending_transactions
        ]);
    }
}