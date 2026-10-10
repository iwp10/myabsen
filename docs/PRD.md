# PRD MyAbsen

Dokumen ini adalah sumber kebenaran untuk fitur MVP. Jika ingin mengubah fitur atau aturan, ubah dokumen ini lebih dulu (lewat Pull Request), baru kode.

## 1. Ringkasan
MyAbsen adalah website absensi **per mata pelajaran** untuk SMK. Guru mengabsen siswa di kelas yang ia ajar, dan siswa bisa melihat status kehadirannya sendiri.

Pembeda utama: absensi per mata pelajaran (bukan per hari), sehingga siswa yang datang pagi lalu tidak masuk di jam tertentu tetap terdeteksi. Rekap harian dihitung otomatis dari data per mapel.

## 2. Role dan hak akses

| Hak | Admin | Guru | Siswa |
|---|---|---|---|
| Kelola master data (jurusan, kelas, mapel, guru, siswa) | Ya | Tidak | Tidak |
| Kelola jadwal | Ya | Tidak | Tidak |
| Mengabsen | Ya (koreksi historis kapan saja pada tanggal lampau, bukan masa depan) | Ya (jadwal sendiri, tanggal dalam 7 hari terakhir yang cocok dengan hari jadwal; termasuk susulan) | Tidak |
| Melihat rekap | Semua kelas | Kelas yang ia ajar | Milik sendiri |
| Ekspor Excel/PDF | Ya (Excel dan PDF) | Ya (Excel saja) | Tidak |
| Pengaturan Periode Aktif | Ya | Tidak | Tidak |
| Pergantian Periode (Salin, Pindah, Lulus) | Ya | Tidak | Tidak |
| Akses Data Terhapus (Pemulihan) | Ya | Tidak | Tidak |

## 3. Fitur MVP

### Umum
- Layout sidebar responsif dengan navigasi per role, identitas SMK Mandiri 02 Balaraja, dan modal konfirmasi saat keluar (logout).
- Antarmuka modern dan konsisten menggunakan font Inter dan Tabler Icons (inline SVG).
- Toggle Mode Terang dan Mode Gelap (*Light/Dark Mode*) berbasis Alpine.js dan Tailwind CSS, dengan Mode Terang sebagai setelan bawaan (*default*).

### Admin
- Dashboard: ringkasan statistik master data (siswa, guru, kelas, mapel) dan pintasan aksi cepat operasional.
- Login fleksibel menggunakan Username, NIP (guru), atau NIS (siswa) dan password; wajib mengganti password saat login pertama kali (`must_change_password`).
- CRUD master data: jurusan, kelas, mapel, guru, dan siswa. Akun pengguna dibuat otomatis dengan password awal dari konfigurasi (`config/absensi.php`), serta fitur reset password guru/siswa oleh admin.
- Impor data siswa secara massal dari file Excel (`ImportSiswaRequest`), dengan laporan baris yang gagal dan validasi duplikasi.
- CRUD jadwal pelajaran (kelas + mapel + guru + hari + jam) dengan validasi pencegahan jadwal bentrok serta kewajiban keselarasan periode jadwal dengan kelasnya, dilengkapi filter hari (Senin..Sabtu), kelas, guru, mata pelajaran, rentang jam mulai ("Jam mulai dari" dan "sampai"), dan pencarian bekerja bersama filter periode dengan urutan bawaan hari lalu jam serta tombol reset.
- Menu Koreksi Absensi: memilih tanggal lampau (kapan saja, bukan masa depan) dan kelas untuk melihat jadwal beserta status sesi (sudah/belum diabsen), lalu membuka form absensi untuk koreksi atau pengisian susulan.
- Pengaturan Periode: mengatur tahun ajaran aktif dan semester aktif yang disimpan di database (tabel `pengaturan`), dengan saran otomatis berbasis tanggal kalender (Juli-Desember = Ganjil, Januari-Juni = Genap), modal konfirmasi sebelum pergantian periode, dan banner pengingat dashboard.
- Menu Pergantian Periode: mengelola transisi akademik antar-periode (route `admin.pergantian-periode.index`), memuat 4 tab/bagian: (0) Panduan alur kerja dan penampil status periode aktif; (1) Salin Kelas: menyalin struktur kelas aktif dari periode asal ke periode tujuan (nama, tingkat, jurusan), otomatis melewati kelas yang sudah ada, tanpa menyalin jadwal atau memindahkan siswa (route `admin.pergantian-periode.salin-kelas`); (2) Pindahkan Siswa: memindahkan siswa aktif dari kelas asal ke kelas tujuan yang berbeda dengan seleksi massal dan pencarian real-time nama/NIS di sisi tampilan (route `admin.pergantian-periode.pindahkan-siswa`); (3) Luluskan Siswa: meluluskan siswa kelas akhir secara massal menggunakan soft delete model Siswa tanpa menghapus akun User (AB-05) sehingga tidak dapat login kembali namun data historis tetap utuh dan dapat dipulihkan sewaktu-waktu (route `admin.pergantian-periode.luluskan-siswa`). Seluruh aksi massal dibungkus transaksi database atomik (`DB::transaction`).
- Menu Data Terhapus (Arsip): melihat daftar data master yang di-soft-delete (Guru, Siswa, Kelas, Mapel) dalam 4 tab terpisah dengan pencarian dan paginasi (route `admin.arsip.index`), serta memulihkan (*restore*) data secara aman dengan validasi dependensi dan pencegahan bentrok (route `admin.arsip.pulihkan`).
- Laporan & Ekspor: melihat rekap absensi seluruh sekolah serta mengekspor ke format Excel (multi-sheet per kelas-mapel) dan dokumen cetak PDF resmi, dilengkapi filter jurusan, kelas, mapel, bulan, dan periode, serta pembatasan kapasitas ekspor aman (maksimal 50 sheet Excel dan 2000 baris PDF via `config/absensi.php`) untuk mencegah kehabisan memori (*OOM*).

