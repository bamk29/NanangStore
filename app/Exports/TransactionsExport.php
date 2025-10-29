<?php

namespace App\Exports;

use App\Exports\Sheets\CancelledTransactionsSheet;
use App\Exports\Sheets\CompletedTransactionsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransactionsExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new CompletedTransactionsSheet($this->filters),
            new CancelledTransactionsSheet($this->filters),
        ];
    }
}