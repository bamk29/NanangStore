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
    public $type = 'set_stock'; // Default to set_stock
    public $notes;
    public $product_name;
    public $current_stock = 0;

    protected $rules = [
        'product_id' => 'required',
        'quantity' => 'required|integer|min:0',
        'type' => 'required',
        'notes' => 'nullable|string',
    ];

    public function mount()
    {
        $this->type = 'set_stock'; // Set default type
    }

    public function selectProduct($productId, $productName, $currentStock = 0)
    {
        $this->product_id = $productId;
        $this->product_name = $productName;
        $this->current_stock = $currentStock;
        
        // For set_stock type, pre-fill with current stock
        if ($this->type === 'set_stock') {
            $this->quantity = $currentStock;
        }
    }

    public function save()
    {
        $this->validate();

        $product = Product::findOrFail($this->product_id);

        if ($this->type === 'set_stock') {
            // Set Stock: Calculate difference and set to target value
            $difference = $this->quantity - $product->stock;
            
            StockAdjustment::create([
                'product_id' => $this->product_id,
                'quantity' => $difference,
                'type' => $this->type,
                'notes' => $this->notes ?: "Set stok dari {$product->stock} menjadi {$this->quantity}",
                'user_id' => Auth::id(),
            ]);

            $product->update(['stock' => $this->quantity]);
            
            session()->flash('success', "Stok berhasil disesuaikan menjadi {$this->quantity}.");
        } else {
            // Other types: Reduce stock (existing behavior)
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
        }

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['product_id', 'quantity', 'notes', 'product_name', 'current_stock']);
        $this->type = 'set_stock'; // Reset to default
    }

    public function getTypesProperty()
    {
        return [
            'set_stock' => 'Set Stok (Penyesuaian)',
            'damage' => 'Barang Rusak',
            'internal_use' => 'Pemakaian Internal',
            'repack_out' => 'Repack (Keluar)',
            'other' => 'Lainnya'
        ];
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
