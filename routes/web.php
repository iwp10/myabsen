<?php

use App\Http\Controllers\Admin\ArsipController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\KoreksiAbsensiController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\PengaturanPeriodeController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::middleware('role:admin,guru,siswa')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    });

    // Admin Routes
    Route::middleware(['role:admin', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('jurusan', JurusanController::class)->except(['show']);
        Route::resource('kelas', KelasController::class)->except(['show'])->parameters([
            'kelas' => 'kelas', // to make parameter $kelas instead of $kela
        ]);
        Route::resource('mapel', MapelController::class)->except(['show']);

        Route::resource('guru', GuruController::class)->except(['show']);
        Route::post('guru/{guru}/reset-password', [GuruController::class, 'resetPassword'])->name('guru.reset-password');

        Route::resource('siswa', SiswaController::class)->except(['show'])->parameters([
            'siswa' => 'siswa',
        ]);
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::post('siswa/{siswa}/reset-password', [SiswaController::class, 'resetPassword'])->name('siswa.reset-password');

        Route::resource('jadwal', JadwalController::class)->except(['show']);

        Route::get('koreksi-absensi', [KoreksiAbsensiController::class, 'index'])->name('koreksi-absensi.index');
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
        Route::get('laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.exportPdf');

        Route::get('pengaturan-periode', [PengaturanPeriodeController::class, 'index'])->name('pengaturan-periode.index');
        Route::post('pengaturan-periode', [PengaturanPeriodeController::class, 'update'])->name('pengaturan-periode.update');

        Route::get('arsip', [ArsipController::class, 'index'])->name('arsip.index');
        Route::post('arsip/{jenis}/{id}/pulihkan', [ArsipController::class, 'pulihkan'])->name('arsip.pulihkan');
    });

    // Guru Routes
    Route::middleware(['role:guru', 'password.changed'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [AbsensiController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [GuruJadwalController::class, 'index'])->name('jadwal');
        Route::get('/koreksi-absensi', [AbsensiController::class, 'koreksiAbsensi'])->name('koreksi-absensi');
        Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('riwayat');
        Route::get('/riwayat/{kelas}/{mapel}', [AbsensiController::class, 'riwayatDetail'])->name('riwayat.detail');
        Route::get('/laporan/export', [AbsensiController::class, 'export'])->name('laporan.export');
    });

    // Guru & Admin Routes
    Route::middleware(['role:guru,admin', 'password.changed'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/absensi/{jadwal}', [AbsensiController::class, 'show'])->name('absensi.show');
        Route::post('/absensi/{jadwal}', [AbsensiController::class, 'store'])->name('absensi.store');
    });

    // Siswa Routes
    Route::middleware(['role:siswa', 'password.changed'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
    });
});
