<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class HrAccountService
{
    protected function defaultPassword(): string
    {
        return config('hr.default_password');
    }

    public function generateEmail(string $name): string
    {
        $domain = config('hr.email_domain');
        $base = (string) \Illuminate\Support\Str::of($name)->lower()->replaceMatches('/[^a-z0-9]/', '');

        if ($base === '') {
            $base = 'hr';
        }

        $email = "{$base}@{$domain}";
        $suffix = 1;

        while (User::where('email', $email)->exists()) {
            $suffix++;
            $email = "{$base}{$suffix}@{$domain}";
        }

        return $email;
    }

    /**
     * $type: 'hr' (biasa) atau 'super_hr'.
     * Super HR SELALU dibuat dengan akses semua cabang (tidak bisa dibatasi).
     */
    public function createHr(string $name, string $type, bool $hasAllBranchAccess, array $branchIds = []): User
    {
        $isSuperHr = $type === 'super_hr';

        if ($isSuperHr) {
            $hasAllBranchAccess = true;
            $branchIds = [];
        }

        $user = User::create([
            'name' => $name,
            'email' => $this->generateEmail($name),
            'password' => Hash::make($this->defaultPassword()),
            'role' => 'hr',
            'is_super_hr' => $isSuperHr,
            'has_all_branch_access' => $hasAllBranchAccess,
            'must_change_password' => true,
        ]);

        if (!$hasAllBranchAccess) {
            $user->branches()->sync($branchIds);
        }

        return $user;
    }

    /**
     * Hanya berlaku untuk HR biasa (bukan Super HR, karena Super HR selalu semua cabang).
     */
    public function updateBranchAccess(User $user, bool $hasAllBranchAccess, array $branchIds = []): User
    {
        $user->update(['has_all_branch_access' => $hasAllBranchAccess]);
        $user->branches()->sync($hasAllBranchAccess ? [] : $branchIds);

        return $user;
    }

    /**
     * Owner-only: naik/turunkan status Super HR.
     * Saat naik jadi Super HR -> otomatis akses semua cabang.
     * Saat diturunkan jadi HR biasa -> akses cabang di-reset (Owner wajib atur ulang).
     */
    public function switchSuperHr(User $user, bool $makeSuper): User
    {
        $user->update([
            'is_super_hr' => $makeSuper,
            'has_all_branch_access' => $makeSuper ? true : false,
        ]);

        $user->branches()->sync([]);

        return $user;
    }

    public function resetPassword(User $user): User
    {
        $user->update([
            'password' => Hash::make($this->defaultPassword()),
            'must_change_password' => true,
        ]);

        return $user;
    }
}