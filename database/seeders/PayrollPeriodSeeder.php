<?php

namespace Database\Seeders;

use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayrollPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $head = User::where('role', 'head')->first();

        $periods = [
            [
                'period_name' => 'Maret 2026',
                'start_date'  => '2026-03-01',
                'end_date'    => '2026-03-31',
                'status'      => 'closed',
                'notes'       => 'Periode Maret 2026',
                'created_by'  => $head->id,
            ],
            [
                'period_name' => 'April 2026',
                'start_date'  => '2026-04-01',
                'end_date'    => '2026-04-30',
                'status'      => 'closed',
                'notes'       => 'Periode April 2026',
                'created_by'  => $head->id,
            ],
            [
                'period_name' => 'Mei 2026',
                'start_date'  => '2026-05-01',
                'end_date'    => '2026-05-31',
                'status'      => 'open',
                'notes'       => 'Periode aktif saat ini',
                'created_by'  => $head->id,
            ],
        ];

        foreach ($periods as $period) {
            PayrollPeriod::create($period);
        }
    }
}