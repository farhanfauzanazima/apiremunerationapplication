<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributionHistory;
use App\Services\PDFService;
use Illuminate\Http\Request;

class PublicSlipController extends Controller
{
    public function __construct(protected PDFService $pdfService) {}

    public function show(Request $request, string $token)
    {
        $distribution = DistributionHistory::where('public_token', $token)->first();

        if (!$distribution) {
            abort(404, 'Link tidak valid atau sudah kadaluarsa');
        }

        $slip = $distribution->slip; // morphTo, otomatis resolve ke SalarySlipTetap/SalarySlipPartime
        $type = $distribution->slip_type === \App\Models\SalarySlipTetap::class ? 'tetap' : 'partime';

        // Nama penanda tangan pada link publik memakai nama HR Dept generik,
        // karena tidak ada sesi login untuk mengambil nama user yang generate.
        $pdf = $this->pdfService->renderSalarySlip($slip, $type, 'HR Dept');

        return $pdf->stream("slip-gaji-{$slip->employee->name}.pdf");
    }
}