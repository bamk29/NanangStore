<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;// <-- JANGAN LUPA TAMBAHKAN INI
use Illuminate\Http\Client\ConnectionException;
class PrintController extends Controller
{
    public function printReceipt(Transaction $transaction)
    {
        return $this->sendToPrintServer($transaction, 'store');
    }

    public function printInvoice(Transaction $transaction)
    {
        return $this->sendToPrintServer($transaction, 'invoice');
    }

    private function sendToPrintServer(Transaction $transaction, string $headerType)
    {
        $transaction->loadMissing('details.product', 'customer', 'user');

        // --- PERHITUNGAN LOGIKA HUTANG ---
        $itemSubtotal = $transaction->details->sum('subtotal');
        $oldDebtPaid = $transaction->total_amount - $itemSubtotal;
        // ---------------------------------

        $items = [];
        foreach ($transaction->details as $detail) {
            $items[] = [
                'productName' => $detail->product->name,
                'quantity' => rtrim(rtrim(number_format($detail->quantity, 2, ',', '.'), '0'), ','),
                'price' => number_format($detail->price, 0, ',', '.'),
                'subtotal' => number_format($detail->subtotal, 0, ',', '.')
            ];
        }

        $printData = [
            'printType' => 'transaction',
            'headerType' => $headerType,
            'dateTime' => $transaction->created_at->format('d/m/y H:i'),
            'cashier' => Str::limit($transaction->user->name, 8),
            'invoiceNumber' => $transaction->invoice_number,
            'customerName' => optional($transaction->customer)->name,
            'items' => $items,

            // --- DATA BARU UNTUK LOGIKA HUTANG ---
            'itemSubtotal' => number_format($itemSubtotal, 0, ',', '.'),
            'oldDebtPaid' => $oldDebtPaid, // Nilai numerik untuk logika
            'oldDebtPaidFormatted' => number_format($oldDebtPaid, 0, ',', '.'),
            'customerDebt' => optional($transaction->customer)->debt ?? 0,
            'customerDebtFormatted' => number_format(optional($transaction->customer)->debt ?? 0, 0, ',', '.'),
            // -------------------------------------

            'totalAmount' => number_format($transaction->total_amount, 0, ',', '.'),
            'paidAmount' => number_format($transaction->paid_amount, 0, ',', '.'),
            'changeAmount' => number_format($transaction->change_amount, 0, ',', '.'),
        ];

        $printServerUrl = 'http://192.168.18.101:8000/print'; // <-- IP STB2 ANDA

        try {
            Http::timeout(5)->post($printServerUrl, $printData);
            return back()->with('success', 'Perintah cetak berhasil dikirim!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal terhubung ke server printer.');
        }
    }

}
