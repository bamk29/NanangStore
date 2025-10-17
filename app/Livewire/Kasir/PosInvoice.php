<?php

namespace App\Livewire\Kasir;

use Livewire\Component;
use App\Models\Transaction;

class PosInvoice extends Component
{
    public $transaction;

  

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
    public function backToPos()
    {
        return redirect()->route('kasir-bersama.index');
    }

    public function print()
    {
        $this->dispatch('printInvoice');
    }

    public function newTransaction()
    {
        return redirect()->route('kasir-bersama.index');
    }

    public function render()
    {
        return view('livewire.kasir.pos-invoice');
    }
}
