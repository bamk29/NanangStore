<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Carbon\Carbon;

class DailyProfitReport extends Component
{
    public $startDate;
    public $endDate;
    public $reportData = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfDay()->format('Y-m-d');
        $this->runReport();
    }

    public function runReport()
    {
        $transactions = Transaction::with('details.product')
            ->where('status', 'completed')
            ->whereBetween('created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->get();

        $dailyProfits = collect();

        foreach ($transactions as $transaction) {
            $date = $transaction->created_at->format('Y-m-d');

            $dayData = $dailyProfits->get($date, [
                'date' => $date,
                'bakso_profit' => 0,
                'nanang_store_profit' => 0,
                'total_profit' => 0,
            ]);

            foreach ($transaction->details as $detail) {
                if (!$detail->product) continue;

                $cost = $detail->cost_price ?? $detail->product->cost_price ?? 0;
                $profit = ($detail->price - $cost) * $detail->quantity;

                if ($detail->product->category_id == 1) {
                    $dayData['bakso_profit'] += $profit;
                } else {
                    $dayData['nanang_store_profit'] += $profit;
                }
                $dayData['total_profit'] += $profit;
            }

            $dailyProfits->put($date, $dayData);
        }

        $this->reportData = $dailyProfits->sortByDesc('date')->values()->all();
    }

    public function render()
    {
        return view('livewire.reports.daily-profit-report');
    }
}
