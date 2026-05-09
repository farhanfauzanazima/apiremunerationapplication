<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\SalaryCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $head     = User::where('role', 'head')->first();
        $cat1     = SalaryCategory::where('category_name', 'Kategori 1')->first();
        $cat2     = SalaryCategory::where('category_name', 'Kategori 2')->first();
        $cat3     = SalaryCategory::where('category_name', 'Kategori 3')->first();
        $magang   = SalaryCategory::where('category_name', 'Magang')->first();

        $employees = [
            [
                'category_id'   => $cat1->id,
                'full_name'     => 'Ahmad Santoso',
                'employee_code' => 'EMP001',
                'email'         => 'ahmad@resto.com',
                'phone'         => '081111111111',
                'join_date'     => '2022-01-15',
                'status'        => 'active',
                'created_by'    => $head->id,
            ],
            [
                'category_id'   => $cat2->id,
                'full_name'     => 'Budi Wijaya',
                'employee_code' => 'EMP002',
                'email'         => 'budi@resto.com',
                'phone'         => '082222222222',
                'join_date'     => '2022-03-01',
                'status'        => 'active',
                'created_by'    => $head->id,
            ],
            [
                'category_id'   => $cat3->id,
                'full_name'     => 'Citra Dewi',
                'employee_code' => 'EMP003',
                'email'         => 'citra@resto.com',
                'phone'         => '083333333333',
                'join_date'     => '2023-06-01',
                'status'        => 'active',
                'created_by'    => $head->id,
            ],
            [
                'category_id'   => $magang->id,
                'full_name'     => 'Dian Pratiwi',
                'employee_code' => 'EMP004',
                'email'         => 'dian@resto.com',
                'phone'         => '084444444444',
                'join_date'     => '2024-01-10',
                'status'        => 'active',
                'created_by'    => $head->id,
            ],
            [
                'category_id'   => $cat2->id,
                'full_name'     => 'Eko Susanto',
                'employee_code' => 'EMP005',
                'email'         => 'eko@resto.com',
                'phone'         => '085555555555',
                'join_date'     => '2023-09-15',
                'status'        => 'active',
                'created_by'    => $head->id,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}