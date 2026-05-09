<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollPeriod\StorePayrollPeriodRequest;
use App\Http\Requests\PayrollPeriod\UpdatePayrollPeriodRequest;
use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    // GET /api/payroll-periods
    public function index(Request $request): JsonResponse
    {
        $query = PayrollPeriod::with('creator:id,name');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by period name
        if ($request->has('search')) {
            $query->where('period_name', 'like', '%' . $request->search . '%');
        }

        $periods = $query->orderBy('start_date', 'desc')->get()
            ->map(function ($period) {
                return [
                    'id'          => $period->id,
                    'period_name' => $period->period_name,
                    'start_date'  => $period->start_date,
                    'end_date'    => $period->end_date,
                    'status'      => $period->status,
                    'notes'       => $period->notes,
                    'created_by'  => $period->creator->name ?? null,
                    'created_at'  => $period->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data periode penggajian berhasil diambil.',
            'data'    => $periods,
        ], 200);
    }

    // POST /api/payroll-periods
    public function store(StorePayrollPeriodRequest $request): JsonResponse
    {
        $period = PayrollPeriod::create([
            'period_name' => $request->period_name,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => 'open',
            'notes'       => $request->notes,
            'created_by'  => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil ditambahkan.',
            'data'    => [
                'id'          => $period->id,
                'period_name' => $period->period_name,
                'start_date'  => $period->start_date,
                'end_date'    => $period->end_date,
                'status'      => $period->status,
                'notes'       => $period->notes,
                'created_at'  => $period->created_at,
            ],
        ], 201);
    }

    // GET /api/payroll-periods/{payroll_period}
    public function show(PayrollPeriod $payroll_period): JsonResponse
    {
        $payroll_period->load('creator:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Detail periode penggajian berhasil diambil.',
            'data'    => [
                'id'          => $payroll_period->id,
                'period_name' => $payroll_period->period_name,
                'start_date'  => $payroll_period->start_date,
                'end_date'    => $payroll_period->end_date,
                'status'      => $payroll_period->status,
                'notes'       => $payroll_period->notes,
                'created_by'  => $payroll_period->creator->name ?? null,
                'created_at'  => $payroll_period->created_at,
                'updated_at'  => $payroll_period->updated_at,
            ],
        ], 200);
    }

    // PUT /api/payroll-periods/{payroll_period}
    public function update(UpdatePayrollPeriodRequest $request, PayrollPeriod $payroll_period): JsonResponse
    {
        // Periode yang sudah closed tidak bisa diedit
        if (!$payroll_period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode yang sudah ditutup tidak dapat diubah.',
            ], 422);
        }

        $payroll_period->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil diperbarui.',
            'data'    => [
                'id'          => $payroll_period->id,
                'period_name' => $payroll_period->period_name,
                'start_date'  => $payroll_period->start_date,
                'end_date'    => $payroll_period->end_date,
                'status'      => $payroll_period->status,
                'notes'       => $payroll_period->notes,
                'updated_at'  => $payroll_period->updated_at,
            ],
        ], 200);
    }

    // DELETE /api/payroll-periods/{payroll_period}
    public function destroy(PayrollPeriod $payroll_period): JsonResponse
    {
        // Periode yang sudah closed tidak bisa dihapus
        if (!$payroll_period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode yang sudah ditutup tidak dapat dihapus.',
            ], 422);
        }

        // Akan diaktifkan kembali setelah Sesi 6 (salary_slips sudah ada)
        // if ($payroll_period->salarySlips()->count() > 0) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Periode tidak dapat dihapus karena sudah memiliki data slip gaji.',
        //     ], 422);
        // }

        $payroll_period->delete();

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil dihapus.',
        ], 200);
    }

    // PUT /api/payroll-periods/{payroll_period}/close
    public function close(PayrollPeriod $payroll_period): JsonResponse
    {
        if (!$payroll_period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode sudah dalam status tertutup.',
            ], 422);
        }

        $payroll_period->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil ditutup.',
            'data'    => [
                'id'          => $payroll_period->id,
                'period_name' => $payroll_period->period_name,
                'status'      => $payroll_period->status,
            ],
        ], 200);
    }

    // PUT /api/payroll-periods/{payroll_period}/reopen
    public function reopen(PayrollPeriod $payroll_period): JsonResponse
    {
        if ($payroll_period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode sudah dalam status terbuka.',
            ], 422);
        }

        $payroll_period->update(['status' => 'open']);

        return response()->json([
            'success' => true,
            'message' => 'Periode penggajian berhasil dibuka kembali.',
            'data'    => [
                'id'          => $payroll_period->id,
                'period_name' => $payroll_period->period_name,
                'status'      => $payroll_period->status,
            ],
        ], 200);
    }
}