<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportPartimeSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Tim Partime';
    }

    public function headings(): array
    {
        return [
            'No', 'Nama', 'Bergabung', 'Jabatan',
            'Hari Kerja', 'Full', 'Shift', 'Reguler', 'Sakit', 'Off',
            'Tunjangan', 'Total Full', 'Total Shift', 'Total Reguler', 'Total Transport',
            'Bonus', 'Total Fee',
            'No Rekening', 'Atas Nama', 'Bank',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data['partime'] as $i => $slip) {
            $rows[] = [
                $i + 1,
                $slip->employee->name,
                \Carbon\Carbon::parse($slip->employee->join_date)->format('d-m-Y'),
                $slip->employee->position->name ?? '-',
                $slip->hari_kerja, $slip->full, $slip->shift, $slip->reguler, $slip->sakit, $slip->off,
                $slip->tunjangan, $slip->total_full, $slip->total_shift, $slip->total_reguler, $slip->total_transport,
                $slip->bonus, $slip->total_fee,
                $slip->employee->bank_account_number ?? '-',
                $slip->employee->bank_account_name ?? '-',
                $slip->employee->bank_name ?? '-',
            ];
        }

        $totals = $this->data['totals'];
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', 'TOTAL', $totals['total_fee_partime'], '', '', ''];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}