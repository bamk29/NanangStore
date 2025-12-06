<?php

namespace App\Livewire\PriceAdjustments;

use Livewire\Component;
use App\Models\Product;
use App\Models\PriceAdjustment;
use Livewire\WithPagination;

class PriceAdjustmentList extends Component
{
    use WithPagination;

    public $product_id;
    public $product_name;
    
    // Current prices
    public $current_cost = 0;
    public $current_retail = 0;
    public $current_wholesale = 0;
    public $current_min_qty = 0;
    public $current_box_cost = 0;
    public $current_units_in_box = 0;
    
    // New prices
    public $new_cost = 0;
    public $new_retail = 0;
    public $new_wholesale = 0;
    public $new_min_qty = 0;
    public $new_box_cost = 0;
    public $new_units_in_box = 0;
    
    public $notes = '';

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'new_cost' => 'required|numeric|min:0',
        'new_retail' => 'required|numeric|min:0',
        'new_wholesale' => 'nullable|numeric|min:0',
        'new_min_qty' => 'nullable|integer|min:0',
        'notes' => 'nullable|string',
    ];

    public function selectProduct($productId, $productName, $cost, $retail, $wholesale, $minQty)
    {
        $this->product_id = $productId;
        $this->product_name = $productName;
        
        // Load full product data for box cost
        $product = Product::find($productId);
        
        // Set current prices
        $this->current_cost = $cost;
        $this->current_retail = $retail;
        $this->current_wholesale = $wholesale ?? 0;
        $this->current_min_qty = $minQty ?? 0;
        $this->current_box_cost = $product->box_cost ?? 0;
        $this->current_units_in_box = $product->units_in_box ?? 0;
        
        // Pre-fill new prices with current
        $this->new_cost = $cost;
        $this->new_retail = $retail;
        $this->new_wholesale = $wholesale ?? 0;
        $this->new_min_qty = $minQty ?? 0;
        $this->new_box_cost = $product->box_cost ?? 0;
        $this->new_units_in_box = $product->units_in_box ?? 0;
    }

    public function save()
    {
        $this->validate();

        // Update product prices
        $product = Product::findOrFail($this->product_id);
        
        // Create price adjustment history
        PriceAdjustment::create([
            'product_id' => $this->product_id,
            'user_id' => auth()->id(),
            'old_cost_price' => $this->current_cost,
            'new_cost_price' => $this->new_cost,
            'old_retail_price' => $this->current_retail,
            'new_retail_price' => $this->new_retail,
            'old_wholesale_price' => $this->current_wholesale,
            'new_wholesale_price' => $this->new_wholesale,
            'old_wholesale_min_qty' => $this->current_min_qty,
            'new_wholesale_min_qty' => $this->new_min_qty,
            'notes' => $this->notes,
        ]);

        // Update product
        $product->update([
            'cost_price' => $this->new_cost,
            'retail_price' => $this->new_retail,
            'wholesale_price' => $this->new_wholesale,
            'wholesale_min_qty' => $this->new_min_qty,
            'box_cost' => $this->new_box_cost,
            'units_in_box' => $this->new_units_in_box,
        ]);

        session()->flash('success', 'Harga produk berhasil diperbarui!');
        
        $this->resetForm();
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset([
            'product_id',
            'product_name',
            'current_cost',
            'current_retail',
            'current_wholesale',
            'current_min_qty',
            'new_cost',
            'new_retail',
            'new_wholesale',
            'new_min_qty',
            'notes',
        ]);
    }

    public function render()
    {
        $adjustments = PriceAdjustment::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('livewire.price-adjustments.price-adjustment-list', [
            'adjustments' => $adjustments,
        ]);
    }
}
