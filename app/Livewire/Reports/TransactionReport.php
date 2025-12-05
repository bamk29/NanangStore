<?php

namespace App\Livewire\Reports;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TransactionReport extends Component
{
    public $startDate;
    public $endDate;
    public $status = 'all';
    public $storeFilter = 'all';
    public $transactions;
    public $products;
    public $selectedProductId = 'all';

    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';

    public $visibleColumns = [
        'number' => true,
        'invoice_number' => true,
        'created_at' => true,
        'customer_id' => true,
        'items' => true,
        'total_amount' => true,
        'payment_method' => true,
        'status' => true,
    ];

    public $columnFilters = [
        'invoice_number' => '',
        'customer' => [],
        'min_total' => null,
        'max_total' => null,
        'min_qty' => null,
        'max_qty' => null,
        'payment_method' => [],
        'status' => [],
    ];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->products = Product::orderBy('name')->get();
        $this->loadTransactions();
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->loadTransactions();
    }

    public function applyFilters()
    {
        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        $query = Transaction::with(['details.product', 'customer', 'user'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Global Status Filter (Legacy/Top Filter)
        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Store Filter
        if ($this->storeFilter === 'bakso') {
            $query->whereHas('details.product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->storeFilter === 'nanang_store') {
            $query->whereHas('details.product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        // Product Filter
        if ($this->selectedProductId !== 'all') {
            $query->whereHas('details', function ($q) {
                $q->where('product_id', $this->selectedProductId);
            });
        }

        // --- Column Filters ---

        // Invoice
        if (!empty($this->columnFilters['invoice_number'])) {
            $query->where('invoice_number', 'like', '%' . $this->columnFilters['invoice_number'] . '%');
        }

        // Customer
        if (!empty($this->columnFilters['customer'])) {
            $query->whereIn('customer_id', $this->columnFilters['customer']);
        }

        // Payment Method
        if (!empty($this->columnFilters['payment_method'])) {
            $query->whereIn('payment_method', $this->columnFilters['payment_method']);
        }

        // Status (Column Filter)
        if (!empty($this->columnFilters['status'])) {
            $query->whereIn('status', $this->columnFilters['status']);
        }

        // Total Amount Range
        if ($this->columnFilters['min_total'] !== null && $this->columnFilters['min_total'] !== '') {
            $query->where('total_amount', '>=', $this->columnFilters['min_total']);
        }
        if ($this->columnFilters['max_total'] !== null && $this->columnFilters['max_total'] !== '') {
            $query->where('total_amount', '<=', $this->columnFilters['max_total']);
        }

        // Quantity Range
        if (($this->columnFilters['min_qty'] !== null && $this->columnFilters['min_qty'] !== '') || 
            ($this->columnFilters['max_qty'] !== null && $this->columnFilters['max_qty'] !== '')) {
            
            $query->whereHas('details', function ($q) {
                if ($this->selectedProductId !== 'all') {
                    $q->where('product_id', $this->selectedProductId);
                }
            }, '>=', 1)
            ->withSum(['details as total_quantity_filtered' => function ($q) {
                if ($this->selectedProductId !== 'all') {
                    $q->where('product_id', $this->selectedProductId);
                }
            }], 'quantity')
            ->having('total_quantity_filtered', '>=', $this->columnFilters['min_qty'] ?? 0);

            if ($this->columnFilters['max_qty'] !== null && $this->columnFilters['max_qty'] !== '') {
                $query->having('total_quantity_filtered', '<=', $this->columnFilters['max_qty']);
            }
        }

        $this->transactions = $query->orderBy($this->sortColumn, $this->sortDirection)->get();
    }

    public function updated($propertyName)
    {
        // Only auto-reload for visibleColumns changes
        // Main filters and column filters require manual application via applyFilters button
        $excludedProperties = ['startDate', 'endDate', 'status', 'selectedProductId', 'storeFilter', 'columnFilters'];
        
        $shouldExclude = false;
        foreach ($excludedProperties as $excluded) {
            if (str_starts_with($propertyName, $excluded)) {
                $shouldExclude = true;
                break;
            }
        }
        
        if (!$shouldExclude) {
            $this->loadTransactions();
        }
    }

    public function exportExcel()
    {
        $filters = [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'storeFilter' => $this->storeFilter,
            'selectedProductId' => $this->selectedProductId,
        ];

        return Excel::download(new TransactionsExport($filters), 'laporan-transaksi-detail.xlsx');
    }

    public function getAvailableCustomersProperty()
    {
        // Get customers from the base filtered transactions (before column filters)
        $query = Transaction::with('customer')
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Apply main filters only (not column filters)
        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->storeFilter === 'bakso') {
            $query->whereHas('details.product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->storeFilter === 'nanang_store') {
            $query->whereHas('details.product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        if ($this->selectedProductId !== 'all') {
            $query->whereHas('details', function ($q) {
                $q->where('product_id', $this->selectedProductId);
            });
        }

        // Get unique customers from these transactions
        return $query->get()
            ->pluck('customer')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public function render()
    {
        return view('livewire.reports.transaction-report', [
            'availableCustomers' => $this->availableCustomers
        ]);
    }
}