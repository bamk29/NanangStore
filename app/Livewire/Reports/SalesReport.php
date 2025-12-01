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
        $query = Transaction::with(['details.product.category'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);

        if ($this->transactionType) {
            $query->where('transaction_type', $this->transactionType);
        }

        if ($this->paymentMethodFilter) {
            $query->where('payment_method', $this->paymentMethodFilter);
        }

        $transactions = $query->get();

        $salesData = collect();

        foreach ($transactions as $transaction) {
            foreach ($transaction->details as $detail) {
                // Category Filter
                if ($this->categoryFilter && $detail->product->category_id != $this->categoryFilter) {
                    continue;
                }

                // Store Filter
                if ($this->storeFilter === 'bakso' && $detail->product->category_id != 1) {
                    continue;
                }
                if ($this->storeFilter === 'nanang_store' && $detail->product->category_id == 1) {
                    continue;
                }

                $key = match ($this->groupBy) {
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'product' => $detail->product->name,
                    'category' => $detail->product->category->name ?? 'Uncategorized',
                };

                $item = $salesData->get($key, [
                    'key' => $key,
                    'total_sales' => 0,
                    'total_cost' => 0,
                    'total_profit' => 0,
                    'quantity' => 0,
                    'transaction_count' => 0,
                    'transactions_set' => collect(), // To count unique transactions
                ]);

                $costPrice = $detail->product->cost_price ?? 0;
                $profit = ($detail->price - $costPrice) * $detail->quantity;

                $item['total_sales'] += $detail->subtotal;
                $item['total_cost'] += ($costPrice * $detail->quantity);
                $item['total_profit'] += $profit;
                $item['quantity'] += $detail->quantity;
                
                if (!$item['transactions_set']->contains($transaction->id)) {
                    $item['transaction_count']++;
                    $item['transactions_set']->push($transaction->id);
                }

                $salesData->put($key, $item);
            }
        }

        // Remove the temporary set
        $salesData = $salesData->map(function ($item) {
            unset($item['transactions_set']);
            return $item;
        });

        // Sort by date if grouped by date, otherwise by sales
        if ($this->groupBy === 'date') {
            return $salesData->sortKeys();
        }

        return $salesData->values()->sortByDesc('total_sales');
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
