<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class DebtReport extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $customersQuery = Customer::where('debt', '>', 0)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->orderBy('debt', 'desc');

        $totalDebt = $customersQuery->sum('debt');
        $customersWithDebt = $customersQuery->paginate(15);

        return view('livewire.reports.debt-report', [
            'customers' => $customersWithDebt,
            'totalDebt' => $totalDebt,
        ]);
    }
}
