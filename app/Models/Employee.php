<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'position_id', 'branch_id', 'join_date',
        'phone', 'email', 'bank_account_number', 'bank_account_name',
        'bank_name', 'employee_type', 'status',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function salarySlipsTetap()
    {
        return $this->hasMany(SalarySlipTetap::class);
    }

    public function salarySlipsPartime()
    {
        return $this->hasMany(SalarySlipPartime::class);
    }

    // Masa kerja dalam bulan, dihitung dari join_date ke hari ini
    public function getTenureMonthsAttribute(): int
    {
        return $this->join_date->diffInMonths(now());
    }

    public function slipTetapForPeriod()
    {
        return $this->hasOne(SalarySlipTetap::class);
    }

    public function slipPartimeForPeriod()
    {
        return $this->hasOne(SalarySlipPartime::class);
    }
}