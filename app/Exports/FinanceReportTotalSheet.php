<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportTotalSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Total';
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function array(): array
    {
        $totals = $this->data['totals'];
        $branch = $this->data['branch'];
        $period = $this->data['period'];

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return [
            ['Cabang', $branch->name],
            ['Periode', ($bulanIndo[$period->month] ?? $period->month) . ' ' . $period->year],
            ['Total Karyawan Tetap', $totals['total_karyawan_tetap'] . ' orang'],
            ['Total Tim Partime', $totals['total_karyawan_partime'] . ' orang'],
            ['Total Tabungan Karyawan (Tetap)', $totals['total_tabungan']],
            ['Total Gaji Karyawan Tetap', $totals['total_gaji_tetap']],
            ['Total Fee Tim Partime', $totals['total_fee_partime']],
            ['TOTAL KESELURUHAN', $totals['total_keseluruhan']],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A9' => ['font' => ['bold' => true]],
            'B9' => ['font' => ['bold' => true]],
        ];
    }
}