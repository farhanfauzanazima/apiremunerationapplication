<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\SendBulkDistributionRequest;
use App\Models\DistributionHistory;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use App\Services\ActivityLogService;
use App\Services\DistributionService;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    public function __construct(
        protected DistributionService $distributionService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function sendBulk(SendBulkDistributionRequest $request)
    {
        $user = $request->user();
        $channel = $request->validated('channel');
        $results = [];

        foreach ($request->validated('items') as $item) {
            $slip = $item['type'] === 'tetap'
                ? SalarySlipTetap::with(['employee.branch', 'payrollPeriod'])->find($item['id'])
                : SalarySlipPartime::with(['employee.branch', 'payrollPeriod'])->find($item['id']);

            if (!$slip || !$user->canAccessBranch($slip->employee->branch_id)) {
                $results[] = ['id' => $item['id'], 'type' => $item['type'], 'success' => false, 'message' => 'Tidak ditemukan atau tidak memiliki akses'];
                continue;
            }

            $distribution = $channel === 'email'
                ? $this->distributionService->sendEmail($slip, $item['type'], $user->name)
                : $this->distributionService->sendWhatsapp($slip, $item['type'], $user->id);

            $results[] = [
                'id' => $item['id'],
                'type' => $item['type'],
                'employee' => $slip->employee->name,
                'success' => $distribution->status === 'sent',
                'message' => $distribution->note ?? 'Terkirim',
            ];
        }

        $this->activityLogService->log($user, 'distribution', "send_bulk_{$channel}", null, ['total' => count($results)]);

        return response()->json([
            'success' => true,
            'message' => 'Proses distribusi selesai',
            'data' => $results,
        ]);
    }

    public function history(Request $request)
    {
        $query = DistributionHistory::with('sentBy')->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        $histories = $query->paginate(30);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat distribusi berhasil diambil',
            'data' => $histories->items()->map(function ($h) {
                return array_merge($h->toArray(), [
                    'employee_name' => optional($h->slip?->employee)->name,
                ]);
            }),
        ]);
    }
}