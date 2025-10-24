<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $productToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc']
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteProduct()
    {
        if ($this->productToDelete) {
            $product = Product::find($this->productToDelete);
            if ($product) {
                $product->delete();
                $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Produk berhasil dihapus.']);
            }
        }
        $this->productToDelete = null;
        $this->dispatch('productDeleted'); // To close modal
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                return $query->where('category_id', $this->categoryFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->with(['category'])
            ->paginate(10);

        return view('livewire.products.product-list', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get()
        ]);
    }

}
