<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PDFService
{
    protected array $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function renderSalarySlip($slip, string $type, string $signedBy)
    {
        $employee = $slip->employee;
        $branch = $employee->branch;
        $period = $slip->payrollPeriod;

        $pdf = Pdf::loadView('pdf.salary-slip', [
            'slip' => $slip,
            'type' => $type,
            'employee' => $employee,
            'branch' => $branch,
            'period' => $period,
            'bulanIndo' => $this->bulanIndo,
            'logoPath' => config('company.logo_path'),
            'signedBy' => $signedBy,
        ]);

        return $pdf->setPaper('a3', 'landscape');
    }

    public function renderFinanceReport(array $data)
    {
        $pdf = Pdf::loadView('pdf.finance-report', [
            'branch' => $data['branch'],
            'period' => $data['period'],
            'tetap' => $data['tetap'],
            'partime' => $data['partime'],
            'totals' => $data['totals'],
            'bulanIndo' => $this->bulanIndo,
            'logoPath' => config('company.logo_path'),
        ]);

        return $pdf->setPaper('a4', 'landscape');
    }
}