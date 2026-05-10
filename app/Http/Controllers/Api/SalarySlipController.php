<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalarySlip\BulkGenerateSalarySlipRequest;
use App\Http\Requests\SalarySlip\StoreSalarySlipRequest;
use App\Http\Requests\SalarySlip\UpdateSalarySlipRequest;
use App\Models\PayrollPeriod;
use App\Models\SalarySlip;
use App\Services\PDFService;
use App\Services\SalaryCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalarySlipController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $calculationService,
        protected PDFService $pdfService,
    ) {}

    // GET /api/salary-slips
    public function index(Request $request): JsonResponse
    {
        $query = SalarySlip::with([
            'employee:id,full_name,employee_code,email',
            'period:id,period_name,start_date,end_date',
            'category:id,category_name',
        ]);

        if ($request->has('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

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
        if ($salary_slip->status === 'sent') {
            return response()->json([
                'success' => false,
                'message' => 'Slip gaji yang sudah terkirim tidak dapat diubah.',
            ], 422);
        }

        // Hapus PDF lama jika ada karena data berubah
        $this->pdfService->deleteSlipPDF($salary_slip);

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

        $this->pdfService->deleteSlipPDF($salary_slip);
        $salary_slip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji berhasil dihapus.',
        ], 200);
    }

    // POST /api/salary-slips/bulk-generate
    public function bulkGenerate(BulkGenerateSalarySlipRequest $request): JsonResponse
    {
        $period = PayrollPeriod::findOrFail($request->period_id);
        if (!$period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membuat slip gaji untuk periode yang sudah ditutup.',
            ], 422);
        }

        $results = [];
        $errors  = [];

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

    // GET /api/salary-slips/{salary_slip}/preview-pdf
    public function previewPDF(SalarySlip $salary_slip): mixed
    {
        try {
            $pdf = $this->pdfService->streamSlipPDF($salary_slip);

            return $pdf->stream(
                'preview-slip-' . $salary_slip->id . '.pdf'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate preview PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/salary-slips/{salary_slip}/download-pdf
    public function downloadPDF(SalarySlip $salary_slip): mixed
    {
        try {
            // Generate dan simpan PDF jika belum ada
            if (!$salary_slip->pdf_path) {
                $this->pdfService->generateSlipPDF($salary_slip);
                $salary_slip->refresh();
            }

            $fileName = 'slip-gaji-'
                . ($salary_slip->employee->employee_code ?? $salary_slip->employee_id)
                . '-' . str_replace(' ', '-', strtolower($salary_slip->period->period_name ?? ''))
                . '.pdf';

            return response()->download(
                storage_path('app/public/' . $salary_slip->pdf_path),
                $fileName,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal download PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/salary-slips/{salary_slip}/generate-pdf
    public function generatePDF(SalarySlip $salary_slip): JsonResponse
    {
        try {
            // Hapus PDF lama jika ada
            $this->pdfService->deleteSlipPDF($salary_slip);

            // Generate PDF baru
            $filePath = $this->pdfService->generateSlipPDF($salary_slip);

            return response()->json([
                'success' => true,
                'message' => 'PDF slip gaji berhasil digenerate.',
                'data'    => [
                    'slip_id'    => $salary_slip->id,
                    'pdf_path'   => $filePath,
                    'pdf_url'    => asset('storage/' . $filePath),
                    'preview_url'  => url('/api/salary-slips/' . $salary_slip->id . '/preview-pdf'),
                    'download_url' => url('/api/salary-slips/' . $salary_slip->id . '/download-pdf'),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    // POST /api/salary-slips/bulk-generate-pdf
    public function bulkGeneratePDF(Request $request): JsonResponse
    {
        $request->validate([
            'period_id'    => 'required|exists:payroll_periods,id',
            'slip_ids'     => 'nullable|array',
            'slip_ids.*'   => 'exists:salary_slips,id',
        ]);

        // Jika slip_ids kosong, ambil semua slip di periode tersebut
        $query = SalarySlip::where('period_id', $request->period_id);
        if ($request->has('slip_ids') && count($request->slip_ids) > 0) {
            $query->whereIn('id', $request->slip_ids);
        }

        $slips   = $query->get();
        $results = [];
        $errors  = [];

        foreach ($slips as $slip) {
            try {
                $this->pdfService->deleteSlipPDF($slip);
                $filePath  = $this->pdfService->generateSlipPDF($slip);
                $results[] = [
                    'slip_id'      => $slip->id,
                    'employee_id'  => $slip->employee_id,
                    'pdf_path'     => $filePath,
                    'pdf_url'      => asset('storage/' . $filePath),
                    'status'       => 'success',
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'slip_id' => $slip->id,
                    'error'   => $e->getMessage(),
                    'status'  => 'failed',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($results) . ' PDF berhasil digenerate, ' . count($errors) . ' gagal.',
            'data'    => [
                'success' => $results,
                'errors'  => $errors,
                'summary' => [
                    'total'   => $slips->count(),
                    'success' => count($results),
                    'failed'  => count($errors),
                ],
            ],
        ], 200);
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
            'pdf_url'              => $slip->pdf_path ? asset('storage/' . $slip->pdf_path) : null,
            'sent_at'              => $slip->sent_at,
            'created_at'           => $slip->created_at,
        ];
    }

    // Format response slip (detail)
    private function formatSlipDetail(SalarySlip $slip): array
    {
        return array_merge($this->formatSlip($slip), [
            'notes'    => $slip->notes,
            'employee' => [
                'id'            => $slip->employee->id ?? null,
                'full_name'     => $slip->employee->full_name ?? null,
                'employee_code' => $slip->employee->employee_code ?? null,
                'email'         => $slip->employee->email ?? null,
                'phone'         => $slip->employee->phone ?? null,
            ],
            'period'   => [
                'id'          => $slip->period->id ?? null,
                'period_name' => $slip->period->period_name ?? null,
                'start_date'  => $slip->period->start_date ?? null,
                'end_date'    => $slip->period->end_date ?? null,
            ],
            'category' => [
                'id'            => $slip->category->id ?? null,
                'category_name' => $slip->category->category_name ?? null,
                'base_salary'   => $slip->category->base_salary ?? null,
                'allowance'     => $slip->category->allowance ?? null,
                'late_penalty'  => $slip->category->late_penalty ?? null,
            ],
            'created_by' => $slip->creator->name ?? null,
            'updated_at' => $slip->updated_at,
        ]);
    }
}