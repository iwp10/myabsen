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
- Auth: Laravel Breeze (Blade), tanpa registrasi publik, login memakai username/NIP/NIS
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

## Konvensi Tampilan dan UI
- Gunakan komponen Blade `resources/views/components/ui/` (`card`, `stat-card`, `status-badge`, `table`, `filter-bar`, `empty-state`, `section-title`, `page-header`, `button`) dan helper presentasi murni `App\Support\Ui`.
- DILARANG merangkai nama class Tailwind secara dinamis (misalnya `'text-' . $color . '-600'`) karena tidak akan terkompilasi oleh Tailwind CSS. Tulis nama class secara literal dan utuh.
- Prinsip "Satu Warna Satu Arti" untuk status kehadiran:
  | Status | Mode Terang | Mode Gelap | Ikon Tabler |
  |---|---|---|---|
  | **Hadir** | `bg-emerald-700 text-white` | `dark:bg-emerald-500 dark:text-gray-900` | check |
  | **Izin** | `bg-blue-600 text-white` | `dark:bg-blue-400 dark:text-gray-900` | info |
  | **Sakit** | `bg-amber-400 text-gray-900` | `dark:bg-amber-400 dark:text-gray-900` | plus |
  | **Alpa** | `bg-red-600 text-white` | `dark:bg-red-400 dark:text-gray-900` | x |
  | **Belum diabsen** | `bg-gray-200 text-gray-800 border-2 border-dashed border-gray-400` | `dark:bg-gray-700 dark:text-gray-100 dark:border-gray-500` | clock |
- Rasio kontras teks minimal 4.5:1 (WCAG AA) pada mode terang dan gelap.
- Ukuran target sentuh mobile minimal 44x44px dengan jarak minimal 8px.
- Input form (`input`, `select`, `textarea`) wajib font 16px dan tinggi minimal 44px untuk mencegah zoom otomatis pada iOS.
- Bingkai kartu dan tabel: `border-2 border-gray-300 dark:border-gray-600`.
- Tabel daftar sederhana memakai class `responsive-stack-table` dengan atribut `data-label` di setiap sel data untuk berubah jadi tumpukan kartu di mobile tanpa duplikasi markup. Form absensi guru memakai tombol Simpan melekat (`sticky bottom-0`) dengan padding safe-area.

## Aturan kerja
- Aturan "PRD dulu baru kode": Jika ingin mengubah fitur atau aturan bisnis, perbarui `docs/PRD.md` terlebih dahulu, baru kemudian menulis kode.
- Kerjakan satu fase per tugas sesuai `docs/PROGRESS.md`. Jangan membuat fitur di luar MVP.
- Perubahan skema selalu lewat migration baru. Jangan edit migration yang sudah dijalankan.
- Hindari N+1 (pakai eager loading) dan pasang index sesuai PRD.
- Setiap aturan bisnis di PRD (AB-xx) harus punya test (feature test).
- Setelah tiap fase: jalankan `php artisan test`, perbarui baris fasenya di `docs/PROGRESS.md`, lalu ringkas file yang berubah.
- Dokumen (`docs/PRD.md`, `README.md`, `docs/PROGRESS.md`) harus diperbarui bersama kode dalam PR yang sama agar tidak tertinggal.
- Jangan menyentuh file `.env`, jangan commit file `.env` atau file dump SQL, dan jangan menaruh secret di kode.
- Jangan pernah menulis password atau kredensial asli di dokumen maupun di kode program (gunakan nilai contoh yang aman atau variabel environment).
- Jika ada yang ambigu atau bertentangan dengan PRD, tanya dulu, jangan berasumsi.

## Git dan tim
- Jangan commit langsung ke `main`. Selalu gunakan Pull Request (PR wajib). Kerja di branch `fitur/...`, `perbaikan/...`, `refactor/...`, `test/...`, `chore/...`, atau `docs/...`.
- Format commit: `feat:`, `fix:`, `refactor:`, `test:`, `chore:`, `docs:` diikuti deskripsi singkat.
- Setiap migrasi baru wajib disebut di PR ("jalankan php artisan migrate").
- Jangan ubah `AGENTS.md` dan `docs/PRD.md` kecuali diminta secara eksplisit.
- Sebelum membuat migration, pastikan branch sudah up to date dengan `main`.
- Di `docs/PROGRESS.md`, edit hanya baris fase yang sedang dikerjakan.
- Jangan pernah commit file `.env` atau file dump SQL.


