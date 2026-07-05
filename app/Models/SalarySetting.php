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
    ];

    // Setting bersifat global tunggal (1 baris untuk semua cabang)
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}