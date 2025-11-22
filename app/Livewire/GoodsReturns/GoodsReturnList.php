<?php

namespace App\Livewire\GoodsReturns;

use App\Models\GoodsReturn;
use Livewire\Component;
use Livewire\WithPagination;

class GoodsReturnList extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $returns = GoodsReturn::with(['supplier', 'user'])
            ->where('return_number', 'like', '%' . $this->search . '%')
            ->orWhereHas('supplier', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.goods-returns.list', [
            'returns' => $returns,
        ]);
    }
}
