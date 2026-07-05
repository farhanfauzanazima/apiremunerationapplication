<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone',
        'role', 'has_all_branch_access', 'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'has_all_branch_access' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branch');
    }

    // Helper methods untuk cek role
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isHead(): bool
    {
        return $this->role === 'head';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccessBranch(int $branchId): bool
    {
        if ($this->isOwner() || $this->has_all_branch_access) {
            return true;
        }

        return $this->branches()->where('branches.id', $branchId)->exists();
    }
    
}