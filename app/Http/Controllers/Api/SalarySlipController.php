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
        protected \App\Services\PDFService $pdfService,
        protected \App\Services\PublicLinkService $publicLinkService,
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

    /**
     * Filter yang didukung (query string, semua opsional):
     * - payroll_period_id
     * - employee_search  (cari nama karyawan)
     * - status           (aktif|nonaktif, status karyawan)
     * - branch_id
     * - tenure           (6_months|1_year)
     * - page_tetap, page_partime (pagination terpisah per tabel)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $allowedBranches = $user->allowedBranchIds();

        $applyFilters = function ($query) use ($request, $allowedBranches) {
            $query->whereHas('employee', function ($q) use ($request, $allowedBranches) {
                if ($allowedBranches !== null) {
                    $q->whereIn('branch_id', $allowedBranches);
                }
                if ($request->filled('employee_search')) {
                    $q->where('name', 'like', '%' . $request->input('employee_search') . '%');
                }
                if ($request->filled('status')) {
                    $q->where('status', $request->input('status'));
                }
                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->input('branch_id'));
                }
                if ($request->filled('tenure')) {
                    $threshold = match ($request->input('tenure')) {
                        '6_months' => now()->subMonths(6),
                        '1_year' => now()->subYear(),
                        default => null,
                    };
                    if ($threshold) {
                        $q->where('join_date', '<=', $threshold);
                    }
                }
            });

            if ($request->filled('payroll_period_id')) {
                $query->where('payroll_period_id', $request->input('payroll_period_id'));
            }

            return $query;
        };

        $tetap = $applyFilters(
            \App\Models\SalarySlipTetap::with(['employee.branch', 'employee.position', 'payrollPeriod'])
        )->orderByDesc('id')->paginate(20, ['*'], 'page_tetap');

        $partime = $applyFilters(
            \App\Models\SalarySlipPartime::with(['employee.branch', 'employee.position', 'payrollPeriod'])
        )->orderByDesc('id')->paginate(20, ['*'], 'page_partime');

        return response()->json([
            'success' => true,
            'message' => 'Daftar slip gaji berhasil diambil',
            'data' => [
                'tetap' => $tetap->items(),
                'partime' => $partime->items(),
            ],
            'raw' => [
                'pagination' => [
                    'tetap' => ['current_page' => $tetap->currentPage(), 'last_page' => $tetap->lastPage(), 'total' => $tetap->total()],
                    'partime' => ['current_page' => $partime->currentPage(), 'last_page' => $partime->lastPage(), 'total' => $partime->total()],
                ],
            ],
        ]);
    }

    public function show(Request $request, string $type, int $id)
    {
        $slip = $this->resolveSlip($type, $id);
        $this->authorizeSlipAccess($request, $slip);

        return response()->json([
            'success' => true,
            'message' => 'Detail slip gaji berhasil diambil',
            'data' => $slip->load(['employee.branch', 'employee.position', 'payrollPeriod']),
        ]);
    }

    public function updateTetap(\App\Http\Requests\SalarySlip\UpdateSalarySlipTetapRequest $request, int $id)
    {
        $slip = \App\Models\SalarySlipTetap::with('employee', 'payrollPeriod')->findOrFail($id);
        $this->authorizeSlipAccess($request, $slip);

        $setting = \App\Models\SalarySetting::current();
        $oldData = $slip->toArray();

        $calculated = $this->calculationService->calculateTetap($slip->employee, $request->validated(), $setting, $slip->payrollPeriod);
        $slip->update($calculated);

        $this->activityLogService->log($request->user(), 'salary_slip', 'update', $oldData, $slip->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji berhasil diperbarui',
            'data' => $slip->fresh(['employee', 'payrollPeriod']),
        ]);
    }

    public function updatePartime(\App\Http\Requests\SalarySlip\UpdateSalarySlipPartimeRequest $request, int $id)
    {
        $slip = \App\Models\SalarySlipPartime::with('employee')->findOrFail($id);
        $this->authorizeSlipAccess($request, $slip);

        $setting = \App\Models\SalarySetting::current();
        $oldData = $slip->toArray();

        $calculated = $this->calculationService->calculatePartime($slip->employee, $request->validated(), $setting);
        $slip->update($calculated);

        $this->activityLogService->log($request->user(), 'salary_slip', 'update', $oldData, $slip->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji berhasil diperbarui',
            'data' => $slip->fresh(['employee', 'payrollPeriod']),
        ]);
    }

    public function destroy(Request $request, string $type, int $id)
    {
        $slip = $this->resolveSlip($type, $id);
        $this->authorizeSlipAccess($request, $slip);

        $oldData = $slip->toArray();
        $slip->delete();

        $this->activityLogService->log($request->user(), 'salary_slip', 'delete', $oldData, null);

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji berhasil dihapus',
        ]);
    }

    protected function resolveSlip(string $type, int $id)
    {
        return match ($type) {
            'tetap' => \App\Models\SalarySlipTetap::with('employee')->findOrFail($id),
            'partime' => \App\Models\SalarySlipPartime::with('employee')->findOrFail($id),
            default => abort(404, 'Tipe slip gaji tidak valid'),
        };
    }

    protected function authorizeSlipAccess(Request $request, $slip): void
    {
        if (!$request->user()->canAccessBranch($slip->employee->branch_id)) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji cabang ini');
        }
    }

    public function previewPDF(Request $request, string $type, int $id)
    {
        $slip = $this->resolveSlip($type, $id);
        $this->authorizeSlipAccess($request, $slip);

        $pdf = $this->pdfService->renderSalarySlip($slip, $type, $request->user()->name);

        return $pdf->stream("slip-gaji-{$slip->employee->name}.pdf");
    }

    public function downloadPDF(Request $request, string $type, int $id)
    {
        $slip = $this->resolveSlip($type, $id);
        $this->authorizeSlipAccess($request, $slip);

        $pdf = $this->pdfService->renderSalarySlip($slip, $type, $request->user()->name);

        return $pdf->download("slip-gaji-{$slip->employee->name}.pdf");
    }

    public function generatePublicLink(Request $request, string $type, int $id)
    {
        $slip = $this->resolveSlip($type, $id);
        $this->authorizeSlipAccess($request, $slip);

        $token = $this->publicLinkService->getOrCreateToken($slip, $type);

        return response()->json([
            'success' => true,
            'message' => 'Link publik berhasil dibuat',
            'data' => [
                'token' => $token,
                'url' => url("/public/slip/{$token}"),
            ],
        ]);
    }
}