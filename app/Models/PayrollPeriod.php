<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'month', 'year', 'notes'];

    public function salarySlipsTetap()
    {
        return $this->hasMany(SalarySlipTetap::class);
    }

    public function salarySlipsPartime()
    {
        return $this->hasMany(SalarySlipPartime::class);
    }
}