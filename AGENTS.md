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
- Wajib merujuk [docs/DESAIN.md](docs/DESAIN.md) sebagai satu-satunya sumber kebenaran token desain dan komponen antarmuka.
- Gunakan komponen Blade `resources/views/components/ui/` (`card`, `stat-card`, `status-badge`, `table`, `filter-bar`, `empty-state`, `section-title`, `page-header`, `button`) dan helper presentasi murni `App\Support\Ui`. Dilarang menuliskan utilitas warna mentah tanpa pasangan `dark:` pada atribut class yang sama.
- DILARANG merangkai nama class Tailwind secara dinamis (misalnya `'text-' . $color . '-600'` atau `bg-{$color}`) karena tidak akan terkompilasi oleh Tailwind CSS. Tulis nama class secara literal dan utuh.
- Dilarang menggunakan teks redup terlarang: `text-(gray|slate|zinc)-(300|400|500)` di mode terang (kecuali placeholder/disabled) dan `dark:text-(gray|slate|zinc)-(500|600|700|800|900)` di mode gelap.
- Prinsip "Satu Warna Satu Arti" untuk status kehadiran (gaya tinted aksen dengan ikon + teks):
  | Status | Mode Terang | Mode Gelap | Ikon Tabler |
  |---|---|---|---|
  | **Hadir** | `bg-emerald-100 text-emerald-900 border-emerald-400` | `dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50` | check |
  | **Izin** | `bg-blue-100 text-blue-900 border-blue-400` | `dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50` | info |
  | **Sakit** | `bg-amber-100 text-amber-900 border-amber-400` | `dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50` | plus |
  | **Alpa** | `bg-red-100 text-red-900 border-red-400` | `dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50` | x |
  | **Belum diabsen** | `bg-slate-100 text-slate-800 border-slate-400 border-dashed` | `dark:bg-slate-700/50 dark:text-slate-200 dark:border-slate-400 border-dashed` | clock |
- Rasio kontras teks minimal 4.5:1 (WCAG AA) pada mode terang dan gelap (3:1 untuk teks besar $\ge$24px atau $\ge$18.66px bold, serta ikon/batas bermakna).
- Ukuran target sentuh mobile minimal 44x44px dengan jarak minimal 8px.
- Input form (`input`, `select`, `textarea`) wajib font 16px dan tinggi minimal 44px untuk mencegah zoom otomatis pada iOS.
- Bingkai kartu dan tabel: `border-2 border-slate-300 dark:border-slate-600`.
- Permukaan elevated (header grup hari/tanggal, panel, input, item jadwal): terang `bg-slate-100` (atau `bg-white`), gelap `dark:bg-slate-800 dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5`. Tidak boleh ada latar terang yang silau di mode gelap.
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


