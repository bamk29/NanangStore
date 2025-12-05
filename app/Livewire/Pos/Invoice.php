<?php

namespace App\Livewire\Pos;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Component;

class Invoice extends Component
{
    public $transaction;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction->load('details.product', 'customer', 'user');
    }

    public function newTransaction()
    {
        return redirect()->route('pos.index');
    }

    public function printInvoice(string $headerType)
{
    // Logika perhitungan tetap sama
    $itemSubtotal = $this->transaction->details->sum('subtotal');
    $totalReductionAmountRaw = $this->transaction->total_reduction_amount; // Digunakan untuk Potongan Akhir
    
    // Perhitungan Hutang Lama yang Dibayar (Menggunakan total_reduction_amount)
    $oldDebtPaid = $this->transaction->total_amount - $itemSubtotal + $totalReductionAmountRaw;

    $items = [];
    foreach ($this->transaction->details as $detail) {
        
        // Harga yang benar-benar digunakan saat transaksi
        $priceUsed = $detail->price_applied; 
        $priceLabel = '';
        
        // ASUMSI: Jika harga jual lebih rendah dari harga retail, kita labeli sebagai Grosir
        if ($priceUsed < $detail->price_retail) {
             $priceLabel = ' (Grosir)';
        }
        
        $items[] = [
            'productName' => $detail->product->name . $priceLabel, // Nama + Label Harga (jika ada)
            'quantity' => rtrim(rtrim(number_format($detail->quantity, 2, ',', '.'), '0'), ','),
            
            // Kunci yang dibutuhkan oleh Node.js untuk mencetak harga per item
            'price_applied_formatted' => number_format($priceUsed, 0, ',', '.'),
            
            'subtotal' => number_format($detail->subtotal, 0, ',', '.')
        ];
    }

    $printData = [
        'printType' => 'transaction',
        'headerType' => $headerType,
        'dateTime' => $this->transaction->created_at->format('d/m/y H:i'),
        'cashier' => Str::limit($this->transaction->user->name, 8),
        'invoiceNumber' => $this->transaction->invoice_number,
        'customerName' => optional($this->transaction->customer)->name,
        'items' => $items,
        'itemSubtotal' => number_format($itemSubtotal, 0, ',', '.'),
        
        // Kunci yang dibutuhkan Node.js untuk Bayar Hutang Lama
        'oldDebtPaid' => $oldDebtPaid, 
        'oldDebtPaidFormatted' => number_format($oldDebtPaid, 0, ',', '.'),

        'customerDebt' => optional($this->transaction->customer)->debt ?? 0,
        'customerDebtFormatted' => number_format(optional($this->transaction->customer)->debt ?? 0, 0, ',', '.'),
        
        // Kunci yang dibutuhkan Node.js untuk Potongan Akhir
        'totalDiscountRaw' => $totalReductionAmountRaw, 
        'totalDiscountFormatted' => number_format($totalReductionAmountRaw, 0, ',', '.'),

        'totalAmount' => number_format($this->transaction->total_amount, 0, ',', '.'),
        'paidAmount' => number_format($this->transaction->paid_amount, 0, ',', '.'),
        'changeAmount' => number_format($this->transaction->change_amount, 0, ',', '.'),
    ];

    $printServerUrl = 'http://localhost:8000/print';

    try {
        Http::timeout(5)->post($printServerUrl, $printData);
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Perintah cetak berhasil dikirim!']);
    } catch (\Exception $e) {
        $errorMessage = 'Gagal terhubung ke server printer.';
        if (Str::contains($e->getMessage(), ['timeout', 'refused'])) {
             $errorMessage .= ' Cek PM2 (pm2 status) di STB Anda.';
        }
        $this->dispatch('show-alert', ['type' => 'error', 'message' => $errorMessage]);
    }
}

    public function render()
    {
        return view('livewire.pos.invoice')
            ->layout('components.layouts.app');
    }
}
