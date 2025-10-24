<?php

namespace App\Exports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UnitsExport implements FromCollection, WithTitle, WithHeadings
{
    public function collection()
    {
        return Unit::select('id', 'name')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Nama Unit'];
    }

    public function title(): string
    {
        return 'Daftar Unit';
    }
}
