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
        // Owner tidak ditampilkan di sini — halaman ini murni untuk mengelola akun HR (biasa & Super HR)
        $hrUsers = User::where('role', 'hr')->with('branches')->orderByDesc('is_super_hr')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar akun HR berhasil diambil',
            'data' => $hrUsers,
        ]);
    }

    public function store(StoreHrRequest $request)
    {
        $requester = $request->user();
        $type = $request->input('type', 'hr');

        if ($type === 'super_hr' && !$requester->isOwner()) {
            abort(403, 'Hanya Owner yang dapat membuat akun Super HR.');
        }

        $user = $this->hrAccountService->createHr(
            $request->validated('name'),
            $type,
            $request->boolean('has_all_branch_access'),
            $request->input('branch_ids', [])
        );

        $this->activityLogService->log($requester, 'hr_management', 'create', null, $user->toArray());

        $label = $type === 'super_hr' ? 'Super HR' : 'HR';

        return response()->json([
            'success' => true,
            'message' => "Akun {$label} berhasil dibuat. Username: {$user->email}, password default: " . config('hr.default_password'),
            'data' => $user->load('branches'),
        ], 201);
    }

    public function update(UpdateHrRequest $request, User $user)
    {
        if ($user->role !== 'hr') {
            return response()->json(['success' => false, 'message' => 'Akun ini bukan akun HR'], 422);
        }

        $requester = $request->user();

        if ($user->is_super_hr && !$requester->isOwner()) {
            abort(403, 'Hanya Owner yang dapat mengubah akun Super HR.');
        }

        $oldData = $user->toArray();

        $user->update(['name' => $request->validated('name')]);

        // Akses cabang hanya relevan untuk HR biasa — Super HR selalu semua cabang.
        // PENTING: gunakan $request->has() di sini karena frontend SEKARANG SELALU
        // mengirim 'has_all_branch_access' (via hidden input default 0 + checkbox),
        // jadi key ini akan selalu ada saat form edit branch/nama dikirim,
        // dan TIDAK ada saat form "switch Super HR" saja yang dikirim.
        if (!$user->is_super_hr && $request->has('has_all_branch_access')) {
            $this->hrAccountService->updateBranchAccess(
                $user,
                $request->boolean('has_all_branch_access'),
                $request->input('branch_ids', [])
            );
        }

        // Switch status Super HR — HANYA Owner yang boleh
        if ($request->has('is_super_hr')) {
            if (!$requester->isOwner()) {
                abort(403, 'Hanya Owner yang dapat mengubah status Super HR.');
            }
            $this->hrAccountService->switchSuperHr($user, $request->boolean('is_super_hr'));
        }

        $this->activityLogService->log($requester, 'hr_management', 'update', $oldData, $user->fresh()->toArray());

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

        $requester = $request->user();

        if ($user->is_super_hr && !$requester->isOwner()) {
            abort(403, 'Hanya Owner yang dapat mereset password akun Super HR.');
        }

        $this->hrAccountService->resetPassword($user);

        $this->activityLogService->log($requester, 'hr_management', 'reset_password', null, ['user_id' => $user->id]);

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

        $requester = $request->user();

        if ($user->is_super_hr && !$requester->isOwner()) {
            abort(403, 'Hanya Owner yang dapat menghapus akun Super HR.');
        }

        $oldData = $user->toArray();
        $user->delete();

        $this->activityLogService->log($requester, 'hr_management', 'delete', $oldData, null);

        return response()->json([
            'success' => true,
            'message' => 'Akun HR berhasil dihapus',
        ]);
    }
}