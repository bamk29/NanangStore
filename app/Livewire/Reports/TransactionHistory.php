<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class TransactionHistory extends Component
{
    use WithPagination;

    public $selectedDate;
    public $search = '';

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $transactions = Transaction::with(['customer', 'user'])
            ->whereDate('created_at', $this->selectedDate)
            ->where(function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(20);

        return view('livewire.reports.transaction-history', [
            'transactions' => $transactions,
        ]);
    }
}