<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollPeriodController;
use App\Http\Controllers\Api\ReportController;
// use App\Http\Controllers\Api\SalaryCategoryController;
use App\Http\Controllers\Api\SalarySlipController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HrManagementController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    // Route::prefix('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);

    // Salary Categories
    // Route::middleware('role:owner,head,admin')->group(function () {
    //     // Admin boleh READ kategori (untuk form slip gaji)
    //     Route::get('salary-categories',           [SalaryCategoryController::class, 'index'])->name('salary-categories.index');
    //     Route::get('salary-categories/{salary_category}', [SalaryCategoryController::class, 'show'])->name('salary-categories.show');
    // });

    // Route::middleware('role:owner')->group(function () {
    //     // Hanya Owner yang boleh CREATE/UPDATE/DELETE kategori
    //     Route::post('salary-categories',                    [SalaryCategoryController::class, 'store'])->name('salary-categories.store');
    //     Route::put('salary-categories/{salary_category}',   [SalaryCategoryController::class, 'update'])->name('salary-categories.update');
    //     Route::delete('salary-categories/{salary_category}', [SalaryCategoryController::class, 'destroy'])->name('salary-categories.destroy');
    // });

    // Employees
    Route::middleware('role:owner,head,admin')->group(function () {
        // Admin boleh READ karyawan (untuk keperluan input slip gaji)
        Route::get('employees',          [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('employees/{employee}/salary-history', [EmployeeController::class, 'salaryHistory'])->name('employees.salary-history');
    });

    Route::middleware('role:owner,head')->group(function () {
        // Hanya Owner & Head yang boleh CREATE/UPDATE/DELETE karyawan
        Route::post('employees',           [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // Payroll Periods
    Route::middleware('role:owner,head,admin')->group(function () {
        // Admin boleh READ periode (untuk keperluan input slip gaji)
        Route::get('payroll-periods',          [PayrollPeriodController::class, 'index'])->name('payroll-periods.index');
        Route::get('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'show'])->name('payroll-periods.show');
    });

    Route::middleware('role:owner,head')->group(function () {
        // Hanya Owner & Head yang boleh CREATE/UPDATE/DELETE periode
        Route::post('payroll-periods',                    [PayrollPeriodController::class, 'store'])->name('payroll-periods.store');
        Route::put('payroll-periods/{payroll_period}',    [PayrollPeriodController::class, 'update'])->name('payroll-periods.update');
        Route::delete('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'destroy'])->name('payroll-periods.destroy');
        Route::put('payroll-periods/{payroll_period}/close',  [PayrollPeriodController::class, 'close'])->name('payroll-periods.close');
        Route::put('payroll-periods/{payroll_period}/reopen', [PayrollPeriodController::class, 'reopen'])->name('payroll-periods.reopen');
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

    Route::middleware('role:owner')->prefix('hr-management')->group(function () {
        Route::get('/', [HrManagementController::class, 'index']);
        Route::post('/', [HrManagementController::class, 'store']);
        Route::put('/{user}', [HrManagementController::class, 'update']);
        Route::post('/{user}/reset-password', [HrManagementController::class, 'resetPassword']);
        Route::delete('/{user}', [HrManagementController::class, 'destroy']);
    });
    
});