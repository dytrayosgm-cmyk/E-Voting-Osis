<?php

use App\Http\Controllers\Admin\AdminAuthController;
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
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');
    });
});
