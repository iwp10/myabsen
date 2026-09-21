# Progress MyAbsen

Cara pakai:
- Setiap orang hanya mengedit **baris fasenya sendiri** (tabel) dan **bagian fasenya sendiri** (checklist), supaya tidak bentrok saat merge.
- Status: `belum` / `dikerjakan` / `selesai`.
- Fase 1 dan 2 dikerjakan satu orang dulu dan di-merge ke `main` sebelum fase lain dimulai.
- Fase 3 sampai 6 bisa dikerjakan paralel oleh orang berbeda setelah fase 2 selesai.

## Ringkasan

| Fase | Isi | Penanggung jawab | Branch | Status |
|---|---|---|---|---|
| 0 | Perencanaan | | | belum |
| 1 | Database (migration, model, seeder) | | | belum |
| 2 | Auth dan role | | | belum |
| 3 | Master data admin | | | belum |
| 4 | Absensi guru | | | belum |
| 5 | Tampilan siswa | | | belum |
| 6 | Rekap dan ekspor | | | belum |
| 7 | Hardening | | | belum |
| 8 | Siap produksi dan deploy | | | belum |

## Checklist per fase

### Fase 0: Perencanaan
- [ ] Agent membaca AGENTS.md dan docs/PRD.md
- [ ] Rencana implementasi disetujui tim

### Fase 1: Database
- [ ] Migration semua tabel sesuai PRD (termasuk soft delete dan index)
- [ ] Model dan relasi
- [ ] Enum `StatusKehadiran`
- [ ] Factory dan seeder demo
- [ ] `php artisan migrate:fresh --seed` berjalan tanpa error

### Fase 2: Auth dan role
- [ ] Breeze (Blade) terpasang, registrasi publik dihapus
- [ ] Login memakai username (bukan email)
- [ ] Middleware `role` dan redirect per role
- [ ] Layout dasar responsif dengan navbar per role
- [ ] Test akses per role

### Fase 3: Master data admin
- [ ] CRUD jurusan, kelas, mapel
- [ ] CRUD guru dan siswa (akun otomatis, reset password)
- [ ] Impor siswa dari Excel
- [ ] CRUD jadwal dengan pencegahan bentrok (AB-06)
- [ ] Pagination dan pencarian

### Fase 4: Absensi guru
- [ ] Dashboard jadwal hari ini
- [ ] Halaman absensi dengan default Hadir
- [ ] Simpan dalam satu transaksi (AB-04)
- [ ] Sesi unik dan edit di hari yang sama (AB-01, AB-03)
- [ ] Koreksi oleh admin, pencatatan `diabsen_oleh` dan `diubah_oleh` (AB-09)
- [ ] Test untuk AB-01 sampai AB-04

### Fase 5: Tampilan siswa
- [ ] Status hari ini per mapel (termasuk Belum diabsen)
- [ ] Riwayat dengan filter
- [ ] Persentase kehadiran (AB-07)
- [ ] Test akses data milik sendiri (AB-08)

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
