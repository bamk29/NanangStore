<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SuppliersExport implements FromCollection, WithTitle, WithHeadings
{
    public function collection()
    {
        return Supplier::select('id', 'name')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Nama Supplier'];
    }

    public function title(): string
    {
        return 'Daftar Supplier';
    }
}
