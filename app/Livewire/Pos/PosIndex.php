<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class PosIndex extends Component
{
    // Properties like $search, $categoryFilter, and pagination are no longer needed here
    // as they will be handled by Alpine.js.

    public function render()
    {
        // The component now only needs to render the view.
        // Product and category data will be fetched by Alpine.js via API.
        return view('livewire.pos.pos-index');
    }
}

