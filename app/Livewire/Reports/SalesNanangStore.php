<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Carbon\Carbon;

class SalesNanangStore extends Component
{
    public $startDate;
    public $endDate;
    public $groupBy = 'date'; // 'date' or 'product'
    public $paymentMethod = 'all';

    public $summaryMetrics = [];
    public $detailedData = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->runReport();
    }

    public function runReport()
    {
        $this->processSalesData();
    }

    private function processSalesData()
    {
        $query = Transaction::with(['details.product'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->whereHas('details.product', function ($q) {
                $q->where('category_id', '!=', 1); // Filter untuk SEMUA KATEGORI KECUALI Bakso (ID 1)
            });

        if ($this->paymentMethod !== 'all') {
            $query->where('payment_method', $this->paymentMethod);
        }

        $transactions = $query->get();

        $totalSales = 0;
        $totalProfit = 0;
        $totalItemsSold = 0;
        $groupedData = collect();

        foreach ($transactions as $transaction) {
            foreach ($transaction->details as $detail) {
                // Skip if product is category 1 (double check)
                if (!$detail->product || $detail->product->category_id == 1) continue;

                $sellingPrice = $detail->price;
                $costPrice = $detail->product->cost_price ?? 0;
                $quantity = $detail->quantity;

                $subtotal = $sellingPrice * $quantity;
                $profit = ($sellingPrice - $costPrice) * $quantity;

                $totalSales += $subtotal;
                $totalProfit += $profit;
                $totalItemsSold += $quantity;

                $key = $this->groupBy === 'date'
                    ? $transaction->created_at->format('Y-m-d')
                    : $detail->product->name;

                $item = $groupedData->get($key, [
                    'key' => $key,
                    'sales' => 0,
                    'profit' => 0,
                    'items_sold' => 0,
                ]);

                $item['sales'] += $subtotal;
                $item['profit'] += $profit;
                $item['items_sold'] += $quantity;

                $groupedData->put($key, $item);
            }
        }

        $this->summaryMetrics = [
            'total_sales' => $totalSales,
            'total_profit' => $totalProfit,
            'total_transactions' => $transactions->count(),
            'total_items_sold' => $totalItemsSold,
        ];

        $this->detailedData = $groupedData->sortByDesc('sales')->values()->all();
    }

    public function render()
    {
        return view('livewire.reports.sales-nanang-store');
    }
}
