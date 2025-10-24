<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExport implements FromCollection, WithTitle, WithHeadings
{
    public function collection()
    {
        return Category::select('id', 'name')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Nama Kategori'];
    }

    public function title(): string
    {
        return 'Daftar Kategori';
    }
}
