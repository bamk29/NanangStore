<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\Category;
use App\Models\TransactionDetail;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class InventoryReport extends Component
{
    use WithPagination;

    public $categoryFilter = '';
    public $stockFilter = 'all';
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function getInventoryData()
    {
        $query = Product::query()
            ->with('category')
            ->when($this->categoryFilter, function ($query) {
                return $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->stockFilter === 'low', function ($query) {
                return $query->where('stock', '<=', 10);
            })
            ->when($this->stockFilter === 'out', function ($query) {
                return $query->where('stock', 0);
            })
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(10);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getMovementData($productId)
    {
        $lastMonth = Carbon::now()->subMonth();

        return TransactionDetail::where('product_id', $productId)
            ->whereHas('transaction', function ($query) use ($lastMonth) {
                $query->where('created_at', '>=', $lastMonth);
            })
            ->with('transaction')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->transaction->created_at->format('Y-m-d');
            })
            ->map(function ($items) {
                return [
                    'quantity' => $items->sum('quantity'),
                    'total' => $items->sum('subtotal')
                ];
            });
    }

    public function exportInventoryCsv()
    {
        $products = $this->getInventoryData();
        $filename = 'inventory_report_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');

        fputcsv($handle, ['Code', 'Name', 'Category', 'Stock', 'Retail Price', 'Wholesale Price']);

        foreach ($products as $product) {
            fputcsv($handle, [
                $product->code,
                $product->name,
                $product->category->name,
                $product->stock,
                $product->retail_price,
                $product->wholesale_price
            ]);
        }

        fclose($handle);
    }

    public function getTotalValue()
    {
        return Product::sum(\DB::raw('stock * retail_price'));
    }

    public function getLowStockCount()
    {
        return Product::where('stock', '<=', 10)->where('stock', '>', 0)->count();
    }

    public function getOutOfStockCount()
    {
        return Product::where('stock', 0)->count();
    }

    public function render()
    {
        return view('livewire.reports.inventory-report', [
            'inventoryData' => $this->getInventoryData(),
            'categories' => Category::orderBy('name')->get(),
            'totalValue' => $this->getTotalValue(),
            'lowStockCount' => $this->getLowStockCount(),
            'outOfStockCount' => $this->getOutOfStockCount(),
        ]);
    }
}
