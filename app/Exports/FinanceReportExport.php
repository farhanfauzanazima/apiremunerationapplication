<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinanceReportExport implements WithMultipleSheets
{
    public function __construct(protected array $data) {}

    public function sheets(): array
    {
        return [
            'Karyawan Tetap' => new FinanceReportTetapSheet($this->data),
            'Tim Partime' => new FinanceReportPartimeSheet($this->data),
            'Total' => new FinanceReportTotalSheet($this->data),
        ];
    }
}