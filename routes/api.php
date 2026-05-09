<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollPeriodController;
use App\Http\Controllers\Api\SalaryCategoryController;
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

});