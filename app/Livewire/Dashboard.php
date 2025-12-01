<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Today's Cash in Drawer
        $paidInCash = Transaction::whereDate('created_at', Carbon::today())
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('paid_amount');

        $changeGiven = Transaction::whereDate('created_at', Carbon::today())
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('change_amount');

        $todayTotalCash = $paidInCash - $changeGiven;

        // Pending Transaction Count
        $pendingTransactionCount = Transaction::where('status', 'pending')->count();

        // Today's Sales for Nanang Store (from FinancialTransaction)
        $nanangStoreTodaySales = \App\Models\FinancialTransaction::whereDate('date', Carbon::today())
            ->where('business_unit', 'nanang_store')
            ->where('type', 'income')
            ->where('category', 'penjualan')
            ->sum('amount');

        // Today's Sales for Giling Bakso (from FinancialTransaction)
        $baksoStoreTodaySales = \App\Models\FinancialTransaction::whereDate('date', Carbon::today())
            ->where('business_unit', 'giling_bakso')
            ->where('type', 'income')
            ->where('category', 'penjualan')
            ->sum('amount');

        return view('livewire.dashboard', [
            'todayTotalCash' => $todayTotalCash,
            'pendingTransactionCount' => $pendingTransactionCount,
            'nanangStoreTodaySales' => $nanangStoreTodaySales,
            'baksoStoreTodaySales' => $baksoStoreTodaySales,
        ]);
    }
}
