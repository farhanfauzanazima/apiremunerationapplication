<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HrAccountService
{
    protected function defaultPassword(): string
    {
        return config('hr.default_password');
    }

    public function generateEmail(string $name): string
    {
        $domain = config('hr.email_domain');
        $base = (string) Str::of($name)->lower()->replaceMatches('/[^a-z0-9]/', '');

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

    public function createHr(string $name, bool $hasAllBranchAccess, array $branchIds = []): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $this->generateEmail($name),
            'password' => Hash::make($this->defaultPassword()),
            'role' => 'hr',
            'has_all_branch_access' => $hasAllBranchAccess,
            'must_change_password' => true,
        ]);

        if (!$hasAllBranchAccess) {
            $user->branches()->sync($branchIds);
        }

        return $user;
    }

    public function updateBranchAccess(User $user, bool $hasAllBranchAccess, array $branchIds = []): User
    {
        $user->update(['has_all_branch_access' => $hasAllBranchAccess]);
        $user->branches()->sync($hasAllBranchAccess ? [] : $branchIds);

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