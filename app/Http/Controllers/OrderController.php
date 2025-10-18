<?php
namespace App\Http\Controllers;

use App\Models\PhoneOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function printToday(Request $request)
    {
        // ... (Logika query database Anda untuk mendapatkan $orders) ...
        $orders = PhoneOrder::with(['customer', 'items.product'])->whereDate('created_at', now())->get(); // Contoh query sederhana
        $date = now()->format('d M Y');

        $formattedOrders = [];
        foreach ($orders as $order) {
            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    'productName' => $item->product->name,
                    'quantity'    => rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.'),
                ];
            }
            $formattedOrders[] = ['id' => $order->id, 'customerName' => $order->customer->name, 'items' => $items, 'notes' => $order->notes];
        }

        $printData = [
            'printType' => 'dailyRecap',
            'date'      => $date,
            'orders'    => $formattedOrders
        ];

        try {
            Http::timeout(10)->post('http://192.168.18.101:8000/print', $printData);
            return back()->with('success', 'Rekap harian berhasil dikirim!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal terhubung ke server printer.');
        }
    }

    public function printOrder(PhoneOrder $order)
    {
        $order->load('customer', 'items.product');
        $items = [];
        foreach ($order->items as $item) {
            $items[] = ['productName' => $item->product->name, 'quantity' => rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.')];
        }

        $printData = [
            'printType' => 'singleOrder',
            'order'     => [
                'id'           => $order->id,
                'customerName' => $order->customer->name,
                'dateTime'     => $order->created_at->format('d M Y H:i'),
                'items'        => $items,
                'notes'        => $order->notes
            ]
        ];

        try {
            Http::timeout(5)->post('http://192.168.18.101:8000/print', $printData);
            return back()->with('success', 'Pesanan #' . $order->id . ' berhasil dikirim!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal terhubung ke server printer.');
        }
    }
}
