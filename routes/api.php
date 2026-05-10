<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollPeriodController;
use App\Http\Controllers\Api\ReportController;
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
        Route::post('salary-slips/bulk-generate',     [SalarySlipController::class, 'bulkGenerate']);
        Route::post('salary-slips/bulk-generate-pdf', [SalarySlipController::class, 'bulkGeneratePDF']);

        Route::apiResource('salary-slips', SalarySlipController::class);

        Route::get('salary-slips/{salary_slip}/preview-pdf',   [SalarySlipController::class, 'previewPDF']);
        Route::get('salary-slips/{salary_slip}/download-pdf',  [SalarySlipController::class, 'downloadPDF']);
        Route::post('salary-slips/{salary_slip}/generate-pdf', [SalarySlipController::class, 'generatePDF']);
    });

    // Email Distribution — Owner, Kepala Toko & Admin
    Route::middleware('role:owner,head,admin')->prefix('email')->group(function () {
        Route::post('send/{salarySlip}',   [EmailController::class, 'send']);
        Route::post('send-bulk',           [EmailController::class, 'sendBulk']);
        Route::post('resend/{salarySlip}', [EmailController::class, 'resend']);
        Route::get('history',              [EmailController::class, 'history']);
        Route::get('history/{salarySlip}', [EmailController::class, 'slipHistory']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::middleware('role:owner')->get('owner', [DashboardController::class, 'owner']);
        Route::middleware('role:owner,head')->get('head', [DashboardController::class, 'head']);
        Route::middleware('role:owner,head,admin')->get('admin', [DashboardController::class, 'admin']);
    });

    // Reports — Owner & Kepala Toko
    Route::middleware('role:owner,head')->prefix('reports')->group(function () {
        Route::get('salary-summary',            [ReportController::class, 'salarySummary']);
        Route::get('salary-summary/export-pdf', [ReportController::class, 'exportPDF']);
        Route::get('employee/{employeeId}',     [ReportController::class, 'employeeReport']);
        Route::get('statistics',                [ReportController::class, 'statistics']);
    });

    // Activity Logs — hanya Owner
    Route::middleware('role:owner')->prefix('activity-logs')->group(function () {
        Route::get('/',    [ActivityLogController::class, 'index']);
        Route::get('/{activity_log}', [ActivityLogController::class, 'show']);
    });

});