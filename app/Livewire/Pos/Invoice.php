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
    // Logika dipindahkan dari PrintController.php
    $itemSubtotal = $this->transaction->details->sum('subtotal');
    
    // Perhitungan Total Hemat
    $totalReductionAmountRaw = $this->transaction->total_reduction_amount;
    
    // Perhitungan Hutang Lama yang Dibayar
    // CATATAN: Pastikan total_amount - itemSubtotal + total_reduction_amount merepresentasikan HUTANG LAMA DIBAYAR.
    $oldDebtPaid = $this->transaction->total_amount - $itemSubtotal + $totalReductionAmountRaw;

    $items = [];
    foreach ($this->transaction->details as $detail) {
        // Logika untuk menentukan apakah ada diskon
        $hasDiscount = $detail->price_retail != $detail->price_applied; // ASUMSI: Model memiliki field price_retail & price_applied

        $items[] = [
            'productName' => $detail->product->name,
            'quantity' => rtrim(rtrim(number_format($detail->quantity, 2, ',', '.'), '0'), ','),
            
            // FIX 1: MENAMBAHKAN KUNCI YANG DIBUTUHKAN NODE.JS UNTUK DISKON/ARROW
            'has_discount' => $hasDiscount,
            'price_retail_formatted' => number_format($detail->price_retail, 0, ',', '.'),
            'price_applied_formatted' => number_format($detail->price_applied, 0, ',', '.'),
            
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
        
        // FIX 2: MENGGANTI includedOldDebt menjadi oldDebtPaid
        'oldDebtPaid' => $oldDebtPaid,
        'oldDebtPaidFormatted' => number_format($oldDebtPaid, 0, ',', '.'),

        'customerDebt' => optional($this->transaction->customer)->debt ?? 0,
        'customerDebtFormatted' => number_format(optional($this->transaction->customer)->debt ?? 0, 0, ',', '.'),
        
        // FIX 3: MENGGANTI totalReductionAmount menjadi totalDiscountRaw (Untuk blok "ANDA HEMAT")
        'totalDiscountRaw' => $totalReductionAmountRaw, 
        'totalDiscountFormatted' => number_format($totalReductionAmountRaw, 0, ',', '.'),

        // NOTE: Kunci 'reductionNotes' tidak digunakan di server.js
        // 'reductionNotes' => $this->transaction->reduction_notes, 

        'totalAmount' => number_format($this->transaction->total_amount, 0, ',', '.'),
        'paidAmount' => number_format($this->transaction->paid_amount, 0, ',', '.'),
        'changeAmount' => number_format($this->transaction->change_amount, 0, ',', '.'),
    ];

    $printServerUrl = 'http://localhost:8000/print';

    try {
        Http::timeout(5)->post($printServerUrl, $printData);
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Perintah cetak berhasil dikirim!']);
    } catch (\Exception $e) {
        // Tampilkan error yang lebih spesifik jika terjadi kegagalan koneksi
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
