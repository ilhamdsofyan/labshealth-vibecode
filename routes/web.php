<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ClinicAgendaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VisitorHistoryController;
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
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Google OAuth
    Route::get('auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/riwayat-kunjungan', [ProfileController::class, 'myHistory'])->name('profile.my-history');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
});

// ─── Authenticated + Permission Protected Routes ────────────
Route::middleware(['auth', 'permission'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('agendas', ClinicAgendaController::class)->except(['show']);

    // Visit Recording
    Route::resource('visits', VisitController::class);
    Route::post('visits/offline-sync', [VisitController::class, 'syncOffline'])->name('visits.offline-sync');
    Route::patch('visits/{visit}/toggle-rest', [VisitController::class, 'toggleRest'])->name('visits.toggle-rest');
    Route::patch('visits/{visit}/toggle-pulang', [VisitController::class, 'togglePulang'])->name('visits.toggle-pulang');
    Route::get('riwayat-kunjungan/siswa/{student}', [VisitorHistoryController::class, 'student'])->name('visitors.students.history');
    Route::get('riwayat-kunjungan/pegawai/{employee}', [VisitorHistoryController::class, 'employee'])->name('visitors.employees.history');
    Route::get('riwayat-kunjungan/search', [VisitorHistoryController::class, 'search'])->name('visitors.search');

    // Reports
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/acc-pulang', [ReportController::class, 'accPulang'])->name('reports.acc-pulang');
    Route::get('reports/analytics', [ReportController::class, 'analytics'])->name('reports.analytics');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/maintenance', [SettingsController::class, 'maintenance'])->name('settings.maintenance');

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
            Route::put('students/{student}/detail', [StudentController::class, 'updateDetail'])->name('students.update-detail');
            Route::delete('students/{student}/avatar', [StudentController::class, 'removeAvatar'])->name('students.remove-avatar');
            Route::resource('students', StudentController::class)->names('students')->except(['show']);

            Route::get('employees/search', [EmployeeController::class, 'search'])->name('employees.search');
            Route::delete('employees/{employee}/avatar', [EmployeeController::class, 'removeAvatar'])->name('employees.remove-avatar');
            Route::resource('employees', EmployeeController::class)->names('employees')->except(['show']);

            Route::get('diseases/search', [DiseaseController::class, 'search'])->name('diseases.search');
            Route::resource('diseases', DiseaseController::class)->names('diseases')->except(['show']);

            Route::get('medications/search', [MedicationController::class, 'search'])->name('medications.search');
            Route::resource('medications', MedicationController::class)->names('medications')->except(['show']);
        });

        // Import Legacy Data
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import', [ImportController::class, 'import'])->name('import.store');
        Route::get('import/template', [ImportController::class, 'downloadTemplate'])->name('import.template');
    });
});