### Guru
- Dashboard: ringkasan statistik mengajar (total kelas, mapel, jadwal), kartu Total Kelas dan Total Mata Pelajaran interaktif (dapat diklik dengan kursor pointer, fokus keyboard, atribut aksesibilitas, membuka modal Alpine daftar kelas yang diajar atau mapel yang diampu pada periode aktif dengan jumlah persis sama dengan angka di kartu dan tautan langsung menuju Jadwal Mengajar dengan parameter terkait), kartu pengingat jadwal belum diabsen dalam 7 hari terakhir (hingga 3 item terdekat tertaut langsung ke form absensi dan pesan positif "Semua jadwal sudah diabsen." bila nihil), daftar jadwal mengajar hari ini, dan tombol pintasan "Jadwal & Koreksi Absensi".
- Menu Jadwal Mengajar: halaman jadwal mingguan interaktif guru dengan filter hari, kelas, dan mata pelajaran (hanya memuat kelas dan mapel yang diajar guru pada periode aktif, divalidasi via `JadwalGuruFilterRequest`), sorotan visual hijau (`border-l-4 border-l-emerald-600 bg-emerald-50/70`) pada item jadwal yang cocok dengan filter kelas/mapel tanpa bentrok dengan badge "Hari ini", banner label filter aktif beserta tombol hapus per filter, dan tombol Reset untuk mengosongkan semua filter.
- Menu Jadwal & Koreksi Absensi: menu sidebar tersendiri (tepat di bawah "Jadwal Mengajar") menampilkan daftar 7 hari terakhir (hari ini s.d. H-6) urut dari yang terbaru dengan hari + tanggal, daftar jadwal milik guru tersebut pada tiap tanggal, badge status sesi, label "Hari ini", dan tautan langsung ke form absensi untuk pengisian susulan maupun koreksi.
- Halaman absensi: seluruh siswa aktif di kelas tampil dengan status bawaan **Hadir**. Guru mengubah siswa yang berhalangan menjadi Izin, Sakit, atau Alpa (keterangan opsional), lalu menyimpan dalam satu transaksi database.
- Membuka kembali sesi yang sudah ada untuk diedit atau diisi susulan (dalam batas koreksi 7 hari terakhir yang cocok dengan hari jadwal).
- Riwayat absensi dua tingkat (route `guru.riwayat` dan `guru.riwayat.detail`): halaman utama menampilkan pilihan kartu kombinasi Kelas-Mapel yang diampu guru pada periode aktif (bawaan) atau periode lama yang dipilih melalui dropdown periode, didukung kotak pencarian kelas/mapel (`q`). Halaman detail berupa tabel matriks kehadiran horizontal per pertemuan (P1..Pn) beserta daftar siswa yang dapat difilter menurut nama/NIS (`q`), dropdown pemilihan periode, tombol ekspor Excel (membawa periode terpilih tanpa terpengaruh pencarian `q`), serta penanda visual (badge "Perlu perhatian" dan latar lembut) bagi siswa dengan persentase kehadiran di bawah ambang batas (`config/absensi.batas_kehadiran_rendah`). Otorisasi ditegakkan terpusat via `JadwalPolicy::viewRiwayat()`.
- Laporan dan ekspor: mengekspor rekap absensi kelas yang diampu dalam format Excel multi-sheet per kelas-mapel (matriks per pertemuan P1..Pn, tanpa opsi PDF), didukung parameter gabungan `kelas_mapel` dengan format `"{kelas_id}-{mapel_id}"` serta parameter periode `tahun_ajaran` dan `semester`.

