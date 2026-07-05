<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            'Kepala Toko', 'Leader Dapur', 'Tim Dapur', 'Leader Depan',
            'Waiters', 'Waiters/Kasir', 'Purchasing', 'Riset & Development', 'Training',
        ];

        foreach ($positions as $name) {
            Position::firstOrCreate(['name' => $name]);
        }
    }
}