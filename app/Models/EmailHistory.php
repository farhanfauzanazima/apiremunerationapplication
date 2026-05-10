<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_slip_id',
        'employee_id',
        'email_to',
        'subject',
        'status',
        'error_message',
        'message_id',
        'sent_at',
        'sent_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Relasi ke SalarySlip
    public function salarySlip()
    {
        return $this->belongsTo(SalarySlip::class, 'salary_slip_id');
    }

    // Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi ke User (yang mengirim)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}