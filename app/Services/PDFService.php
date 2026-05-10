<?php

namespace App\Services;

use App\Models\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PDFService
{
    /**
     * Generate PDF untuk satu slip gaji
     * Simpan ke storage dan return path-nya
     */
    public function generateSlipPDF(SalarySlip $slip): string
    {
        // Load relasi yang dibutuhkan template
        $slip->load([
            'employee',
            'period',
            'category',
        ]);

        // Generate PDF dari blade template
        $pdf = Pdf::loadView('pdf.salary-slip', compact('slip'))
            ->setPaper('a4', 'portrait');

        // Buat nama file yang unik
        $fileName = 'slip-gaji-' . $slip->employee->employee_code
            . '-' . str_replace(' ', '-', strtolower($slip->period->period_name))
            . '-' . $slip->id
            . '.pdf';

        $filePath = 'salary-slips/' . $fileName;

        // Simpan ke storage/app/public/salary-slips/
        Storage::disk('public')->put($filePath, $pdf->output());

        // Update path di database
        $slip->update(['pdf_path' => $filePath]);

        return $filePath;
    }

    /**
     * Generate PDF langsung untuk streaming (preview/download)
     * Tidak disimpan ke storage
     */
    public function streamSlipPDF(SalarySlip $slip): \Barryvdh\DomPDF\PDF
    {
        $slip->load([
            'employee',
            'period',
            'category',
        ]);

        return Pdf::loadView('pdf.salary-slip', compact('slip'))
            ->setPaper('a4', 'portrait');
    }

    /**
     * Hapus file PDF dari storage
     */
    public function deleteSlipPDF(SalarySlip $slip): bool
    {
        if ($slip->pdf_path && Storage::disk('public')->exists($slip->pdf_path)) {
            Storage::disk('public')->delete($slip->pdf_path);
            $slip->update(['pdf_path' => null]);
            return true;
        }

        return false;
    }
}