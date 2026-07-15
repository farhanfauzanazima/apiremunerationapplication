<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SalarySlipTetap extends Model
{
    protected $table = 'salary_slip_tetap';

    protected $fillable = [
        'employee_id', 'payroll_period_id',
        'hari_kerja', 'alfa', 'izin', 'sakit', 'off',
        'hari_shift', 'hari_full', 'hari_parsial',
        'nominal_shift', 'nominal_full', 'nominal_parsial',
        'total_shift', 'total_full', 'total_parsial',
        'masuk', 'jam_lembur', 'lembur', 'telat',
        'gaji_pokok', 'tunjangan_transport', 'tunjangan_jabatan', 'tunjangan_bpjs',
        'tunjangan_masa_kerja', 'bonus_disiplin', 'bonus_omset', 'bonus_kinerja',
        'cashbond', 'tabungan', 'thp', 'total_gaji',
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