<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\HrManagementController;
use App\Http\Controllers\Api\PayrollPeriodController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SalarySlipController;
use App\Http\Controllers\Api\SalarySettingController;
use App\Http\Controllers\Api\PublicSlipController;
use App\Http\Controllers\Api\DistributionController;
use App\Http\Controllers\Api\NotificationSettingController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ROUTES
// ============================================================
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/public/slip/{token}', [PublicSlipController::class, 'show']);
// ============================================================
// PROTECTED ROUTES
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // ---------- Auth ----------
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::match(['put', 'post'], '/auth/change-password', [AuthController::class, 'changePassword']);

    // ---------- Manajemen HR — Owner & Super HR ----------
    Route::middleware('elevated')->prefix('hr-management')->group(function () {
        Route::get('/', [HrManagementController::class, 'index']);
        Route::post('/', [HrManagementController::class, 'store']);
        Route::put('/{user}', [HrManagementController::class, 'update']);
        Route::post('/{user}/reset-password', [HrManagementController::class, 'resetPassword']);
        Route::delete('/{user}', [HrManagementController::class, 'destroy']);
    });

    // ---------- Cabang — baca: owner & hr, tulis: Owner & Super HR ----------
    Route::middleware('role:owner,hr')->group(function () {
        Route::get('/branches', [BranchController::class, 'index']);
        Route::get('/branches/{branch}', [BranchController::class, 'show']);
    });
    Route::middleware('elevated')->group(function () {
        Route::post('/branches', [BranchController::class, 'store']);
        Route::put('/branches/{branch}', [BranchController::class, 'update']);
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy']);
    });

    // ---------- Jabatan — owner & hr sama-sama boleh kelola ----------
    Route::middleware('role:owner,hr')->group(function () {
        Route::get('/positions', [PositionController::class, 'index']);
        Route::post('/positions', [PositionController::class, 'store']);
        Route::put('/positions/{position}', [PositionController::class, 'update']);
        Route::delete('/positions/{position}', [PositionController::class, 'destroy']);
    });

    // ====================================================================
    // BELUM DIROMBAK — placeholder, role sudah benar (owner,hr).
    // Detail kalkulasi/field akan dibangun ulang di sesi terkait.
    // ====================================================================

    // ---------- Karyawan (dirombak di Sesi 5) ----------
    Route::middleware('role:owner,hr')->group(function () {
        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // ---------- Periode Penggajian (dirombak di Sesi 6) ----------
    Route::middleware('role:owner,hr')->group(function () {
        Route::get('payroll-periods', [PayrollPeriodController::class, 'index'])->name('payroll-periods.index');
        Route::get('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'show'])->name('payroll-periods.show');
        Route::post('payroll-periods', [PayrollPeriodController::class, 'store'])->name('payroll-periods.store');
        Route::put('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'update'])->name('payroll-periods.update');
        Route::delete('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'destroy'])->name('payroll-periods.destroy');
    });

    // ---------- Slip Gaji ----------
    Route::middleware('role:owner,hr')->prefix('salary-slips')->group(function () {
        Route::get('bulk-data', [SalarySlipController::class, 'bulkData']);
        Route::post('bulk-generate', [SalarySlipController::class, 'bulkGenerate']);

        Route::get('/', [SalarySlipController::class, 'index']);
        Route::get('{type}/{id}', [SalarySlipController::class, 'show'])->whereIn('type', ['tetap', 'partime']);
        Route::put('tetap/{id}', [SalarySlipController::class, 'updateTetap']);
        Route::put('partime/{id}', [SalarySlipController::class, 'updatePartime']);
        Route::delete('{type}/{id}', [SalarySlipController::class, 'destroy'])->whereIn('type', ['tetap', 'partime']);
        Route::get('{type}/{id}/preview-pdf', [SalarySlipController::class, 'previewPDF'])->whereIn('type', ['tetap', 'partime']);
        Route::get('{type}/{id}/download-pdf', [SalarySlipController::class, 'downloadPDF'])->whereIn('type', ['tetap', 'partime']);
        Route::post('{type}/{id}/generate-link', [SalarySlipController::class, 'generatePublicLink'])->whereIn('type', ['tetap', 'partime']);
    });

    // ---------- Distribusi Gaji ----------
    Route::middleware('role:owner,hr')->prefix('distribution')->group(function () {
        Route::post('send-bulk', [DistributionController::class, 'sendBulk']);
        Route::get('history', [DistributionController::class, 'history']);
        Route::post('resend/{id}', [DistributionController::class, 'resend']);
    });

    // ---------- Dashboard ----------
    Route::middleware('role:owner,hr')->get('/dashboard', [DashboardController::class, 'index']);

    // ---------- Laporan (dirombak di Sesi 11) ----------
    Route::middleware('role:owner,hr')->prefix('reports')->group(function () {
        Route::get('salary-summary', [ReportController::class, 'salarySummary']);
        Route::get('statistics', [ReportController::class, 'statistics']);
        Route::get('employee/{employeeId}', [ReportController::class, 'employeeReport']);
        Route::get('finance-summary', [ReportController::class, 'financeSummary']);
        Route::get('finance-summary/preview-pdf', [ReportController::class, 'financeSummaryPreviewPdf']);
        Route::get('finance-summary/download-pdf', [ReportController::class, 'financeSummaryDownloadPdf']);
        Route::get('finance-summary/download-excel', [ReportController::class, 'financeSummaryDownloadExcel']);
    });

    // ---------- Activity Log — Owner only ----------
    Route::middleware('role:owner')->prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);
        Route::get('/{activity_log}', [ActivityLogController::class, 'show']);
    });

    Route::middleware('role:owner,hr')->group(function () {
        Route::get('/salary-settings', [SalarySettingController::class, 'show']);
        Route::put('/salary-settings', [SalarySettingController::class, 'update']);
    });

    Route::middleware('role:owner,hr')->group(function () {
        Route::get('payroll-periods', [PayrollPeriodController::class, 'index'])->name('payroll-periods.index');
        Route::get('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'show'])->name('payroll-periods.show');
        Route::post('payroll-periods', [PayrollPeriodController::class, 'store'])->name('payroll-periods.store');
        Route::put('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'update'])->name('payroll-periods.update');
        Route::delete('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'destroy'])->name('payroll-periods.destroy');
    });

    // ---------- WhatsApp ----------
    Route::middleware('role:owner,hr')->group(function () {
        Route::get('/notification-settings', [NotificationSettingController::class, 'show']);
        Route::put('/notification-settings', [NotificationSettingController::class, 'update']);
    });
});
