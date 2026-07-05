<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BranchSeeder::class,
            PositionSeeder::class,
            SalarySettingSeeder::class,
            // EmployeeSeeder & PayrollPeriodSeeder tidak dijalankan otomatis,
            // data karyawan & periode diinput manual oleh Owner/HR lewat sistem.
        ]);
    }
}