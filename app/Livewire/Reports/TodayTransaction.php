<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Carbon\Carbon;

class TodayTransaction extends Component
{
    public $selectedDate;
    public $storeFilter = 'all';
    public $transactions;

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        $date = Carbon::parse($this->selectedDate);

        $query = Transaction::with(['details.product', 'customer', 'user'])
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->latest();

        if ($this->storeFilter === 'bakso') {
            $query->whereHas('details.product', function ($q) {
                $q->where('category_id', 1);
            });
        } elseif ($this->storeFilter === 'nanang_store') {
            $query->whereHas('details.product', function ($q) {
                $q->where('category_id', '!=', 1);
            });
        }

        $this->transactions = $query->get();
    }

    public function setStoreFilter($filter)
    {
        $this->storeFilter = $filter;
        $this->loadTransactions();
    }
    
    public function updatedSelectedDate()
    {
        $this->loadTransactions();
    }

    public function render()
    {
        return view('livewire.reports.today-transaction');
    }
}