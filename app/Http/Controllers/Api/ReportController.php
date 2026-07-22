<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use App\Services\FinanceReportService;
use App\Services\PDFService;
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

    public function financeSummary(Request $request, FinanceReportService $financeReportService)
    {
        $request->validate([
            'payroll_period_id' => ['required', 'exists:payroll_periods,id'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $branchId = (int) $request->input('branch_id');

        if (!$request->user()->canAccessBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini');
        }

        $branch = \App\Models\Branch::findOrFail($branchId);
        $period = \App\Models\PayrollPeriod::findOrFail($request->input('payroll_period_id'));

        $data = $financeReportService->build($branch, $period);

        return response()->json([
            'success' => true,
            'message' => 'Laporan keuangan berhasil diambil',
            'data' => $data,
        ]);
    }

    public function financeSummaryPreviewPdf(Request $request, FinanceReportService $financeReportService, PDFService $pdfService)
    {
        [$branch, $period, $data] = $this->resolveFinanceReportRequest($request, $financeReportService);

        $pdf = $pdfService->renderFinanceReport($data);

        return $pdf->stream($this->financeReportFilename($branch, $period));
    }

    public function financeSummaryDownloadPdf(Request $request, FinanceReportService $financeReportService, PDFService $pdfService)
    {
        [$branch, $period, $data] = $this->resolveFinanceReportRequest($request, $financeReportService);

        $pdf = $pdfService->renderFinanceReport($data);

        return $pdf->download($this->financeReportFilename($branch, $period));
    }

    protected function resolveFinanceReportRequest(Request $request, FinanceReportService $financeReportService): array
    {
        $request->validate([
            'payroll_period_id' => ['required', 'exists:payroll_periods,id'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $branchId = (int) $request->input('branch_id');

        if (!$request->user()->canAccessBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini');
        }

        $branch = \App\Models\Branch::findOrFail($branchId);
        $period = \App\Models\PayrollPeriod::findOrFail($request->input('payroll_period_id'));

        return [$branch, $period, $financeReportService->build($branch, $period)];
    }

    protected function financeReportFilename($branch, $period): string
    {
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulan = $bulanIndo[$period->month] ?? $period->month;

        return "Laporan HR untuk Keuangan Periode {$bulan} {$period->year} - {$branch->name}.pdf";
    }

    public function financeSummaryDownloadExcel(Request $request, FinanceReportService $financeReportService)
    {
        [$branch, $period, $data] = $this->resolveFinanceReportRequest($request, $financeReportService);

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulan = $bulanIndo[$period->month] ?? $period->month;
        $filename = "Laporan HR untuk Keuangan Periode {$bulan} {$period->year} - {$branch->name}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\FinanceReportExport($data),
            $filename
        );
    }
}