<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\DistributionHistory;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use Illuminate\Support\Collection;

class DashboardService
{
    public function build(?array $allowedBranchIds, bool $includeTrend, SalaryTrendService $trendService): array
    {
        $branches = Branch::when($allowedBranchIds !== null, fn ($q) => $q->whereIn('id', $allowedBranchIds))
            ->orderBy('name')
            ->get();

        $latestPeriod = PayrollPeriod::orderByDesc('year')->orderByDesc('month')->first();

        $branchStats = $branches->map(fn ($branch) => $this->buildForBranch($branch, $latestPeriod))->values();

        $result = [
            'periode_terakhir' => $latestPeriod,
            'branches' => $branchStats,
        ];

        if ($includeTrend) {
            $result['trend'] = $trendService->build($allowedBranchIds);
        }

        return $result;
    }

    protected function buildForBranch(Branch $branch, ?PayrollPeriod $latestPeriod): array
    {
        $employeeQuery = Employee::where('branch_id', $branch->id);

        $karyawanAktif = (clone $employeeQuery)->where('status', 'aktif')->count();
        $karyawanTetap = (clone $employeeQuery)->where('employee_type', 'tetap')->where('status', 'aktif')->count();
        $karyawanPartime = (clone $employeeQuery)->where('employee_type', 'partime')->where('status', 'aktif')->count();

        $totalGaji = 0;
        $totalSlip = 0;

        if ($latestPeriod) {
            $tetapSlips = SalarySlipTetap::where('payroll_period_id', $latestPeriod->id)
                ->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id))
                ->get(['id', 'total_gaji']);

            $partimeSlips = SalarySlipPartime::where('payroll_period_id', $latestPeriod->id)
                ->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id))
                ->get(['id', 'total_fee']);

            $totalGaji = $tetapSlips->sum('total_gaji') + $partimeSlips->sum('total_fee');
            $totalSlip = $tetapSlips->count() + $partimeSlips->count();
        }

        $distribusi = $this->distributionStatsForBranch($branch->id);

        return [
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'karyawan_aktif' => $karyawanAktif,
            'karyawan_tetap' => $karyawanTetap,
            'karyawan_partime' => $karyawanPartime,
            'total_gaji_periode_terakhir' => (int) $totalGaji,
            'total_slip_periode_terakhir' => $totalSlip,
            'distribusi' => $distribusi,
        ];
    }

    protected function distributionStatsForBranch(int $branchId): array
    {
        $default = ['terkirim' => 0, 'gagal' => 0, 'pending' => 0];

        $tetapIds = SalarySlipTetap::whereHas('employee', fn ($q) => $q->where('branch_id', $branchId))->pluck('id');
        $partimeIds = SalarySlipPartime::whereHas('employee', fn ($q) => $q->where('branch_id', $branchId))->pluck('id');

        if ($tetapIds->isEmpty() && $partimeIds->isEmpty()) {
            return $default;
        }

        $counts = DistributionHistory::query()
            ->where(function ($q) use ($tetapIds, $partimeIds) {
                $q->where(function ($q2) use ($tetapIds) {
                    $q2->where('slip_type', SalarySlipTetap::class)->whereIn('slip_id', $tetapIds);
                })->orWhere(function ($q2) use ($partimeIds) {
                    $q2->where('slip_type', SalarySlipPartime::class)->whereIn('slip_id', $partimeIds);
                });
            })
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'terkirim' => (int) ($counts['sent'] ?? 0),
            'gagal' => (int) ($counts['failed'] ?? 0),
            'pending' => (int) ($counts['pending'] ?? 0),
        ];
    }
}