<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailHistory;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySlip;
use App\Models\SalaryCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // GET /api/dashboard/owner
    public function owner(): JsonResponse
    {
        // Total biaya gaji bulan ini (periode open)
        $activePeriod = PayrollPeriod::where('status', 'open')
            ->latest()
            ->first();

        $totalSalaryThisPeriod = 0;
        $totalSlipsThisPeriod  = 0;
        $sentSlipsThisPeriod   = 0;

        if ($activePeriod) {
            $totalSalaryThisPeriod = SalarySlip::where('period_id', $activePeriod->id)
                ->sum('total_salary');
            $totalSlipsThisPeriod  = SalarySlip::where('period_id', $activePeriod->id)
                ->count();
            $sentSlipsThisPeriod   = SalarySlip::where('period_id', $activePeriod->id)
                ->where('status', 'sent')
                ->count();
        }

        // Total karyawan aktif
        $totalActiveEmployees = Employee::where('status', 'active')->count();

        // Ringkasan per kategori
        $categoryStats = SalaryCategory::withCount([
            'employees' => fn($q) => $q->where('status', 'active'),
        ])->get()->map(fn($cat) => [
            'category_name'    => $cat->category_name,
            'employee_count'   => $cat->employees_count,
            'base_salary'      => $cat->base_salary,
        ]);

        // Tren biaya gaji 6 bulan terakhir
        $salaryTrend = PayrollPeriod::with(['salarySlips'])
            ->where('status', 'closed')
            ->latest('end_date')
            ->take(6)
            ->get()
            ->map(fn($period) => [
                'period_name'  => $period->period_name,
                'start_date'   => $period->start_date,
                'end_date'     => $period->end_date,
                'total_salary' => $period->salarySlips->sum('total_salary'),
                'total_slips'  => $period->salarySlips->count(),
            ])
            ->sortBy('start_date')
            ->values();

        // Status pengiriman email bulan ini
        $emailStats = null;
        if ($activePeriod) {
            $emailStats = [
                'sent'    => EmailHistory::whereHas('salarySlip', fn($q) =>
                    $q->where('period_id', $activePeriod->id))
                    ->where('status', 'sent')->count(),
                'failed'  => EmailHistory::whereHas('salarySlip', fn($q) =>
                    $q->where('period_id', $activePeriod->id))
                    ->where('status', 'failed')->count(),
                'pending' => EmailHistory::whereHas('salarySlip', fn($q) =>
                    $q->where('period_id', $activePeriod->id))
                    ->where('status', 'pending')->count(),
            ];
        }

        // Periode terbaru (5 terakhir)
        $recentPeriods = PayrollPeriod::latest('start_date')
            ->take(5)
            ->get()
            ->map(fn($period) => [
                'id'          => $period->id,
                'period_name' => $period->period_name,
                'start_date'  => $period->start_date,
                'end_date'    => $period->end_date,
                'status'      => $period->status,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard owner berhasil diambil.',
            'data'    => [
                'active_period'   => $activePeriod ? [
                    'id'          => $activePeriod->id,
                    'period_name' => $activePeriod->period_name,
                    'start_date'  => $activePeriod->start_date,
                    'end_date'    => $activePeriod->end_date,
                ] : null,
                'summary'         => [
                    'total_active_employees'  => $totalActiveEmployees,
                    'total_salary_this_period'=> (float) $totalSalaryThisPeriod,
                    'total_slips_this_period' => $totalSlipsThisPeriod,
                    'sent_slips_this_period'  => $sentSlipsThisPeriod,
                    'draft_slips_this_period' => $totalSlipsThisPeriod - $sentSlipsThisPeriod,
                ],
                'category_stats'  => $categoryStats,
                'salary_trend'    => $salaryTrend,
                'email_stats'     => $emailStats,
                'recent_periods'  => $recentPeriods,
            ],
        ], 200);
    }

    // GET /api/dashboard/head
    public function head(): JsonResponse
    {
        // Periode aktif
        $activePeriod = PayrollPeriod::where('status', 'open')
            ->latest()
            ->first();

        $totalSlips  = 0;
        $sentSlips   = 0;
        $draftSlips  = 0;

        if ($activePeriod) {
            $totalSlips = SalarySlip::where('period_id', $activePeriod->id)->count();
            $sentSlips  = SalarySlip::where('period_id', $activePeriod->id)
                ->where('status', 'sent')->count();
            $draftSlips = $totalSlips - $sentSlips;
        }

        // Total karyawan
        $totalEmployees  = Employee::where('status', 'active')->count();

        // Karyawan per kategori
        $employeeByCategory = SalaryCategory::withCount([
            'employees' => fn($q) => $q->where('status', 'active'),
        ])->get()->map(fn($cat) => [
            'category_name'  => $cat->category_name,
            'employee_count' => $cat->employees_count,
        ]);

        // Slip gaji terbaru di periode aktif
        $recentSlips = [];
        if ($activePeriod) {
            $recentSlips = SalarySlip::with([
                'employee:id,full_name,employee_code',
                'category:id,category_name',
            ])
            ->where('period_id', $activePeriod->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($slip) => [
                'id'           => $slip->id,
                'employee'     => $slip->employee->full_name ?? null,
                'category'     => $slip->category->category_name ?? null,
                'total_salary' => $slip->total_salary,
                'status'       => $slip->status,
                'created_at'   => $slip->created_at,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard kepala toko berhasil diambil.',
            'data'    => [
                'active_period'        => $activePeriod ? [
                    'id'          => $activePeriod->id,
                    'period_name' => $activePeriod->period_name,
                    'start_date'  => $activePeriod->start_date,
                    'end_date'    => $activePeriod->end_date,
                ] : null,
                'summary'              => [
                    'total_employees'  => $totalEmployees,
                    'total_slips'      => $totalSlips,
                    'sent_slips'       => $sentSlips,
                    'draft_slips'      => $draftSlips,
                ],
                'employee_by_category' => $employeeByCategory,
                'recent_slips'         => $recentSlips,
            ],
        ], 200);
    }

    // GET /api/dashboard/admin
    public function admin(): JsonResponse
    {
        // Periode aktif
        $activePeriod = PayrollPeriod::where('status', 'open')
            ->latest()
            ->first();

        $mySlips     = 0;
        $sentSlips   = 0;
        $draftSlips  = 0;
        $totalSalary = 0;

        if ($activePeriod) {
            $mySlips    = SalarySlip::where('period_id', $activePeriod->id)
                ->where('created_by', auth()->id())
                ->count();
            $sentSlips  = SalarySlip::where('period_id', $activePeriod->id)
                ->where('created_by', auth()->id())
                ->where('status', 'sent')
                ->count();
            $draftSlips  = $mySlips - $sentSlips;
            $totalSalary = SalarySlip::where('period_id', $activePeriod->id)
                ->where('created_by', auth()->id())
                ->sum('total_salary');
        }

        // Slip terbaru yang dibuat admin ini
        $recentSlips = SalarySlip::with([
            'employee:id,full_name,employee_code',
            'period:id,period_name',
        ])
        ->where('created_by', auth()->id())
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($slip) => [
            'id'           => $slip->id,
            'employee'     => $slip->employee->full_name ?? null,
            'period'       => $slip->period->period_name ?? null,
            'total_salary' => $slip->total_salary,
            'status'       => $slip->status,
            'created_at'   => $slip->created_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard admin berhasil diambil.',
            'data'    => [
                'active_period' => $activePeriod ? [
                    'id'          => $activePeriod->id,
                    'period_name' => $activePeriod->period_name,
                    'start_date'  => $activePeriod->start_date,
                    'end_date'    => $activePeriod->end_date,
                ] : null,
                'summary'       => [
                    'my_slips_this_period'    => $mySlips,
                    'sent_slips'              => $sentSlips,
                    'draft_slips'             => $draftSlips,
                    'total_salary_processed'  => (float) $totalSalary,
                ],
                'recent_slips'  => $recentSlips,
            ],
        ], 200);
    }
}