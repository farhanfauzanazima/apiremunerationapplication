<?php

namespace Database\Seeders;

use App\Models\SalarySetting;
use Illuminate\Database\Seeder;

class SalarySettingSeeder extends Seeder
{
    public function run(): void
    {
        SalarySetting::create([
            'id' => 1,
            'transport_tetap' => 10000,
            'transport_partime' => 5000,
            'tenure_months_threshold' => 6,
            'tenure_bonus_amount' => 100000,
            'disiplin_bonus_tetap' => 10000,
            'rate_full' => 60000,
            'rate_shift' => 40000,
            'rate_reguler' => 25000,
            'lembur_1_2_jam' => 20000,
            'lembur_3_4_jam' => 35000,
            'lembur_5_jam' => 40000,
        ]);
    }
}