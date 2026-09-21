<?php

use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard', ['role' => 'Admin']);
        })->name('dashboard');
    });

    // Guru Routes
    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [AbsensiController::class, 'dashboard'])->name('dashboard');
        Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('riwayat');
    });

    // Guru & Admin Routes
    Route::middleware('role:guru,admin')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/absensi/{jadwal}', [AbsensiController::class, 'show'])->name('absensi.show');
        Route::post('/absensi/{jadwal}', [AbsensiController::class, 'store'])->name('absensi.store');
    });

    // Siswa Routes
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Siswa\DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/riwayat', [\App\Http\Controllers\Siswa\DashboardController::class, 'riwayat'])->name('riwayat');
    });
});

require __DIR__.'/auth.php';
