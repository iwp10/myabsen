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

## 3. Fitur MVP

### Umum
- Layout sidebar responsif dengan navigasi per role.
- Antarmuka modern dan konsisten menggunakan font Inter dan Tabler Icons.

### Admin
- Dashboard: ringkasan statistik master data (siswa, guru, kelas, mapel) dan pintasan aksi cepat.
- Login fleksibel dengan username, NIP (guru), atau NIS (siswa) dan password.
- CRUD jurusan, kelas, mapel, guru, siswa. Akun user dibuat otomatis, password awal bisa direset.
- Impor siswa dari Excel, dengan laporan baris yang gagal.
- CRUD jadwal (kelas + mapel + guru + hari + jam) dengan pencegahan bentrok.
- Melihat semua rekap dan melakukan koreksi historis absensi (mengubah data absensi pada tanggal di masa lampau kapan saja).
- Menu Koreksi Absensi: memilih tanggal (tidak boleh masa depan) dan kelas untuk melihat jadwal beserta status sesi (sudah/belum diabsen), lalu membuka form absensi untuk koreksi/susulan.
- Pengaturan Periode: mengatur tahun ajaran aktif dan semester aktif yang disimpan di database (tabel `pengaturan`), dengan saran otomatis berbasis tanggal kalender (Juli-Desember = Ganjil, Januari-Juni = Genap) dan konfirmasi sebelum pergantian periode.

### Guru
- Dashboard: ringkasan statistik (kelas, mapel, jadwal), daftar jadwal mengajar hari ini, dan tombol "Jadwal & Koreksi Absensi" yang selalu terlihat.
- Halaman jadwal mingguan interaktif: kartu jadwal mingguan menampilkan tanggal dalam jendela 7 hari terakhir yang cocok dengan hari jadwal, badge status ("Hari ini", "Sudah diabsen", atau "Belum diabsen"), dan bisa diklik untuk membuka absensi (koreksi/susulan).
- Menu dan halaman "Jadwal & Koreksi Absensi" di sidebar guru (tepat di bawah "Jadwal Mengajar"): menampilkan daftar 7 hari terakhir (hari ini sampai H-6) urut dari yang terbaru dengan hari + tanggal, daftar jadwal milik guru tersebut pada tiap tanggal, badge status ("Sudah diabsen" / "Belum diabsen", label "Hari ini"), tanggal tanpa jadwal tampil ringkas ("Tidak ada jadwal"), dan link langsung ke form absensi untuk koreksi/susulan.
- Halaman absensi: semua siswa kelas tampil dengan status default **Hadir**. Guru mengubah yang berbeda menjadi Izin, Sakit, atau Alpa, keterangan opsional, lalu menyimpan. Menampilkan dengan jelas tanggal sesi yang sedang dibuka.
- Membuka kembali sesi yang sudah ada untuk diedit atau diisi susulan (dalam batas koreksi 7 hari terakhir yang cocok dengan hari jadwal).
- Riwayat absensi dua tingkat (rute `guru.riwayat` dan `guru.riwayat.detail`): halaman utama menampilkan pilihan kartu kombinasi Kelas-Mapel yang diampu guru pada periode aktif, kemudian membuka halaman detail berupa tabel matriks kehadiran horizontal per pertemuan (P1..Pn) beserta daftar siswa dan tombol ekspor Excel.
- Laporan dan ekspor: mengekspor rekap absensi kelas dalam format Excel multi-sheet per kelas-mapel (matriks per pertemuan P1..Pn, tanpa PDF), didukung parameter `kelas_mapel` dengan format `"{kelas_id}-{mapel_id}"`.

### Siswa (read-only)
- Dashboard: status hari ini per mapel sesuai jadwal (*Belum diabsen / Hadir / Izin / Sakit / Alpa*).
- Riwayat kehadiran dengan filter tanggal dan mapel.
- Persentase kehadiran per mapel.

## 4. Aturan bisnis
Setiap aturan di bawah harus punya test.

