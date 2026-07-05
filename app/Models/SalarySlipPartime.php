<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SalarySlipPartime extends Model
{
    protected $table = 'salary_slip_partime';

    protected $fillable = [
        'employee_id', 'payroll_period_id',
        'hari_kerja', 'full', 'shift', 'reguler', 'sakit', 'off',
        'tunjangan', 'total_full', 'total_shift', 'total_reguler', 'total_transport',
        'bonus', 'total_fee',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function distributions(): MorphMany
    {
        return $this->morphMany(DistributionHistory::class, 'slip');
    }
}