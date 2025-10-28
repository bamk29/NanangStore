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

        // Today's Sales for Nanang Store (category_id != 1)
        $nanangStoreTodaySales = TransactionDetail::whereHas('transaction', function ($q) {
                $q->whereDate('created_at', Carbon::today())->where('status', 'completed');
            })
            ->whereHas('product', function ($q) {
                $q->where('category_id', '!=', 1);
            })
            ->sum('subtotal');

        // Today's Sales for Giling Bakso (category_id = 1)
        $baksoStoreTodaySales = TransactionDetail::whereHas('transaction', function ($q) {
                $q->whereDate('created_at', Carbon::today())->where('status', 'completed');
            })
            ->whereHas('product', function ($q) {
                $q->where('category_id', 1);
            })
            ->sum('subtotal');

        return view('livewire.dashboard', [
            'todayTotalCash' => $todayTotalCash,
            'pendingTransactionCount' => $pendingTransactionCount,
            'nanangStoreTodaySales' => $nanangStoreTodaySales,
            'baksoStoreTodaySales' => $baksoStoreTodaySales,
        ]);
    }
}
