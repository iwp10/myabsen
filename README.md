# 🏫 MyAbsen

> **Aplikasi Absensi Siswa per Mata Pelajaran untuk SMK**

MyAbsen adalah sistem informasi absensi siswa berbasis web yang dirancang khusus untuk Sekolah Menengah Kejuruan (SMK) sebagai proyek PKM. Berbeda dengan absensi harian biasa, sistem ini mencatat kehadiran **per mata pelajaran**, memungkinkan pelacakan kehadiran siswa di setiap jam pelajaran secara akurat.

---

## 📑 Daftar Isi
# 🏫 MyAbsen

> **Aplikasi Absensi Siswa per Mata Pelajaran untuk SMK**

MyAbsen adalah sistem informasi absensi siswa berbasis web yang dirancang khusus untuk Sekolah Menengah Kejuruan (SMK) sebagai proyek PKM. Berbeda dengan absensi harian biasa, sistem ini mencatat kehadiran **per mata pelajaran**, memungkinkan pelacakan kehadiran siswa di setiap jam pelajaran secara akurat.

---

## 📑 Daftar Isi
- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Struktur Folder](#-struktur-folder)
- [Panduan Instalasi Lokal](#-panduan-instalasi-lokal)
- [Pembaruan (Setelah git pull)](#-pembaruan-setelah-git-pull)
- [Kredensial Demo](#-kredensial-demo)
- [Panduan Seeder & Migrasi](#-panduan-seeder--migrasi-lokal-vs-produksi)
- [Variabel Environment (.env)](#-variabel-environment-env)
- [Perintah Penting](#-perintah-penting)
- [Pemecahan Masalah](#-pemecahan-masalah)
- [Dokumentasi Proyek](#-dokumentasi-proyek)
- [Alur Kerja Tim](#-alur-kerja-tim)

---

## ✨ Fitur Utama

Aplikasi memiliki tiga peran pengguna dengan batasan akses masing-masing:

### 🌟 Fitur Unggulan
- **Otentikasi Fleksibel:** Pengguna dapat masuk menggunakan Username bawaan, ATAU Nomor Induk (NIP untuk Guru, NIS untuk Siswa).
- **Keamanan Akun:** Wajib ganti password pada login pertama kali bagi akun baru atau yang baru direset (`must_change_password`).
- **Aksesibilitas Visual:** Dilengkapi *toggle* Mode Terang dan Mode Gelap (*Light/Dark Mode*) berbasis Alpine.js dan Tailwind CSS (setelan bawaan: Mode Terang).
- **Identitas Sekolah:** Antarmuka disesuaikan dengan SMK Mandiri 02 Balaraja menggunakan font modern Inter dan Tabler Icons (inline SVG).

### 👑 Admin
- **Dashboard:** Ringkasan statistik master data (siswa, guru, kelas, mapel) dan pintasan aksi cepat operasional.
- **Master Data:** Mengelola jurusan, kelas, mata pelajaran, guru, dan siswa dengan perlindungan riwayat data (*soft delete*).
- **Import Data:** Memasukkan data siswa secara massal melalui file Excel dengan validasi duplikasi.
- **Manajemen Jadwal:** Mengatur jadwal pelajaran dengan validasi pencegahan jadwal bentrok dan kewajiban keselarasan periode dengan kelasnya.
- **Menu Koreksi Absensi:** Meninjau jadwal dan status sesi absensi pada tanggal lampau per kelas serta membuka form koreksi/susulan historis kapan saja (bukan masa depan).
- **Pengaturan Periode:** Mengelola tahun ajaran aktif dan semester aktif langsung dari antarmuka web (disimpan di basis data tabel `pengaturan`), disertai saran otomatis berbasis kalender dan modal konfirmasi.
- **Data Terhapus (Arsip):** Meninjau data master yang telah di-soft-delete (Guru, Siswa, Kelas, Mapel) dalam 4 tab dan memulihkannya kembali secara aman dengan validasi dependensi dan pencegahan bentrok.
- **Laporan & Ekspor:** Mengunduh rekap absensi sekolah dalam format Excel (multi-sheet per kelas-mapel) dan dokumen cetak PDF resmi, dilengkapi filter jurusan, kelas, mapel, bulan, dan periode, serta batas ekspor aman (maksimal 50 sheet Excel dan 2000 baris PDF via `config/absensi.php`).

### 👨‍🏫 Guru
- **Dashboard:** Menampilkan jadwal mengajar pada hari tersebut, ringkasan total mengajar, dan pintasan ke koreksi absensi.
- **Jadwal Mengajar:** Halaman jadwal mingguan interaktif dengan kartu jadwal memuat tanggal 7 hari terakhir yang cocok dan badge status sesi.
- **Menu Jadwal & Koreksi Absensi:** Mengakses jadwal mengajar dan mengoreksi/mengisi susulan absensi dalam jendela **7 hari terakhir** (hari ini s.d. H-6 yang harinya cocok).
- **Absensi Cepat:** Seluruh siswa kelas tampil dengan status bawaan **Hadir**. Guru hanya mengubah status siswa yang *Izin*, *Sakit*, atau *Alpa* (keterangan opsional).
- **Riwayat Dua Tingkat:** Meninjau riwayat kehadiran per kelas & mapel dalam format matriks per pertemuan (P1..Pn), dengan penanda "(nonaktif)" untuk siswa terhapus berriwayat.
- **Ekspor Laporan:** Mengekspor rekap absensi kelas yang diampu ke format Excel multi-sheet per kelas-mapel (tanpa PDF).

### 🎓 Siswa (Read-Only)
- **Dashboard:** Melihat status kehadiran harian per mata pelajaran secara langsung (*Belum diabsen / Hadir / Izin / Sakit / Alpa*), kartu persentase kehadiran, dan ringkasan kehadiran transparan.
- **Riwayat Kehadiran:** Meninjau histori kehadiran lengkap dengan filter tanggal dan mata pelajaran, serta persentase kehadiran per mapel (rumus AB-07: Hadir, Izin, Sakit dihitung positif; Alpa sebagai pengurang).

---

## 🛠️ Teknologi

Proyek ini dibangun menggunakan *stack* teknologi resmi:

- **Backend:** Laravel 12 (`laravel/framework: ^12.0`), PHP 8.3+
- **Pola Arsitektur (Separation of Concerns):**
  - **Thin Controller:** Menerima request HTTP, memanggil Service yang sesuai, dan mengembalikan response atau view.
  - **FormRequest:** Khusus memvalidasi integritas input pengguna di sisi server.
  - **Policy (`JadwalPolicy`):** Memusatkan otorisasi hak akses guru dan hak koreksi historis admin.
  - **Service Pattern (`AbsensiService`, `LaporanService`, `ArsipService`, `PeriodeService`, `PasswordAwalService`):** Pusat seluruh *business logic*, transaksi absensi, kalkulasi persentase kehadiran, ekspor laporan, pemulihan data terhapus, dan tata kelola akun aman.
  - **Model:** Khusus menangani *relationships*, *query scopes*, dan *persistence concerns* (termasuk *soft deletes*).
  - **View:** Blade Templating dengan Tailwind CSS dan Alpine.js untuk layer presentasi.
- **Database:** MySQL 8
- **Frontend:** Blade Templating, Tailwind CSS v3 (`tailwindcss: ^3.1.0`), Alpine.js (`alpinejs: ^3.4.2`)
- **Autentikasi:** Laravel Breeze (`laravel/breeze: ^2.4`, Blade)
- **Testing:** Pest (`pestphp/pest: ^4.7`) dan PHPUnit (Feature & Unit Testing)
- **Library Tambahan:** `maatwebsite/excel: ^4.0` (Export/Import Excel), `barryvdh/laravel-dompdf: ^3.1` (Export PDF)

---

## 📂 Struktur Folder

```text
myabsen/
├── app/
│   ├── Enums/             # Enum StatusKehadiran (hadir, izin, sakit, alpa)
│   ├── Exports/           # LaporanAbsensiExport, LaporanAbsensiPerKelasSheet
│   ├── Http/
│   │   ├── Controllers/   # Admin, Guru, Siswa, Auth, Profile
│   │   ├── Middleware/    # RoleMiddleware, EnsurePasswordChanged
│   │   └── Requests/      # FormRequest validasi per modul
│   ├── Imports/           # SiswaImport (impor Excel)
│   ├── Models/            # Model Eloquent (User, Guru, Siswa, Kelas, Mapel, Jadwal, SesiAbsensi, DetailAbsensi, Pengaturan)
│   ├── Policies/          # JadwalPolicy
│   ├── Services/          # AbsensiService, LaporanService, ArsipService, PeriodeService, PasswordAwalService
│   └── Support/           # KelasMapel (parser format kelas-mapel)
├── config/                # absensi.php, app.php, database.php, dll.
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Migrasi skema database
│   └── seeders/           # DatabaseSeeder, AdminSeeder, DemoSeeder
├── docs/                  # PRD.md, PROGRESS.md
├── resources/views/       # Blade templates (admin, guru, siswa, layouts, components)
├── routes/                # web.php, auth.php, console.php
└── tests/                 # Feature & Unit tests (Pest & PHPUnit)
```

---

## 📸 Preview Tampilan Aplikasi
**

## 🚀 Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek di komputer lokal bagi developer baru.

### Prasyarat
Pastikan komputer Anda sudah terinstal:
- PHP 8.3 atau lebih baru
- Composer
- Node.js & NPM
- MySQL (Direkomendasikan menggunakan [Laragon](https://laragon.org) untuk pengguna Windows)
- Git

### Langkah Instalasi Berurutan

1. **Clone Repository**
   ```bash
   git clone https://github.com/iwp10/myabsen.git
   cd myabsen
   ```

2. **Install Dependensi Backend & Frontend**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   Salin file konfigurasi contoh ke `.env`:
   ```bash
   copy .env.example .env
   ```
   *(Untuk macOS/Linux atau Git Bash, gunakan `cp .env.example .env`)*

4. **Siapkan Database di MySQL**
   Nyalakan servis MySQL (di Laragon, klik **Start All**), lalu buat **dua database kosong**:
   - `myabsen` : Database aplikasi utama.
   - `myabsen_test` : Database khusus automated test (sesuai konfigurasi `phpunit.xml` agar pengujian tidak menyentuh database utama).

   > **Catatan:** File `.env.example` bawaan menggunakan koneksi MySQL `root` tanpa password (standar Laragon). Jika pengaturan MySQL Anda berbeda, sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di file `.env`.

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database & Seeder Demo**
   Jalankan migrasi dan isi data demo lengkap:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Di lingkungan non-produksi, perintah ini otomatis menjalankan `DemoSeeder` via `DatabaseSeeder`.*

7. **Kompilasi Aset Frontend**
   Pilih salah satu dari perintah berikut:
   - `npm run build` : Dikompilasi sekali untuk menjalankan aplikasi.
   - `npm run dev` : Dijalankan di terminal terpisah selama proses pengembangan tampilan.

8. **Jalankan Server Lokal**
   Buka terminal baru dan jalankan:
   ```bash
   php artisan serve
   ```

9. **Akses Aplikasi**
   Buka browser dan kunjungi: `http://127.0.0.1:8000`

---

## 🔄 Pembaruan (Setelah `git pull`)

Setiap kali Anda menarik pembaruan terbaru dari branch utama (`git pull`), jalankan perintah berikut secara berurutan:

1. **Pembaruan dependensi PHP:**
   ```bash
   composer install
   ```
2. **Pembaruan dependensi Frontend:**
   ```bash
   npm install
   ```
3. **Pembaruan skema database:**
   ```bash
   php artisan migrate
   ```

> 💡 **Tips:** Menjalankan ketiga perintah ini setelah setiap `git pull` adalah praktik terbaik untuk memastikan lingkungan lokal selalu sinkron.

---

## 🔑 Kredensial Demo

Data demo ini dibuat oleh `DemoSeeder` khusus untuk lingkungan pengembangan lokal (*development*):

| Role | Username / Nomor Induk | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Guru | `guru1` ATAU `198001012000011001` (NIP) | `password` |
| Siswa | `siswa1` ATAU `1001` (NIS) | `password` |

*(Tersedia pula siswa demo lain: `siswa2` s.d. `siswa5` dengan NIS `1002` s.d. `1005` dan password `password`).*

> 💡 **Info:** Anda dapat mencoba fitur login fleksibel dengan memasukkan NIP atau NIS pada kolom Username.
> ⚠️ **Peringatan:** Akun demo ini hanya berlaku di lingkungan non-produksi. `DemoSeeder` secara otomatis diblokir di lingkungan produksi.

---

## 🏭 Panduan Seeder & Migrasi (Lokal vs Produksi)

### 💻 Lingkungan Lokal (Development)
Untuk inisialisasi ulang database lokal beserta data demo lengkap:
```bash
php artisan migrate:fresh --seed
```
*`DatabaseSeeder` otomatis memanggil `DemoSeeder` yang mengisi data admin, guru, siswa, kelas, jadwal, dan histori absensi contoh.*

### 🚀 Lingkungan Produksi (Deployment)
Jalankan migrasi skema dan inisialisasi akun administrator tunggal yang aman:
```bash
php artisan migrate --force
php artisan db:seed --class=AdminSeeder --force
```

> ⚠️ **PERINGATAN KERAS:** **Jangan pernah menjalankan `migrate:fresh` di server produksi!** `migrate:fresh` menghapus seluruh tabel dan riwayat absensi.
> 💡 **Proteksi Otomatis:** `DemoSeeder` secara ketat menolak dijalankan jika `APP_ENV=production`.
> 📖 **Panduan deploy:** segera hadir

---

## ⚙️ Variabel Environment (.env)

Aplikasi menggunakan variabel konfigurasi berikut (sesuai `.env.example` dan `config/absensi.php`):

| Variabel | Deskripsi | Aturan & Nilai Bawaan |
|---|---|---|
| `APP_TIMEZONE` | Zona waktu aplikasi dan absensi server. | `Asia/Jakarta` |
| `DB_DATABASE` | Nama basis data aplikasi utama. | `myabsen` |
| `ABSENSI_PASSWORD_AWAL` | Password default untuk pembuatan akun baru guru/siswa & reset password oleh admin. | Di produksi: wajib minimal 8 karakter dan bukan `password`. Di lokal: fallback ke `password`. |
| `ADMIN_NAME` | Nama akun administrator awal untuk `AdminSeeder`. | Opsional (bawaan: `Administrator`). |
| `ADMIN_USERNAME` | Username administrator awal untuk `AdminSeeder`. | Opsional (bawaan: `admin`). |
| `ADMIN_PASSWORD` | Password administrator awal untuk `AdminSeeder`. | Di produksi: minimal 12 karakter dan bukan `password`. Jika kosong, dibuat acak 16 karakter dan ditampilkan sekali di terminal. |

Variabel batas keamanan internal pada `config/absensi.php`:
- `batas_koreksi_hari`: Batas jendela hari koreksi absensi guru (bawaan: 7 hari).
- `batas_sheet_ekspor`: Batas jumlah sheet ekspor Excel admin (bawaan: 50 sheet).
- `batas_baris_pdf`: Batas jumlah baris ekspor PDF admin (bawaan: 2000 baris).

---

## 💻 Perintah Penting

| Perintah | Deskripsi |
|---|---|
| `php artisan test` | Menjalankan seluruh *test suite* (Feature & Unit tests) menggunakan database MySQL `myabsen_test` (sesuai `phpunit.xml`), aman dan tidak menghapus database utama. |
| `php vendor/bin/pint` | Merapikan format kode PHP (Laravel Pint Code Style). **Wajib dijalankan sebelum commit.** |
| `npm run build` | Mengompilasi dan mengoptimalkan aset frontend untuk produksi. |
| `npm run dev` | Menjalankan Vite server secara langsung untuk pengembangan frontend. |
| `php artisan optimize:clear` | Membersihkan seluruh cache sistem (konfigurasi, route, dan view). |

---

## 🛠️ Pemecahan Masalah

| Kendala / Gejala | Solusi |
|---|---|
| `Unknown database 'myabsen_test'` saat `php artisan test` | Database `myabsen_test` belum dibuat di MySQL. Buat database tersebut via HeidiSQL / Laragon Database tool. |
| `Unknown database 'myabsen'` | Database `myabsen` belum dibuat di MySQL. Buat database `myabsen`, lalu jalankan `php artisan migrate:fresh --seed`. |
| `Connection refused` | Servis MySQL belum aktif. Pastikan Laragon/XAMPP sudah berjalan (**Start All**). |
| Login gagal (*Credentials do not match*) | Database belum diisi data awal. Jalankan `php artisan migrate:fresh --seed` (lokal). |
| Perubahan `.env` tidak berefek | Cache konfigurasi masih aktif. Jalankan `php artisan optimize:clear`. |
| Tampilan polos / tanpa CSS | Aset belum dikompilasi. Jalankan `npm run build` atau `npm run dev`. |
| Error setelah `git pull` | Ada perubahan skema atau dependensi. Jalankan `composer install`, `npm install`, dan `php artisan migrate`. |

---

## 📚 Dokumentasi Proyek

Sebelum mulai menulis kode, seluruh anggota tim wajib memahami dokumen berikut:

- [`AGENTS.md`](AGENTS.md) - Aturan wajib (Stack, Konvensi Kode, Panduan Git) untuk tim developer dan AI Assistant.
- [`docs/PRD.md`](docs/PRD.md) - Product Requirements Document (Sumber kebenaran fitur, aturan bisnis, dan skema database).
- [`docs/PROGRESS.md`](docs/PROGRESS.md) - Status pengerjaan tiap fase, tracking tugas, dan pembagian kerja tim.

---

## 🤝 Alur Kerja Tim

1. Selalu mulai dari branch utama:
   ```bash
   git checkout main
   git pull origin main
   ```
   Setelah `git pull`, selalu jalankan migrasi jika ada pembaruan: `php artisan migrate`.
2. Buat branch baru untuk tugas Anda sesuai peruntukan:
   - Fitur baru: `git checkout -b fitur/nama-fitur`
   - Perbaikan bug: `git checkout -b perbaikan/nama-bug`
   - Refactor: `git checkout -b refactor/nama-refactor`
   - Test: `git checkout -b test/nama-test`
   - Chore: `git checkout -b chore/nama-chore`
   - Dokumentasi: `git checkout -b docs/nama-docs`
3. Tulis kode Anda. Pastikan tidak melanggar aturan di `AGENTS.md` dan `PRD.md`.
4. Jalankan pengujian dan linter sebelum commit:
   ```bash
   php artisan test
   php vendor/bin/pint
   ```
5. Lakukan commit dengan pesan terstruktur:
   - `feat: [deskripsi]` (fitur baru)
   - `fix: [deskripsi]` (perbaikan bug)
   - `refactor: [deskripsi]` (perombakan struktur kode)
   - `test: [deskripsi]` (penambahan/perbaikan pengujian)
   - `chore: [deskripsi]` (pemeliharaan dependensi/konfigurasi)
   - `docs: [deskripsi]` (perubahan dokumentasi)
6. Push branch Anda: `git push origin nama-branch`.
7. Buat **Pull Request** (PR) di GitHub ke branch `main`. Jika terdapat migrasi baru, wajib sertakan catatan: **"jalankan php artisan migrate"** pada deskripsi PR.

> 🚫 **Dilarang keras:** Melakukan push atau commit langsung ke branch `main`! Selalu gunakan alur Pull Request.