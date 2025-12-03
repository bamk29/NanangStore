<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EditProduct extends Component
{
    public Product $product;

    public $name;
    public $code;
    public $description;
    public $category_id;
    public $supplier_id;
    public $stock;
    public $retail_price;
    public $wholesale_price;
    public $wholesale_min_qty;
    public $cost_price;

    public $base_unit_id;
    public $box_unit_id;
    public $unit_price;
    public $box_price;
    public $units_in_box;
    public $unit_cost;
    public $box_cost;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,' . $this->product->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'stock' => 'required|numeric|min:0',
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
            'box_cost' => 'nullable|numeric|min:0'

        ];
    }

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->code = $product->code;
        $this->description = $product->description;
        $this->category_id = $product->category_id;
        $this->supplier_id = $product->supplier_id;
        $this->stock = $product->stock;
        $this->retail_price = $product->retail_price;
        $this->wholesale_price = $product->wholesale_price;
        $this->wholesale_min_qty = $product->wholesale_min_qty;
        $this->cost_price = $product->cost_price;
        $this->base_unit_id = $product->base_unit_id;
        $this->box_unit_id = $product->box_unit_id;
        $this->unit_price = $product->unit_price;
        $this->box_price = $product->box_price;
        $this->units_in_box = $product->units_in_box;
        $this->unit_cost = $product->unit_cost;
        $this->box_cost = $product->box_cost;

    }

    public $calculatedBoxStock = 0;

    public function getRecommendedCostPriceProperty()
    {
        if ($this->box_cost > 0 && $this->units_in_box > 0) {
            return $this->box_cost / $this->units_in_box;
        }
        return 0;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['stock', 'units_in_box'])) {
            if ($this->units_in_box > 0) {
                $this->calculatedBoxStock = floor($this->stock / $this->units_in_box);
            } else {
                $this->calculatedBoxStock = 0;
            }
        }
    }

    public function save()
    {
        try {
            $validatedData = $this->validate();
            $this->product->update($validatedData);

            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Produk berhasil diperbarui.']);
            return redirect()->route('products.index');

        } catch (ValidationException $e) {
            // Log validation errors for debugging
            Log::error('Validation Errors on EditProduct: ', $e->errors());

            // Dispatch a user-friendly alert
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Gagal menyimpan. Periksa kembali data yang Anda masukkan.'
            ]);

            // Let Livewire show validation messages next to fields
            throw $e;

        } catch (\Exception $e) {
            // Catch other exceptions
            Log::error('An unexpected error occurred in EditProduct: ' . $e->getMessage());

            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan tak terduga: ' . $e->getMessage()
            ]);
        }
    }

    public function printLabel()
    {
        $printData = [
            'printType' => 'priceLabel',
            'product' => [
                'name' => $this->name,
                'code' => $this->code,
                'price' => $this->retail_price,
                'base_unit' => $this->base_unit_id,
                'box_unit' => $this->box_unit_id,
            ]
        ];

        try {
            Http::timeout(5)->post('http://localhost:8000/print', $printData);
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Label harga untuk ' . $this->name . ' dikirim ke printer!']);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal terhubung ke server printer.']);
        }
    }

    public function printQR()
    {
        $printData = [
            'printType' => 'qrLabel',
            'product' => [
                'name' => $this->name,
                'code' => $this->code,
                'price' => $this->retail_price,
            ]
        ];

        try {
            Http::timeout(5)->post('http://localhost:8000/print', $printData);
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Label QR Code untuk ' . $this->name . ' dikirim ke printer!']);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal terhubung ke server printer.']);
        }
    }

    public function render()
    {
        return view('livewire.products.edit-product', [
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'base_units' => Unit::orderBy('name')->get(),
            'box_units' => Unit::orderBy('name')->get(),
        ]);
    }
}

