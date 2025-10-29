<?php

namespace App\Exports\Sheets;

use App\Models\TransactionDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CompletedTransactionsSheet implements FromQuery, WithTitle, WithHeadings, WithMapping
{
    private $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = TransactionDetail::with(['transaction.customer', 'transaction.user', 'product'])
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$this->filters['startDate'], $this->filters['endDate']]);
            });

        if ($this->filters['storeFilter'] === 'bakso') {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->filters['storeFilter'] === 'nanang_store') {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        if ($this->filters['selectedProductId'] !== 'all') {
            $query->where('product_id', $this->filters['selectedProductId']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Tanggal',
            'Pelanggan',
            'Kasir',
            'Nama Produk',
            'Kuantitas',
            'Harga Satuan',
            'Subtotal Item',
            'Total Invoice',
            'Dibayar',
            'Kembalian',
            'Kekurangan (Hutang)',
            'Metode Pembayaran',
            'Status Transaksi',
        ];
    }

    public function map($detail): array
    {
        $shortage = $detail->transaction->total_amount > $detail->transaction->paid_amount 
            ? $detail->transaction->total_amount - $detail->transaction->paid_amount 
            : 0;

        return [
            $detail->transaction->invoice_number,
            $detail->transaction->created_at->format('d/m/Y H:i'),
            $detail->transaction->customer->name ?? '-',
            $detail->transaction->user->name ?? '-',
            $detail->product->name ?? 'Produk Dihapus',
            $detail->quantity,
            $detail->price,
            $detail->subtotal,
            $detail->transaction->total_amount,
            $detail->transaction->paid_amount,
            $detail->transaction->change,
            $shortage,
            ucfirst($detail->transaction->payment_method),
            ucfirst($detail->transaction->status),
        ];
    }

    public function title(): string
    {
        return 'Transaksi Selesai (Detail)';
    }
}