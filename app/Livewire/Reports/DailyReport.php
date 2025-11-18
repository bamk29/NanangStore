<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Carbon\Carbon;

class DailyReport extends Component
{
    public $selectedDate;
    public $storeFilter = 'all'; // all, bakso, nanang_store
    public $summary = [];
    public $productSalesData = [];

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->runReport();
    }

    public function runReport()
    {
        $date = Carbon::parse($this->selectedDate);

        // 1. Get all relevant transaction *details* directly
        $detailsQuery = \App\Models\TransactionDetail::whereHas('transaction', function ($q) use ($date) {
            $q->whereDate('created_at', $date)->where('status', 'completed');
        })->with(['product', 'transaction']); // Eager load relationships

        // 2. Apply store filter
        if ($this->storeFilter === 'bakso') {
            $detailsQuery->whereHas('product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->storeFilter === 'nanang_store') {
            $detailsQuery->whereHas('product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        $filteredDetails = $detailsQuery->get();

        // 3. Calculate summaries from the filtered details
        $totalSales = $filteredDetails->sum('subtotal');
        $totalCost = $filteredDetails->reduce(function ($carry, $detail) {
            if (!$detail->product) return $carry;
            $cost = $detail->product->cost_price * $detail->quantity;
            return $carry + $cost;
        }, 0);
        $totalProfit = $filteredDetails->reduce(function ($carry, $detail) {
            if (!$detail->product) return $carry;
            $profit = ($detail->price - $detail->product->cost_price) * $detail->quantity;
            return $carry + $profit;
        }, 0);

        // 4. Group details by transaction to calculate payment methods
        $transactions = $filteredDetails->groupBy('transaction_id');
        $salesByPaymentMethod = [
            'cash' => 0,
            'transfer' => 0,
            'debt' => 0,
        ];
        
        foreach ($transactions as $transactionId => $details) {
            $paymentMethod = $details->first()->transaction->payment_method;
            if (array_key_exists($paymentMethod, $salesByPaymentMethod)) {
                $salesByPaymentMethod[$paymentMethod] += $details->sum('subtotal');
            }
        }

        // 5. Calculate product performance
        $productPerformance = collect();
        foreach ($filteredDetails as $detail) {
            if (!$detail->product) continue;

            $sellingPrice = $detail->price;
            $costPrice = $detail->product->cost_price ?? 0;
            $quantity = $detail->quantity;
            $profit = ($sellingPrice - $costPrice) * $quantity;

            $productId = $detail->product_id;
            $item = $productPerformance->get($productId, [
                'product_name' => $detail->product->name,
                'remaining_stock' => $detail->product->stock,
                'total_quantity' => 0,
                'total_sales' => 0,
                'total_profit' => 0,
                'cost_price' => $costPrice,
                'avg_selling_price' => 0,
                'prices' => [],
            ]);

            $item['total_quantity'] += $quantity;
            $item['total_sales'] += $detail->subtotal;
            $item['total_profit'] += $profit;
            $item['prices'][] = $sellingPrice;

            $productPerformance->put($productId, $item);
        }

        $this->productSalesData = $productPerformance->map(function ($item) {
            if (count($item['prices']) > 0) {
                $item['avg_selling_price'] = collect($item['prices'])->avg();
            }
            unset($item['prices']);
            return $item;
        })->sortByDesc('total_profit')->values()->all();

        $this->summary = [
            'total_sales' => $totalSales,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'total_transactions' => $transactions->count(), // Count of unique transactions
            'sales_by_payment' => $salesByPaymentMethod,
        ];
    }

    public function setStoreFilter($filter)
    {
        $this->storeFilter = $filter;
        $this->runReport();
    }

    public function render()
    {
        return view('livewire.reports.daily-report');
    }
}
