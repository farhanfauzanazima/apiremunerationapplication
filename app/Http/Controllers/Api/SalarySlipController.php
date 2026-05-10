<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalarySlip\BulkGenerateSalarySlipRequest;
use App\Http\Requests\SalarySlip\StoreSalarySlipRequest;
use App\Http\Requests\SalarySlip\UpdateSalarySlipRequest;
use App\Models\PayrollPeriod;
use App\Models\SalarySlip;
use App\Services\SalaryCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalarySlipController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $calculationService
    ) {}

    // GET /api/salary-slips
    public function index(Request $request): JsonResponse
    {
        $query = SalarySlip::with([
            'employee:id,full_name,employee_code,email',
            'period:id,period_name,start_date,end_date',
            'category:id,category_name',
        ]);

        // Filter by periode
        if ($request->has('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $slips = $query->orderBy('created_at', 'desc')->get()
            ->map(fn($slip) => $this->formatSlip($slip));

        return response()->json([
            'success' => true,
            'message' => 'Data slip gaji berhasil diambil.',
            'data'    => $slips,
        ], 200);
    }

    // POST /api/salary-slips
    public function store(StoreSalarySlipRequest $request): JsonResponse
    {
        // Cek periode masih open
        $period = PayrollPeriod::findOrFail($request->period_id);
        if (!$period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membuat slip gaji untuk periode yang sudah ditutup.',
            ], 422);
        }

        $slip = $this->calculationService->createOrUpdateSlip(
            $request->validated(),
            auth()->id()
        );

        $slip->load([
            'employee:id,full_name,employee_code',
            'period:id,period_name',
            'category:id,category_name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji berhasil dibuat.',
            'data'    => $this->formatSlip($slip),
        ], 201);
    }

    // GET /api/salary-slips/{salary_slip}
    public function show(SalarySlip $salary_slip): JsonResponse
    {
        $salary_slip->load([
            'employee:id,full_name,employee_code,email,phone',
            'period:id,period_name,start_date,end_date',
            'category:id,category_name,base_salary,allowance,late_penalty',
            'creator:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail slip gaji berhasil diambil.',
            'data'    => $this->formatSlipDetail($salary_slip),
        ], 200);
    }

    // PUT /api/salary-slips/{salary_slip}
    public function update(UpdateSalarySlipRequest $request, SalarySlip $salary_slip): JsonResponse
    {
        // Slip yang sudah terkirim tidak bisa diedit
        if ($salary_slip->status === 'sent') {
            return response()->json([
                'success' => false,
                'message' => 'Slip gaji yang sudah terkirim tidak dapat diubah.',
            ], 422);
        }

        $data = array_merge($request->validated(), [
            'period_id'   => $salary_slip->period_id,
            'employee_id' => $salary_slip->employee_id,
        ]);

        $slip = $this->calculationService->createOrUpdateSlip(
            $data,
            auth()->id()
        );

        $slip->load([
            'employee:id,full_name,employee_code',
            'period:id,period_name',
            'category:id,category_name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji berhasil diperbarui.',
            'data'    => $this->formatSlip($slip),
        ], 200);
    }

    // DELETE /api/salary-slips/{salary_slip}
    public function destroy(SalarySlip $salary_slip): JsonResponse
    {
        if ($salary_slip->status === 'sent') {
            return response()->json([
                'success' => false,
                'message' => 'Slip gaji yang sudah terkirim tidak dapat dihapus.',
            ], 422);
        }

        // Hapus file PDF jika ada
        if ($salary_slip->pdf_path && file_exists(storage_path('app/public/' . $salary_slip->pdf_path))) {
            unlink(storage_path('app/public/' . $salary_slip->pdf_path));
        }

        $salary_slip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji berhasil dihapus.',
        ], 200);
    }

    // POST /api/salary-slips/bulk-generate
    public function bulkGenerate(BulkGenerateSalarySlipRequest $request): JsonResponse
    {
        // Cek periode masih open
        $period = PayrollPeriod::findOrFail($request->period_id);
        if (!$period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membuat slip gaji untuk periode yang sudah ditutup.',
            ], 422);
        }

        $results   = [];
        $errors    = [];

        foreach ($request->employees as $employeeData) {
            try {
                $data = array_merge($employeeData, [
                    'period_id' => $request->period_id,
                ]);

                $slip = $this->calculationService->createOrUpdateSlip(
                    $data,
                    auth()->id()
                );

                $slip->load('employee:id,full_name,employee_code');

                $results[] = [
                    'employee_id'   => $slip->employee_id,
                    'employee_name' => $slip->employee->full_name,
                    'total_salary'  => $slip->total_salary,
                    'status'        => 'success',
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'employee_id' => $employeeData['employee_id'],
                    'error'       => $e->getMessage(),
                    'status'      => 'failed',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($results) . ' slip gaji berhasil dibuat, ' . count($errors) . ' gagal.',
            'data'    => [
                'success' => $results,
                'errors'  => $errors,
                'summary' => [
                    'total'   => count($request->employees),
                    'success' => count($results),
                    'failed'  => count($errors),
                ],
            ],
        ], 201);
    }

    // Format response slip (ringkas)
    private function formatSlip(SalarySlip $slip): array
    {
        return [
            'id'                   => $slip->id,
            'period'               => [
                'id'          => $slip->period->id ?? null,
                'period_name' => $slip->period->period_name ?? null,
            ],
            'employee'             => [
                'id'            => $slip->employee->id ?? null,
                'full_name'     => $slip->employee->full_name ?? null,
                'employee_code' => $slip->employee->employee_code ?? null,
            ],
            'category'             => [
                'id'            => $slip->category->id ?? null,
                'category_name' => $slip->category->category_name ?? null,
            ],
            'total_working_days'   => $slip->total_working_days,
            'late_count'           => $slip->late_count,
            'bonus'                => $slip->bonus,
            'additional_deduction' => $slip->additional_deduction,
            'base_salary_amount'   => $slip->base_salary_amount,
            'allowance_amount'     => $slip->allowance_amount,
            'late_penalty_amount'  => $slip->late_penalty_amount,
            'total_salary'         => $slip->total_salary,
            'status'               => $slip->status,
            'pdf_path'             => $slip->pdf_path,
            'sent_at'              => $slip->sent_at,
            'created_at'           => $slip->created_at,
        ];
    }

    // Format response slip (detail)
    private function formatSlipDetail(SalarySlip $slip): array
    {
        return array_merge($this->formatSlip($slip), [
            'notes'       => $slip->notes,
            'employee'    => [
                'id'            => $slip->employee->id ?? null,
                'full_name'     => $slip->employee->full_name ?? null,
                'employee_code' => $slip->employee->employee_code ?? null,
                'email'         => $slip->employee->email ?? null,
                'phone'         => $slip->employee->phone ?? null,
            ],
            'period'      => [
                'id'          => $slip->period->id ?? null,
                'period_name' => $slip->period->period_name ?? null,
                'start_date'  => $slip->period->start_date ?? null,
                'end_date'    => $slip->period->end_date ?? null,
            ],
            'category'    => [
                'id'            => $slip->category->id ?? null,
                'category_name' => $slip->category->category_name ?? null,
                'base_salary'   => $slip->category->base_salary ?? null,
                'allowance'     => $slip->category->allowance ?? null,
                'late_penalty'  => $slip->category->late_penalty ?? null,
            ],
            'created_by'  => $slip->creator->name ?? null,
            'updated_at'  => $slip->updated_at,
        ]);
    }
}