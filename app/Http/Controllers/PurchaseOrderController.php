<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product.baseUnit', 'items.product.boxUnit']);
        
        $format = request('format', 'a4'); // 'a4', 'thermal'
        $withPrice = request('with_price', 1); // 1 or 0

        return view('purchase-orders.print', compact('purchaseOrder', 'format', 'withPrice'));
    }
}
