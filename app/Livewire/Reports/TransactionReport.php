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

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->products = Product::orderBy('name')->get();
        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        $query = Transaction::with(['details.product', 'customer', 'user'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

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

        $this->transactions = $query->latest()->get();
    }

    public function updated($propertyName)
    {
        $this->loadTransactions();
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

    public function render()
    {
        return view('livewire.reports.transaction-report');
    }
}