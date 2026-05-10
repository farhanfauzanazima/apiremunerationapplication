<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailHistory;
use App\Models\SalarySlip;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function __construct(
        protected EmailService $emailService,
    ) {}

    // POST /api/email/send/{salarySlip}
    public function send(SalarySlip $salarySlip): JsonResponse
    {
        $salarySlip->load(['employee', 'period', 'category']);

        if (!$salarySlip->employee->email) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak memiliki alamat email.',
            ], 422);
        }

        $result = $this->emailService->sendSlipEmail(
            $salarySlip,
            auth()->id()
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Slip gaji berhasil dikirim ke ' . $result['email_to'],
                'data'    => $result,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim slip gaji.',
            'data'    => $result,
        ], 500);
    }

    // POST /api/email/send-bulk
    public function sendBulk(Request $request): JsonResponse
    {
        $request->validate([
            'period_id'  => 'required|exists:payroll_periods,id',
            'slip_ids'   => 'nullable|array',
            'slip_ids.*' => 'exists:salary_slips,id',
        ]);

        $query = SalarySlip::where('period_id', $request->period_id)
            ->where('status', 'draft');

        if ($request->has('slip_ids') && count($request->slip_ids) > 0) {
            $query->whereIn('id', $request->slip_ids);
        }

        $slips = $query->get();

        if ($slips->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada slip gaji yang ditemukan untuk dikirim.',
            ], 422);
        }

        $slipIds = $slips->pluck('id')->toArray();

        $results = $this->emailService->sendBulkSlipEmail(
            $slipIds,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => $results['summary']['success'] . ' email berhasil dikirim, '
                . $results['summary']['failed'] . ' gagal.',
            'data'    => $results,
        ], 200);
    }

    // GET /api/email/history
    public function history(Request $request): JsonResponse
    {
        $query = EmailHistory::with([
            'salarySlip.period:id,period_name',
            'employee:id,full_name,employee_code',
            'sender:id,name',
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('period_id')) {
            $query->whereHas('salarySlip', function ($q) use ($request) {
                $q->where('period_id', $request->period_id);
            });
        }

        $histories = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pengiriman email berhasil diambil.',
            'data'    => $histories->map(function ($history) {
                return [
                    'id'         => $history->id,
                    'slip_id'    => $history->salary_slip_id,
                    'period'     => $history->salarySlip->period->period_name ?? null,
                    'employee'   => [
                        'id'            => $history->employee->id ?? null,
                        'full_name'     => $history->employee->full_name ?? null,
                        'employee_code' => $history->employee->employee_code ?? null,
                    ],
                    'email_to'   => $history->email_to,
                    'subject'    => $history->subject,
                    'status'     => $history->status,
                    'error'      => $history->error_message,
                    'sent_at'    => $history->sent_at,
                    'sent_by'    => $history->sender->name ?? null,
                    'created_at' => $history->created_at,
                ];
            }),
            'pagination' => [
                'total'        => $histories->total(),
                'per_page'     => $histories->perPage(),
                'current_page' => $histories->currentPage(),
                'last_page'    => $histories->lastPage(),
            ],
        ], 200);
    }

    // GET /api/email/history/{salarySlip}
    public function slipHistory(SalarySlip $salarySlip): JsonResponse
    {
        $histories = EmailHistory::with('sender:id,name')
            ->where('salary_slip_id', $salarySlip->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'id'         => $history->id,
                    'email_to'   => $history->email_to,
                    'subject'    => $history->subject,
                    'status'     => $history->status,
                    'error'      => $history->error_message,
                    'sent_at'    => $history->sent_at,
                    'sent_by'    => $history->sender->name ?? null,
                    'created_at' => $history->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pengiriman email slip berhasil diambil.',
            'data'    => [
                'slip_id'   => $salarySlip->id,
                'histories' => $histories,
            ],
        ], 200);
    }

    // POST /api/email/resend/{salarySlip}
    public function resend(SalarySlip $salarySlip): JsonResponse
    {
        $salarySlip->load(['employee', 'period', 'category']);

        // Reset status slip ke draft agar bisa dikirim ulang
        $salarySlip->update(['status' => 'draft']);

        $result = $this->emailService->sendSlipEmail(
            $salarySlip,
            auth()->id()
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Slip gaji berhasil dikirim ulang ke ' . $result['email_to'],
                'data'    => $result,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim ulang slip gaji.',
            'data'    => $result,
        ], 500);
    }
}