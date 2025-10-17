<?php

namespace App\Livewire\Pos;

use App\Models\Transaction;
use Livewire\Component;

class Invoice extends Component
{
    public $transaction;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction->load('customer');
    }

    public function newTransaction()
    {
        return redirect()->route('pos.index');
    }

    public function render()
    {
        return view('livewire.pos.invoice')
            ->layout('components.layouts.app');
    }
}
