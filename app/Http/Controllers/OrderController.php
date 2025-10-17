<?php

namespace App\Http\Controllers;

use App\Models\PhoneOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function printToday(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $ordersQuery = PhoneOrder::with(['customer', 'items.product']);

        if ($request->has('ids')) {
            $ids = $request->input('ids', []);
            $ordersQuery->whereIn('id', $ids);
        } else {
            // Fallback to filter logic
            $search = $request->input('search', '');
            $status = $request->input('status', '');
            
            $ordersQuery->whereDate('created_at', $date)
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($search, function ($query) use ($search) {
                    $query->where('id', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        });
                });
        }

        $orders = $ordersQuery->latest()->get();

        return view('orders.print-today', compact('orders', 'date'));
    }
}
