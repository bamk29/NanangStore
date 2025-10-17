<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="ISO-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi {{ $transaction->invoice_number }}</title>
    <style>
    @page {
        size: 58mm auto; /* Lebar 58mm, tinggi menyesuaikan konten */
        margin: 0;
    }
    * {
        margin: 0;
        padding: 0;
        font-family: monospace;
        font-size: 12px; /* Ukuran font dasar diubah menjadi 12px */
        line-height: 1.4;
        color: #000;
    }
    body {
        width: 58mm;
        box-sizing: border-box;
    }
    .receipt-container {
        width: 100%;
    }
    .header {
        text-align: center;
        margin-bottom: 2px; /* Margin diperkecil */
    }
    .header .store-name {
        font-size: 14px;
        font-weight: bold;
    }
    .info-section, .items-section, .totals-section, .debt-section, .footer {
        margin-top: 2px; /* Margin diperkecil */
        padding-top: 2px; /* Padding diperkecil */
        border-top: 1px dashed #000;
    }
    .info-line {
        display: flex;
        justify-content: space-between;
    }
    .item {
        margin-bottom: 2px;
    }
    .item .product-name {
        /* Word-break can be added if needed */
    }
    .item .item-details {
        display: flex;
        justify-content: space-between;
    }
    .item .item-details .price-calc {
        padding-left: 10px;
    }
    .total-line {
        display: flex;
        justify-content: space-between;
        margin-top: 2px;
    }
    .total-line.grand-total span {
        font-size: 13px;
        font-weight: bold;
    }
    .debt-section {
        text-align: center;
    }
    .debt-title {
        font-weight: bold;
        margin-bottom: 2px; /* Margin diperkecil */
    }
    .footer {
        text-align: center;
    }
</style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <p class="store-name">NANANG MARKET</p>
            <p>Jl. Raya Jendral Sudirman No. 123</p>
            <p>Telp: 0812-3456-7890</p>
        </div>

        <div class="info-section">
            <div class="info-line">
                <span>{{ $transaction->created_at->format('d/m/y H:i') }}</span>
                <span>Kasir: {{ Str::limit($transaction->user->name, 8) }}</span>
            </div>
            <div>No: {{ $transaction->invoice_number }}</div>
            @if($transaction->customer)
            <div>Plg: {{ $transaction->customer->name }}</div>
            @endif
        </div>

        <div class="items-section">
            @foreach($transaction->details as $detail)
            <div class="item">
                <div class="product-name">{{ $detail->product->name }}</div>
                <div class="item-details">
                    <span class="price-calc">
                        {{ rtrim(rtrim(number_format($detail->quantity, 2, ',', '.'), '0'), ',') }} x {{ number_format($detail->price, 0, ',', '.') }}
                    </span>
                    <span>{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="totals-section">
            @php
                $itemSubtotal = $transaction->details->sum('subtotal');
                $oldDebtPaid = $transaction->total_amount - $itemSubtotal;
            @endphp

            @if($oldDebtPaid > 0)
                <div class="total-line">
                    <span>Tagihan Hari Ini</span>
                    <span>{{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                </div>
                <div class="total-line">
                    <span>Bayar Hutang Lama</span>
                    <span>{{ number_format($oldDebtPaid, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="total-line grand-total">
                <span>TOTAL</span>
                <span>{{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="total-line">
                <span>BAYAR</span>
                <span>{{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="total-line">
                <span>KEMBALI</span>
                <span>{{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($transaction->customer && $transaction->customer->debt > 0)
        <div class="debt-section">
            <div class="debt-title">-- SISA HUTANG --</div>
            <div class="total-line grand-total">
                <span>TOTAL SISA HUTANG</span>
                <span>{{ number_format($transaction->customer->debt, 0, ',', '.') }}</span>
            </div>
        </div>
        @elseif($oldDebtPaid > 0)
        <div class="debt-section">
             <div class="debt-title">-- HUTANG LUNAS --</div>
        </div>
        @endif

        <div class="footer">
            @if($transaction->customer && $transaction->customer->debt > 0)
            <p style="font-style: italic; margin-bottom: 2px; font-size: 8px;">"Menunda membayar utang bagi yang mampu adalah kezaliman."</p>
            @endif
            <p>-- Terima Kasih --</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
        }
    </script>
</body>
</html>
