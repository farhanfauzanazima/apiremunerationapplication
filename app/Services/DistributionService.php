<?php

namespace App\Services;

use App\Mail\SalarySlipMail;
use App\Models\DistributionHistory;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use App\Services\PDFService;
use App\Services\PublicLinkService;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Mail;

class DistributionService
{
    public function __construct(
        protected PDFService $pdfService,
        protected PublicLinkService $publicLinkService,
        protected WhatsappService $whatsappService,
    ) {}

    protected function bulanIndo(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function sendEmail($slip, string $type, string $signedBy): DistributionHistory
    {
        $slipClass = $type === 'tetap' ? SalarySlipTetap::class : SalarySlipPartime::class;
        $employee = $slip->employee;

        $distribution = DistributionHistory::firstOrNew([
            'slip_id' => $slip->id,
            'slip_type' => $slipClass,
            'channel' => 'email',
        ]);

        if (empty($employee->email)) {
            $distribution->status = 'failed';
            $distribution->note = 'Karyawan tidak memiliki alamat email';
            $distribution->save();

            return $distribution;
        }

        try {
            $pdf = $this->pdfService->renderSalarySlip($slip, $type, $signedBy);

            Mail::to($employee->email)->send(new SalarySlipMail($slip, $type, $pdf, $this->bulanIndo()));

            $distribution->status = 'sent';
            $distribution->sent_at = now();
            $distribution->note = null;
        } catch (\Throwable $e) {
            $distribution->status = 'failed';
            $distribution->note = $e->getMessage();
        }

        $distribution->save();

        return $distribution;
    }

    public function sendWhatsapp($slip, string $type, ?int $sentBy = null): DistributionHistory
    {
        $employee = $slip->employee;

        $token = $this->publicLinkService->getOrCreateToken($slip, $type, 'whatsapp');
        $distribution = DistributionHistory::where([
            'slip_id' => $slip->id,
            'slip_type' => $type === 'tetap' ? SalarySlipTetap::class : SalarySlipPartime::class,
            'channel' => 'whatsapp',
        ])->first();

        if (empty($employee->phone)) {
            $distribution->update(['status' => 'failed', 'note' => 'Karyawan tidak memiliki nomor HP']);

            return $distribution;
        }

        $period = $slip->payrollPeriod;
        $bulan = $this->bulanIndo()[$period->month] ?? $period->month;
        $link = url("/public/slip/{$token}");

        $message = "Halo {$employee->name}, berikut rincian gaji kamu bulan {$bulan} {$period->year}. "
            . "Akses link dibawah ini untuk mendownload PDF-nya:\n{$link}";

        $result = $this->whatsappService->sendMessage($employee->phone, $message);

        $distribution->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'note' => $result['message'],
            'sent_at' => $result['success'] ? now() : null,
            'sent_by' => $sentBy,
        ]);

        return $distribution->fresh();
    }
}
