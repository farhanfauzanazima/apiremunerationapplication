<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollPeriodController;
use App\Http\Controllers\Api\SalaryCategoryController;
use App\Http\Controllers\Api\SalarySlipController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout',          [AuthController::class, 'logout']);
        Route::get('/profile',          [AuthController::class, 'profile']);
        Route::put('/profile',          [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Salary Categories — hanya Owner
    Route::middleware('role:owner')->group(function () {
        Route::apiResource('salary-categories', SalaryCategoryController::class);
    });

    // Employees — Owner & Kepala Toko
    Route::middleware('role:owner,head')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
        Route::get('employees/{employee}/salary-history', [EmployeeController::class, 'salaryHistory']);
    });

    // Payroll Periods — Owner & Kepala Toko
    Route::middleware('role:owner,head')->group(function () {
        Route::apiResource('payroll-periods', PayrollPeriodController::class);
        Route::put('payroll-periods/{payroll_period}/close',  [PayrollPeriodController::class, 'close']);
        Route::put('payroll-periods/{payroll_period}/reopen', [PayrollPeriodController::class, 'reopen']);
    });

    // Salary Slips — Owner, Kepala Toko & Admin
    Route::middleware('role:owner,head,admin')->group(function () {
        // Harus di atas apiResource
        Route::post('salary-slips/bulk-generate',     [SalarySlipController::class, 'bulkGenerate']);
        Route::post('salary-slips/bulk-generate-pdf', [SalarySlipController::class, 'bulkGeneratePDF']);

        Route::apiResource('salary-slips', SalarySlipController::class);

        // PDF routes
        Route::get('salary-slips/{salary_slip}/preview-pdf',  [SalarySlipController::class, 'previewPDF']);
        Route::get('salary-slips/{salary_slip}/download-pdf', [SalarySlipController::class, 'downloadPDF']);
        Route::post('salary-slips/{salary_slip}/generate-pdf', [SalarySlipController::class, 'generatePDF']);
    });

});