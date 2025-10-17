<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Pesanan #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm;
            font-size: 8pt;
            margin: 0;
            padding: 3mm;
        }
        .header, .footer {
            text-align: center;
        }
        .header h1 {
            font-size: 10pt;
            margin: 0;
        }
        .header p, .info p {
            font-size: 8pt;
            margin: 2px 0;
        }
        .info {
            margin-top: 10px;
            margin-bottom: 10px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
        }
        .items-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .items-table .item {
            display: flex;
            justify-content: space-between;
        }
        .notes {
            margin-top: 5px;
            font-style: italic;
            font-size: 7pt;
        }
        @media print {
            @page { margin: 0; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nanang's Store</h1>
        <p>Jl. Raya No. 123, Kota</p>
        <p>Telp: 081234567890</p>
    </div>

    <div class="info">
        <p>No: #{{ $order->id }} | {{ $order->created_at->format('d/m/y H:i') }}</p>
        <p>Pelanggan: {{ $order->customer->name }}</p>
    </div>

    <div class="items-table">
        @foreach($order->items as $item)
        <div class="item">
            <span>{{ $item->product->name }}</span>
            <span>x{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</span>
        </div>
        @endforeach
    </div>

    @if($order->notes)
        <div class="notes">
            <strong>Catatan:</strong> {{ $order->notes }}
        </div>
    @endif

    <div class="footer">
        <p>--------------------------</p>
        <p>Terima kasih atas pesanan Anda!</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
            // Fallback for browsers that might not trigger onafterprint reliably
            setTimeout(function() {
                window.close();
            }, 500);
        }
    </script>
</body>
</html>
