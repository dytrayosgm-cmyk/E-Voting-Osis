<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Voter\VotingBooth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Voter (Bilik Suara Digital) — publik, tidak butuh login akun.
| Identitas divalidasi lewat NIS di dalam komponen VotingBooth sendiri.
|--------------------------------------------------------------------------
*/
Route::get('/', VotingBooth::class)->name('voter.booth');

/*
|--------------------------------------------------------------------------
| Rute Admin — dilindungi guard 'admin' (lihat config/auth.php).
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('secure.domain')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

        Route::get('/management', [AdminManagementController::class, 'index'])->name('management.index');
        Route::get('/management/create', [AdminManagementController::class, 'create'])->name('management.create');
        Route::post('/management', [AdminManagementController::class, 'store'])->name('management.store');
        Route::get('/password', [AdminManagementController::class, 'passwordForm'])->name('password.form');
        Route::post('/password', [AdminManagementController::class, 'updatePassword'])->name('password.update');
    });
});