### Siswa (read-only)
- Dashboard: status kehadiran hari ini per mata pelajaran sesuai jadwal (*Belum diabsen / Hadir / Izin / Sakit / Alpa*), kartu persentase kehadiran keseluruhan, dan rincian ringkasan kehadiran transparan (total hadir, izin, sakit, alpa, serta total pertemuan) di mana kartu Total Hadir, Izin, Sakit, dan Alpa bersifat interaktif (kursor pointer, fokus keyboard, atribut aksesibilitas) dan tertaut langsung ke halaman Riwayat tab "Semua Riwayat" dengan status terpilih serta parameter `periode=semua` sesuai cakupan angka kartu.
- Menu Jadwal Pelajaran (route `siswa.jadwal`): jadwal mingguan kelas siswa pada periode aktif, dikelompokkan per hari (Senin sampai Sabtu) urut jam mulai, dilengkapi filter hari dan filter mata pelajaran (hanya memuat mapel kelas siswa pada periode aktif, divalidasi via `JadwalSiswaFilterRequest`), sorotan visual hijau pada item jadwal yang cocok dengan filter mapel tanpa bentrok dengan badge "Hari ini", banner label filter aktif dengan tombol hapus filter, tombol reset, dan empty state ramah bila belum memiliki kelas atau jadwal.
- Menu Mata Pelajaran (route `siswa.mapel`): menu sidebar baru di antara Jadwal Pelajaran dan Riwayat, menampilkan daftar kartu mata pelajaran yang ada pada jadwal kelas siswa di periode aktif beserta nama mapel, kode, nama guru pengampu, ringkasan jadwal pertemuan (hari dan jam pelajaran), dan empty state ramah; setiap kartu dapat diklik langsung menuju Jadwal Pelajaran dengan parameter `mapel_id`.
- Riwayat kehadiran dua tingkat (route `siswa.riwayat` dan `siswa.riwayat.mapel`):
  - **Tingkat 1 - Tab Per Mata Pelajaran (Bawaan):** Grid kartu interaktif per mata pelajaran yang memiliki catatan absensi siswa pada periode terpilih (bawaan: periode aktif). Setiap kartu menampilkan nama mapel, guru pengampu, persentase kehadiran (rumus AB-07), rincian jumlah Hadir/Izin/Sakit/Alpa, total pertemuan, dan indikator warna persentase. Seluruh kartu dapat diklik menuju halaman detail. Dropdown periode divalidasi via `RiwayatSiswaFilterRequest` dan dilengkapi *empty state* ramah jika belum ada riwayat.
  - **Tingkat 1 - Tab Semua Riwayat:** Daftar riwayat linier lengkap dengan filter status kehadiran (Hadir, Izin, Sakit, Alpa), mata pelajaran, bulan (`input type="month"`, parameter `bulan`, format `Y-m`), tanggal, dan periode (termasuk opsi 'Semua Periode'); urutan terbaru dulu; header ringkas "Menampilkan {n} catatan {status}" di mana angka $n$ sama persis dengan angka kartu dashboard untuk status dan periode tersebut; tombol reset; paginasi membawa query string filter; dan penegasan isolasi data siswa (AB-08).
  - **Tingkat 2 - Detail Per Mapel (Hanya-Baca):** Halaman matriks presensi P1..Pn berurut tanggal untuk satu mata pelajaran terpilih (route `siswa.riwayat.mapel`). Menampilkan judul mapel, nama guru pengampu, label & dropdown periode, ringkasan kehadiran (persentase AB-07 dan rincian H/I/S/A), matriks horizontal dengan kolom sticky (No, NIS, Nama Siswa, P1..Pn, H, I, S, A, %) khusus SATU BARIS milik siswa itu sendiri beserta catatan keterangan izin/sakit/alpa bila ada, dan tombol navigasi kembali yang mempertahankan parameter periode. Murni hanya-baca tanpa form/tombol aksi/ekspor.

## 4. Aturan bisnis
Setiap aturan di bawah harus punya test.

- **AB-01** Satu sesi unik per (jadwal, tanggal). Jika sesinya sudah ada, sistem membuka sesi itu untuk diedit, tidak membuat duplikat.
- **AB-02** "Belum diabsen" berarti belum ada baris `detail_absensi`. Tidak pernah disimpan sebagai alpa.
- **AB-03** Guru hanya mengabsen jadwal miliknya, pada tanggal yang jatuh di hari jadwal tersebut dan dalam batas 7 hari terakhir (hari ini dan 6 hari sebelumnya, Asia/Jakarta); jadwal yang belum diabsen boleh diisi susulan dalam batas itu; admin boleh koreksi tanggal lampau apa pun kapan saja; tanggal masa depan ditolak untuk semua role. MVP tidak membatasi jam, hanya hari.
- **AB-04** Saat sesi disimpan, semua siswa kelas mendapat satu baris `detail_absensi` (default hadir kecuali diubah), dalam satu transaksi database (`DB::transaction`).
- **AB-05** Mekanisme Master Data, Integritas Histori & Data Terhapus:
  - Penghapusan master data Siswa, Guru, Kelas, dan Mapel yang sudah memiliki riwayat absensi tidak dihapus permanen melainkan menggunakan mekanisme `SoftDeletes`. Master data Jurusan tidak menggunakan soft delete.
  - Khusus tabel `Jadwal` TIDAK menggunakan soft delete: jadwal tidak dapat dihapus jika sudah memiliki sesi absensi (`sesi_absensi`), dan hanya dihapus permanen jika belum pernah memiliki sesi absensi.
  - Guru atau siswa yang profilnya telah di-soft-delete diblokir dari login dan sesi aktifnya langsung dihentikan pada seluruh rute termasuk `/profile` dan penggantian password (`/password`). Akun User mereka tetap tersimpan di database guna menjaga integritas riwayat absensi, dan akses akan aktif kembali secara otomatis jika profil dipulihkan (*restore*).
  - Data yang di-soft-delete dapat dipulihkan admin melalui menu "Data Terhapus" (`admin/arsip`) dengan pengecekan integritas sebelum restore: (a) Siswa ditolak jika kelasnya masih terhapus atau NIS bentrok dengan siswa/user aktif lain; (b) Guru ditolak jika NIP bentrok dengan guru/user aktif lain; (c) Mapel ditolak jika kode bentrok dengan mapel aktif lain; (d) Kelas ditolak jika jurusannya sudah tidak ada atau kombinasi nama, tahun ajaran, dan semester bentrok dengan kelas aktif.
  - Saat pembuatan data baru atau impor siswa, jika NIP/NIS/kode telah dipakai data terhapus, validasi menolak dengan pesan ramah yang menyebut nama pemilik data dan mengarahkan admin ke menu Data Terhapus.
  - Pada seluruh laporan dan riwayat (riwayat detail guru, ekspor Excel, dan PDF admin), siswa terhapus yang memiliki riwayat absensi (`detail_absensi`) pada kelas-mapel dan periode/filter terkait tetap ditampilkan dengan tanda "(nonaktif)" di samping nama, sedangkan siswa terhapus tanpa riwayat pada cakupan tersebut tidak ditampilkan.
