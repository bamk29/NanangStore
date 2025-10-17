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
    public $sortBy = 'total_profit'; // total_profit, total_sales, total_quantity

    public $productsData = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->runReport();
    }

    public function runReport()
    {
        $query = TransactionDetail::with('product.category')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()]);

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
            
            $totalProfit = $productDetails->reduce(function ($carry, $detail) {
                $costPrice = $detail->product->cost_price ?? 0;
                return $carry + (($detail->price - $costPrice) * $detail->quantity);
            }, 0);

            return [
                'product_name' => $product->name,
                'category_name' => $product->category->name ?? 'N/A',
                'total_quantity' => $totalQuantity,
                'total_sales' => $totalSales,
                'total_profit' => $totalProfit,
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
