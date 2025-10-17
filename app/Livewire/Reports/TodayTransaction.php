<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class TodayTransaction extends Component
{
    public function triggerFlashMessage()
    {
        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'Ini adalah pesan flash untuk uji coba.'
        ]);
    }

    public function render()
    {
        return view('livewire.reports.today-transaction');
    }
}
