<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrManagement\StoreHrRequest;
use App\Http\Requests\HrManagement\UpdateHrRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\HrAccountService;
use Illuminate\Http\Request;

class HrManagementController extends Controller
{
    public function __construct(
        protected HrAccountService $hrAccountService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function index()
    {
        $hrUsers = User::where('role', 'hr')->with('branches')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar akun HR berhasil diambil',
            'data' => $hrUsers,
        ]);
    }

    public function store(StoreHrRequest $request)
    {
        $user = $this->hrAccountService->createHr(
            $request->validated('name'),
            $request->boolean('has_all_branch_access'),
            $request->input('branch_ids', [])
        );

        $this->activityLogService->log($request->user(), 'hr_management', 'create', null, $user->toArray());

        return response()->json([
            'success' => true,
            'message' => "Akun HR berhasil dibuat. Username: {$user->email}, password default: " . config('hr.default_password'),
            'data' => $user->load('branches'),
        ], 201);
    }

    public function update(UpdateHrRequest $request, User $user)
    {
        if ($user->role !== 'hr') {
            return response()->json(['success' => false, 'message' => 'Akun ini bukan akun HR'], 422);
        }

        $oldData = $user->toArray();

        $user->update(['name' => $request->validated('name')]);
        $this->hrAccountService->updateBranchAccess(
            $user,
            $request->boolean('has_all_branch_access'),
            $request->input('branch_ids', [])
        );

        $this->activityLogService->log($request->user(), 'hr_management', 'update', $oldData, $user->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Akun HR berhasil diperbarui',
            'data' => $user->fresh('branches'),
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->role !== 'hr') {
            return response()->json(['success' => false, 'message' => 'Akun ini bukan akun HR'], 422);
        }

        $this->hrAccountService->resetPassword($user);

        $this->activityLogService->log($request->user(), 'hr_management', 'reset_password', null, ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset ke default: ' . config('hr.default_password'),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->role !== 'hr') {
            return response()->json(['success' => false, 'message' => 'Akun ini bukan akun HR'], 422);
        }

        $oldData = $user->toArray();
        $user->delete();

        $this->activityLogService->log($request->user(), 'hr_management', 'delete', $oldData, null);

        return response()->json([
            'success' => true,
            'message' => 'Akun HR berhasil dihapus',
        ]);
    }
}