<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PurchaseOrderList extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $supplierFilter = '';
    public $search = '';
    public $poToDeleteId;

    public function updating(){
        $this->resetPage();
    }

    public function receiveOrder($orderId)
    {
        DB::transaction(function () use ($orderId) {
            $po = PurchaseOrder::with('items.product')->find($orderId);

            if (!$po || $po->status !== 'ordered') {
                $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Hanya pesanan dengan status "Ordered" yang bisa diterima.']);
                return;
            }

            foreach ($po->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('stock', $item->quantity);
                    $product->cost_price = $item->cost;
                    
                    // Recalculate box_stock
                    if ($product->units_in_box > 0) {
                        $product->refresh();
                        $product->box_stock = floor($product->stock / $product->units_in_box);
                    }

                    $product->save();
                }
            }

            $po->status = 'received';
            $po->received_date = now();
            $po->save();

            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Barang dari PO ' . $po->order_number . ' berhasil diterima dan stok telah ditambahkan.']);
        });
    }

    public function cancelOrder($orderId)
    {
        $po = PurchaseOrder::find($orderId);
        if ($po && in_array($po->status, ['draft', 'ordered'])) {
            $po->status = 'cancelled';
            $po->save();
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Purchase Order berhasil dibatalkan.']);
        } else {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Hanya PO dengan status "Draft" atau "Ordered" yang bisa dibatalkan.']);
        }
    }

    public function confirmDelete($id)
    {
        $this->poToDeleteId = $id;
    }

    public function delete()
    {
        $po = PurchaseOrder::find($this->poToDeleteId);
        if ($po && $po->status === 'draft') {
            $po->delete();
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Purchase Order berhasil dihapus permanen.']);
        } else {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Hanya PO dengan status "Draft" yang bisa dihapus.']);
        }
        $this->poToDeleteId = null;
    }

    public function render()
    {
        $query = PurchaseOrder::with('supplier')
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->supplierFilter, fn($q) => $q->where('supplier_id', $this->supplierFilter))
            ->when($this->search, fn($q) => $q->where('order_number', 'like', '%'.$this->search.'%'))
            ->latest();

        return view('livewire.purchase-orders.purchase-order-list', [
            'purchaseOrders' => $query->paginate(15),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
