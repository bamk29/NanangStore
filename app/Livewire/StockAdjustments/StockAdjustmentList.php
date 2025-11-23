<?php

namespace App\Livewire\StockAdjustments;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class StockAdjustmentList extends Component
{
    use WithPagination;

    public $product_id;
    public $quantity;
    public $type;
    public $notes;
    public $product_name;
    public $search = '';
    public $products = [];

    protected $rules = [
        'product_id' => 'required',
        'quantity' => 'required|integer|min:1',
        'type' => 'required',
        'notes' => 'nullable|string',
    ];

    public function updatedSearch($value)
    {
        if (strlen($this->search) < 2) {
            $this->products = [];
            return;
        }
        $this->products = Product::where('name', 'like', '%'.$this->search.'%')
            ->orWhere('code', 'like', '%'.$this->search.'%')
            ->take(5)
            ->get();
    }

    public function selectProduct($productId, $productName)
    {
        $this->product_id = $productId;
        $this->product_name = $productName;
        $this->search = $productName;
        $this->products = [];
    }

    public function save()
    {
        $this->validate();

        $product = Product::findOrFail($this->product_id);

        // For "barang keluar", quantity should be negative
        $adjustedQuantity = -abs($this->quantity);

        if ($product->stock < abs($adjustedQuantity)) {
            $this->addError('quantity', 'Stok tidak mencukupi.');
            return;
        }

        StockAdjustment::create([
            'product_id' => $this->product_id,
            'quantity' => $adjustedQuantity,
            'type' => $this->type,
            'notes' => $this->notes,
            'user_id' => Auth::id(),
        ]);

        $product->decrement('stock', abs($adjustedQuantity));

        session()->flash('success', 'Penyesuaian stok berhasil disimpan.');
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['product_id', 'quantity', 'type', 'notes', 'product_name', 'search']);
    }

    public function getTypesProperty()
    {
        return ['damage' => 'Barang Rusak', 'internal_use' => 'Pemakaian Internal', 'repack_out' => 'Repack (Keluar)', 'other' => 'Lainnya'];
    }

    public function render()
    {
        $adjustments = StockAdjustment::with('product', 'user')
            ->latest()
            ->paginate(10);
            
        return view('livewire.stock-adjustments.stock-adjustment-list', [
            'adjustments' => $adjustments,
            'types' => $this->types,
        ]);
    }
}
