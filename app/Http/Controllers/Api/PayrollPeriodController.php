<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollPeriod\StorePayrollPeriodRequest;
use App\Http\Requests\PayrollPeriod\UpdatePayrollPeriodRequest;
use App\Models\PayrollPeriod;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function index(Request $request)
    {
        $query = PayrollPeriod::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        $periods = $query->orderByDesc('year')->orderByDesc('month')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar periode penggajian berhasil diambil',
            'data' => $periods,
        ]);
    }

    public function show(PayrollPeriod $payrollPeriod)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail periode penggajian berhasil diambil',
            'data' => $payrollPeriod,
        ]);
    }

    public function store(StorePayrollPeriodRequest $request)
    {
        $period = PayrollPeriod::create($request->validated());

        $this->activityLogService->log($request->user(), 'payroll_period', 'create', null, $period->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil ditambahkan',
            'data' => $period,
        ], 201);
    }

    public function update(UpdatePayrollPeriodRequest $request, PayrollPeriod $payrollPeriod)
    {
        $oldData = $payrollPeriod->toArray();
        $payrollPeriod->update($request->validated());

        $this->activityLogService->log($request->user(), 'payroll_period', 'update', $oldData, $payrollPeriod->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil diperbarui',
            'data' => $payrollPeriod->fresh(),
        ]);
    }

    public function destroy(Request $request, PayrollPeriod $payrollPeriod)
    {
        if ($payrollPeriod->salarySlipsTetap()->exists() || $payrollPeriod->salarySlipsPartime()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak dapat dihapus karena sudah memiliki data slip gaji',
            ], 422);
        }

        $oldData = $payrollPeriod->toArray();
        $payrollPeriod->delete();

        $this->activityLogService->log($request->user(), 'payroll_period', 'delete', $oldData, null);

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil dihapus',
        ]);
    }
}