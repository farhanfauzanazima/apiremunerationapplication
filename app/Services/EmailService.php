<?php

namespace App\Services;

use App\Mail\SalarySlipMail;
use App\Models\EmailHistory;
use App\Models\SalarySlip;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Kirim slip gaji ke satu karyawan
     */
    public function sendSlipEmail(SalarySlip $slip, int $sentBy): array
    {
        // Load relasi yang dibutuhkan
        $slip->load(['employee', 'period', 'category']);

        // Buat record history dengan status pending
        $history = EmailHistory::create([
            'salary_slip_id' => $slip->id,
            'employee_id'    => $slip->employee_id,
            'email_to'       => $slip->employee->email,
            'subject'        => 'Slip Gaji - '
                . $slip->employee->full_name
                . ' - '
                . $slip->period->period_name,
            'status'         => 'pending',
            'sent_by'        => $sentBy,
        ]);

        try {
            // Kirim email via Resend
            Mail::to($slip->employee->email)
                ->send(new SalarySlipMail($slip));

            // Update history — berhasil
            $history->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            // Update status slip menjadi sent
            $slip->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            return [
                'success'       => true,
                'employee_name' => $slip->employee->full_name,
                'email_to'      => $slip->employee->email,
                'status'        => 'sent',
                'sent_at'       => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            // Update history — gagal
            $history->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success'       => false,
                'employee_name' => $slip->employee->full_name,
                'email_to'      => $slip->employee->email,
                'status'        => 'failed',
                'error'         => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim slip gaji ke banyak karyawan (bulk)
     */
    public function sendBulkSlipEmail(array $slipIds, int $sentBy): array
    {
        $results = [];
        $errors  = [];

        foreach ($slipIds as $slipId) {
            $slip = SalarySlip::find($slipId);

            if (!$slip) {
                $errors[] = [
                    'slip_id' => $slipId,
                    'error'   => 'Slip gaji tidak ditemukan.',
                    'status'  => 'failed',
                ];
                continue;
            }

            $result = $this->sendSlipEmail($slip, $sentBy);

            if ($result['success']) {
                $results[] = array_merge($result, ['slip_id' => $slipId]);
            } else {
                $errors[] = array_merge($result, ['slip_id' => $slipId]);
            }
        }

        return [
            'success' => $results,
            'errors'  => $errors,
            'summary' => [
                'total'   => count($slipIds),
                'success' => count($results),
                'failed'  => count($errors),
            ],
        ];
    }
}