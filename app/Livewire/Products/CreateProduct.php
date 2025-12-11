<?php

namespace App\Livewire\Products;

use Livewire\Attributes\Validate;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use App\Models\Unit;

class CreateProduct extends Component
{
    #[Validate]
    public $name = '';
    public $code = '';
    public $description = '';
    public $category_id = '';
    public $supplier_id = '';
    public $stock = 0;
    public $retail_price = 0;
    public $wholesale_price = 0;
    public $wholesale_min_qty = 1;
    public $cost_price = 0;

    public $base_unit_id = '';
    public $box_unit_id = '';
    public $unit_price = 0;
    public $box_price = 0;
    public $units_in_box = 1;
    public $unit_cost = 0;
    public $box_cost = 0;
    public $calculatedBoxStock = 0;

    public function getRecommendedCostPriceProperty()
    {
        if ($this->box_cost > 0 && $this->units_in_box > 0) {
            return $this->box_cost / $this->units_in_box;
        }
        return 0;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'code' => 'required|unique:products,code',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'retail_price' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'wholesale_min_qty' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'base_unit_id' => 'nullable|exists:units,id',
            'box_unit_id' => 'nullable|exists:units,id',
            'unit_price' => 'nullable|numeric|min:0',
            'box_price' => 'nullable|numeric|min:0',
            'units_in_box' => 'nullable|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'box_cost' => 'nullable|numeric|min:0',
        ];
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['stock', 'units_in_box'])) {
            if ($this->units_in_box > 0) {
                $this->calculatedBoxStock = floor((int)$this->stock / (int)$this->units_in_box);
            } else {
                $this->calculatedBoxStock = 0;
            }
        }
    }

    public function save()
    {
        $validated = $this->validate();

        // 1. Ambil stok awal & set 0 untuk creation
        $initialStock = (int) $validated['stock'];
        $validated['stock'] = 0;

        // 2. Buat produk
        $product = Product::create($validated);

        // 3. Catat stok awal di ledger (jika ada)
        if ($initialStock > 0) {
            $product->adjustStock($initialStock, 'item_add', 'Initial Stock via Create Product');
        }

       $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Produk berhasil ditambahkan.']);
        return redirect()->route('products.index');
    }

    public function generateCode()
    {
        $prefix = 'P';
        $lastProduct = Product::latest('id')->first();
        $nextId = $lastProduct ? $lastProduct->id + 1 : 1;
        $this->code = $prefix . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    public function mount()
    {
        $this->generateCode();
    }

    public function render()
    {
        return view('livewire.products.create-product', [
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'base_units' => Unit::orderBy('name')->get(),
            'box_units' => Unit::orderBy('name')->get()
        ]);
    }
}
