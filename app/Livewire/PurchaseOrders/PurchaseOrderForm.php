<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderForm extends Component
{
    public $orderId;
    public $order_number;
    public $supplier_id;
    public $status;
    public $total_amount = 0;
    public $order_date;
    public $notes;

    public $items = [];
    public $suppliers = [];

    public $po;

    protected function rules()
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'status' => 'required|in:draft,ordered,received,cancelled,partially_received',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
        ];
    }

    public function mount($orderId = null)
    {
        if ($orderId) {
            $po = PurchaseOrder::with('items.product')->findOrFail($orderId);
            $this->orderId = $po->id;
            $this->order_number = $po->order_number;
            $this->supplier_id = $po->supplier_id;
            $this->status = $po->status;
            $this->total_amount = $po->total_amount;
            $this->order_date = $po->order_date;
            $this->notes = $po->notes;

            foreach ($po->items as $item) {
                if ($item->product) {
                    $product = $item->product;
                    $purchaseByBox = false;
                    $quantityInForm = $item->quantity;

                    // Logic to determine if it was a box purchase
                    if ($product->units_in_box > 1 && ($item->quantity % $product->units_in_box == 0)) {
                        $purchaseByBox = true;
                        $quantityInForm = $item->quantity / $product->units_in_box;
                    }

                    $this->items[] = [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $product->name,
                        'purchase_by_box' => $purchaseByBox,
                        'quantity' => $quantityInForm,
                        'items_per_box' => $product->units_in_box ?? 1,
                        
                        // Refined Cost Logic: Prioritize historical cost from the PO item itself.
                        'cost' => $item->cost, // The historical unit cost is the source of truth.
                        'unit_cost' => $item->cost, // The unit cost for this line item IS the historical cost.
                        'box_cost' => ($product->units_in_box > 1) ? ($item->cost * $product->units_in_box) : $item->cost, // Calculate historical box cost.

                        'total_cost' => $item->total_cost,
                        'received_quantity' => $item->received_quantity ?? 0,
                        'quantity_to_receive' => 0,
                    ];
                }
            }
        } else {
            $this->order_date = now()->format('Y-m-d');
            $this->status = 'draft';
        }

        $this->suppliers = Supplier::orderBy('name')->get();
    }

    // public function mount($orderId = null)
    // {
    //     $product = Product::find($productId);
    //     if (!$product) return;

    //     $itemsPerBox = $product->units_in_box > 0 ? $product->units_in_box : 1;
    //     $unitCost = $product->unit_cost ?? 0;
    //     $boxCost = $product->box_cost ?? 0;

    //     $finalUnitCost = 0;
    //     $finalBoxCost = 0;

    //     // Prioritize box_cost as the source of truth
    //     if ($boxCost > 0) {
    //         $finalBoxCost = $boxCost;
    //         $finalUnitCost = round($boxCost / $itemsPerBox);
    //     }
    //     // Fallback to unit_cost if box_cost is not available
    //     else if ($unitCost > 0) {
    //         $finalUnitCost = $unitCost;
    //         $finalBoxCost = $unitCost * $itemsPerBox;
    //     }

    //     $this->items[] = [
    //         'id' => null,
    //         'product_id' => $product->id,
    //         'product_name' => $product->name,
    //         'purchase_by_box' => true, // Default to purchasing by box
    //         'quantity' => 1,
    //         'items_per_box' => $itemsPerBox,
    //         'box_cost' => $finalBoxCost,
    //         'cost' => $finalUnitCost, // Represents the cost per single unit
    //         'unit_cost' => $finalUnitCost,
    //         'total_cost' => $finalBoxCost, // Initial total is for 1 box
    //         'received_quantity' => 0,
    //         'quantity_to_receive' => 0,
    //     ];

    //     $this->product_search = '';
    //     $this->search_results = [];
    //     $this->calculateGrandTotal();
    // }

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

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $po = PurchaseOrder::findOrNew($this->orderId);
            $po->fill($this->only(['supplier_id', 'status', 'order_date', 'notes']));
            $po->user_id = Auth::id();
            $po->total_amount = $this->total_amount;

            if (!$po->exists) {
                $po->order_number = 'PO-' . now()->format('YmdHis');
            }
            $po->save();

            $currentItemIds = [];
            foreach ($this->items as $itemData) {
                $totalUnits = $itemData['purchase_by_box']
                    ? $itemData['quantity'] * $itemData['items_per_box']
                    : $itemData['quantity'];

                $item = $po->items()->updateOrCreate(
                    ['id' => $itemData['id']],
                    [
                        'product_id' => $itemData['product_id'],
                        'quantity' => $totalUnits,
                        'cost' => $itemData['cost'],
                        'total_cost' => $itemData['total_cost'],
                    ]
                );
                $currentItemIds[] = $item->id;
            }
            $po->items()->whereNotIn('id', $currentItemIds)->delete();
        });

        session()->flash('message', 'Purchase Order berhasil disimpan.');
        return redirect()->route('purchase-orders.index');
    }

    public function receiveStock()
    {
        DB::transaction(function () {
            $po = PurchaseOrder::findOrFail($this->orderId);
            $all_items_fully_received = true;

            // 1. Update stock and costs for received items
            foreach ($this->items as $itemData) {
                $qtyToReceive = (int)($itemData['quantity_to_receive'] ?? 0);

                if ($qtyToReceive > 0) {
                    $item = PurchaseOrderItem::find($itemData['id']);
                    if ($item) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('stock', $qtyToReceive);
                            $product->cost_price = $item->cost;
                            $product->unit_cost = $itemData['unit_cost'];
                            $product->box_cost = $itemData['box_cost'];
                            $product->units_in_box = $itemData['items_per_box'];

                            if ($product->units_in_box > 0) {
                                $product->refresh(); // Refresh to get latest stock
                                $product->box_stock = floor($product->stock / $product->units_in_box);
                            }
                            $product->save();

                            $item->increment('received_quantity', $qtyToReceive);
                        }
                    }
                }
            }

            // 2. Check if all items are fully received and update PO status
            foreach ($po->fresh()->items as $item) {
                if ($item->received_quantity < $item->quantity) {
                    $all_items_fully_received = false;
                    break;
                }
            }

            if ($all_items_fully_received) {
                $po->status = 'received';
            }
            $po->received_date = now();
            $po->save();

            // 3. Log the financial transaction
            $expenseByCategory = [];
            foreach ($this->items as $itemData) {
                $qtyToReceive = (int)($itemData['quantity_to_receive'] ?? 0);
                if ($qtyToReceive > 0) {
                    $product = Product::find($itemData['product_id']);
                    if ($product) {
                        $businessUnit = ($product->category_id == 1) ? 'giling_bakso' : 'nanang_store';
                        if (!isset($expenseByCategory[$businessUnit])) {
                            $expenseByCategory[$businessUnit] = 0;
                        }
                        $expenseByCategory[$businessUnit] += $itemData['cost'] * $qtyToReceive;
                    }
                }
            }

            foreach ($expenseByCategory as $unit => $amount) {
                if ($amount > 0) {
                    \App\Models\FinancialTransaction::create([
                        'business_unit' => $unit,
                        'type' => 'expense',
                        'category' => 'pembelian_stok',
                        'amount' => $amount,
                        'description' => 'Pembelian stok dari PO #' . $po->order_number,
                        'purchase_order_id' => $po->id,
                        'user_id' => auth()->id(),
                        'date' => now()->toDateString(),
                    ]);
                }
            }
        });

        session()->flash('message', 'Stok dan harga modal berhasil diperbarui.');
        return redirect()->route('purchase-orders.edit', $this->orderId);
    }

    public function render()
    {
        return view('livewire.purchase-orders.purchase-order-form');
    }
}
