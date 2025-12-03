<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\Category;
use App\Models\TransactionDetail;
use App\Models\GoodsReceiptItem;
use App\Models\StockAdjustment;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class InventoryReport extends Component
{
    use WithPagination;

    public $categoryFilter = '';
    public $stockFilter = 'all';
    public $storeFilter = ''; // 'giling_bakso' or 'nanang_store'
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $showHistoryModal = false;
    public $selectedProduct = null;
    public $movementData = [];

    // Adjustment Properties
    public $showAdjustmentModal = false;
    public $adjustmentQuantity = 0;
    public $adjustmentType = 'repack_out'; // repack_out, damage, internal_use, repack_in
    public $adjustmentNotes = '';

    public function openHistoryModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->movementData = $this->getMovementData($productId);
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->selectedProduct = null;
        $this->movementData = [];
    }

    public function openAdjustmentModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->adjustmentQuantity = 0;
        $this->adjustmentType = 'damage';
        $this->adjustmentNotes = '';
        $this->showAdjustmentModal = true;
    }

    public function closeAdjustmentModal()
    {
        $this->showAdjustmentModal = false;
        $this->selectedProduct = null;
    }

    public function adjustStock()
    {
        $this->validate([
            'adjustmentQuantity' => 'required|numeric|not_in:0',
            'adjustmentType' => 'required|string',
            'adjustmentNotes' => 'nullable|string',
        ]);

        if ($this->selectedProduct) {
            // Create Stock Adjustment Record
            StockAdjustment::create([
                'product_id' => $this->selectedProduct->id,
                'quantity' => $this->adjustmentQuantity, // Can be negative or positive
                'type' => $this->adjustmentType,
                'notes' => $this->adjustmentNotes,
                'user_id' => auth()->id(),
            ]);

            // Update Product Stock
            $this->selectedProduct->increment('stock', $this->adjustmentQuantity);
            
            // Recalculate box stock if applicable
            if ($this->selectedProduct->units_in_box > 0) {
                $this->selectedProduct->box_stock = floor($this->selectedProduct->stock / $this->selectedProduct->units_in_box);
                $this->selectedProduct->save();
            }

            session()->flash('message', 'Stock adjusted successfully.');
            $this->closeAdjustmentModal();
            
            // Refresh history if open (though modal closes, good practice)
            if ($this->showHistoryModal) {
                $this->movementData = $this->getMovementData($this->selectedProduct->id);
            }
        }
    }

    public function getInventoryData()
    {
        $query = Product::query()
            ->with('category')
            ->when($this->categoryFilter, function ($query) {
                return $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->storeFilter, function ($query) {
                if ($this->storeFilter === 'giling_bakso') {
                    return $query->where('category_id', 1);
                } elseif ($this->storeFilter === 'nanang_store') {
                    return $query->where('category_id', '!=', 1);
                }
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

        // 1. Sales (Out)
        $sales = TransactionDetail::where('product_id', $productId)
            ->whereHas('transaction', function ($query) use ($lastMonth) {
                $query->where('created_at', '>=', $lastMonth)
                      ->where('status', 'completed');
            })
            ->with('transaction')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->transaction->created_at,
                    'type' => 'sale',
                    'reference' => $item->transaction->invoice_number,
                    'quantity_in' => 0,
                    'quantity_out' => $item->quantity,
                    'notes' => 'Penjualan ke: ' . ($item->transaction->customer_name ?? $item->transaction->customer->name ?? 'Guest'),
                ];
            });

        // 2. Purchases (In)
        $purchases = GoodsReceiptItem::where('product_id', $productId)
            ->whereHas('goodsReceipt', function ($query) use ($lastMonth) {
                $query->where('receipt_date', '>=', $lastMonth);
            })
            ->with('goodsReceipt')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->goodsReceipt->receipt_date),
                    'type' => 'purchase',
                    'reference' => $item->goodsReceipt->receipt_number,
                    'quantity_in' => $item->quantity_received,
                    'quantity_out' => 0,
                    'notes' => 'Penerimaan Barang',
                ];
            });

        // 3. Adjustments (In/Out)
        $adjustments = StockAdjustment::where('product_id', $productId)
            ->where('created_at', '>=', $lastMonth)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->created_at,
                    'type' => 'adjustment',
                    'reference' => 'ADJ-' . $item->id,
                    'quantity_in' => $item->quantity > 0 ? $item->quantity : 0,
                    'quantity_out' => $item->quantity < 0 ? abs($item->quantity) : 0,
                    'notes' => $item->notes ?? $item->type,
                ];
            });

        // Merge and Sort
        return $sales->concat($purchases)->concat($adjustments)->sortByDesc('date');
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
