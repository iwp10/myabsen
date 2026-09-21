# MyAbsen

Absensi per mata pelajaran untuk SMK. Guru mengabsen siswa di kelas yang ia ajar, dan siswa bisa melihat status kehadirannya sendiri.

> Proyek PKM. Status terbaru tiap fase ada di [`docs/PROGRESS.md`](docs/PROGRESS.md).

## Fitur MVP

- **Admin:** kelola jurusan, kelas, mapel, guru, siswa, dan jadwal; impor siswa dari Excel; koreksi absensi; lihat semua rekap.
- **Guru:** lihat jadwal mengajar hari ini, absen per mata pelajaran (default Hadir, ubah yang Izin/Sakit/Alpa), rekap dan ekspor Excel/PDF.
- **Siswa:** lihat status kehadiran hari ini per mapel, riwayat, dan persentase kehadiran.

Detail lengkap ada di [`docs/PRD.md`](docs/PRD.md).

## Teknologi

- Laravel 12, PHP 8.3 atau lebih baru
- MySQL 8
- Blade, Tailwind CSS, Alpine.js
- Laravel Breeze (auth), Pest (test), maatwebsite/excel, barryvdh/laravel-dompdf

## Menjalankan di komputer lokal

Prasyarat: PHP 8.3+, Composer, Node.js, MySQL (paling mudah lewat [Laragon](https://laragon.org) di Windows), dan Git.

1. Nyalakan MySQL (di Laragon klik **Start All**), lalu buat database kosong bernama `myabsen`.
2. Clone repo:
   ```
   git clone https://github.com/iwp10/myabsen.git
   cd myabsen
   ```
3. Pasang dependensi:
   ```
   composer install
   npm install
   ```
4. Salin file environment dan buat key:
   ```
   copy .env.example .env
   php artisan key:generate
   ```
   (Di Git Bash, Linux, atau macOS: `cp .env.example .env`)

   Bawaan `.env.example` sudah memakai MySQL dengan user `root` tanpa password (bawaan Laragon). Kalau MySQL-mu berbeda, sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env`.
5. Buat tabel dan isi data demo:
   ```
   php artisan migrate --seed
   ```
6. Siapkan tampilan, pilih salah satu:
   - `npm run build`: dijalankan sekali, cukup untuk mencoba aplikasi
   - `npm run dev`: dibiarkan berjalan di terminal kedua, dipakai saat mengedit tampilan
7. Jalankan aplikasi:
   ```
   php artisan serve
   ```
8. Buka `http://127.0.0.1:8000`.

## Setelah `git pull`

Jalankan yang relevan:
- `composer install` jika `composer.lock` berubah
- `npm install` jika `package.json` atau `package-lock.json` berubah
- `php artisan migrate` jika ada migration baru

Kalau ragu, jalankan ketiganya. Tidak berbahaya.

## Akun demo

Hanya untuk development lokal. Jangan dipakai di produksi. Login memakai **username**, bukan email.

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Guru | `guru1` | `password` |
| Siswa | `siswa1` | `password` |

## Perintah yang sering dipakai

| Perintah | Fungsi |
|---|---|
| `php artisan test` | Menjalankan semua test. Memakai SQLite in-memory, jadi tidak menyentuh database MySQL |
| `php vendor/bin/pint` | Merapikan format kode (jalankan sebelum commit) |
| `php artisan migrate:fresh --seed` | Menghapus **semua tabel** di database yang ada di `.env`, lalu mengisi ulang data demo |
| `php artisan config:clear` | Membersihkan cache konfigurasi (jalankan setelah mengubah `.env`) |
| `npm run build` | Build aset untuk produksi |

Catatan tentang test: pengaturan `DB_CONNECTION=sqlite` dan `DB_DATABASE=:memory:` di `phpunit.xml` **tidak boleh dikomentari atau dihapus**. Kalau dihapus, test akan berjalan di database MySQL milikmu dan menghapus datanya.

## Masalah yang sering muncul

| Gejala | Penyebab dan solusi |
|---|---|
| `Unknown database 'laravel'` atau `'myabsen'` | Database belum dibuat, atau baris `DB_*` di `.env` masih diawali `#`. Buat database `myabsen`, hapus `#`, lalu jalankan `php artisan config:clear` |
| `Connection refused` | MySQL belum menyala. Klik **Start All** di Laragon |
| `Deprecated: PDO::MYSQL_ATTR_SSL_CA` | Hanya peringatan, tidak berbahaya. Abaikan |
| Login `admin` ditolak | Data demo belum ada. Jalankan `php artisan migrate --seed` |
| Halaman tampil tanpa desain | Aset belum dibuat. Jalankan `npm run build` atau `npm run dev` |
| `ls -la` error di PowerShell | PowerShell tidak mengenal opsi itu. Pakai `dir` atau `ls` saja |

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
- Jangan `git checkout main` sebelum Pull Request branch-mu di-merge.
- Siapa pun yang mengubah langkah setup (paket baru, variabel `.env` baru) memperbarui README ini di Pull Request yang sama.

## Tim

| Nama | Peran | Fase yang dikerjakan |
|---|---|---|
| (nama) | (peran) | (fase) |
| (nama) | (peran) | (fase) |