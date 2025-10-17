<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class PosIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $selectedProduct = null;

    public function mount()
    {
        $this->selectedProduct = null;
    }

    public function selectProduct($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $this->selectedProduct = $product;
            $this->dispatch('productSelected', $productId);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Produk tidak ditemukan'
            ]);
        }
    }

    public function render()
    {
        $products = Product::query()
            // Join with product_usages table to get the usage_count
            ->leftJoin('product_usages', 'products.id', '=', 'product_usages.product_id')

            // Search logic
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    // Use products.name and products.code to avoid ambiguous column errors
                    $q->where('products.name', 'like', '%' . $this->search . '%')
                      ->orWhere('products.code', 'like', '%' . $this->search . '%');
                });
            })

            // Category filter
            ->when($this->categoryFilter, function ($query) {
                return $query->where('products.category_id', $this->categoryFilter);
            })

            ->where('products.stock', '>', 0)
            ->with('category')

            // IMPORTANT: Select only columns from the products table
            ->select('products.*')

            // Order by popularity (usage_count) first, then by name
            ->orderBy('product_usages.usage_count', 'desc')
            ->orderBy('products.name', 'asc')

            ->paginate(24);

        return view('livewire.pos.pos-index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get()
        ]);
    }
}
