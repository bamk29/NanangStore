<?php

namespace App\Livewire\Financials;

use Livewire\Component;
use App\Models\FinancialTransaction;
use Livewire\WithPagination;

class ManageTransaction extends Component
{
    use WithPagination;

    public $businessUnit;

    // Form properties
    public $showModal = false;
    public $transactionId;
    public $type = 'expense';
    public $amount;
    public $description;
    public $date;
    public $category;

    // Filter properties
    public $startDate;
    public $endDate;
    public $typeFilter = '';
    public $categoryFilter = '';

    protected $listeners = ['financialsUpdated' => '$refresh'];

    protected function rules()
    {
        return [
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'category' => 'required|string|max:50',
        ];
    }

    public function mount($businessUnit)
    {
        $this->businessUnit = $businessUnit;
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->date = now()->format('Y-m-d');
    }

    public function applyFilters()
    {
        $this->resetPage();
        $this->dispatch('filters-updated', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->reset([
            'transactionId', 'type', 'amount', 'description', 'category'
        ]);
        $this->date = now()->format('Y-m-d');
        $this->type = 'expense';
    }

    public function saveTransaction()
    {
        $validatedData = $this->validate();
        
        FinancialTransaction::create(array_merge($validatedData, [
            'business_unit' => $this->businessUnit,
            'user_id' => auth()->id(),
        ]));

        $this->showModal = false;
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Transaksi berhasil ditambahkan.']);
        $this->dispatch('financialsUpdated'); // Refresh parent dashboard and self
    }

    public function render()
    {
        $query = FinancialTransaction::where('business_unit', $this->businessUnit)
            ->where('category', '!=', 'saldo_awal')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->when($this->typeFilter, function ($q) {
                $q->where('type', $this->typeFilter);
            })
            ->when($this->categoryFilter, function ($q) {
                $q->where('category', 'like', '%' . $this->categoryFilter . '%');
            });

        $transactions = $query->latest('date')->paginate(10);

        return view('livewire.financials.manage-transaction', [
            'transactions' => $transactions,
        ]);
    }
}