<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Batas Hari Koreksi Absensi
    |--------------------------------------------------------------------------
    |
    | Menentukan jumlah hari maksimum ke belakang (termasuk hari ini)
    | di mana guru diizinkan untuk mengabsen atau mengoreksi sesi absensi.
    | Contoh: 7 berarti hari ini dan 6 hari sebelumnya.
    |
    */
    'batas_koreksi_hari' => 7,

    /*
    |--------------------------------------------------------------------------
    | Batas Persentase Kehadiran Rendah
    |--------------------------------------------------------------------------
    |
    | Ambang batas persentase kehadiran siswa yang memerlukan perhatian (dalam persen).
    | Siswa dengan persentase di bawah batas ini akan diberi penanda khusus pada
    | riwayat absensi guru.
    |
    */
    'batas_kehadiran_rendah' => (int) env('ABSENSI_BATAS_KEHADIRAN_RENDAH', 75),

    /*
    |--------------------------------------------------------------------------
    | Batas Ekspor Laporan
    |--------------------------------------------------------------------------
    |
    | Batas jumlah sheet untuk ekspor Excel multi-sheet dan batas jumlah baris
    | untuk ekspor PDF agar tidak membebani memori server (OOM) dan CPU.
    |
    */
    'batas_sheet_ekspor' => 50,
    'batas_baris_pdf' => 2000,

    /*
    |--------------------------------------------------------------------------
    | Password Awal Pengguna Baru & Reset
    |--------------------------------------------------------------------------
    |
    | Menentukan password default untuk pembuatan akun baru guru/siswa
    | serta proses reset password oleh admin.
    |
    */
    'password_awal' => env('ABSENSI_PASSWORD_AWAL', 'password'),

    /*
    |--------------------------------------------------------------------------
    | Kredensial Administrator Awal (AdminSeeder)
    |--------------------------------------------------------------------------
    |
    | Digunakan oleh AdminSeeder untuk menginisialisasi akun admin awal.
    |
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'username' => env('ADMIN_USERNAME', 'admin'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
