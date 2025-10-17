<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Models\PhoneOrder;
use Illuminate\Http\Request;

class PhoneOrderController extends Controller
{
    public function print(PhoneOrder $order)
    {
        $order->load('customer', 'items.product');
        return view('orders.print', compact('order'));
    }
}