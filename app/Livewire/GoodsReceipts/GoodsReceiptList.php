<?php

namespace App\Livewire\GoodsReceipts;

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class GoodsReceiptList extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $receiptToDeleteId;

    public function confirmDelete($id)
    {
        $this->receiptToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $receipt = GoodsReceipt::with('items.product')->find($this->receiptToDeleteId);

        if (!$receipt) {
            session()->flash('error', 'Penerimaan barang tidak ditemukan.');
            return;
        }

        try {
            DB::transaction(function () use ($receipt) {
                // 1. Reverse stock for each item
                foreach ($receipt->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        // Ensure stock doesn't go negative
                        if ($product->stock < $item->quantity) {
                            throw new \Exception("Gagal menghapus: Stok produk {$product->name} tidak akan cukup jika dibatalkan (Sisa: {$product->stock}, Dibatalkan: {$item->quantity}).");
                        }
                        
                        $product->decrement('stock', (float) $item->quantity);
                        
                        // Revert Cost Price if this was the latest receipt
                        // We check if the latest item for this product is the one we are deleting
                        $latestItem = \App\Models\GoodsReceiptItem::where('product_id', $product->id)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if ($latestItem && $latestItem->id == $item->id) {
                            // Find the previous item
                            $previousItem = \App\Models\GoodsReceiptItem::where('product_id', $product->id)
                                ->where('id', '!=', $item->id)
                                ->orderBy('created_at', 'desc')
                                ->first();
                            
                            if ($previousItem) {
                                $product->cost_price = $previousItem->cost;
                            }
                        }

                        if ($product->units_in_box > 1) {
                            $product->box_stock = floor($product->stock / $product->units_in_box);
                            // Also update box cost if cost price changed
                            $product->box_cost = $product->cost_price * $product->units_in_box;
                        }
                        $product->save();
                    }
                }

                // 2. Delete associated financial transaction
                // 2. Delete associated financial transaction
                FinancialTransaction::where('description', 'Penerimaan barang via ' . $receipt->receipt_number)->delete();

                // 3. Delete the receipt itself (items will be cascade deleted)
                $receipt->delete();
            });

            session()->flash('message', 'Penerimaan barang berhasil dihapus dan stok telah dikembalikan.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->showDeleteModal = false;
        $this->render(); // Re-render the component to reflect changes
    }

    public function render()
    {
        $receipts = GoodsReceipt::with(['supplier', 'purchaseOrder', 'user'])
            ->where('receipt_number', 'like', '%' . $this->search . '%')
            ->orWhereHas('supplier', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.goods-receipts.list', [
            'receipts' => $receipts,
        ]);
    }
}
