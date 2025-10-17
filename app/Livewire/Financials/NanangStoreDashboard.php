<?php

namespace App\Livewire\Financials;

class NanangStoreDashboard extends BaseFinancialDashboard
{
    public function mount()
    {
        $this->businessUnit = 'nanang_store';
        parent::mount();
    }

    public function render()
    {
        return view('livewire.financials.nanang-store-dashboard');
    }
}