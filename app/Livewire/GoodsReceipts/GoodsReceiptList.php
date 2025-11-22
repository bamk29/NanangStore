<?php

namespace App\Livewire\GoodsReceipts;

use App\Models\GoodsReceipt;
use Livewire\Component;
use Livewire\WithPagination;

class GoodsReceiptList extends Component
{
    use WithPagination;

    public $search = '';

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