- **AB-06** Pencegahan Bentrok & Keselarasan Periode Jadwal: Satu guru tidak boleh punya dua jadwal yang jamnya beririsan di hari yang sama pada tahun ajaran dan semester yang sama. Berlaku juga untuk satu kelas. Selain itu, jadwal wajib berperiode sama dengan kelasnya (tahun ajaran dan semester jadwal harus sama persis dengan tahun ajaran dan semester kelas terkait pada operasi penambahan maupun pengubahan jadwal). Pengubahan periode (`tahun_ajaran` atau `semester`) pada kelas yang sudah memiliki jadwal pada periode lama ditolak dengan pesan: "Kelas ini sudah punya {n} jadwal pada periode {semester} {tahun_ajaran}. Pindahkan atau hapus jadwal tersebut sebelum mengubah periode kelas."
- **AB-07** Kalkulasi Persentase Kehadiran: Persentase = ((Hadir + Izin + Sakit) / Total Sesi Diabsen) * 100. Status Hadir, Izin, dan Sakit dihitung sebagai kehadiran positif. Alpa adalah satu-satunya status yang mengurangi persentase. Pembagi adalah jumlah sesi yang sudah diabsen untuk siswa itu (sesi yang belum diabsen tidak dihitung). Jika total sesi adalah nol, mengembalikan nilai 0.
- **AB-08** Batasan Hak Akses: Siswa hanya bisa melihat data miliknya. Akses riwayat siswa (`siswa.riwayat` dan `siswa.riwayat.mapel`) selalu dibatasi khusus data siswa yang login (`$user->siswa->id`); akses detail mapel (`siswa.riwayat.mapel`) tanpa catatan absensi siswa pada periode terkait menghasilkan HTTP 404 Not Found, dan respon HTML tidak memuat nama, NIS, atau data siswa lain. Guru hanya bisa melihat kelas yang ia ajar (berdasarkan jadwal). Otorisasi akses detail riwayat guru (`guru.riwayat.detail`) ditegakkan melalui `JadwalPolicy::viewRiwayat()`; guru hanya berhak mengakses kelas-mapel yang diampunya pada periode tersebut, sedangkan akses ke jadwal/kelas-mapel milik guru lain ditolak dengan HTTP 403 Forbidden dan akses tanpa jadwal pada periode tersebut menghasilkan 404 Not Found. Pengguna di luar role terkait (admin dan siswa pada riwayat guru; admin dan guru pada riwayat siswa) ditolak dengan 403, tamu dialihkan ke login, dan profil nonaktif diblokir sesuai AB-05.
- **AB-09** Jejak Audit Sesi: Setiap sesi mencatat siapa yang mengabsen (`diabsen_oleh`) dan siapa yang terakhir mengubah (`diubah_oleh`). Foreign key `diabsen_oleh` dan `diubah_oleh` pada `sesi_absensi` menggunakan `restrictOnDelete` ke `users(id)` untuk mencegah hilangnya jejak audit absensi.
- **AB-10** Autentikasi dan Manajemen Akun:
  - Akun pengguna hanya dibuat oleh admin dengan password awal terkonfigurasi (`config/absensi.password_awal`) dan wajib diganti saat login pertama (`must_change_password = true`), tidak ada registrasi publik.
  - Pengguna dengan status `must_change_password = true` dibatasi hanya dapat mengakses form profil (`/profile`), pengubahan password (`/password`), dan logout; akses ke route operasional lain dialihkan ke profil dengan banner peringatan, atau ditolak HTTP 403 Forbidden pada request JSON/AJAX.
  - Password baru minimal 8 karakter, tidak boleh sama dengan password lama, dan tidak boleh sama dengan password awal ('password' atau nilai konfigurasi).
  - Alur Notifikasi Ganti Password:
    - Berhasil: menampilkan banner hijau ("Password berhasil diganti."). Jika sebelumnya pengguna wajib ganti password (`must_change_password = true`), sistem mengarahkan pengguna ke dashboard sesuai role (`admin.dashboard`, `guru.dashboard`, `siswa.dashboard`) dan menampilkan banner hijau di sana. Jika ganti password sukarela, pengguna tetap berada di halaman profil dengan banner hijau (otomatis hilang setelah beberapa detik via Alpine.js).
    - Gagal: pengguna tetap berada di halaman profil, menampilkan banner merah ("Password gagal diganti. Periksa isian di bawah."), kolom isian yang salah diberi tanda border merah tanpa mengisi ulang nilai password lama/baru, dan menampilkan pesan spesifik per kolom berbahasa Indonesia (password lama salah, password baru kurang dari 8 karakter, sama dengan password awal atau lama, konfirmasi tidak cocok). Pesan error hanya ditampilkan sekali (tidak dobel).
  - Keamanan Sesi, Logout, dan Penanganan Sesi Berakhir:
    - Seluruh halaman HTML terautentikasi (grup `auth`) mengirim header `Cache-Control: no-store, no-cache, must-revalidate, max-age=0`, `Pragma: no-cache`, dan `Expires` agar tombol navigasi Back setelah logout selalu memicu pemuatan ulang dari server dan dialihkan ke login. Respon file/unduhan biner (Excel, PDF) dan halaman tamu tidak terpengaruh.
    - Operasi `POST /logout` bersifat idempotent: jika dipanggil saat pengguna sudah berstatus tamu (guest), sistem mengarahkan ke halaman login dengan pesan ramah "Anda sudah keluar." tanpa memicu error 419/500.
    - Penanganan global `TokenMismatchException` (HTTP 419): request JSON membalas 419 JSON; request web tamu dialihkan ke halaman login dengan pesan "Sesi Anda sudah berakhir. Silakan masuk kembali."; request web terautentikasi dialihkan kembali ke halaman sebelumnya dengan pesan "Halaman kedaluwarsa, silakan ulangi."; serta disediakan halaman cadangan `resources/views/errors/419.blade.php` berbahasa Indonesia dengan gaya visual konsisten (mendukung dark mode) dan tombol kembali ke halaman login.
  - Login resmi fleksibel memakai `username` ATAU `NIP` (guru) ATAU `NIS` (siswa).
  - Jika akun guru atau siswa memiliki profil yang berstatus soft delete, login ditolak dengan pesan: "Akun ini sudah tidak aktif. Silakan hubungi admin sekolah." tanpa membuka celah bypass rate limiting (rate limiter tetap mencatat kegagalan).
