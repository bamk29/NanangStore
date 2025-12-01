<?php

namespace App\Livewire\Reports;

use App\Models\TransactionDetail;
use App\Models\Category;
use Livewire\Component;
use Carbon\Carbon;

class ProductReport extends Component
{
    public $startDate;
    public $endDate;
    public $categoryFilter = 'all';
    public $storeFilter = 'all'; // all, nanang_store, bakso
    public $sortBy = 'total_profit'; // total_profit, total_sales, total_quantity

    public $productsData = [];

    public function mount()
    {
        $this->setDateRange('today');
    }

    public function setDateRange($range)
    {
        if ($range === 'today') {
            $this->startDate = Carbon::today()->format('Y-m-d');
            $this->endDate = Carbon::today()->format('Y-m-d');
        } elseif ($range === 'week') {
            $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        } elseif ($range === 'month') {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
        
        $this->runReport();
    }

    public function runReport()
    {
        $query = TransactionDetail::with('product.category')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()]);

        if ($this->storeFilter === 'bakso') {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->storeFilter === 'nanang_store') {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        if ($this->categoryFilter !== 'all') {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', $this->categoryFilter);
            });
        }

        $details = $query->get();

        $groupedData = $details->groupBy('product_id')->map(function ($productDetails) {
            $firstDetail = $productDetails->first();
            $product = $firstDetail->product;

            $totalQuantity = $productDetails->sum('quantity');
            $totalSales = $productDetails->sum('subtotal');
            
            // Calculate Total Cost based on current product cost price (as historical cost is not available)
            $currentCostPrice = $product->cost_price ?? 0;
            $totalCost = $currentCostPrice * $totalQuantity;
            
            // Calculate Profit based on (Sales - Cost)
            // Note: This assumes current cost price applies to all historical sales, which is a limitation but acceptable per plan.
            $totalProfit = $totalSales - $totalCost;
            
            $avgPrice = $totalQuantity > 0 ? $totalSales / $totalQuantity : 0;
            $marginPercentage = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;

            return [
                'product_name' => $product->name,
                'category_name' => $product->category->name ?? 'N/A',
                'total_quantity' => $totalQuantity,
                'total_sales' => $totalSales,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'avg_price' => $avgPrice,
                'margin_percentage' => $marginPercentage,
            ];
        });

        $this->productsData = $groupedData->sortByDesc($this->sortBy)->all();
    }

    public function render()
    {
        return view('livewire.reports.product-report', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
