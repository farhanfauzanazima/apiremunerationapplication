<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'base_salary',
        'allowance',
        'overtime_rate',
        'late_penalty',
        'description',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'base_salary'   => 'decimal:2',
        'allowance'     => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'late_penalty'  => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    // Relasi ke User (yang membuat kategori)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke Employee
    public function employees()
    {
        return $this->hasMany(Employee::class, 'category_id');
    }
}