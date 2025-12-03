<?php

namespace App\Livewire\GoodsReceipts;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoodsReceiptForm extends Component
{
    // Form state
    public $receiptId;
    public $receipt_number;
    public $supplier_id;
    public $purchase_order_id;
    public $receipt_date;
    public $notes;
    public $total_amount = 0;
    public $is_finalized = false;

    // UI state
    public $items = [];
    public $suppliers = [];
    public $purchaseOrders = [];

    protected function rules()
    {
        return [
            'receipt_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.cost' => 'required|numeric|min:0',
            'items.*.retail_price' => 'required|numeric|min:0',
            'items.*.wholesale_price' => 'nullable|numeric|min:0',
            'items.*.wholesale_min_qty' => 'nullable|numeric|min:0',
        ];
    }

    public function mount($receiptId = null)
    {
        $this->suppliers = Supplier::orderBy('name')->get();
        $this->purchaseOrders = PurchaseOrder::whereIn('status', ['ordered', 'partially_received'])->orderBy('order_number')->get();

        if ($receiptId) {
            $receipt = GoodsReceipt::with('items.product')->findOrFail($receiptId);
            $this->receiptId = $receipt->id;
            $this->receipt_number = $receipt->receipt_number;
            $this->supplier_id = $receipt->supplier_id;
            $this->purchase_order_id = $receipt->purchase_order_id;
            $this->receipt_date = $receipt->receipt_date;
            $this->notes = $receipt->notes;
            $this->total_amount = $receipt->total_amount;
            $this->is_finalized = true; // Existing records are considered finalized

            foreach ($receipt->items as $item) {
                $this->items[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity_received' => $item->quantity_received,
                    'cost' => $item->cost,
                    'retail_price' => $item->product->retail_price,
                    'wholesale_price' => $item->product->wholesale_price,
                    'wholesale_min_qty' => $item->product->wholesale_min_qty,
                    'total_cost' => $item->total_cost,
                ];
            }
        } else {
            $this->receipt_date = now()->format('Y-m-d');
            
            // Check for po_id query parameter
            if (request()->has('po_id')) {
                $poId = request()->query('po_id');
                $this->purchase_order_id = $poId;
                $this->updatedPurchaseOrderId($poId);
            }
        }
    }

    public function updatedPurchaseOrderId($poId)
    {
        if (!$poId) {
            $this->supplier_id = null;
            $this->dispatch('items-loaded', []);
            return;
        }

        $po = PurchaseOrder::with('items.product')->findOrFail($poId);
        $this->supplier_id = $po->supplier_id;
        $items = [];

        foreach ($po->items as $poItem) {
            $remaining_qty = $poItem->quantity - $poItem->received_quantity;
            if ($remaining_qty > 0) {
                $items[] = [
                    'product_id' => $poItem->product_id,
                    'product_name' => $poItem->product->name,
                    'quantity_received' => $remaining_qty,
                    'cost' => $poItem->cost,
                    'retail_price' => $poItem->product->retail_price,
                    'wholesale_price' => $poItem->product->wholesale_price,
                    'wholesale_min_qty' => $poItem->product->wholesale_min_qty,
                    'total_cost' => $remaining_qty * $poItem->cost,
                    'po_item_id' => $poItem->id,
                ];
            }
        }
        $this->dispatch('items-loaded', $items);
    }

    public function saveReceipt()
    {
        $this->validate();

        DB::transaction(function () {
            $receipt = new GoodsReceipt();
            $receipt->fill($this->only(['supplier_id', 'purchase_order_id', 'receipt_date', 'notes']));
            $receipt->receipt_number = 'GR-' . now()->format('YmdHis');
            $receipt->user_id = Auth::id();
            $receipt->total_amount = $this->total_amount;
            $receipt->save();

            $expenseByCategory = [];

            foreach ($this->items as $itemData) {
                // 1. Create Receipt Item
                $receipt->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity_received' => $itemData['quantity_received'],
                    'cost' => $itemData['cost'],
                    'total_cost' => $itemData['total_cost'],
                ]);

                // 2. Update Product Stock & Cost
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->increment('stock', $itemData['quantity_received']);
                    $product->cost_price = $itemData['cost'];
                    $product->retail_price = $itemData['retail_price'];
                    $product->wholesale_price = $itemData['wholesale_price'] ?? 0;
                    $product->wholesale_min_qty = $itemData['wholesale_min_qty'] ?? 0;
                    
                    if ($product->units_in_box > 1) {
                        $product->box_cost = $itemData['cost'] * $product->units_in_box;
                        $product->box_stock = floor($product->stock / $product->units_in_box);
                    }
                    $product->save();

                    // Prepare for financial logging
                    $businessUnit = ($product->category_id == 1) ? 'giling_bakso' : 'nanang_store';
                    if (!isset($expenseByCategory[$businessUnit])) {
                        $expenseByCategory[$businessUnit] = 0;
                    }
                    $expenseByCategory[$businessUnit] += $itemData['total_cost'];
                }

                // 3. Update PO Item received quantity if applicable
                if ($this->purchase_order_id && isset($itemData['po_item_id'])) {
                    $poItem = PurchaseOrderItem::find($itemData['po_item_id']);
                    if ($poItem) {
                        $poItem->increment('received_quantity', $itemData['quantity_received']);
                    }
                }
            }

            // 4. Update PO status if applicable
            if ($this->purchase_order_id) {
                $po = PurchaseOrder::with('items')->find($this->purchase_order_id);
                $all_items_fully_received = $po->items->every(fn($item) => $item->received_quantity >= $item->quantity);
                
                $po->status = $all_items_fully_received ? 'received' : 'partially_received';
                $po->save();
            }

            // 5. Log Financial Transaction
            foreach ($expenseByCategory as $unit => $amount) {
                if ($amount > 0) {
                    \App\Models\FinancialTransaction::create([
                        'business_unit' => $unit,
                        'type' => 'expense',
                        'category' => 'pembelian_stok',
                        'amount' => $amount,
                        'description' => 'Penerimaan barang via ' . $receipt->receipt_number,
                        // 'goods_receipt_id' => $receipt->id, // Removed as column doesn't exist
                        'user_id' => auth()->id(),
                        'date' => $this->receipt_date,
                    ]);
                }
            }
        });

        session()->flash('message', 'Penerimaan barang berhasil disimpan dan stok telah diperbarui.');
        return redirect()->route('goods-receipts.index');
    }

    public function render()
    {
        return view('livewire.goods-receipts.form');
    }
}
