<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributionHistory;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $allowed = $user->allowedBranchIds(); // null = semua cabang

        $employeeQuery = Employee::query();
        if ($allowed !== null) {
            $employeeQuery->whereIn('branch_id', $allowed);
        }

        $totalKaryawanAktif = (clone $employeeQuery)->where('status', 'aktif')->count();
        $totalKaryawanTetap = (clone $employeeQuery)->where('employee_type', 'tetap')->where('status', 'aktif')->count();
        $totalKaryawanPartime = (clone $employeeQuery)->where('employee_type', 'partime')->where('status', 'aktif')->count();

        $latestPeriod = PayrollPeriod::orderByDesc('year')->orderByDesc('month')->first();

        $tetapQuery = SalarySlipTetap::query()->whereHas('employee', function ($q) use ($allowed) {
            if ($allowed !== null) $q->whereIn('branch_id', $allowed);
        });
        $partimeQuery = SalarySlipPartime::query()->whereHas('employee', function ($q) use ($allowed) {
            if ($allowed !== null) $q->whereIn('branch_id', $allowed);
        });

        $totalGajiPeriodeTerakhir = 0;
        $totalSlipPeriodeTerakhir = 0;

        if ($latestPeriod) {
            $totalGajiPeriodeTerakhir = (clone $tetapQuery)->where('payroll_period_id', $latestPeriod->id)->sum('total_gaji')
                + (clone $partimeQuery)->where('payroll_period_id', $latestPeriod->id)->sum('total_fee');

            $totalSlipPeriodeTerakhir = (clone $tetapQuery)->where('payroll_period_id', $latestPeriod->id)->count()
                + (clone $partimeQuery)->where('payroll_period_id', $latestPeriod->id)->count();
        }

        $distributionQuery = DistributionHistory::query();
        $totalTerkirim = (clone $distributionQuery)->where('status', 'sent')->count();
        $totalGagal = (clone $distributionQuery)->where('status', 'failed')->count();
        $totalPending = (clone $distributionQuery)->where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => [
                'total_karyawan_aktif' => $totalKaryawanAktif,
                'total_karyawan_tetap' => $totalKaryawanTetap,
                'total_karyawan_partime' => $totalKaryawanPartime,
                'periode_terakhir' => $latestPeriod,
                'total_gaji_periode_terakhir' => $totalGajiPeriodeTerakhir,
                'total_slip_periode_terakhir' => $totalSlipPeriodeTerakhir,
                'distribusi' => [
                    'terkirim' => $totalTerkirim,
                    'gagal' => $totalGagal,
                    'pending' => $totalPending,
                ],
            ],
        ]);
    }
}