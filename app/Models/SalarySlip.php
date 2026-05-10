<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'employee_id',
        'category_id',
        'total_working_days',
        'late_count',
        'bonus',
        'additional_deduction',
        'notes',
        'base_salary_amount',
        'allowance_amount',
        'late_penalty_amount',
        'total_salary',
        'status',
        'pdf_path',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'base_salary_amount'   => 'decimal:2',
        'allowance_amount'     => 'decimal:2',
        'late_penalty_amount'  => 'decimal:2',
        'total_salary'         => 'decimal:2',
        'bonus'                => 'decimal:2',
        'additional_deduction' => 'decimal:2',
        'sent_at'              => 'datetime',
    ];

    // Relasi ke PayrollPeriod
    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }

    // Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi ke SalaryCategory
    public function category()
    {
        return $this->belongsTo(SalaryCategory::class, 'category_id');
    }

    // Relasi ke User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke EmailHistory (akan digunakan di sesi berikutnya)
    public function emailHistories()
    {
        return $this->hasMany(EmailHistory::class, 'salary_slip_id');
    }
}