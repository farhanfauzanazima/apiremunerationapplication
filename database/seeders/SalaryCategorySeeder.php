<?php

namespace Database\Seeders;

use App\Models\SalaryCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalaryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', 'owner')->first();

        $categories = [
            [
                'category_name' => 'Kategori 1',
                'base_salary'   => 3000000,
                'allowance'     => 500000,
                'overtime_rate' => 20000,
                'late_penalty'  => 50000,
                'description'   => 'Karyawan tetap senior',
                'created_by'    => $owner->id,
                'is_active'     => true,
            ],
            [
                'category_name' => 'Kategori 2',
                'base_salary'   => 2500000,
                'allowance'     => 300000,
                'overtime_rate' => 15000,
                'late_penalty'  => 35000,
                'description'   => 'Karyawan tetap junior',
                'created_by'    => $owner->id,
                'is_active'     => true,
            ],
            [
                'category_name' => 'Kategori 3',
                'base_salary'   => 2000000,
                'allowance'     => 200000,
                'overtime_rate' => 12000,
                'late_penalty'  => 25000,
                'description'   => 'Karyawan kontrak',
                'created_by'    => $owner->id,
                'is_active'     => true,
            ],
            [
                'category_name' => 'Magang',
                'base_salary'   => 1000000,
                'allowance'     => 100000,
                'overtime_rate' => 0,
                'late_penalty'  => 15000,
                'description'   => 'Karyawan magang',
                'created_by'    => $owner->id,
                'is_active'     => true,
            ],
        ];

        foreach ($categories as $category) {
            SalaryCategory::create($category);
        }
    }
}