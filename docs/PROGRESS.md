# Progress MyAbsen

Cara pakai:
- Setiap orang hanya mengedit **baris fasenya sendiri** (tabel) dan **bagian fasenya sendiri** (checklist), supaya tidak bentrok saat merge.
- Status: `belum` / `dikerjakan` / `selesai`.
- Fase 1 dan 2 dikerjakan satu orang dulu dan di-merge ke `main` sebelum fase lain dimulai.
- Fase 3 sampai 6 bisa dikerjakan paralel oleh orang berbeda setelah fase 2 selesai.

## Ringkasan

| Fase | Isi | Penanggung jawab | Branch | Status |
|---|---|---|---|---|
| 0 | Perencanaan | Agent | main | selesai |
| 1 | Database (migration, model, seeder) | Agent | main | selesai |
| 2 | Auth dan role | Agent | fitur/fase-2-auth-role | selesai |
| 3 | Master data admin | Agent | fitur/fase-3-master-data | dikerjakan |
| 4 | Absensi guru | Agent | fitur/fase-4-absensi-guru | selesai |
| 5 | Tampilan siswa | Agent | fitur/fase-5-tampilan-siswa | selesai |
| 6 | Rekap dan ekspor | | | ditunda |
| 7 | Hardening | | | ditunda |
| 8 | Siap produksi dan deploy | | | ditunda |

## Checklist per fase

### Fase 0: Perencanaan
- [x] Agent membaca AGENTS.md dan docs/PRD.md
- [x] Rencana implementasi disetujui tim

### Fase 1: Database
- [x] Migration semua tabel sesuai PRD (termasuk soft delete dan index)
- [x] Model dan relasi
- [x] Enum `StatusKehadiran`
- [x] Factory dan seeder demo
- [x] `php artisan migrate:fresh --seed` berjalan tanpa error

### Fase 2: Auth dan role
- [x] Breeze (Blade) terpasang, registrasi publik dihapus
- [x] Login memakai username (bukan email)
- [x] Middleware `role` dan redirect per role
- [x] Layout dasar responsif dengan navbar per role
- [x] Test akses per role

### Fase 3: Master data admin
- [x] CRUD jurusan, kelas, mapel
- [x] CRUD guru dan siswa (akun otomatis, reset password)
- [x] Impor siswa dari Excel
- [x] CRUD jadwal dengan pencegahan bentrok (AB-06)
- [ ] Pagination dan pencarian

### Fase 4: Absensi guru
- [x] Dashboard jadwal hari ini
- [x] Halaman absensi dengan default Hadir
- [x] Simpan dalam satu transaksi (AB-04)
- [x] Sesi unik dan edit di hari yang sama (AB-01, AB-03)
- [x] Koreksi oleh admin, pencatatan `diabsen_oleh` dan `diubah_oleh` (AB-09)
- [x] Test untuk AB-01 sampai AB-04

### Fase 5: Tampilan siswa
- [x] Status hari ini per mapel (termasuk Belum diabsen)
- [x] Riwayat dengan filter
- [x] Persentase kehadiran (AB-07)
- [x] Test akses data milik sendiri (AB-08)

### Fase 6: Rekap dan ekspor
- [ ] Rekap per kelas, mapel, periode (agregasi SQL)
- [ ] Ekspor Excel
- [ ] Ekspor PDF
- [ ] Guru hanya melihat kelas yang ia ajar

### Fase 7: Hardening
- [ ] Audit N+1 dan index
- [ ] Rate limiting login
- [ ] Pesan validasi dan halaman error Bahasa Indonesia
- [ ] Empty state dan tampilan mobile
- [ ] README (cara install dan akun demo)

### Fase 8: Siap produksi
- [ ] AdminSeeder khusus produksi (tanpa data demo)
- [ ] Checklist `.env` produksi
- [ ] `docs/DEPLOY.md`
- [ ] Uji dengan `APP_DEBUG=false` dan `php artisan optimize`

## Catatan dan hambatan
Tulis satu baris per catatan dengan format: `tanggal | fase | catatan`.
2026-09-21 | 3,6,7,8 | Fase 3, 6, 7, 8 ditunda untuk fokus MVP/BETA (Fase 1, 2, 4, 5). Master data digenerate via Seeder.
