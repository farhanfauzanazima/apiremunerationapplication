<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalarySlip\BulkGenerateSalarySlipRequest;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySetting;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use App\Services\ActivityLogService;
use App\Services\SalaryCalculationService;
use Illuminate\Http\Request;

class SalarySlipController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $calculationService,
        protected ActivityLogService $activityLogService,
    ) {}

    /**
     * Data untuk halaman input massal: daftar karyawan tetap & partime
     * di cabang tertentu, digabung dengan slip yang sudah ada (jika sudah pernah diisi).
     */
    public function bulkData(Request $request)
    {
        $request->validate([
            'payroll_period_id' => ['required', 'exists:payroll_periods,id'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        if (!$request->user()->canAccessBranch((int) $request->input('branch_id'))) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini');
        }

        $periodId = $request->input('payroll_period_id');
        $branchId = $request->input('branch_id');

        $employeesTetap = Employee::where('branch_id', $branchId)
            ->where('employee_type', 'tetap')
            ->where('status', 'aktif')
            ->with(['position', 'slipTetapForPeriod' => fn ($q) => $q->where('payroll_period_id', $periodId)])
            ->orderBy('name')
            ->get();

        $employeesPartime = Employee::where('branch_id', $branchId)
            ->where('employee_type', 'partime')
            ->where('status', 'aktif')
            ->with(['position', 'slipPartimeForPeriod' => fn ($q) => $q->where('payroll_period_id', $periodId)])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data input massal berhasil diambil',
            'data' => [
                'employees_tetap' => $employeesTetap,
                'employees_partime' => $employeesPartime,
                'setting' => SalarySetting::current(),
            ],
        ]);
    }

    public function bulkGenerate(BulkGenerateSalarySlipRequest $request)
    {
        if (!$request->user()->canAccessBranch((int) $request->validated('branch_id'))) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini');
        }

        $period = PayrollPeriod::findOrFail($request->validated('payroll_period_id'));
        $setting = SalarySetting::current();

        $resultTetap = [];
        $resultPartime = [];

        foreach ($request->input('tetap', []) as $row) {
            $employee = Employee::findOrFail($row['employee_id']);
            $calculated = $this->calculationService->calculateTetap($employee, $row, $setting, $period);

            $slip = SalarySlipTetap::updateOrCreate(
                ['employee_id' => $employee->id, 'payroll_period_id' => $period->id],
                $calculated
            );

            $resultTetap[] = $slip;
        }

        foreach ($request->input('partime', []) as $row) {
            $employee = Employee::findOrFail($row['employee_id']);
            $calculated = $this->calculationService->calculatePartime($employee, $row, $setting);

            $slip = SalarySlipPartime::updateOrCreate(
                ['employee_id' => $employee->id, 'payroll_period_id' => $period->id],
                $calculated
            );

            $resultPartime[] = $slip;
        }

        $this->activityLogService->log(
            $request->user(),
            'salary_slip',
            'bulk_generate',
            null,
            ['payroll_period_id' => $period->id, 'branch_id' => $request->validated('branch_id'), 'total_tetap' => count($resultTetap), 'total_partime' => count($resultPartime)]
        );

        return response()->json([
            'success' => true,
            'message' => count($resultTetap) + count($resultPartime) . ' slip gaji berhasil disimpan',
            'data' => [
                'tetap' => $resultTetap,
                'partime' => $resultPartime,
            ],
        ]);
    }
}