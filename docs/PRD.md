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
| Mengabsen | Ya (koreksi historis kapan saja pada tanggal lampau) | Ya (jadwal sendiri, hari yang sama) | Tidak |
| Melihat rekap | Semua kelas | Kelas yang ia ajar | Milik sendiri |
| Ekspor Excel/PDF | Ya | Ya | Tidak |

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

### Guru
- Dashboard: ringkasan statistik (kelas, mapel, jadwal) dan daftar jadwal mengajar hari ini.
- Halaman jadwal mingguan: melihat seluruh jadwal mengajar dalam seminggu.
- Halaman absensi: semua siswa kelas tampil dengan status default **Hadir**. Guru mengubah yang berbeda menjadi Izin, Sakit, atau Alpa, keterangan opsional, lalu menyimpan.
- Membuka kembali sesi yang sudah ada untuk diedit (hanya di hari yang sama).
- Riwayat sesi dan rekap per kelas, mapel, dan periode, dengan ekspor Excel dan PDF.

### Siswa (read-only)
- Dashboard: status hari ini per mapel sesuai jadwal (*Belum diabsen / Hadir / Izin / Sakit / Alpa*).
- Riwayat kehadiran dengan filter tanggal dan mapel.
- Persentase kehadiran per mapel.

## 4. Aturan bisnis
Setiap aturan di bawah harus punya test.

- **AB-01** Satu sesi unik per (jadwal, tanggal). Jika sesinya sudah ada, sistem membuka sesi itu untuk diedit, tidak membuat duplikat.
- **AB-02** "Belum diabsen" berarti belum ada baris `detail_absensi`. Tidak pernah disimpan sebagai alpa.
- **AB-03** Guru hanya bisa mengabsen jadwalnya sendiri, dan hanya pada tanggal hari ini yang cocok dengan hari jadwal. Guru hanya bisa mengedit sesi yang tanggalnya hari ini. Role Admin secara eksplisit diizinkan melakukan koreksi historis (mengubah data absensi pada tanggal di masa lampau kapan saja). MVP tidak membatasi jam, hanya hari.
- **AB-04** Saat sesi disimpan, semua siswa kelas mendapat satu baris `detail_absensi` (default hadir kecuali diubah), dalam satu transaksi database.
- **AB-05** Mekanisme Master Data & Integritas Histori: Seluruh penghapusan master data (Siswa, Guru, Kelas, Mapel, Jadwal) yang sudah memiliki riwayat absensi tidak dihapus permanen melainkan menggunakan mekanisme `SoftDeletes` (atau diproteksi dari hard-delete) untuk menjamin histori absensi masa lalu tetap utuh dan valid.
- **AB-06** Satu guru tidak boleh punya dua jadwal yang jamnya beririsan di hari yang sama. Berlaku juga untuk satu kelas.
- **AB-07** Kalkulasi Persentase Kehadiran: Persentase = ((Hadir + Izin + Sakit) / Total Sesi Diabsen) * 100. Status Hadir, Izin, dan Sakit dihitung sebagai hadir. Alpa tidak dihitung. Pembagi adalah jumlah sesi yang sudah diabsen untuk siswa itu (sesi yang belum diabsen tidak dihitung).
- **AB-08** Siswa hanya bisa melihat data miliknya. Guru hanya bisa melihat kelas yang ia ajar (berdasarkan jadwal).
- **AB-09** Setiap sesi mencatat siapa yang mengabsen (`diabsen_oleh`) dan siapa yang terakhir mengubah (`diubah_oleh`).
- **AB-10** Akun hanya dibuat admin, tidak ada registrasi publik. Login resmi fleksibel memakai `username` ATAU `NIP` (guru) ATAU `NIS` (siswa).
- **AB-11** Standar Waktu: Seluruh sistem, operasi tanggal, pencatatan sesi, dan jam absensi menggunakan standar zona waktu `Asia/Jakarta`.

## 5. Skema database

```
users(id, name, username unique, email null, password, role enum[admin,guru,siswa])
jurusan(id, nama, kode)
kelas(id, jurusan_id, nama, tingkat, tahun_ajaran, deleted_at null)
guru(id, user_id, nip null, deleted_at null)
siswa(id, user_id, kelas_id, nis unique, deleted_at null)
mapel(id, nama, kode, deleted_at null)
jadwal(id, kelas_id, mapel_id, guru_id, hari enum[senin..sabtu], jam_mulai, jam_selesai, tahun_ajaran, deleted_at null)
sesi_absensi(id, jadwal_id, tanggal, diabsen_oleh, diubah_oleh null, catatan null, timestamps)
  UNIQUE(jadwal_id, tanggal)
detail_absensi(id, sesi_absensi_id, siswa_id, status enum[hadir,izin,sakit,alpa], keterangan null, timestamps)
  UNIQUE(sesi_absensi_id, siswa_id)
```

Index: `siswa(kelas_id)`, `jadwal(guru_id, hari)`, `jadwal(kelas_id, hari)`, `sesi_absensi(tanggal)`, `detail_absensi(siswa_id)`.

Catatan: `diabsen_oleh` dan `diubah_oleh` merujuk ke `users.id`.

## 6. Kebutuhan non-fungsional
- **Arsitektur Teknis:** Pemisahan tanggung jawab (*Separation of Concerns*) secara ketat antar layer:
  - **Controller:** Berperan tipis (*Thin Controller*) yang bertugas menerima request HTTP, memanggil Service yang sesuai, dan mengembalikan response JSON atau view Blade.
  - **FormRequest:** Khusus untuk validasi input data dari pengguna di sisi server dan menghubungkan pemeriksaan otorisasi awal.
  - **Policy:** Khusus untuk memusatkan otorisasi hak akses (*authorization rules*), termasuk batasan jadwal mengajar guru dan hak koreksi historis admin.
  - **Service Pattern (`AbsensiService`):** Sebagai pusat seluruh logika bisnis (*business logic*), kalkulasi persentase kehadiran, agregasi rekapitulasi, dan eksekusi transaksi absensi.
  - **Model:** Khusus menangani pemetaan relasi Eloquent (*relationships*), query scopes, dan kekhawatiran persistensi (*persistence concern*).
  - **View:** Khusus untuk layer presentasi UI menggunakan Blade Templating, Tailwind CSS, dan Alpine.js.
- **Performa:** halaman absensi untuk kelas 40 siswa terbuka kurang dari 2 detik. Rekap memakai agregasi SQL, tanpa N+1.
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
