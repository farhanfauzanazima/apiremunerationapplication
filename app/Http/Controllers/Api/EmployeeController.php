<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Employee::with(['position', 'branch']);

        // Scoping akses cabang untuk HR
        $allowed = $user->allowedBranchIds();
        if ($allowed !== null) {
            $query->whereIn('branch_id', $allowed);
        }

        // Filter pencarian nama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Filter cabang (tetap dibatasi allowedBranchIds di atas)
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Filter jenis karyawan
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->input('employee_type'));
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter masa kerja: '6_months' atau '1_year'
        if ($request->filled('tenure')) {
            $threshold = match ($request->input('tenure')) {
                '6_months' => now()->subMonths(6),
                '1_year' => now()->subYear(),
                default => null,
            };

            if ($threshold) {
                $query->where('join_date', '<=', $threshold);
            }
        }

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Daftar karyawan berhasil diambil',
            'data' => $employees->items(),
            'raw' => [
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'total' => $employees->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, Employee $employee)
    {
        $this->authorizeBranchAccess($request, $employee);

        return response()->json([
            'success' => true,
            'message' => 'Detail karyawan berhasil diambil',
            'data' => $employee->load(['position', 'branch']),
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $this->authorizeBranchIdAccess($request, $data['branch_id']);

        $data['status'] = $data['status'] ?? 'aktif';
        $employee = Employee::create($data);

        $this->activityLogService->log($request->user(), 'employee', 'create', null, $employee->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil ditambahkan',
            'data' => $employee->load(['position', 'branch']),
        ], 201);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->authorizeBranchAccess($request, $employee);
        $this->authorizeBranchIdAccess($request, $request->validated('branch_id'));

        $oldData = $employee->toArray();
        $employee->update($request->validated());

        $this->activityLogService->log($request->user(), 'employee', 'update', $oldData, $employee->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil diperbarui',
            'data' => $employee->fresh(['position', 'branch']),
        ]);
    }

    public function destroy(Request $request, Employee $employee)
    {
        $this->authorizeBranchAccess($request, $employee);

        if ($employee->salarySlipsTetap()->exists() || $employee->salarySlipsPartime()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak dapat dihapus karena sudah memiliki riwayat slip gaji',
            ], 422);
        }

        $oldData = $employee->toArray();
        $employee->delete();

        $this->activityLogService->log($request->user(), 'employee', 'delete', $oldData, null);

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dihapus',
        ]);
    }

    protected function authorizeBranchAccess(Request $request, Employee $employee): void
    {
        if (!$request->user()->canAccessBranch($employee->branch_id)) {
            abort(403, 'Anda tidak memiliki akses ke karyawan cabang ini');
        }
    }

    protected function authorizeBranchIdAccess(Request $request, int $branchId): void
    {
        if (!$request->user()->canAccessBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola karyawan di cabang ini');
        }
    }
}