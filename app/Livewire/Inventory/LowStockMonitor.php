<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class LowStockMonitor extends Component
{
    use WithPagination;

    public $selectedProducts = [];
    public $selectAll = false;
    
    // Thresholds
    const THRESHOLD_WARNING = 15;
    const THRESHOLD_URGENT = 10;
    const THRESHOLD_DANGER = 5;

    public function mount()
    {
        // Auto-select danger items by default? Let's just user decide.
    }

    public function getStockStatus($stock)
    {
        if ($stock <= self::THRESHOLD_DANGER) return 'danger';
        if ($stock <= self::THRESHOLD_URGENT) return 'urgent';
        if ($stock <= self::THRESHOLD_WARNING) return 'warning';
        return 'normal';
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProducts = $this->getLowStockQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function getLowStockQuery()
    {
        return Product::with('supplier')
            ->where('stock', '<=', self::THRESHOLD_WARNING)
            ->orderBy('stock', 'asc');
    }

    public function generatePurchaseOrders()
    {
        if (empty($this->selectedProducts)) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Pilih produk terlebih dahulu.']);
            return;
        }

        $products = Product::whereIn('id', $this->selectedProducts)->get();
        
        // Group by Supplier
        $grouped = $products->groupBy('supplier_id');
        $poCount = 0;

        try {
            DB::beginTransaction();

            $defaultedCount = 0;
            
            foreach ($grouped as $supplierId => $items) {
                // Handle null/empty supplierId (products without supplier)
                // Use default supplier ID 1 (or any existing supplier) as placeholder
                $finalSupplierId = $supplierId;
                if (empty($finalSupplierId)) {
                    $finalSupplierId = 1; // Default to ID 1
                    $defaultedCount += count($items);
                }
                
                // Create PO
                $po = PurchaseOrder::create([
                    'order_number' => 'PO-' . date('YmdHis') . '-' . rand(100, 999),
                    'supplier_id' => $finalSupplierId, 
                    'user_id' => Auth::id(),
                    'status' => 'draft',
                    'order_date' => now(),
                    'total_amount' => 0, // Will recalculate
                ]);

                $totalAmount = 0;

                foreach ($items as $item) {
                    $qtyToOrder = $item->wholesale_min_qty > 0 ? $item->wholesale_min_qty : 10; // Default order qty
                    
                    $cost = 0;

                    // 1. Prioritas: Cost Price (Harga Modal Satuan)
                    if ($item->cost_price > 0) {
                        $cost = $item->cost_price;
                    } 
                    // 2. Prioritas: Hitung dari Box Cost
                    else {
                        $boxCost = $item->box_cost;
                        
                        // LOGIC BARU: Jika box_cost 0, hitung dari (isi per box * cost_price)
                        // Note: Ini mungkin jarang terpakai jika langkah 1 sudah dapat, tapi sesuai request untuk backup/validasi.
                        if ($boxCost <= 0 && $item->units_in_box > 0 && $item->cost_price > 0) {
                             $boxCost = $item->units_in_box * $item->cost_price;
                        }

                        if ($boxCost > 0 && $item->units_in_box > 0) {
                            $cost = $boxCost / $item->units_in_box;
                        }
                    }

                    // 3. Fallback: Unit Cost lama
                    if ($cost <= 0) {
                        $cost = $item->unit_cost > 0 ? $item->unit_cost : 0;
                    } 

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $item->id,
                        'quantity' => $qtyToOrder,
                        'cost' => $cost,
                        'total_cost' => $qtyToOrder * $cost,
                        'unit_type' => 'unit', // Default to unit for auto-generated
                        'items_per_box' => $item->units_in_box > 0 ? $item->units_in_box : 1,
                        'box_cost' => $item->box_cost > 0 ? $item->box_cost : ($cost * ($item->units_in_box > 0 ? $item->units_in_box : 1)),
                    ]);

                    $totalAmount += ($qtyToOrder * $cost);
                }

                $po->update(['total_amount' => $totalAmount]);
                $poCount++;
            }

            DB::commit();
            
            $this->selectedProducts = [];
            $this->selectAll = false;
            
            $msg = "Berhasil membuat $poCount Draft Purchase Order!";
            if ($defaultedCount > 0) {
                $msg .= " ($defaultedCount produk tanpa supplier dialihkan ke Supplier Default (ID:1). Silakan edit di menu PO)";
            }

            $this->dispatch('show-alert', [
                'type' => 'success', 
                'message' => $msg
            ]);

            // Redirect to PO List?
            // return redirect()->route('purchase-orders.index');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Gagal membuat PO: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.inventory.low-stock-monitor', [
            'products' => $this->getLowStockQuery()->paginate(20)
        ]);
    }
}
