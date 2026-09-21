# MyAbsen: Aturan untuk Agent

## Proyek
MyAbsen adalah website absensi per mata pelajaran untuk SMK (proyek PKM). Ada tiga role: admin, guru, siswa.

Sumber kebenaran:
- `docs/PRD.md`: fitur, aturan bisnis, skema database
- `docs/PROGRESS.md`: status fase dan pembagian kerja

Baca keduanya sebelum mengerjakan tugas apa pun. Jika kode bertentangan dengan PRD, PRD yang benar.

## Stack (jangan diganti tanpa persetujuan)
- Laravel 12, PHP 8.3, MySQL 8
- Blade + Tailwind CSS + Alpine.js (tanpa React, Vue, atau Livewire)
- Auth: Laravel Breeze (Blade), tanpa registrasi publik, login memakai `username`
- Excel: maatwebsite/excel. PDF: barryvdh/laravel-dompdf
- Test: feature test (Pest atau PHPUnit)
- Paket baru di luar daftar ini: tanya dulu, jangan langsung dipasang.

## Konvensi kode
- Nama tabel, model, dan istilah domain memakai Bahasa Indonesia (Siswa, Kelas, Mapel, Jadwal, SesiAbsensi, DetailAbsensi). Istilah framework tetap Inggris.
- Semua teks UI, pesan validasi, dan flash message dalam Bahasa Indonesia.
- Struktur: Controller tipis, logika bisnis di `app/Services`, validasi di FormRequest, otorisasi di Policy.
- Role dicek lewat middleware `role`, bukan dengan `if` manual di controller.
- Status kehadiran hanya didefinisikan di satu tempat: enum `App\Enums\StatusKehadiran`.
- Timezone Asia/Jakarta. Tanggal dan jam absensi selalu dari server, tidak pernah dari input pengguna.
- Format kode dengan Laravel Pint sebelum commit.

## Struktur per fitur
- Setiap fitur berdiri sendiri: satu Controller, satu Service, FormRequest, Policy, folder view (`resources/views/<fitur>`), dan test tersendiri.
- Jangan menaruh logika fitur di file fitur lain.
- Logika yang dipakai bersama (misalnya hitung persentase kehadiran) ditaruh di satu Service bersama, tidak disalin ke banyak tempat.

## Aturan domain (ringkas, detail ada di PRD)
- Status: `hadir`, `izin`, `sakit`, `alpa`.
- "Belum diabsen" berarti belum ada baris `detail_absensi`. Jangan pernah disimpan sebagai `alpa`.
- Sesi absensi unik per (`jadwal_id`, `tanggal`).
- Data siswa, guru, kelas, dan mapel yang sudah punya riwayat absensi tidak dihapus permanen (pakai soft delete).

## Aturan kerja
- Kerjakan satu fase per tugas sesuai `docs/PROGRESS.md`. Jangan membuat fitur di luar MVP.
- Perubahan skema selalu lewat migration baru. Jangan edit migration yang sudah dijalankan.
- Hindari N+1 (pakai eager loading) dan pasang index sesuai PRD.
- Setiap aturan bisnis di PRD (AB-xx) harus punya test.
- Setelah tiap fase: jalankan `php artisan test`, perbarui baris fasenya di `docs/PROGRESS.md`, lalu ringkas file yang berubah.
- Jangan menyentuh file `.env` dan jangan menaruh secret di kode.
- Jika ada yang ambigu atau bertentangan dengan PRD, tanya dulu, jangan berasumsi.

## Git dan tim
- Jangan commit langsung ke `main`. Kerja di branch `fitur/...` atau `perbaikan/...`.
- Format commit: `feat:`, `fix:`, `docs:`, `test:`, `chore:` diikuti deskripsi singkat.
- Jangan ubah `AGENTS.md` dan `docs/PRD.md` kecuali diminta secara eksplisit.
- Sebelum membuat migration, pastikan branch sudah up to date dengan `main`.
- Di `docs/PROGRESS.md`, edit hanya baris fase yang sedang dikerjakan.
- Jangan pernah commit file `.env`.
