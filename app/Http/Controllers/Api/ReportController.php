<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // GET /api/reports/salary-summary
    // Rekap gaji per periode
    public function salarySummary(Request $request): JsonResponse
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::findOrFail($request->period_id);

        $slips = SalarySlip::with([
            'employee:id,full_name,employee_code,email',
            'category:id,category_name',
        ])
        ->where('period_id', $request->period_id)
        ->get();

        // Hitung ringkasan
        $summary = [
            'total_employees'        => $slips->count(),
            'total_salary'           => $slips->sum('total_salary'),
            'total_base_salary'      => $slips->sum('base_salary_amount'),
            'total_allowance'        => $slips->sum('allowance_amount'),
            'total_bonus'            => $slips->sum('bonus'),
            'total_late_penalty'     => $slips->sum('late_penalty_amount'),
            'total_deduction'        => $slips->sum('additional_deduction'),
            'total_sent'             => $slips->where('status', 'sent')->count(),
            'total_draft'            => $slips->where('status', 'draft')->count(),
        ];

        // Data per karyawan
        $employees = $slips->map(fn($slip) => [
            'slip_id'              => $slip->id,
            'employee_code'        => $slip->employee->employee_code ?? null,
            'full_name'            => $slip->employee->full_name ?? null,
            'email'                => $slip->employee->email ?? null,
            'category'             => $slip->category->category_name ?? null,
            'total_working_days'   => $slip->total_working_days,
            'late_count'           => $slip->late_count,
            'base_salary_amount'   => $slip->base_salary_amount,
            'allowance_amount'     => $slip->allowance_amount,
            'bonus'                => $slip->bonus,
            'late_penalty_amount'  => $slip->late_penalty_amount,
            'additional_deduction' => $slip->additional_deduction,
            'total_salary'         => $slip->total_salary,
            'status'               => $slip->status,
        ]);

        // Ringkasan per kategori
        $byCategory = $slips->groupBy('category_id')->map(function ($group) {
            $first = $group->first();
            return [
                'category_name'  => $first->category->category_name ?? null,
                'total_employee' => $group->count(),
                'total_salary'   => $group->sum('total_salary'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Laporan penggajian berhasil diambil.',
            'data'    => [
                'period'      => [
                    'id'          => $period->id,
                    'period_name' => $period->period_name,
                    'start_date'  => $period->start_date,
                    'end_date'    => $period->end_date,
                    'status'      => $period->status,
                ],
                'summary'     => $summary,
                'by_category' => $byCategory,
                'employees'   => $employees,
            ],
        ], 200);
    }

    // GET /api/reports/salary-summary/export-pdf
    // Export laporan ke PDF
    public function exportPDF(Request $request): mixed
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::findOrFail($request->period_id);

        $slips = SalarySlip::with([
            'employee:id,full_name,employee_code,email',
            'category:id,category_name',
        ])
        ->where('period_id', $request->period_id)
        ->get();

        $summary = [
            'total_employees'    => $slips->count(),
            'total_salary'       => $slips->sum('total_salary'),
            'total_base_salary'  => $slips->sum('base_salary_amount'),
            'total_allowance'    => $slips->sum('allowance_amount'),
            'total_bonus'        => $slips->sum('bonus'),
            'total_late_penalty' => $slips->sum('late_penalty_amount'),
            'total_deduction'    => $slips->sum('additional_deduction'),
        ];

        $pdf = Pdf::loadView('pdf.salary-report', compact('period', 'slips', 'summary'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-gaji-' . str_replace(' ', '-', strtolower($period->period_name)) . '.pdf');
    }

    // GET /api/reports/employee/{employee}
    // Laporan detail per karyawan
    public function employeeReport(Request $request, $employeeId): JsonResponse
    {
        $employee = Employee::with('category:id,category_name')->findOrFail($employeeId);

        $query = SalarySlip::with('period:id,period_name,start_date,end_date')
            ->where('employee_id', $employeeId);

        // Filter by tahun jika ada
        if ($request->has('year')) {
            $query->whereHas('period', function ($q) use ($request) {
                $q->whereYear('start_date', $request->year);
            });
        }

        $slips = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Laporan karyawan berhasil diambil.',
            'data'    => [
                'employee'       => [
                    'id'            => $employee->id,
                    'full_name'     => $employee->full_name,
                    'employee_code' => $employee->employee_code,
                    'email'         => $employee->email,
                    'category'      => $employee->category->category_name ?? null,
                    'status'        => $employee->status,
                ],
                'summary'        => [
                    'total_periods'  => $slips->count(),
                    'total_received' => $slips->sum('total_salary'),
                    'average_salary' => $slips->count() > 0
                        ? round($slips->sum('total_salary') / $slips->count(), 2)
                        : 0,
                ],
                'salary_history' => $slips->map(fn($slip) => [
                    'slip_id'            => $slip->id,
                    'period'             => $slip->period->period_name ?? null,
                    'start_date'         => $slip->period->start_date ?? null,
                    'end_date'           => $slip->period->end_date ?? null,
                    'total_working_days' => $slip->total_working_days,
                    'late_count'         => $slip->late_count,
                    'base_salary'        => $slip->base_salary_amount,
                    'allowance'          => $slip->allowance_amount,
                    'bonus'              => $slip->bonus,
                    'late_penalty'       => $slip->late_penalty_amount,
                    'deduction'          => $slip->additional_deduction,
                    'total_salary'       => $slip->total_salary,
                    'status'             => $slip->status,
                ]),
            ],
        ], 200);
    }

    // GET /api/reports/statistics
    // Statistik tren gaji
    public function statistics(Request $request): JsonResponse
    {
        // Tren 12 bulan terakhir
        $trend = PayrollPeriod::with('salarySlips')
            ->latest('start_date')
            ->take(12)
            ->get()
            ->map(fn($period) => [
                'period_name'    => $period->period_name,
                'start_date'     => $period->start_date,
                'total_salary'   => $period->salarySlips->sum('total_salary'),
                'total_employee' => $period->salarySlips->count(),
                'avg_salary'     => $period->salarySlips->count() > 0
                    ? round($period->salarySlips->sum('total_salary') / $period->salarySlips->count(), 2)
                    : 0,
            ])
            ->sortBy('start_date')
            ->values();

        // Distribusi per kategori (periode aktif atau terbaru)
        $latestPeriod = PayrollPeriod::latest('start_date')->first();
        $categoryDist = [];

        if ($latestPeriod) {
            $categoryDist = SalarySlip::with('category:id,category_name')
                ->where('period_id', $latestPeriod->id)
                ->get()
                ->groupBy('category_id')
                ->map(function ($group) {
                    return [
                        'category_name'  => $group->first()->category->category_name ?? null,
                        'total_employee' => $group->count(),
                        'total_salary'   => $group->sum('total_salary'),
                        'avg_salary'     => round($group->sum('total_salary') / $group->count(), 2),
                    ];
                })
                ->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Statistik berhasil diambil.',
            'data'    => [
                'salary_trend'         => $trend,
                'category_distribution'=> $categoryDist,
            ],
        ], 200);
    }
}