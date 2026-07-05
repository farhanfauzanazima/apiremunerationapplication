<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        $this->activityLogService->log($user, 'auth', 'login', null, null);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => $user->load('branches'),
                'must_change_password' => $user->must_change_password,
            ],
        ]);
    }

    public function logout()
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        $this->activityLogService->log($user, 'auth', 'logout', null, null);

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    public function profile()
    {
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diambil',
            'data' => auth()->user()->load('branches'),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user,
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->validated('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->validated('new_password')),
            'must_change_password' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah',
        ]);
    }
}