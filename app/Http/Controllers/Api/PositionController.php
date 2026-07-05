<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Position\StorePositionRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Models\Position;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Daftar jabatan berhasil diambil',
            'data' => Position::withCount('employees')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePositionRequest $request)
    {
        $position = Position::create($request->validated());

        $this->activityLogService->log($request->user(), 'position', 'create', null, $position->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil ditambahkan',
            'data' => $position,
        ], 201);
    }

    public function update(UpdatePositionRequest $request, Position $position)
    {
        $oldData = $position->toArray();
        $position->update($request->validated());

        $this->activityLogService->log($request->user(), 'position', 'update', $oldData, $position->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil diperbarui',
            'data' => $position->fresh(),
        ]);
    }

    public function destroy(Request $request, Position $position)
    {
        if ($position->employees()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan tidak dapat dihapus karena masih dipakai karyawan',
            ], 422);
        }

        $oldData = $position->toArray();
        $position->delete();

        $this->activityLogService->log($request->user(), 'position', 'delete', $oldData, null);

        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil dihapus',
        ]);
    }
}