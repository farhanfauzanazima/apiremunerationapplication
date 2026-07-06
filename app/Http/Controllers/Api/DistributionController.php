<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\SendBulkDistributionRequest;
use App\Jobs\SendSalarySlipEmailJob;
use App\Jobs\SendSalarySlipWhatsappJob;
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
        $queued = 0;
        $skipped = [];

        foreach ($request->validated('items') as $item) {
            $slipClass = $item['type'] === 'tetap' ? SalarySlipTetap::class : SalarySlipPartime::class;
            $slip = $slipClass::with('employee')->find($item['id']);

            if (!$slip || !$user->canAccessBranch($slip->employee->branch_id)) {
                $skipped[] = $item;
                continue;
            }

            // Catat status "pending" dulu supaya langsung terlihat di Riwayat Distribusi
            DistributionHistory::firstOrCreate(
                ['slip_id' => $slip->id, 'slip_type' => $slipClass, 'channel' => $channel],
                ['status' => 'pending']
            );

            if ($channel === 'email') {
                SendSalarySlipEmailJob::dispatch($item['type'], $item['id'], $user->name);
            } else {
                SendSalarySlipWhatsappJob::dispatch($item['type'], $item['id'], $user->id);
            }

            $queued++;
        }

        $this->activityLogService->log($user, 'distribution', "queue_bulk_{$channel}", null, ['total_queued' => $queued]);

        $channelLabel = $channel === 'email' ? 'Email' : 'WhatsApp';

        return response()->json([
            'success' => true,
            'message' => "{$queued} slip gaji sedang diproses untuk dikirim via {$channelLabel}. Cek Riwayat Distribusi beberapa saat lagi untuk melihat status terkini.",
            'data' => [
                'queued' => $queued,
                'skipped' => $skipped,
            ],
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