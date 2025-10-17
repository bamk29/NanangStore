<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Pesanan Harian</title>
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
        .header p {
            font-size: 8pt;
            margin: 2px 0;
        }
        .order-separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .order-header {
            margin-bottom: 5px;
        }
        .order-header p {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1px;
            text-align: left;
        }
        .product-name {
            width: 60%;
        }
        .quantity {
            width: 15%;
            text-align: right;
        }
        .notes {
            margin-top: 5px;
            font-style: italic;
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
        <p>--------------------------</p>
        <h2>Rekap Pesanan</h2>
        <p>Tanggal: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
    </div>

    @forelse($orders as $order)
        <div class="order-separator"></div>
        <div class="order-header">
            <p><strong>ID: #{{ $order->id }}</strong> | {{ $order->customer->name }}</p>
        </div>

        <table>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="product-name">{{ $item->product->name }}</td>
                    <td class="quantity">x{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($order->notes)
            <div class="notes">
                Catatan: {{ $order->notes }}
            </div>
        @endif
    @empty
        <div class="order-separator"></div>
        <p style="text-align:center;">Tidak ada pesanan untuk tanggal ini.</p>
    @endforelse

    <div class="footer">
        <p>--------------------------</p>
        <p>Terima kasih!</p>
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
