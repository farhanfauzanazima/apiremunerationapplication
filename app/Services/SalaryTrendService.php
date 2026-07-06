<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\PayrollPeriod;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;

class SalaryTrendService
{
    public function build(?array $allowedBranchIds, int $limitPeriods = 6): array
    {
        $periods = PayrollPeriod::orderByDesc('year')->orderByDesc('month')
            ->limit($limitPeriods)->get()->reverse()->values();

        $branches = Branch::when($allowedBranchIds !== null, fn ($q) => $q->whereIn('id', $allowedBranchIds))->get();

        $overall = $periods->map(fn ($period) => [
            'period' => $period->name,
            'total' => $this->totalForPeriod($period->id, $allowedBranchIds),
        ])->values()->all();

        $perBranch = [];
        foreach ($branches as $branch) {
            $perBranch[$branch->name] = $periods->map(fn ($period) => [
                'period' => $period->name,
                'total' => $this->totalForPeriod($period->id, [$branch->id]),
            ])->values()->all();
        }

        return ['overall' => $overall, 'per_branch' => $perBranch];
    }

    protected function totalForPeriod(int $periodId, ?array $branchIds): int
    {
        $tetap = SalarySlipTetap::where('payroll_period_id', $periodId)
            ->whereHas('employee', fn ($q) => $branchIds !== null ? $q->whereIn('branch_id', $branchIds) : $q)
            ->sum('total_gaji');

        $partime = SalarySlipPartime::where('payroll_period_id', $periodId)
            ->whereHas('employee', fn ($q) => $branchIds !== null ? $q->whereIn('branch_id', $branchIds) : $q)
            ->sum('total_fee');

        return (int) ($tetap + $partime);
    }
}