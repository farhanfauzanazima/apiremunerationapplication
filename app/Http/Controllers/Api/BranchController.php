<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\Branch;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Branch::query()->withCount('employees');

        // HR tanpa akses semua cabang hanya melihat cabang miliknya
        $allowed = $user->allowedBranchIds();
        if ($allowed !== null) {
            $query->whereIn('id', $allowed);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar cabang berhasil diambil',
            'data' => $query->orderBy('name')->get(),
        ]);
    }

    public function show(Branch $branch)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail cabang berhasil diambil',
            'data' => $branch,
        ]);
    }

    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        $this->activityLogService->log($request->user(), 'branch', 'create', null, $branch->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil ditambahkan',
            'data' => $branch,
        ], 201);
    }

    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $oldData = $branch->toArray();
        $branch->update($request->validated());

        $this->activityLogService->log($request->user(), 'branch', 'update', $oldData, $branch->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil diperbarui',
            'data' => $branch->fresh(),
        ]);
    }

    public function destroy(Request $request, Branch $branch)
    {
        if ($branch->employees()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cabang tidak dapat dihapus karena masih memiliki data karyawan',
            ], 422);
        }

        $oldData = $branch->toArray();
        $branch->delete();

        $this->activityLogService->log($request->user(), 'branch', 'delete', $oldData, null);

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil dihapus',
        ]);
    }
}