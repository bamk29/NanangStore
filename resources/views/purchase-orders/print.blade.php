<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order #{{ $purchaseOrder->order_number }}</title>
    <style>
        @if($format === 'thermal')
            @page {
                size: 58mm auto; /* or 80mm depending on valid CSS support, usually controlled by printer driver */
                margin: 0;
            }
            body {
                font-family: 'Courier New', monospace; /* Thermal standard */
                font-size: 10px;
                width: 58mm; /* Force width */
                margin: 0;
                padding: 5px;
            }
            .header h1 { font-size: 12px; }
            .header p { font-size: 9px; }
            .dashed-line { border-bottom: 1px dashed #000; margin: 5px 0; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
        @else
            @page {
                size: A4;
                margin: 2cm;
            }
            body {
                font-family: 'Arial', sans-serif;
                font-size: 13px;
                color: #000;
                line-height: 1.3;
                margin: 0;
                padding: 0;
            }
        @endif

        /* Common Utils */
        .text-bold { font-weight: bold; }
        @media print {
            .no-print, .no-print * { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
        }
        .btn-print {
            background-color: #007bff; color: white; border: none; padding: 10px 20px;
            cursor: pointer; border-radius: 5px; font-size: 14px; margin: 20px; display: block;
        }

        /* Legacy A4 Styles continued... */
        .container { width: 100%; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .header h1 { margin: 0; text-transform: uppercase; }
        @if($format === 'a4')
            .header h1 { font-size: 20px; }
        @endif
        .header p { margin: 5px 0 0; color: #444; }

        .layout-table { width: 100%; border: none; margin-bottom: 20px; }
        .layout-table td { border: none; vertical-align: top; padding: 0; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        .data-table th { background-color: #eee; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        
        .signature-table { width: 100%; margin-top: 40px; border: none; }
        .signature-table td { border: none; text-align: center; vertical-align: top; padding: 0 10px; width: 33%; }
        .signature-line { border-bottom: 1px solid #000; margin-top: 60px; margin-bottom: 5px; width: 80%; margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">Cetak Dokumen</button>

@if($format === 'thermal')
    <!-- THERMAL LAYOUT -->
    <div class="header" style="border-bottom: 1px dashed black;">
        <h1>NANANG STORE</h1>
        <p>Purchase Order</p>
        <p>{{ $purchaseOrder->order_number }}</p>
    </div>
    
    <div style="margin-bottom: 5px;">
        Date: {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') }}<br>
        To: {{ substr($purchaseOrder->supplier->name, 0, 15) }}
    </div>
    <div class="dashed-line"></div>

    @foreach($purchaseOrder->items as $item)
        @php
            $unitType = $item->unit_type ?? 'unit';
            $itemsPerBox = $item->items_per_box > 0 ? $item->items_per_box : 1;
            
            // Dynamic Unit Names
            $baseUnitName = $item->product->baseUnit->name ?? 'Pcs';
            $boxUnitName = $item->product->boxUnit->name ?? 'Box';
            
            if ($unitType === 'box') {
                $qty = $item->quantity / $itemsPerBox;
                $unit = $boxUnitName;
                $price = $item->box_cost > 0 ? $item->box_cost : ($item->cost * $itemsPerBox);
            } else {
                $qty = $item->quantity;
                $unit = $baseUnitName;
                $price = $item->cost;
            }
        @endphp
        <div style="margin-bottom: 2px;">
            {{ $item->product->name }}<br>
            {{ number_format($qty, 0, ',', '.') }} {{ $unit }} 
            @if($withPrice)
                x {{ number_format($price, 0, ',', '.') }}
                <span style="float: right;">{{ number_format($item->total_cost, 0, ',', '.') }}</span>
            @endif
        </div>
    @endforeach

    <div class="dashed-line"></div>

    @if($withPrice)
    <div class="text-right text-bold">
        TOTAL: Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
    </div>
    @endif
    
    <div style="margin-top: 10px; text-align: center;">
        <br><br>
        ( .................... )<br>
        Admin
    </div>

@else
    <!-- A4 LAYOUT -->
    <div class="header">
        <h1>Purchase Order (PO)</h1>
        <p>NanangStore - Pusat Grosir & Eceran</p>
        <p>Jl. Contoh No. 123, Kota, Indonesia</p>
    </div>

    <table class="layout-table">
        <tr>
            <td width="55%">
                <h3 style="font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin: 0 0 5px 0; width: 80%;">Kepada:</h3>
                <p style="margin: 0; font-weight: bold;">{{ $purchaseOrder->supplier->name }}</p>
                <p style="margin: 2px 0;">{{ $purchaseOrder->supplier->address ?? 'Alamat tidak tersedia' }}</p>
                <p style="margin: 2px 0;">Telp: {{ $purchaseOrder->supplier->phone ?? '-' }}</p>
            </td>
            <td width="45%" align="right">
                <h3 style="font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin: 0 0 5px 0;">Detail PO:</h3>
                <table style="width: 100%; border: none;">
                    <tr>
                        <td align="right" style="padding: 2px;">No. PO :</td>
                        <td align="right" style="padding: 2px;"><strong>{{ $purchaseOrder->order_number }}</strong></td>
                    </tr>
                    <tr>
                        <td align="right" style="padding: 2px;">Tanggal :</td>
                        <td align="right" style="padding: 2px;">{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="40%">Nama Produk</th>
                <th class="text-right" width="15%">Qty</th>
                <th class="text-right" width="20%">Harga Satuan / Box</th>
                <th class="text-right" width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
                @php
                    $isBox = false;
                    $unitType = $item->unit_type ?? 'unit';
                    $itemsPerBox = $item->items_per_box > 0 ? $item->items_per_box : ($item->product->units_in_box > 0 ? $item->product->units_in_box : 1);
                    
                    // Dynamic Unit Names
                    $baseUnitName = $item->product->baseUnit->name ?? 'Pcs';
                    $boxUnitName = $item->product->boxUnit->name ?? 'Box';
                    
                    if ($unitType === 'box') {
                        $isBox = true;
                        $qtyDisplay = $item->quantity / $itemsPerBox;
                        $unitDisplay = $boxUnitName;
                        $priceDisplay = $item->box_cost > 0 ? $item->box_cost : ($item->cost * $itemsPerBox);
                    } else {
                        $qtyDisplay = $item->quantity;
                        $unitDisplay = $baseUnitName;
                        $priceDisplay = $item->cost;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product->name }}<br>
                        <small style="color: #666;">Kode: {{ $item->product->code }}</small>
                    </td>
                    <td class="text-right">
                        {{ number_format($qtyDisplay, 0, ',', '.') }} {{ $unitDisplay }}
                        @if($isBox)
                            <br><small style="color: #666;">({{ number_format($item->quantity, 0, ',', '.') }} {{ $baseUnitName }})</small>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($withPrice)
                            Rp {{ number_format($priceDisplay, 0, ',', '.') }}
                            @if($isBox)
                                <br><small style="color: #666;">(@ Rp {{ number_format($item->cost, 0, ',', '.') }})</small>
                            @endif
                        @else
                            ....................
                        @endif
                    </td>
                    <td class="text-right">
                        @if($withPrice)
                            Rp {{ number_format($item->total_cost, 0, ',', '.') }}
                        @else
                            ....................
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total Nilai Pesanan</th>
                <th class="text-right">
                    @if($withPrice)
                        Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                    @else
                        Rp ....................
                    @endif
                </th>
            </tr>
        </tfoot>
    </table>

    @if($purchaseOrder->notes)
    <div style="margin-top: 10px; padding: 10px; border: 1px dashed #ccc;">
        <strong>Catatan:</strong> {{ $purchaseOrder->notes }}
    </div>
    @endif

    <table class="signature-table">
        <tr>
            <td>
                <p>Disiapkan Oleh,</p>
                <div class="signature-line"></div>
                <p>{{ auth()->user()->name ?? 'Admin' }}</p>
            </td>
            <td>
                <p>Disetujui Oleh,</p>
                <div class="signature-line"></div>
                <p>Manager / Owner</p>
            </td>
            <td>
                <p>Diterima Oleh,</p>
                <div class="signature-line"></div>
                <p>{{ $purchaseOrder->supplier->name }}</p>
            </td>
        </tr>
    </table>
@endif

</body>
</html>