- **AB-11** Periode Aktif & Filter Periode Riwayat: Periode akademik aktif (tahun ajaran dan semester) diatur oleh admin melalui menu "Pengaturan Periode" dan disimpan di basis data (tabel `pengaturan`, bukan file .env/config). Daftar master data admin (Kelas, Jadwal) bawaannya menampilkan semua periode dan bisa difilter; halaman operasional (dashboard guru, dashboard siswa, jadwal pelajaran guru/siswa, koreksi) menggunakan periode aktif sebagai filter default. Halaman riwayat guru (kartu kelas-mapel dan detail matriks pertemuan) serta riwayat siswa secara bawaan menampilkan data periode aktif, dan pengguna dapat beralih ke periode terdahulu yang memiliki data jadwal/absensi mereka melalui dropdown filter periode. Sistem menyediakan saran otomatis berbasis tanggal kalender (Juli-Desember = Ganjil, Januari-Juni = Genap) dan meminta konfirmasi pengguna sebelum periode aktif diperbarui.
- **AB-12** Standar Waktu: Seluruh sistem, operasi tanggal, pencatatan sesi, dan jam absensi menggunakan standar zona waktu `Asia/Jakarta`.
- **AB-13** Pergantian Periode, Pemindahan Siswa & Keutuhan Riwayat Kelas:
  - Proteksi Hapus Kelas: Kelas yang masih memiliki siswa aktif tidak dapat dihapus (`KelasController::destroy`), sistem menolak dengan pesan ramah: `"Kelas ini masih berisi {n} siswa aktif. Pindahkan atau luluskan siswa terlebih dahulu."`. Kelas yang hanya berisi siswa yang telah di-soft-delete atau tidak memiliki siswa boleh dihapus (soft delete) dengan ketentuan tidak memiliki jadwal aktif.
  - Keutuhan Riwayat Kelas: Daftar siswa suatu kelas pada cakupan sesi tertentu (`Siswa::scopeUntukLaporan` / `AbsensiService::getSiswaUntukLaporan`) adalah gabungan siswa aktif kelas tersebut ditambah seluruh siswa (baik aktif yang telah berpindah kelas maupun yang telah di-soft-delete) yang memiliki riwayat `detail_absensi` pada sesi-sesi cakupan tersebut. Perhitungan persentase kehadiran masing-masing siswa tetap menggunakan rumus AB-07 dari sesinya sendiri. Siswa yang telah pindah kelas tidak diberi tanda khusus, sedangkan siswa terhapus diberi tanda `(nonaktif)`. Form absensi sesi baru hanya memuat siswa aktif yang `kelas_id`-nya adalah kelas tersebut.
  - Salin Kelas: Menyalin struktur kelas (nama, tingkat, jurusan) ke periode tujuan. Kelas dengan nama dan jurusan yang sama di periode tujuan dilewati. Kelas terhapus, jadwal, dan siswa tidak ikut disalin. Periode tujuan wajib valid dan berbeda dari periode asal.
  - Pindahkan Siswa: Memindahkan siswa aktif ke kelas tujuan yang aktif dan berbeda (hanya memperbarui `siswa.kelas_id`; sesi dan detail absensi lama tidak diubah). Validasi server memastikan setiap siswa yang dipindahkan benar-benar siswa aktif kelas asal.
  - Luluskan Siswa: Meluluskan siswa secara massal menggunakan soft delete model Siswa (`$siswa->delete()`). JANGAN PERNAH forceDelete, meskipun siswa belum memiliki riwayat absensi. Akun User tidak dihapus sehingga aturan AB-05 memblokir login. Siswa dapat dipulihkan melalui menu Data Terhapus.
  - Batas Operasi: Maksimal 100 kelas per operasi salin kelas dan 500 siswa per operasi pemindahan/kelulusan. Seluruh aksi massal dijalankan dalam `DB::transaction`.

