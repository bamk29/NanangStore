<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

class ProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $productToDelete = null;
    public $viewingHistoryProductId = null;

    public function viewHistory($productId)
    {
        $this->viewingHistoryProductId = $productId;
    }

    public function closeHistory()
    {
        $this->viewingHistoryProductId = null;
    }

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

    public function printQR($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $printData = [
            'printType' => 'qrLabel',
            'product' => [
                'name' => $product->name,
                'code' => $product->code,
                'price' => $product->retail_price,
            ]
        ];

        try {
            Http::timeout(5)->post('http://localhost:8000/print', $printData);
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Label QR Code untuk ' . $product->name . ' dikirim ke printer!']);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal terhubung ke server printer.']);
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
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
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

    public function updateProductDescription($productId, $description)
    {
        $product = Product::find($productId);
        if ($product) {
            $product->update(['description' => $description]);
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Deskripsi diperbarui.']);
        }
    }
    public function generateAbbreviation($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $abbreviation = $this->createAbbreviation($product->name);
            $product->update(['description' => $abbreviation]);
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Singkatan dibuat: ' . $abbreviation]);
        }
    }

    public function bulkGenerateAbbreviations()
    {
        // Only process products with empty descriptions
        $products = Product::whereNull('description')->orWhere('description', '')->get();
        $count = 0;

        foreach ($products as $product) {
            $abbreviation = $this->createAbbreviation($product->name);
            $product->update(['description' => $abbreviation]);
            $count++;
        }

        $this->dispatch('show-alert', ['type' => 'success', 'message' => $count . ' produk berhasil dibuatkan singkatan otomatis.']);
    }

    private function createAbbreviation($name)
    {
        $words = explode(' ', $name);
        $abbrWords = [];
        
        foreach ($words as $word) {
            if (empty($word)) continue;
            
            // Keep first letter (vowel or consonant)
            $first = mb_substr($word, 0, 1);
            // Remove vowels from the rest
            $rest = mb_substr($word, 1);
            $rest = preg_replace('/[aeiouAEIOU]/', '', $rest);
            
            $abbrWords[] = $first . $rest;
        }
        
        $result = implode(' ', $abbrWords);
        // Fallback if somehow empty
        return empty($result) ? $name : $result;
    }
}
