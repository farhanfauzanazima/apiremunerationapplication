<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\PayrollPeriod;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;

class FinanceReportService
{
    public function build(Branch $branch, PayrollPeriod $period): array
    {
        $tetapSlips = SalarySlipTetap::with(['employee.position'])
            ->where('payroll_period_id', $period->id)
            ->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id))
            ->join('employees', 'employees.id', '=', 'salary_slip_tetap.employee_id')
            ->orderBy('employees.name')
            ->select('salary_slip_tetap.*')
            ->get();

        $partimeSlips = SalarySlipPartime::with(['employee.position'])
            ->where('payroll_period_id', $period->id)
            ->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id))
            ->join('employees', 'employees.id', '=', 'salary_slip_partime.employee_id')
            ->orderBy('employees.name')
            ->select('salary_slip_partime.*')
            ->get();

        $totals = [
            'total_karyawan_tetap' => $tetapSlips->count(),
            'total_karyawan_partime' => $partimeSlips->count(),
            'total_tabungan' => (int) $tetapSlips->sum('tabungan'),
            'total_thp_tetap' => (int) $tetapSlips->sum('thp'),
            'total_gaji_tetap' => (int) $tetapSlips->sum('total_gaji'),
            'total_fee_partime' => (int) $partimeSlips->sum('total_fee'),
            'total_keseluruhan' => (int) ($tetapSlips->sum('total_gaji') + $partimeSlips->sum('total_fee')),
        ];

        return [
            'branch' => $branch,
            'period' => $period,
            'tetap' => $tetapSlips,
            'partime' => $partimeSlips,
            'totals' => $totals,
        ];
    }
}