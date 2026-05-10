<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/auth/login
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Catat activity log
        ActivityLogService::log(
            'login',
            'auth',
            'User ' . $user->name . ' (' . $user->role . ') login ke sistem',
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'user'       => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                    'phone' => $user->phone,
                ],
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    // POST /api/auth/logout
    public function logout(): JsonResponse
    {
        $user = auth()->user();

        // Catat activity log sebelum token dihapus
        ActivityLogService::log(
            'logout',
            'auth',
            'User ' . $user->name . ' logout dari sistem',
        );

        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);
    }

    // GET /api/auth/profile
    public function profile(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'phone'     => $user->phone,
                'is_active' => $user->is_active,
                'created_at'=> $user->created_at,
            ],
        ], 200);
    }

    // PUT /api/auth/profile
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user    = auth()->user();
        $oldData = $user->only('name', 'email', 'phone');

        $user->update($request->validated());

        ActivityLogService::log(
            'update',
            'auth',
            'User ' . $user->name . ' memperbarui profil',
            $oldData,
            $request->validated(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
                'phone' => $user->phone,
            ],
        ], 200);
    }

    // POST /api/auth/change-password
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        ActivityLogService::log(
            'change_password',
            'auth',
            'User ' . $user->name . ' mengubah password',
        );

        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login kembali.',
        ], 200);
    }
}