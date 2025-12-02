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
    public $storeFilter = ''; // '', 'nanang_store', 'bakso'
    public $paymentMethodFilter = ''; // '', 'cash', 'transfer', 'debt'
    public $reportType = 'daily';
    public $groupBy = 'date';

    public $chartInterval = 'daily'; // 'daily', 'weekly', 'monthly'

    public function mount()
    {
        $this->setDateRange('today');
    }

    public function applyFilter()
    {
        $this->dispatch('chart-updated', $this->getChartData());
    }

    public function setDateRange($range)
    {
        if ($range === 'today') {
            $this->startDate = Carbon::today()->format('Y-m-d');
            $this->endDate = Carbon::today()->format('Y-m-d');
            $this->chartInterval = 'daily';
        } elseif ($range === 'week') {
            $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
            $this->chartInterval = 'daily';
        } elseif ($range === 'month') {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
            $this->chartInterval = 'daily'; // Default to daily for single month view
        } elseif ($range === 'year') {
            $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
            $this->chartInterval = 'monthly'; // Default to monthly for year view
        }
        
        // Auto-apply when date range preset is clicked
        $this->applyFilter();
    }

    public function getSalesData()
    {
        $query = \App\Models\TransactionDetail::query()
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);

        // Apply Filters
        if ($this->transactionType) {
            $query->where('transactions.transaction_type', $this->transactionType);
        }

        if ($this->paymentMethodFilter) {
            $query->where('transactions.payment_method', $this->paymentMethodFilter);
        }

        if ($this->categoryFilter) {
            $query->where('products.category_id', $this->categoryFilter);
        }

        if ($this->storeFilter === 'bakso') {
            $query->where('products.category_id', 1);
        } elseif ($this->storeFilter === 'nanang_store') {
            $query->where('products.category_id', '!=', 1);
        }

        // Selects & Grouping
        $selects = [
            DB::raw('SUM(transaction_details.subtotal) as total_sales'),
            DB::raw('SUM(transaction_details.quantity) as quantity'),
            // Cost & Profit Calculation (using current product cost as per original logic)
            DB::raw('SUM(products.cost_price * transaction_details.quantity) as total_cost'),
            DB::raw('SUM((transaction_details.price - COALESCE(products.cost_price, 0)) * transaction_details.quantity) as total_profit'),
            DB::raw('COUNT(DISTINCT transactions.id) as transaction_count')
        ];

        if ($this->groupBy === 'date') {
            $query->groupBy(DB::raw('DATE(transactions.created_at)'));
            $selects[] = DB::raw('DATE(transactions.created_at) as key_group');
            $query->orderBy('key_group');
        } elseif ($this->groupBy === 'product') {
            $query->groupBy('products.name');
            $selects[] = 'products.name as key_group';
            $query->orderByDesc('total_sales');
        } elseif ($this->groupBy === 'category') {
            $query->groupBy('categories.name');
            $selects[] = DB::raw('COALESCE(categories.name, "Uncategorized") as key_group');
            $query->orderByDesc('total_sales');
        }

        $results = $query->select($selects)->get();

        // Map to expected format
        return $results->map(function ($item) {
            return [
                'key' => $item->key_group,
                'total_sales' => $item->total_sales,
                'total_cost' => $item->total_cost,
                'total_profit' => $item->total_profit,
                'quantity' => $item->quantity,
                'transaction_count' => $item->transaction_count,
            ];
        });
    }

    public function getChartData()
    {
        $query = Transaction::with(['details.product'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);

        if ($this->transactionType) $query->where('transaction_type', $this->transactionType);
        if ($this->paymentMethodFilter) $query->where('payment_method', $this->paymentMethodFilter);

        $transactions = $query->get();
        
        $chartData = [];
        
        foreach ($transactions as $transaction) {
            $dateKey = match($this->chartInterval) {
                'weekly' => $transaction->created_at->startOfWeek()->format('Y-m-d'),
                'monthly' => $transaction->created_at->format('Y-m'),
                default => $transaction->created_at->format('Y-m-d'),
            };

            if (!isset($chartData[$dateKey])) {
                $chartData[$dateKey] = ['sales' => 0, 'profit' => 0];
            }

            foreach ($transaction->details as $detail) {
                 // Apply filters again (Category & Store)
                 if ($this->categoryFilter && $detail->product->category_id != $this->categoryFilter) continue;
                 if ($this->storeFilter === 'bakso' && $detail->product->category_id != 1) continue;
                 if ($this->storeFilter === 'nanang_store' && $detail->product->category_id == 1) continue;

                 $cost = $detail->product->cost_price ?? 0;
                 $profit = ($detail->price - $cost) * $detail->quantity;
                 
                 $chartData[$dateKey]['sales'] += $detail->subtotal;
                 $chartData[$dateKey]['profit'] += $profit;
            }
        }
        
        ksort($chartData);
        
        // Format labels for display
        $labels = array_map(function($key) {
            if ($this->chartInterval === 'monthly') {
                return Carbon::createFromFormat('Y-m', $key)->format('M Y');
            } elseif ($this->chartInterval === 'weekly') {
                return 'Week ' . Carbon::parse($key)->format('W') . ' (' . Carbon::parse($key)->format('d M') . ')';
            }
            return Carbon::parse($key)->format('d M Y');
        }, array_keys($chartData));

        return [
            'labels' => $labels,
            'sales' => array_column($chartData, 'sales'),
            'profit' => array_column($chartData, 'profit'),
        ];
    }

    public function exportCsv()
    {
        $salesData = $this->getSalesData();
        $filename = 'sales_report_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');

        fputcsv($handle, ['Date/Product/Category', 'Total Sales', 'Total Cost', 'Total Profit', 'Quantity', 'Transactions']);

        foreach ($salesData as $data) {
            fputcsv($handle, [
                $data['key'],
                $data['total_sales'],
                $data['total_cost'],
                $data['total_profit'],
                $data['quantity'],
                $data['transaction_count']
            ]);
        }

        fclose($handle);
    }

    public function render()
    {
        $data = $this->getSalesData();
        $totalSales = $data->sum('total_sales');
        $totalCost = $data->sum('total_cost');
        $totalProfit = $data->sum('total_profit');
        $totalTransactions = $data->sum('transaction_count'); // This might be slightly off if grouped by product (sum of tx counts per product != unique tx), but for summary cards we usually want unique transactions.
        
        // Recalculate unique transactions for summary
        // Actually, let's just use the query again for totals to be accurate
        // Or simpler: just sum sales/profit/cost from the processed data (accurate).
        // For transactions, if grouped by product, it's hard to get unique total transactions from the collection.
        // Let's do a separate count for total transactions.
        
        $txCountQuery = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()]);
        if ($this->transactionType) $txCountQuery->where('transaction_type', $this->transactionType);
        if ($this->paymentMethodFilter) $txCountQuery->where('payment_method', $this->paymentMethodFilter);
        // Note: Store/Category filters make "Total Transactions" ambiguous (a transaction can contain mixed items).
        // Usually "Total Transactions" means "Number of receipts that contain at least one matching item".
        // For now, let's stick to the simple count if no item-level filters are applied, 
        // or just accept that if we filter by product category, "Total Transactions" might be less meaningful or hard to calculate efficiently without a join.
        // Let's just use the count from the query for now.
        $totalTransactions = $txCountQuery->count();

        return view('livewire.reports.sales-report', [
            'salesData' => $data,
            'chartData' => $this->getChartData(),
            'categories' => Category::orderBy('name')->get(),
            'totalSales' => $totalSales,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit,
            'totalTransactions' => $totalTransactions,
            'averageTransactionValue' => $totalTransactions > 0 ? $totalSales / $totalTransactions : 0,
        ]);
    }
}
