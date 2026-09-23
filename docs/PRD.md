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
| Mengabsen | Ya (koreksi kapan saja) | Ya (jadwal sendiri, hari yang sama) | Tidak |
| Melihat rekap | Semua kelas | Kelas yang ia ajar | Milik sendiri |
| Ekspor Excel/PDF | Ya | Ya | Tidak |

## 3. Fitur MVP

### Admin
- Login dengan username dan password.
- CRUD jurusan, kelas, mapel, guru, siswa. Akun user dibuat otomatis, password awal bisa direset.
- Impor siswa dari Excel, dengan laporan baris yang gagal.
- CRUD jadwal (kelas + mapel + guru + hari + jam) dengan pencegahan bentrok.
- Melihat semua rekap dan mengoreksi absensi.

### Guru
- Dashboard: daftar jadwal mengajar hari ini.
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
- **AB-03** Guru hanya bisa mengabsen jadwalnya sendiri, dan hanya pada tanggal hari ini yang cocok dengan hari jadwal. Guru hanya bisa mengedit sesi yang tanggalnya hari ini. Admin bisa mengoreksi kapan saja. MVP tidak membatasi jam, hanya hari.
- **AB-04** Saat sesi disimpan, semua siswa kelas mendapat satu baris `detail_absensi` (default hadir kecuali diubah), dalam satu transaksi database.
- **AB-05** Data siswa, guru, kelas, dan mapel yang sudah punya riwayat absensi tidak dihapus permanen (soft delete).
- **AB-06** Satu guru tidak boleh punya dua jadwal yang jamnya beririsan di hari yang sama. Berlaku juga untuk satu kelas.
- **AB-07** Persentase kehadiran = jumlah status Hadir dibagi jumlah sesi yang sudah diabsen untuk siswa itu (per mapel atau periode), dikali 100. Izin dan sakit tidak dihitung sebagai hadir.
- **AB-08** Siswa hanya bisa melihat data miliknya. Guru hanya bisa melihat kelas yang ia ajar (berdasarkan jadwal).
- **AB-09** Setiap sesi mencatat siapa yang mengabsen (`diabsen_oleh`) dan siapa yang terakhir mengubah (`diubah_oleh`).
- **AB-10** Akun hanya dibuat admin, tidak ada registrasi publik. Login memakai `username` (NIS untuk siswa, NIP atau username yang ditetapkan admin untuk guru dan admin).

## 5. Skema database

```
users(id, name, username unique, email null, password, role enum[admin,guru,siswa])
jurusan(id, nama, kode)
kelas(id, jurusan_id, nama, tingkat, tahun_ajaran, deleted_at)
guru(id, user_id, nip null, deleted_at)
siswa(id, user_id, kelas_id, nis unique, deleted_at)
mapel(id, nama, kode, deleted_at)
jadwal(id, kelas_id, mapel_id, guru_id, hari enum[senin..sabtu], jam_mulai, jam_selesai, tahun_ajaran)
sesi_absensi(id, jadwal_id, tanggal, diabsen_oleh, diubah_oleh null, catatan null, timestamps)
  UNIQUE(jadwal_id, tanggal)
detail_absensi(id, sesi_absensi_id, siswa_id, status enum[hadir,izin,sakit,alpa], keterangan null, timestamps)
  UNIQUE(sesi_absensi_id, siswa_id)
```

Index: `siswa(kelas_id)`, `jadwal(guru_id, hari)`, `jadwal(kelas_id, hari)`, `sesi_absensi(tanggal)`, `detail_absensi(siswa_id)`.

Catatan: `diabsen_oleh` dan `diubah_oleh` merujuk ke `users.id`.

## 6. Kebutuhan non-fungsional
- **Performa:** halaman absensi untuk kelas 40 siswa terbuka kurang dari 2 detik. Rekap memakai agregasi SQL, tanpa N+1.
- **Keamanan:** password di-hash, proteksi CSRF, otorisasi lewat Policy, rate limiting pada login, validasi di sisi server.
- **Tampilan:** responsif, nyaman dipakai guru dari HP.
- **Bahasa:** seluruh antarmuka Bahasa Indonesia.
- **Waktu:** Asia/Jakarta.

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

## 9. Update
- Otentikasi: Sistem login fleksibel menggunakan Username, NIP (Guru), atau NIS (Siswa).
- UI/UX: Penambahan toggle Mode Terang/Gelap menggunakan Alpine.js dan Tailwind, dengan Mode Terang sebagai setelan bawaan (default).
