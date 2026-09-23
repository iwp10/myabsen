# 🏫 MyAbsen

> **Aplikasi Absensi Siswa per Mata Pelajaran untuk SMK**

MyAbsen adalah sistem informasi absensi siswa berbasis web yang dirancang khusus untuk Sekolah Menengah Kejuruan (SMK) sebagai proyek PKM. Berbeda dengan absensi harian biasa, sistem ini mencatat kehadiran **per mata pelajaran**, memungkinkan pelacakan kehadiran siswa di setiap jam pelajaran secara akurat.

---

## 📑 Daftar Isi
- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Panduan Instalasi Lokal](#-panduan-instalasi-lokal)
- [Kredensial Demo](#-kredensial-demo)
- [Perintah Penting](#-perintah-penting)
- [Pembaruan (Git Pull)](#-pembaruan-setelah-git-pull)
- [Pemecahan Masalah](#-pemecahan-masalah)
- [Dokumentasi Proyek](#-dokumentasi-proyek)
- [Alur Kerja Tim](#-alur-kerja-tim)


---
Aplikasi ini memiliki tiga peran utama dengan batasan akses masing-masing, serta dilengkapi fitur aksesibilitas tingkat lanjut:

### 🌟 Fitur Unggulan (Baru)
- **Otentikasi Fleksibel:** Pengguna dapat login menggunakan Username bawaan, ATAU menggunakan Nomor Induk (NIP untuk Guru, NIS untuk Siswa).
- **Aksesibilitas Visual:** Dilengkapi *toggle* Light Mode dan Dark Mode untuk kenyamanan mata pengguna dari berbagai rentang usia (default: Light Mode).

### 👑 Admin
- **Master Data:** Mengelola data jurusan, kelas, mata pelajaran, guru, dan siswa.
- **Manajemen Jadwal:** Mengatur jadwal pelajaran dengan validasi pencegahan jadwal bentrok.
- **Import Data:** Memasukkan data siswa secara massal melalui file Excel.
- **Pemantauan & Koreksi:** Melihat seluruh rekap absensi sekolah dan hak untuk mengoreksi absensi.

### 👨‍🏫 Guru
- **Dashboard Cerdas:** Menampilkan jadwal mengajar pada hari tersebut.
- **Absensi Cepat:** Sistem memberikan status default **Hadir** untuk seluruh kelas. Guru hanya mengubah status siswa yang *Izin*, *Sakit*, atau *Alpa*.
- **Manajemen Sesi:** Dapat mengedit kembali sesi absensi pada hari yang sama.
- **Laporan:** Mengekspor rekap kelas dalam format Excel.

### 🎓 Siswa (Read-Only)
- **Monitoring Pribadi:** Melihat status kehadiran harian per mata pelajaran.
- **Statistik & Riwayat:** Melacak persentase tingkat kehadiran dan riwayat lengkapnya.

---

## 🛠️ Teknologi

Proyek ini dibangun menggunakan *stack* teknologi berikut (sesuai dengan aturan proyek):

- **Backend:** Laravel 12, PHP 8.3+
- **Database:** MySQL 8
- **Frontend:** Blade Templating, Tailwind CSS, Alpine.js
- **Autentikasi:** Laravel Breeze (Blade)
- **Testing:** Pest (Feature Testing)
- **Library Tambahan:** `maatwebsite/excel` (Export/Import Excel), `barryvdh/laravel-dompdf` (Export PDF)

---

## 📸 Preview Tampilan Aplikasi
**

## 🚀 Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek di komputer lokal bagi anggota tim pengembangan.

### Prasyarat
Pastikan komputer Anda sudah terinstal:
- PHP 8.3 atau lebih baru
- Composer
- Node.js & NPM
- MySQL (Direkomendasikan menggunakan [Laragon](https://laragon.org) untuk pengguna Windows)
- Git

### Langkah Instalasi

1. **Siapkan Database**
   Nyalakan MySQL (di Laragon, klik **Start All**), lalu buat database kosong dengan nama `myabsen`.

2. **Clone Repository**
   ```bash
   git clone https://github.com/iwp10/myabsen.git
   cd myabsen
   ```

3. **Install Dependensi Backend & Frontend**
   ```bash
   composer install
   npm install
   ```

4. **Konfigurasi Environment**
   Salin file konfigurasi dan generate application key:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
   *(Untuk pengguna macOS/Linux atau Git Bash, gunakan `cp .env.example .env`)*

   > **Catatan Database:** File `.env.example` bawaan menggunakan user MySQL `root` tanpa password (standar Laragon). Jika pengaturan MySQL Anda berbeda, sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di file `.env` Anda.

5. **Migrasi Database & Data Demo**
   Jalankan perintah berikut untuk membuat struktur tabel dan mengisi data awal (seeder):
   ```bash
   php artisan migrate --seed
   ```

6. **Kompilasi Aset Frontend**
   Pilih salah satu dari perintah berikut:
   - `npm run build` : Dijalankan sekali, cukup untuk menjalankan aplikasi.
   - `npm run dev` : Dibiarkan berjalan (di terminal terpisah), gunakan saat Anda sedang mengedit file tampilan (Blade/CSS/JS).

7. **Jalankan Aplikasi**
   Buka terminal baru dan jalankan server lokal Laravel:
   ```bash
   php artisan serve
   ```

8. **Akses Web**
   Buka browser dan akses: `http://127.0.0.1:8000`

---

## 🔑 Kredensial Demo


Gunakan akun berikut untuk mencoba fitur-fitur aplikasi selama tahap pengembangan lokal. 

| Role | Username / Nomor Induk | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Guru | `guru1` ATAU `198001012000011001` | `password` |
| Siswa | `siswa1` ATAU `1001` | `password` |

> 💡 **Info:** Anda bisa mencoba fleksibilitas login dengan memasukkan NIP atau NIS pada kolom Username saat login.
> ⚠️ **Peringatan:** Akun ini hanya untuk *development*. Jangan gunakan kredensial ini di server produksi.

---

## 🔄 Pembaruan (Setelah `git pull`)

Ketika Anda menarik pembaruan terbaru dari repository (`git pull`), jalankan perintah berikut sesuai dengan perubahan yang ada:

- Jika `composer.lock` berubah: `composer install`
- Jika `package.json` atau `package-lock.json` berubah: `npm install`
- Jika ada file migration baru: `php artisan migrate`

> **Tips:** Jika ragu, jalankan ketiganya. Menjalankan perintah ini berulang kali tidak akan merusak sistem.

---

## 💻 Perintah Penting

| Perintah | Deskripsi |
|---|---|
| `php artisan test` | Menjalankan *feature tests*. Berjalan dengan SQLite *in-memory* sehingga **tidak akan menghapus** data di MySQL. |
| `php vendor/bin/pint` | Merapikan format kode PHP (Code Style). **Wajib dijalankan sebelum commit.** |
| `php artisan migrate:fresh --seed` | Menghapus seluruh tabel dan melakukan instalasi ulang database beserta data demo (Hati-hati!). |
| `php artisan config:clear` | Membersihkan cache konfigurasi (jalankan setiap kali Anda mengubah file `.env`). |
| `npm run build` | Melakukan build aset *frontend* untuk produksi. |

---

## 🛠️ Pemecahan Masalah

| Kendala / Gejala | Solusi |
|---|---|
| `Unknown database 'laravel'` atau `'myabsen'` | Anda belum membuat database di MySQL, atau konfigurasi DB di `.env` belum benar. Pastikan DB `myabsen` ada, lalu jalankan `php artisan config:clear`. |
| `Connection refused` | Server MySQL belum menyala. Pastikan servis MySQL berjalan (klik Start All di Laragon). |
| Login gagal (Credentials do not match) | Anda belum menjalankan seeder. Jalankan `php artisan migrate --seed`. |
| Tampilan berantakan (tanpa CSS) | Aset belum dikompilasi. Jalankan `npm run build` atau `npm run dev`. |
| Peringatan PDO `PDO::MYSQL_ATTR_SSL_CA` | Ini hanya *warning* bawaan PHP 8/MySQL 8, bisa diabaikan. |

---

## 📚 Dokumentasi Proyek

Sebelum mulai menulis kode, tim wajib membaca dokumen berikut:

- [`AGENTS.md`](AGENTS.md) - Aturan wajib (Stack, Konvensi Kode, Git) untuk tim developer dan AI Assistant (Antigravity).
- [`docs/PRD.md`](docs/PRD.md) - Product Requirements Document (Sumber kebenaran fitur, aturan bisnis, dan skema database).
- [`docs/PROGRESS.md`](docs/PROGRESS.md) - Status pengerjaan tiap fase, tracking tugas, dan pembagian kerja tim.

---

## 🤝 Alur Kerja Tim

1. Selalu mulai dari branch utama: `git checkout main` lalu `git pull`.
2. Buat branch baru untuk tugas Anda:
   - Fitur baru: `git checkout -b fitur/nama-fitur`
   - Perbaikan bug: `git checkout -b perbaikan/nama-bug`
3. Tulis kode Anda. Pastikan tidak melanggar aturan di `AGENTS.md` dan `PRD.md`.
4. Jalankan `php artisan test` (pastikan semua lolos) dan `php vendor/bin/pint` untuk merapikan kode.
5. Lakukan commit dengan pesan terstruktur:
   - `feat: [deskripsi]` (fitur baru)
   - `fix: [deskripsi]` (perbaikan)
   - `docs: [deskripsi]` (ubah dokumentasi)
6. Push branch Anda: `git push origin nama-branch`.
7. Buat **Pull Request** (PR) di GitHub ke branch `main`.

> 🚫 **Dilarang keras:** Melakukan push atau commit langsung ke branch `main`! Selalu gunakan Pull Request.