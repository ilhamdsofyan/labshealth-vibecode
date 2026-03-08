<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClinicAgendaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\Admin\DiseaseController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MedicationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StudentClassHistoryController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// ─── Guest Routes ────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.post');

    // Google OAuth
    Route::get('auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Authenticated + Permission Protected Routes ────────────
Route::middleware(['auth', 'permission'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('agendas', [ClinicAgendaController::class, 'index'])->name('agendas.index');
    Route::get('agendas/create', [ClinicAgendaController::class, 'create'])->name('agendas.create');
    Route::post('agendas', [ClinicAgendaController::class, 'store'])->name('agendas.store');

    // Visit Recording
    Route::resource('visits', VisitController::class);

    // Reports
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/acc-pulang', [ReportController::class, 'accPulang'])->name('reports.acc-pulang');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    // Admin Panel
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('menus', MenuController::class);
        Route::post('menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
        
        // Master Data
        Route::resource('class-histories', StudentClassHistoryController::class)->only(['index']);

        // Master Data CRUD
        Route::prefix('master')->name('master.')->group(function () {
            Route::get('students/search', [StudentController::class, 'search'])->name('students.search');
            Route::resource('students', StudentController::class)->names('students');

            Route::get('employees/search', [EmployeeController::class, 'search'])->name('employees.search');
            Route::resource('employees', EmployeeController::class)->names('employees');

            Route::get('diseases/search', [DiseaseController::class, 'search'])->name('diseases.search');
            Route::resource('diseases', DiseaseController::class)->names('diseases');

            Route::get('medications/search', [MedicationController::class, 'search'])->name('medications.search');
            Route::resource('medications', MedicationController::class)->names('medications');
        });

        // Import Legacy Data
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import', [ImportController::class, 'import'])->name('import.store');
        Route::get('import/template', [ImportController::class, 'downloadTemplate'])->name('import.template');
    });
});
