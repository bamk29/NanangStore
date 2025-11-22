<?php

namespace App\Livewire\GoodsReturns;

use App\Models\GoodsReturn;
use App\Models\Supplier;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoodsReturnForm extends Component
{
    // Form state
    public $returnId;
    public $return_number;
    public $supplier_id;
    public $return_date;
    public $notes;
    public $total_amount = 0;
    public $is_finalized = false;

    // UI state
    public $items = [];
    public $suppliers = [];
    public $product_search = '';
    public $search_results = [];

    protected function rules()
    {
        return [
            'return_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.cost' => 'required|numeric|min:0',
        ];
    }

    public function mount($returnId = null)
    {
        $this->suppliers = Supplier::orderBy('name')->get();

        if ($returnId) {
            $return = GoodsReturn::with('items.product')->findOrFail($returnId);
            $this->returnId = $return->id;
            $this->return_number = $return->return_number;
            $this->supplier_id = $return->supplier_id;
            $this->return_date = $return->return_date;
            $this->notes = $return->notes;
            $this->total_amount = $return->total_amount;
            $this->is_finalized = true; // Existing records are considered finalized

            foreach ($return->items as $item) {
                $this->items[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'cost' => $item->cost,
                    'total_cost' => $item->total_cost,
                ];
            }
        } else {
            $this->return_date = now()->format('Y-m-d');
        }
    }

    public function updatedProductSearch()
    {
        if (strlen($this->product_search) >= 2) {
            $this->search_results = Product::where('name', 'like', '%' . $this->product_search . '%')
                ->orWhere('code', 'like', '%' . $this->product_search . '%')
                ->take(5)
                ->get();
        } else {
            $this->search_results = [];
        }
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Check if product is already in the list
        foreach ($this->items as $item) {
            if ($item['product_id'] == $productId) {
                $this->product_search = '';
                $this->search_results = [];
                return; // Don't add duplicates
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'cost' => $product->cost_price, // Default to last cost price
            'total_cost' => $product->cost_price,
        ];

        $this->product_search = '';
        $this->search_results = [];
        $this->calculateGrandTotal();
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if (in_array($field, ['quantity', 'cost'])) {
            $quantity = (float)($this->items[$index]['quantity'] ?? 0);
            $cost = (float)($this->items[$index]['cost'] ?? 0);
            $this->items[$index]['total_cost'] = $quantity * $cost;
            $this->calculateGrandTotal();
        }
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateGrandTotal();
    }

    public function calculateGrandTotal()
    {
        $this->total_amount = array_sum(array_column($this->items, 'total_cost'));
    }

    public function saveReturn()
    {
        $this->validate();

        DB::transaction(function () {
            $return = new GoodsReturn();
            $return->fill($this->only(['supplier_id', 'return_date', 'notes']));
            $return->return_number = 'GRT-' . now()->format('YmdHis');
            $return->user_id = Auth::id();
            $return->total_amount = $this->total_amount;
            $return->status = 'completed'; // Returns are completed upon creation
            $return->save();

            $creditByCategory = [];

            foreach ($this->items as $itemData) {
                // 1. Create Return Item
                $return->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'cost' => $itemData['cost'],
                    'total_cost' => $itemData['total_cost'],
                ]);

                // 2. Decrement Product Stock
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    // Ensure stock doesn't go negative
                    if ($product->stock < $itemData['quantity']) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi untuk diretur.");
                    }
                    $product->decrement('stock', $itemData['quantity']);
                    if ($product->units_in_box > 1) {
                        $product->box_stock = floor($product->stock / $product->units_in_box);
                    }
                    $product->save();

                    // Prepare for financial logging (as a credit/refund)
                    $businessUnit = ($product->category_id == 1) ? 'giling_bakso' : 'nanang_store';
                    if (!isset($creditByCategory[$businessUnit])) {
                        $creditByCategory[$businessUnit] = 0;
                    }
                    $creditByCategory[$businessUnit] += $itemData['total_cost'];
                }
            }

            // 3. Log Financial Transaction (as a credit/refund)
            foreach ($creditByCategory as $unit => $amount) {
                if ($amount > 0) {
                    \App\Models\FinancialTransaction::create([
                        'business_unit' => $unit,
                        'type' => 'income', // Treated as income (credit) from supplier perspective
                        'category' => 'retur_barang',
                        'amount' => $amount,
                        'description' => 'Retur barang via ' . $return->return_number,
                        'goods_return_id' => $return->id,
                        'user_id' => auth()->id(),
                        'date' => $this->return_date,
                    ]);
                }
            }
        });

        session()->flash('message', 'Retur barang berhasil disimpan dan stok telah diperbarui.');
        return redirect()->route('goods-returns.index');
    }

    public function render()
    {
        return view('livewire.goods-returns.form');
    }
}
