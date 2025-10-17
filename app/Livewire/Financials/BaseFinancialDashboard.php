<?php

namespace App\Livewire\Financials;

use Livewire\Component;
use App\Models\FinancialTransaction;
use Carbon\Carbon;

class BaseFinancialDashboard extends Component
{
    public $businessUnit;
    public $startDate;
    public $endDate;

    // Summary Cards
    public $currentBalance = 0;
    public $filteredIncome = 0;
    public $filteredExpense = 0;

    // For initial balance modal
    public $showBalanceModal = false;
    public $initial_balance = 0;

    protected $listeners = ['financialsUpdated' => 'calculateSummaries', 'filters-updated' => 'updateFilters'];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->calculateSummaries();
    }

    public function updateFilters($filters)
    {
        $this->startDate = $filters['startDate'] ?? $this->startDate;
        $this->endDate = $filters['endDate'] ?? $this->endDate;
        $this->calculateSummaries();
    }

    public function calculateSummaries()
    {
        $this->filteredIncome = FinancialTransaction::where('business_unit', $this->businessUnit)
            ->where('type', 'income')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $this->filteredExpense = FinancialTransaction::where('business_unit', $this->businessUnit)
            ->where('type', 'expense')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $totalIncome = FinancialTransaction::where('business_unit', $this->businessUnit)
            ->where('type', 'income')
            ->sum('amount');
            
        $totalExpense = FinancialTransaction::where('business_unit', $this->businessUnit)
            ->where('type', 'expense')
            ->sum('amount');

        $this->currentBalance = $totalIncome - $totalExpense;
    }

    public function openBalanceModal()
    {
        $balance = FinancialTransaction::where('business_unit', $this->businessUnit)
            ->where('category', 'saldo_awal')->first();
        $this->initial_balance = $balance ? $balance->amount : 0;
        $this->showBalanceModal = true;
    }

    public function saveInitialBalance()
    {
        $this->validate(['initial_balance' => 'required|numeric|min:0']);

        FinancialTransaction::updateOrCreate(
            [
                'business_unit' => $this->businessUnit,
                'category' => 'saldo_awal',
            ],
            [
                'type' => 'income',
                'amount' => $this->initial_balance,
                'description' => 'Saldo awal kas',
                'date' => now()->toDateString(),
                'user_id' => auth()->id(),
            ]
        );

        $this->showBalanceModal = false;
        $this->calculateSummaries();
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Saldo awal berhasil disimpan.']);
    }
}