## 5. Skema database

```sql
users(id, name, username unique, email null, password, role enum[admin,guru,siswa], must_change_password boolean default false, remember_token null, timestamps)
jurusan(id, nama, kode, timestamps)
kelas(id, jurusan_id, nama, tingkat, tahun_ajaran, semester enum[Ganjil,Genap] default Ganjil, timestamps, deleted_at null)
guru(id, user_id, nip null, timestamps, deleted_at null)
siswa(id, user_id, kelas_id, nis unique, timestamps, deleted_at null)
mapel(id, nama, kode, timestamps, deleted_at null)
jadwal(id, kelas_id, mapel_id, guru_id, hari enum[senin..sabtu], jam_mulai, jam_selesai, tahun_ajaran, semester enum[Ganjil,Genap] default Ganjil, timestamps)
sesi_absensi(id, jadwal_id, tanggal, diabsen_oleh, diubah_oleh null, catatan null, timestamps)
  UNIQUE(jadwal_id, tanggal)
detail_absensi(id, sesi_absensi_id, siswa_id, status enum[hadir,izin,sakit,alpa], keterangan null, timestamps)
  UNIQUE(sesi_absensi_id, siswa_id)
pengaturan(id, kunci string unique, nilai text null, timestamps)
```

Relasi Foreign Key:
- `kelas.jurusan_id` -> `jurusan.id` (ON DELETE CASCADE)
- `guru.user_id` -> `users.id` (ON DELETE CASCADE)
- `siswa.user_id` -> `users.id` (ON DELETE CASCADE)
- `siswa.kelas_id` -> `kelas.id` (ON DELETE CASCADE)
- `jadwal.kelas_id` -> `kelas.id` (ON DELETE CASCADE)
- `jadwal.mapel_id` -> `mapel.id` (ON DELETE CASCADE)
- `jadwal.guru_id` -> `guru.id` (ON DELETE CASCADE)
- `sesi_absensi.jadwal_id` -> `jadwal.id` (ON DELETE CASCADE)
- `sesi_absensi.diabsen_oleh` -> `users.id` (ON DELETE RESTRICT)
- `sesi_absensi.diubah_oleh` -> `users.id` (ON DELETE RESTRICT)
- `detail_absensi.sesi_absensi_id` -> `sesi_absensi.id` (ON DELETE CASCADE)
- `detail_absensi.siswa_id` -> `siswa.id` (ON DELETE CASCADE)

Index:
- `siswa(kelas_id)`
- `jadwal(guru_id, hari)`
- `jadwal(kelas_id, hari)`
- `sesi_absensi(tanggal)`
- `detail_absensi(siswa_id)`

## 6. Kebutuhan non-fungsional
- **Arsitektur Teknis:** Pemisahan tanggung jawab (*Separation of Concerns*) secara ketat antar layer:
  - **Controller:** Berperan tipis (*Thin Controller*) yang bertugas menerima request HTTP, memanggil Service yang sesuai, dan mengembalikan response JSON atau view Blade.
  - **FormRequest:** Khusus untuk validasi input data dari pengguna di sisi server dan menghubungkan pemeriksaan otorisasi awal.
  - **Policy (`JadwalPolicy`):** Khusus untuk memusatkan otorisasi hak akses (*authorization rules*), termasuk batasan mengajar guru dan hak koreksi historis admin.
  - **Service Pattern (`AbsensiService`, `LaporanService`, `ArsipService`, `PeriodeService`, `PasswordAwalService`):** Sebagai pusat seluruh logika bisnis (*business logic*), kalkulasi persentase kehadiran, agregasi rekapitulasi, eksekusi transaksi absensi, pemulihan data terhapus, dan tata kelola akun aman.
  - **Model:** Khusus menangani pemetaan relasi Eloquent (*relationships*), query scopes, dan kekhawatiran persistensi (*persistence concern*, termasuk *soft deletes*).
  - **View:** Khusus untuk layer presentasi UI menggunakan Blade Templating, Tailwind CSS, dan Alpine.js.
