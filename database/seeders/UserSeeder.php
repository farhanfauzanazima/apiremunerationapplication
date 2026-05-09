<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Owner
        User::create([
            'name'      => 'Owner Restoran',
            'email'     => 'owner@resto.com',
            'password'  => Hash::make('password123'),
            'role'      => 'owner',
            'phone'     => '081234567890',
            'is_active' => true,
        ]);

        // Kepala Toko
        User::create([
            'name'      => 'Kepala Toko',
            'email'     => 'head@resto.com',
            'password'  => Hash::make('password123'),
            'role'      => 'head',
            'phone'     => '081234567891',
            'is_active' => true,
        ]);

        // Admin Toko
        User::create([
            'name'      => 'Admin Toko',
            'email'     => 'admin@resto.com',
            'password'  => Hash::make('password123'),
            'role'      => 'admin',
            'phone'     => '081234567892',
            'is_active' => true,
        ]);
    }
}