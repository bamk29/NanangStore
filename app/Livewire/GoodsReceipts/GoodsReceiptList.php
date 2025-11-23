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
                            throw new \Exception("Gagal menghapus: Stok produk {$product->name} tidak akan cukup jika dibatalkan.");
                        }
                        $product->decrement('stock', $item->quantity);
                        if ($product->units_in_box > 1) {
                            $product->box_stock = floor($product->stock / $product->units_in_box);
                        }
                        $product->save();
                    }
                }

                // 2. Delete associated financial transaction
                FinancialTransaction::where('goods_receipt_id', $receipt->id)->delete();

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
