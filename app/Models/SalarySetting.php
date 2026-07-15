<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalarySetting extends Model
{
    protected $fillable = [
        'transport_tetap',
        'transport_partime',
        'tenure_months_threshold',
        'tenure_bonus_amount',
        'disiplin_bonus_tetap',
        'rate_full',
        'rate_shift',
        'rate_reguler',
        'lembur_1_2_jam',
        'lembur_3_4_jam',
        'lembur_5_jam',
    ];

    // Setting bersifat global tunggal (1 baris untuk semua cabang)
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}