- **Performa:** Halaman absensi untuk kelas 40 siswa terbuka kurang dari 2 detik. Rekap memakai agregasi SQL, tanpa N+1. Ekspor laporan admin dibatasi untuk mencegah kehabisan memori (*OOM*) dan beban CPU: batas jumlah sheet Excel (`batas_sheet_ekspor`, default 50) dan batas baris PDF (`batas_baris_pdf`, default 2000) yang dapat disesuaikan pada file konfigurasi (`config/absensi.php`). Halaman laporan admin dilengkapi filter Jurusan dan filter Bulan untuk mempersempit cakupan data ekspor.
- **Keamanan:** Password di-hash dengan algoritma Bcrypt, proteksi CSRF pada semua form, otorisasi via Policy, pembatasan percobaan login (*rate limiting* 5 kali sebelum lockout), proteksi akun dengan `must_change_password`, pemutusan sesi akun nonaktif, header `Cache-Control: no-store` pada halaman terautentikasi, logout idempotent, penanganan sesi kedaluwarsa ramah pengguna, serta validasi ketat di sisi server.
- **Tampilan:** Responsif untuk perangkat desktop maupun mobile, layout sidebar modern, font Inter, ikon Tabler, serta toggle Mode Terang/Gelap menggunakan Alpine.js dan Tailwind CSS (dengan Mode Terang sebagai setelan bawaan).
- **Bahasa:** Seluruh antarmuka, pesan validasi, dan notifikasi menggunakan Bahasa Indonesia.
- **Waktu:** Standar sistem menggunakan timezone `Asia/Jakarta` secara konsisten pada seluruh pencatatan dan perhitungan absensi.

## 7. Di luar MVP (tahap lanjut)
Tidak dikerjakan sebelum MVP stabil dan diuji di sekolah:
- Notifikasi WhatsApp atau email ke orang tua
- Geofencing dan QR Code
- Pengajuan izin/sakit oleh siswa dengan unggah bukti
- Guru pengganti
- Absensi guru dan pegawai
- Face recognition
- Integrasi dengan nilai/rapor
- Aplikasi mobile native

## 8. Log keputusan

