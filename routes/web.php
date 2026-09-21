<?php

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
        Route::get('/dashboard', function () {
            return view('dashboard', ['role' => 'Guru']);
        })->name('dashboard');
    });

    // Siswa Routes
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard', ['role' => 'Siswa']);
        })->name('dashboard');
    });
});

require __DIR__.'/auth.php';
