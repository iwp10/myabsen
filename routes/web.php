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

        Route::resource('jurusan', \App\Http\Controllers\Admin\JurusanController::class)->except(['show']);
        Route::resource('kelas', \App\Http\Controllers\Admin\KelasController::class)->except(['show'])->parameters([
            'kelas' => 'kelas' // to make parameter $kelas instead of $kela
        ]);
        Route::resource('mapel', \App\Http\Controllers\Admin\MapelController::class)->except(['show']);
        
        Route::resource('guru', \App\Http\Controllers\Admin\GuruController::class)->except(['show']);
        Route::post('guru/{guru}/reset-password', [\App\Http\Controllers\Admin\GuruController::class, 'resetPassword'])->name('guru.reset-password');
        
        Route::resource('siswa', \App\Http\Controllers\Admin\SiswaController::class)->except(['show'])->parameters([
            'siswa' => 'siswa'
        ]);
        Route::post('siswa/import', [\App\Http\Controllers\Admin\SiswaController::class, 'import'])->name('siswa.import');
        Route::post('siswa/{siswa}/reset-password', [\App\Http\Controllers\Admin\SiswaController::class, 'resetPassword'])->name('siswa.reset-password');
        
        Route::resource('jadwal', \App\Http\Controllers\Admin\JadwalController::class)->except(['show']);
        
        Route::get('laporan', [\App\Http\Controllers\Admin\LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export', [\App\Http\Controllers\Admin\LaporanController::class, 'export'])->name('laporan.export');
    });

    // Guru Routes
    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [AbsensiController::class, 'dashboard'])->name('dashboard');
        Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('riwayat');
        Route::get('/laporan/export', [AbsensiController::class, 'export'])->name('laporan.export');
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
