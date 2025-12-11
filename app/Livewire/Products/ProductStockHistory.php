<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\WithPagination;

class ProductStockHistory extends Component
{
    use WithPagination;

    public $productId;

    public function mount($productId)
    {
        $this->productId = $productId;
    }

    public function render()
    {
        return view('livewire.products.product-stock-history', [
            'movements' => \App\Models\StockMovement::where('product_id', $this->productId)
                ->with(['user', 'transaction.customer', 'goodsReceipt'])
                ->latest()
                ->paginate(10)
        ]);
    }
}
