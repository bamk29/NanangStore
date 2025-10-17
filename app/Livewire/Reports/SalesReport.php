<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class SalesReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $categoryFilter = '';
    public $transactionType = '';
    public $reportType = 'daily';
    public $groupBy = 'date';

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function getSalesData()
    {
        $query = Transaction::with(['details.product.category'])
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);

        if ($this->transactionType) {
            $query->where('transaction_type', $this->transactionType);
        }

        $transactions = $query->get();

        $salesData = collect();

        foreach ($transactions as $transaction) {
            foreach ($transaction->details as $detail) {
                if ($this->categoryFilter && $detail->product->category_id != $this->categoryFilter) {
                    continue;
                }

                $key = match ($this->groupBy) {
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'product' => $detail->product->name,
                    'category' => $detail->product->category->name,
                };

                $item = $salesData->get($key, [
                    'key' => $key,
                    'total_sales' => 0,
                    'quantity' => 0,
                    'transaction_count' => 0,
                ]);

                $item['total_sales'] += $detail->subtotal;
                $item['quantity'] += $detail->quantity;
                if ($this->groupBy === 'date') {
                    $item['transaction_count']++;
                }

                // simpan kembali ke collection
                $salesData->put($key, $item);
            }
        }

        return $salesData->values()->sortByDesc('total_sales');
    }


    public function exportCsv()
    {
        $salesData = $this->getSalesData();
        $filename = 'sales_report_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');

        // Add headers
        fputcsv($handle, ['Date/Product/Category', 'Total Sales', 'Quantity', 'Transaction Count']);

        foreach ($salesData as $data) {
            fputcsv($handle, [
                $data['key'],
                number_format($data['total_sales'], 2),
                $data['quantity'],
                $data['transaction_count'] ?? 'N/A'
            ]);
        }

        fclose($handle);
    }

    public function getTotalSales()
    {
        return $this->getSalesData()->sum('total_sales');
    }

    public function getTotalTransactions()
    {
        return Transaction::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay()
        ])->count();
    }

    public function getAverageTransactionValue()
    {
        $totalTransactions = $this->getTotalTransactions();
        return $totalTransactions > 0 ? $this->getTotalSales() / $totalTransactions : 0;
    }

    public function render()
    {
        return view('livewire.reports.sales-report', [
            'salesData' => $this->getSalesData(),
            'categories' => Category::orderBy('name')->get(),
            'totalSales' => $this->getTotalSales(),
            'totalTransactions' => $this->getTotalTransactions(),
            'averageTransactionValue' => $this->getAverageTransactionValue(),
        ]);

    }
}
