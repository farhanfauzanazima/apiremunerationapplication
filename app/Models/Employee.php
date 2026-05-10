<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'full_name',
        'employee_code',
        'email',
        'phone',
        'join_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    // Relasi ke SalaryCategory
    public function category()
    {
        return $this->belongsTo(SalaryCategory::class, 'category_id');
    }

    // Relasi ke User (yang membuat data karyawan)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke SalarySlip (akan digunakan di sesi berikutnya)
    public function salarySlips()
    {
        return $this->hasMany(SalarySlip::class, 'employee_id');
    }
}