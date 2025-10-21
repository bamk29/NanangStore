<?php

namespace App\Livewire;

use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $todayTotalTransactions = Transaction::whereDate('created_at', Carbon::today())->sum('total_amount');

        return view('livewire.dashboard', [
            'todayTotalTransactions' => $todayTotalTransactions
        ]);
    }
}
