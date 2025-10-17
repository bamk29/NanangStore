<?php

namespace App\Livewire\Financials;

class GilingBaksoDashboard extends BaseFinancialDashboard
{
    public function mount()
    {
        $this->businessUnit = 'giling_bakso';
        parent::mount();
    }

    public function render()
    {
        return view('livewire.financials.giling-bakso-dashboard');
    }
}