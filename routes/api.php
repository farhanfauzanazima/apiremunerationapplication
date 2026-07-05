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
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ROUTES
// ============================================================
Route::post('/auth/login', [AuthController::class, 'login']);

// ============================================================
// PROTECTED ROUTES
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // ---------- Auth ----------
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);

    // ---------- Manajemen HR — Owner only ----------
    Route::middleware('role:owner')->prefix('hr-management')->group(function () {
        Route::get('/', [HrManagementController::class, 'index']);
        Route::post('/', [HrManagementController::class, 'store']);
        Route::put('/{user}', [HrManagementController::class, 'update']);
        Route::post('/{user}/reset-password', [HrManagementController::class, 'resetPassword']);
        Route::delete('/{user}', [HrManagementController::class, 'destroy']);
    });

    // ---------- Cabang — baca: owner & hr (discope di controller), tulis: owner only ----------
    Route::middleware('role:owner,hr')->group(function () {
        Route::get('/branches', [BranchController::class, 'index']);
        Route::get('/branches/{branch}', [BranchController::class, 'show']);
    });
    Route::middleware('role:owner')->group(function () {
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

    // ---------- Slip Gaji (dirombak di Sesi 7 & 8 — struktur tetap/partime) ----------
    Route::middleware('role:owner,hr')->group(function () {
        Route::post('salary-slips/bulk-generate', [SalarySlipController::class, 'bulkGenerate']);
        Route::apiResource('salary-slips', SalarySlipController::class);
        Route::get('salary-slips/{salary_slip}/preview-pdf', [SalarySlipController::class, 'previewPDF']);
        Route::get('salary-slips/{salary_slip}/download-pdf', [SalarySlipController::class, 'downloadPDF']);
    });

    // ---------- Distribusi Gaji (dirombak di Sesi 10 — email & whatsapp) ----------
    Route::middleware('role:owner,hr')->prefix('distribution')->group(function () {
        Route::post('send-email', [EmailController::class, 'sendBulk']);
        Route::post('send-whatsapp', [EmailController::class, 'sendBulkWhatsapp']);
        Route::get('history', [EmailController::class, 'history']);
    });

    // ---------- Dashboard ----------
    Route::middleware('role:owner,hr')->prefix('dashboard')->group(function () {
        Route::get('owner', [DashboardController::class, 'owner']);
        Route::get('hr', [DashboardController::class, 'hr']);
    });

    // ---------- Laporan (dirombak di Sesi 11) ----------
    Route::middleware('role:owner,hr')->prefix('reports')->group(function () {
        Route::get('salary-summary', [ReportController::class, 'salarySummary']);
        Route::get('salary-summary/export-pdf', [ReportController::class, 'exportPDF']);
        Route::get('employee/{employeeId}', [ReportController::class, 'employeeReport']);
        Route::get('statistics', [ReportController::class, 'statistics']);
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
});
