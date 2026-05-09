<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_name',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Relasi ke User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke SalarySlip (akan digunakan di sesi berikutnya)
    public function salarySlips()
    {
        return $this->hasMany(SalarySlip::class, 'period_id');
    }

    // Helper: cek apakah periode masih open
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}