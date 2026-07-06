<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected function scopedQuery($modelClass, Request $request)
    {
        $allowed = $request->user()->allowedBranchIds();

        $query = $modelClass::query()->whereHas('employee', function ($q) use ($allowed, $request) {
            if ($allowed !== null) $q->whereIn('branch_id', $allowed);
            if ($request->filled('branch_id')) $q->where('branch_id', $request->input('branch_id'));
        });

        if ($request->filled('payroll_period_id')) {
            $query->where('payroll_period_id', $request->input('payroll_period_id'));
        }

        return $query;
    }

    public function salarySummary(Request $request)
    {
        $tetap = $this->scopedQuery(SalarySlipTetap::class, $request)
            ->with('employee.branch')->get();
        $partime = $this->scopedQuery(SalarySlipPartime::class, $request)
            ->with('employee.branch')->get();

        $perCabang = [];

        foreach ($tetap as $slip) {
            $branch = $slip->employee->branch->name ?? 'Tanpa Cabang';
            $perCabang[$branch]['total_tetap'] = ($perCabang[$branch]['total_tetap'] ?? 0) + $slip->total_gaji;
            $perCabang[$branch]['jumlah_tetap'] = ($perCabang[$branch]['jumlah_tetap'] ?? 0) + 1;
        }

        foreach ($partime as $slip) {
            $branch = $slip->employee->branch->name ?? 'Tanpa Cabang';
            $perCabang[$branch]['total_partime'] = ($perCabang[$branch]['total_partime'] ?? 0) + $slip->total_fee;
            $perCabang[$branch]['jumlah_partime'] = ($perCabang[$branch]['jumlah_partime'] ?? 0) + 1;
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekap gaji berhasil diambil',
            'data' => [
                'per_cabang' => $perCabang,
                'grand_total' => $tetap->sum('total_gaji') + $partime->sum('total_fee'),
            ],
        ]);
    }

    public function statistics(Request $request)
    {
        $allowed = $request->user()->allowedBranchIds();
        $trend = app(\App\Services\SalaryTrendService::class)->build($allowed);

        return response()->json([
            'success' => true,
            'message' => 'Statistik tren gaji berhasil diambil',
            'data' => $trend,
        ]);
    }

    public function employeeReport(Request $request, int $employeeId)
    {
        $employee = Employee::with('branch', 'position')->findOrFail($employeeId);

        if (!$request->user()->canAccessBranch($employee->branch_id)) {
            abort(403, 'Anda tidak memiliki akses ke karyawan cabang ini');
        }

        $slips = $employee->employee_type === 'tetap'
            ? $employee->salarySlipsTetap()->with('payrollPeriod')->orderByDesc('id')->get()
            : $employee->salarySlipsPartime()->with('payrollPeriod')->orderByDesc('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat gaji karyawan berhasil diambil',
            'data' => [
                'employee' => $employee,
                'slips' => $slips,
            ],
        ]);
    }
}