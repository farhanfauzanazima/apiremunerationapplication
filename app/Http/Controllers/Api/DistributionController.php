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
        $query = DistributionHistory::with(['sentBy', 'slip.employee'])->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        $histories = $query->paginate(30);

        $data = collect($histories->items())->map(function ($h) {
            return array_merge($h->toArray(), [
                'employee_name' => optional($h->slip?->employee)->name,
            ]);
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat distribusi berhasil diambil',
            'data' => $data,
        ]);
    }

    public function resend(Request $request, int $id)
    {
        $distribution = \App\Models\DistributionHistory::findOrFail($id);
        $slip = $distribution->slip;

        if (!$slip || !$request->user()->canAccessBranch($slip->employee->branch_id)) {
            abort(403, 'Anda tidak memiliki akses untuk mengirim ulang distribusi ini');
        }

        $type = $distribution->slip_type === \App\Models\SalarySlipTetap::class ? 'tetap' : 'partime';

        $distribution->update(['status' => 'pending', 'note' => null]);

        if ($distribution->channel === 'email') {
            \App\Jobs\SendSalarySlipEmailJob::dispatch($type, $slip->id, $request->user()->name);
        } else {
            \App\Jobs\SendSalarySlipWhatsappJob::dispatch($type, $slip->id, $request->user()->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Distribusi sedang dikirim ulang. Pastikan queue worker (php artisan queue:work) sedang berjalan.',
        ]);
    }
}