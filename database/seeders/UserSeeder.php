<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Owner',
            'email' => 'owner@warungsatelanud.id',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'has_all_branch_access' => true,
            'must_change_password' => false,
        ]);
    }
}