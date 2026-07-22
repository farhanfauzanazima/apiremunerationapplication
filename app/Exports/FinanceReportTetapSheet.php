<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportTetapSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Karyawan Tetap';
    }

    public function headings(): array
    {
        return [
            'No', 'Nama', 'Bergabung', 'Jabatan',
            'Hari Kerja', 'Alfa', 'Izin', 'Sakit', 'Off', 'Masuk',
            'Hari Shift', 'Hari Full', 'Hari Parsial',
            'Nominal Shift', 'Nominal Full', 'Nominal Parsial',
            'Total Shift', 'Total Full', 'Total Parsial', 'Gaji Pokok',
            'Jam Lembur', 'Total Lembur', 'Telat',
            'Transport', 'T. Jabatan', 'BPJS', 'T. Masa Kerja',
            'Bonus Disiplin', 'Bonus Omset', 'Bonus Kinerja',
            'Cashbond', 'Tabungan', 'THP', 'Total Gaji',
            'No Rekening', 'Atas Nama', 'Bank',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data['tetap'] as $i => $slip) {
            $rows[] = [
                $i + 1,
                $slip->employee->name,
                \Carbon\Carbon::parse($slip->employee->join_date)->format('d-m-Y'),
                $slip->employee->position->name ?? '-',
                $slip->hari_kerja, $slip->alfa, $slip->izin, $slip->sakit, $slip->off, $slip->masuk,
                $slip->hari_shift, $slip->hari_full, $slip->hari_parsial,
                $slip->nominal_shift, $slip->nominal_full, $slip->nominal_parsial,
                $slip->total_shift, $slip->total_full, $slip->total_parsial, $slip->gaji_pokok,
                $slip->jam_lembur, $slip->lembur, $slip->telat,
                $slip->tunjangan_transport, $slip->tunjangan_jabatan, $slip->tunjangan_bpjs, $slip->tunjangan_masa_kerja,
                $slip->bonus_disiplin, $slip->bonus_omset, $slip->bonus_kinerja,
                $slip->cashbond, $slip->tabungan, $slip->thp, $slip->total_gaji,
                $slip->employee->bank_account_number ?? '-',
                $slip->employee->bank_account_name ?? '-',
                $slip->employee->bank_name ?? '-',
            ];
        }

        // Baris Total di bagian bawah sheet ini sendiri
        $totals = $this->data['totals'];
        $rows[] = array_merge(
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', 'TOTAL', $totals['total_gaji_tetap'], '', '', '']
        );

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}