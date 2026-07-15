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
        // ============================================================
        // UKURAN KERTAS SLIP GAJI INDIVIDUAL
        // Saat ini: A4 PORTRAIT (210mm x 297mm, berdiri)
        // Ini yang tampil saat Preview/Download PDF per satu karyawan.
        // Kalau ingin ubah ukuran, ganti baris setPaper() di paling bawah method ini.
        // Contoh: 'a4','landscape' (tidur) | 'a5','portrait' (lebih kecil) | 'letter','portrait' (US Letter)
        // ============================================================
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

        return $pdf->setPaper('a4', 'portrait'); // <-- UKURAN DI SINI: A4 PORTRAIT (jangan ubah tanpa alasan, ini standar slip gaji resmi)
    }

    public function renderFinanceReport(array $data)
    {
        // ============================================================
        // UKURAN KERTAS LAPORAN KEUANGAN (banyak karyawan sekaligus, per cabang)
        // Saat ini: A3 LANDSCAPE (420mm x 297mm, tidur) — dipilih karena
        // tabel Karyawan Tetap punya 24 kolom, butuh ruang sangat lebar.
        // Kalau masih kurang lebar setelah kolom baru ditambahkan, opsi naik:
        // - 'legal','landscape' (sedikit lebih lebar dari A3 di beberapa printer)
        // - Custom point: [0, 0, 1684, 1191] (lebar x tinggi dalam point, 1pt=1/72 inch)
        // Kalau ingin turun ke A4 (lebih kecil, TIDAK disarankan untuk >15 kolom
        // karena akan sangat padat/kepotong): 'a4','landscape'
        // ============================================================
        $pdf = Pdf::loadView('pdf.finance-report', [
            'branch' => $data['branch'],
            'period' => $data['period'],
            'tetap' => $data['tetap'],
            'partime' => $data['partime'],
            'totals' => $data['totals'],
            'bulanIndo' => $this->bulanIndo,
            'logoPath' => config('company.logo_path'),
        ]);

        return $pdf->setPaper('a3', 'landscape'); // <-- UKURAN DI SINI: A3 LANDSCAPE
    }
}