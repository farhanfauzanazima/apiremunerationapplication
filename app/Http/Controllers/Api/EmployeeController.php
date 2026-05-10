<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // GET /api/employees
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with('category:id,category_name', 'creator:id,name');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_code', 'like', '%' . $request->search . '%');
            });
        }

        $employees = $query->orderBy('full_name', 'asc')->get()
            ->map(function ($employee) {
                return [
                    'id'            => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'full_name'     => $employee->full_name,
                    'email'         => $employee->email,
                    'phone'         => $employee->phone,
                    'join_date'     => $employee->join_date,
                    'status'        => $employee->status,
                    'category'      => [
                        'id'            => $employee->category->id,
                        'category_name' => $employee->category->category_name,
                    ],
                    'created_by'    => $employee->creator->name ?? null,
                    'created_at'    => $employee->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diambil.',
            'data'    => $employees,
        ], 200);
    }

    // POST /api/employees
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create([
            'category_id'   => $request->category_id,
            'full_name'     => $request->full_name,
            'employee_code' => $request->employee_code,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'join_date'     => $request->join_date,
            'status'        => 'active',
            'created_by'    => auth()->id(),
        ]);

        $employee->load('category:id,category_name');

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil ditambahkan.',
            'data'    => [
                'id'            => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name'     => $employee->full_name,
                'email'         => $employee->email,
                'phone'         => $employee->phone,
                'join_date'     => $employee->join_date,
                'status'        => $employee->status,
                'category'      => [
                    'id'            => $employee->category->id,
                    'category_name' => $employee->category->category_name,
                ],
            ],
        ], 201);
    }

    // GET /api/employees/{employee}
    public function show(Employee $employee): JsonResponse
    {
        $employee->load('category:id,category_name,base_salary,allowance,late_penalty', 'creator:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Detail karyawan berhasil diambil.',
            'data'    => [
                'id'            => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name'     => $employee->full_name,
                'email'         => $employee->email,
                'phone'         => $employee->phone,
                'join_date'     => $employee->join_date,
                'status'        => $employee->status,
                'category'      => $employee->category,
                'created_by'    => $employee->creator->name ?? null,
                'created_at'    => $employee->created_at,
                'updated_at'    => $employee->updated_at,
            ],
        ], 200);
    }

    // PUT /api/employees/{employee}
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());
        $employee->load('category:id,category_name');

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data'    => [
                'id'            => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name'     => $employee->full_name,
                'email'         => $employee->email,
                'phone'         => $employee->phone,
                'join_date'     => $employee->join_date,
                'status'        => $employee->status,
                'category'      => $employee->category,
            ],
        ], 200);
    }

    // DELETE /api/employees/{employee}
    public function destroy(Employee $employee): JsonResponse
    {
        // Cek apakah karyawan memiliki riwayat slip gaji
        if ($employee->salarySlips()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak dapat dihapus karena memiliki riwayat slip gaji.',
            ], 422);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil dihapus.',
        ], 200);
    }

    // GET /api/employees/{employee}/salary-history
    public function salaryHistory(Employee $employee): JsonResponse
    {
        $employee->load([
            'salarySlips.period:id,period_name,start_date,end_date',
            'salarySlips.category:id,category_name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat gaji karyawan berhasil diambil.',
            'data'    => [
                'employee'      => [
                    'id'        => $employee->id,
                    'full_name' => $employee->full_name,
                    'email'     => $employee->email,
                ],
                'salary_history' => $employee->salarySlips,
            ],
        ], 200);
    }
}