- **AB-01** Satu sesi unik per (jadwal, tanggal). Jika sesinya sudah ada, sistem membuka sesi itu untuk diedit, tidak membuat duplikat.
- **AB-02** "Belum diabsen" berarti belum ada baris `detail_absensi`. Tidak pernah disimpan sebagai alpa.
- **AB-03** Guru hanya mengabsen jadwal miliknya, pada tanggal yang jatuh di hari jadwal tersebut dan dalam 7 hari terakhir (hari ini dan 6 hari sebelumnya, Asia/Jakarta); jadwal yang belum diabsen boleh diisi susulan dalam batas itu; admin boleh koreksi tanggal apa pun; tanggal masa depan ditolak untuk semua role. MVP tidak membatasi jam, hanya hari.
- **AB-04** Saat sesi disimpan, semua siswa kelas mendapat satu baris `detail_absensi` (default hadir kecuali diubah), dalam satu transaksi database.
- **AB-05** Mekanisme Master Data & Integritas Histori: Penghapusan master data Siswa, Guru, Kelas, dan Mapel yang sudah memiliki riwayat absensi tidak dihapus permanen melainkan menggunakan mekanisme `SoftDeletes`. Khusus tabel `Jadwal` TIDAK menggunakan soft delete: jadwal tidak dapat dihapus jika sudah memiliki sesi absensi (`sesi_absensi`), dan hanya dihapus permanen jika belum pernah memiliki sesi absensi. Guru atau siswa yang profilnya telah di-soft-delete diblokir dari login dan sesi aktifnya langsung dihentikan. Akun User mereka tetap tersimpan di database guna menjaga integritas riwayat absensi, dan akses akan aktif kembali secara otomatis jika profil dipulihkan (restore).
- **AB-06** Satu guru tidak boleh punya dua jadwal yang jamnya beririsan di hari yang sama pada tahun ajaran dan semester yang sama. Berlaku juga untuk satu kelas.
- **AB-07** Kalkulasi Persentase Kehadiran: Persentase = ((Hadir + Izin + Sakit) / Total Sesi Diabsen) * 100. Status Hadir, Izin, dan Sakit dihitung sebagai hadir. Alpa tidak dihitung. Pembagi adalah jumlah sesi yang sudah diabsen untuk siswa itu (sesi yang belum diabsen tidak dihitung).
- **AB-08** Siswa hanya bisa melihat data miliknya. Guru hanya bisa melihat kelas yang ia ajar (berdasarkan jadwal). Otorisasi akses detail riwayat guru (`guru.riwayat.detail`) ditegakkan melalui `JadwalPolicy::viewRiwayat()`; guru hanya berhak mengakses kelas-mapel yang diampunya, sedangkan akses ke jadwal/kelas-mapel milik guru lain ditolak dengan HTTP 403 Forbidden. Pengguna tanpa role guru (admin dan siswa) ditolak dengan 403, dan tamu dialihkan ke login.
- **AB-09** Setiap sesi mencatat siapa yang mengabsen (`diabsen_oleh`) dan siapa yang terakhir mengubah (`diubah_oleh`).
- **AB-10** Akun hanya dibuat admin, tidak ada registrasi publik. Login resmi fleksibel memakai `username` ATAU `NIP` (guru) ATAU `NIS` (siswa). Jika akun guru atau siswa memiliki profil yang berstatus soft delete, login ditolak dengan pesan: "Akun ini sudah tidak aktif. Silakan hubungi admin sekolah." tanpa membuka celah bypass rate limiting (rate limiter tetap mencatat kegagalan).
- **AB-11** Periode Aktif: Periode akademik aktif (tahun ajaran dan semester) diatur oleh admin melalui menu "Pengaturan Periode" dan disimpan di database (tabel `pengaturan`, bukan file config/.env). Daftar master data admin (Kelas, Jadwal) bawaannya menampilkan semua periode dan bisa difilter; halaman operasional (dashboard guru, dashboard siswa, jadwal pelajaran guru/siswa, koreksi, dan riwayat guru) serta rekapitulasi/ekspor menggunakan periode aktif sebagai filter default; periode lainnya hanya dapat diakses melalui filter eksplisit. Sistem menyediakan saran otomatis berbasis tanggal kalender (Juli-Desember = Ganjil, Januari-Juni = Genap) dan meminta konfirmasi pengguna sebelum periode aktif diperbarui.
- **AB-12** Standar Waktu: Seluruh sistem, operasi tanggal, pencatatan sesi, dan jam absensi menggunakan standar zona waktu `Asia/Jakarta`.

## 5. Skema database

```
users(id, name, username unique, email null, password, role enum[admin,guru,siswa], timestamps)
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

Index: `siswa(kelas_id)`, `jadwal(guru_id, hari)`, `jadwal(kelas_id, hari)`, `sesi_absensi(tanggal)`, `detail_absensi(siswa_id)`.

Catatan: `diabsen_oleh` dan `diubah_oleh` merujuk ke `users.id` dengan aturan FK `restrictOnDelete` untuk menjaga integritas data riwayat absensi.

## 6. Kebutuhan non-fungsional
- **Arsitektur Teknis:** Pemisahan tanggung jawab (*Separation of Concerns*) secara ketat antar layer:
  - **Controller:** Berperan tipis (*Thin Controller*) yang bertugas menerima request HTTP, memanggil Service yang sesuai, dan mengembalikan response JSON atau view Blade.
  - **FormRequest:** Khusus untuk validasi input data dari pengguna di sisi server dan menghubungkan pemeriksaan otorisasi awal.
  - **Policy:** Khusus untuk memusatkan otorisasi hak akses (*authorization rules*), termasuk batasan jadwal mengajar guru dan hak koreksi historis admin.
  - **Service Pattern (`AbsensiService` & `LaporanService`):** Sebagai pusat seluruh logika bisnis (*business logic*), kalkulasi persentase kehadiran, agregasi rekapitulasi, dan eksekusi transaksi absensi serta ekspor laporan.
  - **Model:** Khusus menangani pemetaan relasi Eloquent (*relationships*), query scopes, dan kekhawatiran persistensi (*persistence concern*).
  - **View:** Khusus untuk layer presentasi UI menggunakan Blade Templating, Tailwind CSS, dan Alpine.js.
- **Performa:** halaman absensi untuk kelas 40 siswa terbuka kurang dari 2 detik. Rekap memakai agregasi SQL, tanpa N+1. Ekspor laporan admin dibatasi untuk mencegah *Out Of Memory* (OOM) dan pemborosan CPU: batas jumlah sheet Excel (`batas_sheet_ekspor`, default 50) dan batas baris PDF (`batas_baris_pdf`, default 2000) yang dapat disesuaikan pada file konfigurasi (`config/absensi.php`). Halaman laporan admin dilengkapi filter Jurusan untuk mempersempit cakupan data ekspor.
- **Keamanan:** password di-hash, proteksi CSRF, otorisasi lewat Policy, rate limiting pada login, validasi di sisi server.
- **Tampilan:** responsif, nyaman dipakai guru dari HP, layout sidebar, font Inter, ikon Tabler, serta toggle Mode Terang/Gelap menggunakan Alpine.js dan Tailwind (dengan Mode Terang sebagai setelan bawaan/default).
- **Bahasa:** seluruh antarmuka Bahasa Indonesia.
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

