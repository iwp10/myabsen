# MyAbsen

Absensi per mata pelajaran untuk SMK. Guru mengabsen siswa di kelas yang ia ajar, dan siswa bisa melihat status kehadirannya sendiri.

> Proyek PKM. Status: dalam pengembangan (lihat `docs/PROGRESS.md`).

## Fitur MVP

- **Admin:** kelola jurusan, kelas, mapel, guru, siswa, dan jadwal; impor siswa dari Excel; koreksi absensi; lihat semua rekap.
- **Guru:** lihat jadwal mengajar hari ini, absen per mata pelajaran (default Hadir, ubah yang Izin/Sakit/Alpa), rekap dan ekspor Excel/PDF.
- **Siswa:** lihat status kehadiran hari ini per mapel, riwayat, dan persentase kehadiran.

Detail lengkap ada di [`docs/PRD.md`](docs/PRD.md).

## Teknologi

- Laravel 12, PHP 8.3
- MySQL 8
- Blade, Tailwind CSS, Alpine.js
- Laravel Breeze (auth), maatwebsite/excel, barryvdh/laravel-dompdf

## Menjalankan di komputer lokal

Prasyarat: PHP 8.3, Composer, Node.js, MySQL (paling mudah lewat [Laragon](https://laragon.org) di Windows), dan Git.

1. Clone repo:
   ```
   git clone https://github.com/iwp10/myabsen.git
   cd myabsen
   ```
2. Pasang dependensi:
   ```
   composer install
   npm install
   ```
3. Salin file environment dan buat key:
   ```
   copy .env.example .env
   php artisan key:generate
   ```
   (Di Git Bash, Linux, atau macOS: `cp .env.example .env`)
4. Buat database kosong bernama `myabsen` di MySQL, lalu cek `DB_USERNAME` dan `DB_PASSWORD` di `.env` sudah sesuai.
5. Jalankan migration dan data demo:
   ```
   php artisan migrate --seed
   ```
6. Jalankan aplikasi (dua terminal):
   ```
   php artisan serve
   npm run dev
   ```
7. Buka `http://127.0.0.1:8000`.

## Akun demo

Hanya untuk development lokal. Jangan dipakai di produksi.

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Guru | `guru1` | `password` |
| Siswa | `siswa1` | `password` |

## Perintah yang sering dipakai

| Perintah | Fungsi |
|---|---|
| `php artisan test` | Menjalankan semua test |
| `php vendor/bin/pint` | Merapikan format kode (jalankan sebelum commit) |
| `php artisan migrate:fresh --seed` | Reset database lokal dan isi ulang data demo |
| `npm run build` | Build aset untuk produksi |

## Dokumen proyek

| File | Isi |
|---|---|
| [`AGENTS.md`](AGENTS.md) | Aturan dan konvensi untuk AI agent (Antigravity) |
| [`docs/PRD.md`](docs/PRD.md) | Fitur, aturan bisnis, dan skema database |
| [`docs/PROGRESS.md`](docs/PROGRESS.md) | Status fase dan pembagian kerja tim |
| `docs/DEPLOY.md` | Langkah deploy ke hosting (dibuat di Fase 8) |

## Alur kerja tim

1. `git checkout main` lalu `git pull`.
2. Buat branch: `git checkout -b fitur/nama-fitur` (atau `perbaikan/nama-masalah`).
3. Kerjakan, lalu jalankan `php artisan test` dan `php vendor/bin/pint`.
4. Commit dengan format `feat:`, `fix:`, `docs:`, `test:`, atau `chore:`.
5. Push dan buka Pull Request. Merge setelah direview.

Aturan penting:
- Jangan push langsung ke `main` dan jangan commit file `.env`.
- Ubah fitur atau aturan bisnis di `docs/PRD.md` **dulu** (lewat PR), baru kodenya.
- Perubahan skema selalu lewat migration baru. Satu orang mengubah skema pada satu waktu.

## Tim

(Isi nama anggota dan pembagian peran.)