| Tanggal | Keputusan |
|---|---|
| 2026-09-21 | Absensi per mata pelajaran, bukan per hari |
| 2026-09-21 | Semua siswa default Hadir, guru hanya mengubah yang berbeda |
| 2026-09-21 | Stack: Laravel 12, Blade + Tailwind + Alpine, MySQL 8 |
| 2026-09-21 | Login memakai username (NIS/NIP), bukan email |
| 2026-10-02 | Perubahan AB-07 (status Hadir, Izin, dan Sakit dihitung hadir, Alpa tidak dihitung; pembagi = jumlah sesi yang sudah diabsen untuk siswa), login resmi fleksibel memakai username ATAU NIP (guru) ATAU NIS (siswa), serta penambahan fitur MVP (halaman jadwal mingguan guru, dashboard admin statistik + pintasan, dashboard guru statistik + jadwal hari ini, layout sidebar, font Inter, ikon Tabler) |
| 2026-10-03 | Perubahan AB-03: Guru diizinkan mengabsen, mengisi susulan, dan mengoreksi absensi jadwal miliknya pada tanggal dalam 7 hari terakhir (hari ini dan 6 hari sebelumnya, Asia/Jakarta) yang harinya cocok dengan jadwal; admin boleh koreksi tanggal lampau apa pun; tanggal masa depan ditolak untuk semua role; penambahan halaman jadwal mingguan guru interaktif dan menu Koreksi Absensi admin |
| 2026-10-03 | Penambahan menu sidebar guru "Jadwal & Koreksi Absensi" tepat di bawah "Jadwal Mengajar", menampilkan daftar 7 hari terakhir (hari ini sampai H-6) secara terurut dari terbaru untuk pengisian susulan dan koreksi absensi jadwal guru |
| 2026-10-04 | Penambahan kolom semester (Ganjil/Genap) pada kelas dan jadwal; penegasan AB-05 bahwa jadwal tidak menggunakan soft delete (proteksi penghapusan berbasis keberadaan riwayat sesi); penegasan AB-06 bentrok jadwal pada tahun ajaran dan semester yang sama; redesign riwayat guru menjadi dua tingkat (pemilihan kartu Kelas-Mapel lalu matriks horizontal P1..Pn); format ekspor Excel multi-sheet matriks per pertemuan; serta parameter gabungan kelas_mapel ("{kelas_id}-{mapel_id}") |
| 2026-10-05 | Penetapan AB-11 bahwa periode aktif (tahun ajaran dan semester) dikelola oleh admin via antarmuka web dan disimpan di basis data (tabel pengaturan), bukan melalui file .env/config, untuk memudahkan operasional sekolah tanpa menyentuh server |
| 2026-10-05 | Penegasan otorisasi riwayat detail guru melalui JadwalPolicy::viewRiwayat() sesuai AB-08, sentralisasi parser dan pembentuk format kelas_mapel ke class App\Support\KelasMapel, dan pemindahan validasi impor siswa ke FormRequest ImportSiswaRequest |
| 2026-10-05 | Ekspor PDF guru dihapus atas keputusan pemilik proyek (guru hanya mengekspor Excel; admin tetap Excel dan PDF) serta foreign key diabsen_oleh dan diubah_oleh pada sesi_absensi diubah menjadi restrictOnDelete untuk menjamin integritas histori absensi |
| 2026-10-05 | Pemblokiran akses login dan pemutusan sesi berjalan untuk akun guru dan siswa yang profilnya telah di-soft-delete (AB-05 & AB-10), dengan pesan ramah tanpa mengubah perilaku rate limiter, akun admin, maupun alur soft delete |
| 2026-10-05 | Penyamaan perlakuan siswa soft-delete di seluruh laporan (riwayat detail guru, Excel, dan PDF admin): siswa terhapus tetap ditampilkan dengan tanda "(nonaktif)" jika memiliki riwayat absensi pada cakupan filter, header guru terhapus di Excel tetap menampilkan nama (bukan "-"), serta kalkulasi persentase konsisten memakai AB-07 |
| 2026-10-05 | Penyediaan menu admin "Data Terhapus" (Arsip) untuk memulihkan data master yang di-soft-delete (Guru, Siswa, Kelas, Mapel) dengan validasi dependensi dan pencegahan bentrok (AB-05), disertai pesan validasi edukatif saat input NIP/NIS/kode duplikat dengan data terhapus |
| 2026-10-05 | Pembatasan ekspor laporan admin (maksimal 50 sheet Excel dan 2000 baris PDF via config/absensi.php) untuk mencegah kehabisan memori (OOM) dan beban CPU, disertai penambahan filter jurusan dan filter bulan pada laporan admin |
| 2026-10-06 | Validasi konsistensi periode jadwal dengan kelasnya (AB-06: tahun ajaran dan semester jadwal wajib identik dengan kelas terkait pada Store & Update); perlindungan route /profile dan /password di bawah middleware role (role:admin,guru,siswa) agar pemutusan sesi akun nonaktif (AB-05) berlaku menyeluruh |
| 2026-10-06 | Persiapan akun produksi: isolasi DemoSeeder untuk non-produksi, pembuatan AdminSeeder idempotent aman di produksi, sentralisasi password awal terkonfigurasi (config/absensi.php), penambahan kolom must_change_password pada users, pembatasan akses profil/logout bagi akun dengan password awal, dan penolakan password baru yang sama dengan password awal atau kurang dari 8 karakter (AB-10) |
| 2026-10-08 | Perbaikan notifikasi ganti password (banner hijau sukses dengan auto-dismiss, banner merah gagal dengan highlight kolom), pengalihan pengguna must_change_password ke dashboard role dengan pesan sukses, penegasan header no-store pada seluruh halaman terautentikasi (mencegah akses via tombol Back setelah logout), logout idempotent untuk tamu tanpa 419/500, penanganan global TokenMismatchException (419) dengan pesan ramah bahasa Indonesia, dan pembuatan view error 419 terpadu (AB-10) |
| 2026-10-08 | Implementasi pilihan periode dan pencarian pada riwayat guru (kartu kelas-mapel dan matriks siswa) serta riwayat siswa, ekspor Excel guru berbasis periode terpilih, dan penolakan perubahan periode kelas jika sudah memiliki jadwal pada periode lama (AB-06, AB-08, AB-11) |
| 2026-10-08 | Implementasi fitur Pergantian Periode (salin kelas, pindahkan siswa, luluskan siswa massal) dan perbaikan proteksi hapus kelas (AB-13). Tabel `anggota_kelas` sengaja tidak dibuat untuk mempertahankan kesederhanaan skema basis data MVP tanpa migrasi baru; batasan yang diketahui: tidak ada catatan tanggal mutasi siswa dan jadwal pelajaran diisi manual per periode. |
| 2026-10-10 | Penambahan empat fitur ringan antarmuka dan penyaringan tanpa migrasi baru: (1) Filter jadwal admin (hari, kelas, guru, mapel, rentang jam divalidasi JadwalFilterRequest) dan filter hari jadwal guru dengan sorotan hari ini; (2) Menu Jadwal Pelajaran siswa (siswa.jadwal); (3) Pengingat dashboard guru untuk jadwal belum diabsen 7 hari terakhir; (4) Penanda kehadiran rendah di riwayat guru (config absensi.batas_kehadiran_rendah) dan filter status riwayat siswa. |
| 2026-10-10 | Redesign halaman Riwayat Siswa menjadi dua tingkat mengikuti pola Riwayat Guru tanpa migrasi dan tabel baru: Tab Per Mata Pelajaran (grid kartu dengan nama mapel, guru pengampu, persentase AB-07, H/I/S/A, total sesi, indikator warna) dan Tab Semua Riwayat (daftar linier dengan filter lengkap), serta halaman Tingkat 2 Detail Per Mapel (route siswa.riwayat.mapel) berupa matriks horizontal P1..Pn hanya-baca untuk satu baris siswa bersangkutan dengan isolasi data AB-08 tanpa N+1. |
| 2026-10-10 | Peningkatan interaksi navigasi guru dan siswa tanpa migrasi/tabel baru: (1) Kartu Total Kelas dan Total Mapel di dashboard guru interaktif membuka modal Alpine daftar kelas/mapel dengan tautan langsung ke filter Jadwal Mengajar; (2) Filter kelas dan mapel pada Jadwal Mengajar guru dengan sorotan hijau (emerald) dan banner label filter aktif; (3) Menu baru Mata Pelajaran siswa (route siswa.mapel) di sidebar antara Jadwal dan Riwayat serta filter mapel_id pada Jadwal Pelajaran siswa; (4) Kartu Hadir/Izin/Sakit/Alpa di dashboard siswa interaktif tertaut ke tab Semua Riwayat dengan status dan periode=semua yang sama, didukung filter bulan (Y-m) dan header ringkas Menampilkan {n} catatan {status}. |



