# Progress MyAbsen

Cara pakai:
- Setiap orang hanya mengedit **baris fasenya sendiri** (tabel) dan **bagian fasenya sendiri** (checklist), supaya tidak bentrok saat merge.
- Status: `belum` / `dikerjakan` / `selesai`.
- Fase 1 dan 2 dikerjakan satu orang dulu dan di-merge ke `main` sebelum fase lain dimulai.
- Fase 3 sampai 6 bisa dikerjakan paralel oleh orang berbeda setelah fase 2 selesai.

## Ringkasan

MyAbsen telah menyelesaikan seluruh fitur inti MVP untuk role Admin, Guru, dan Siswa: otentikasi fleksibel (Username/NIP/NIS), dashboard responsif berbasis font Inter dan Tabler Icons, presensi cepat default Hadir, manajemen sesi dengan jendela koreksi 7 hari, riwayat guru dua tingkat, ekspor Excel multi-sheet matriks pertemuan (P1..Pn), ekspor PDF resmi, serta perlindungan integritas histori absensi dengan 123 automated test lulus (PASS).

| Fase | Isi | Penanggung jawab | Branch | Status |
|---|---|---|---|---|
| 0 | Perencanaan | Agent | main | selesai |
| 1 | Database (migration, model, seeder) | Agent | main | selesai |
| 2 | Auth dan role | Agent | fitur/fase-2-auth-role | selesai |
| 3 | Master data admin | Agent | fitur/fase-3-master-data | selesai |
| 4 | Absensi guru | Agent | fitur/fase-4-absensi-guru | selesai |
| 5 | Tampilan siswa | Agent | fitur/fase-5-tampilan-siswa | selesai |
| 6 | Rekap dan ekspor | Agent | fitur/fase-6-rekap-ekspor | selesai |
| 7 | Hardening | Agent | fitur/fase-7-hardening | selesai |
| 8 | Siap produksi dan deploy | Agent | feat/siap-produksi-akun | dikerjakan |
| 9 | Tambahan | Agent | main | selesai |
| 11 | Improvement & Dokumentasi | Agent | main | selesai |

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
- [x] Pemblokiran akses login dan pemutusan sesi berjalan untuk akun guru dan siswa yang profilnya di-soft-delete (AB-05, AB-10)

### Fase 3: Master data admin
- [x] CRUD jurusan, kelas, mapel
- [x] CRUD guru dan siswa (akun otomatis, reset password)
- [x] Impor siswa dari Excel
- [x] CRUD jadwal dengan pencegahan bentrok (AB-06)
- [x] Pagination dan pencarian

### Fase 4: Absensi guru
- [x] Dashboard jadwal hari ini
- [x] Halaman absensi dengan default Hadir
- [x] Simpan dalam satu transaksi (AB-04)
- [x] Sesi unik (AB-01) dan koreksi/susulan absensi dalam batas 7 hari terakhir untuk guru / kapan saja untuk admin (AB-03)
- [x] Koreksi oleh admin, pencatatan `diabsen_oleh` dan `diubah_oleh` (AB-09)
- [x] Test untuk AB-01 sampai AB-04

### Fase 5: Tampilan siswa
- [x] Status hari ini per mapel (termasuk Belum diabsen)
- [x] Riwayat dengan filter
- [x] Persentase kehadiran (AB-07)
- [x] Test akses data milik sendiri (AB-08)
- [x] Menyesuaikan perhitungan persentase (status Hadir, Izin, dan Sakit dihitung sebagai hadir, Alpa tidak dihitung, pembagi = jumlah sesi yang sudah diabsen untuk siswa itu) dan menambahkan breakdown detail kehadiran transparan di dashboard siswa.

### Fase 6: Rekap dan ekspor
- [x] Rekap per kelas, mapel, periode (agregasi SQL)
- [x] Ekspor Excel
- [x] Ekspor PDF
- [x] Guru hanya melihat kelas yang ia ajar

### Fase 7: Hardening
- [x] Audit N+1 dan index
- [x] Rate limiting login
- [x] Pesan validasi dan halaman error Bahasa Indonesia
- [x] Validasi form sisi server dan penonaktifan validasi bawaan browser (`novalidate`)
- [x] Empty state dan tampilan mobile
- [x] README (cara install dan akun demo)

### Fase 8: Siap produksi
- [x] AdminSeeder khusus produksi (tanpa data demo)
- [x] Password awal aman terkonfigurasi dan wajib ganti password saat login pertama (AB-10)
- [ ] Checklist `.env` produksi
- [ ] `docs/DEPLOY.md`
- [ ] Uji dengan `APP_DEBUG=false` dan `php artisan optimize`

### Fase 9: Tambahan
- [x] Otentikasi: Sistem login fleksibel menggunakan Username, NIP (Guru), atau NIS (Siswa).
- [x] UI/UX: Penambahan toggle Mode Terang/Gelap menggunakan Alpine.js dan Tailwind, dengan Mode Terang sebagai setelan bawaan (default).
- [x] UI/UX: Merombak halaman login dengan background kustom responsif dengan rate-limiting Alpine.js.
- [x] UI/UX: Migrasi layout utama menjadi Sidebar Menu bertema biru dengan identitas SMK Mandiri 02 Balaraja.
- [x] UI/UX: Perbaikan Dashboard Siswa (kontras warna, persentase kehadiran positif Izin & Sakit, serta breakdown transparan).
- [x] UI/UX: Perombakan Dashboard Guru (banner sapaan solid blue, kartu statistik total kelas/mapel/jadwal, dan tombol navigasi pintar saat jadwal kosong).
- [x] UI/UX: Mengganti font utama sistem menjadi Inter untuk meningkatkan aksesibilitas dan kenyamanan membaca.
- [x] Fitur Guru: Halaman jadwal mingguan interaktif (kartu jadwal memuat tanggal dalam 7 hari terakhir, status absensi, dan link langsung ke form absensi/koreksi/susulan).
- [x] Fitur Guru: Menu sidebar "Jadwal & Koreksi Absensi" tepat di bawah "Jadwal Mengajar", halaman daftar 7 hari terakhir (hari ini sampai H-6) urut dari terbaru dengan status sesi, label Hari ini, dan empty state ringkas.
- [x] Fitur Guru: Redesign riwayat absensi guru dua tingkat (halaman pemilihan kartu Kelas-Mapel dan halaman detail tabel matriks pertemuan P1..Pn).
- [x] Fitur Admin: Menu Koreksi Absensi di sidebar dan pintasan dashboard untuk mencari jadwal berdasarkan tanggal lampau dan kelas serta melakukan koreksi/susulan absensi.
- [x] UI/UX: Merombak Dashboard Admin dengan banner sapaan, statistik master data (Siswa, Guru, Kelas, Mapel), dan pintasan aksi cepat.
- [x] UI/UX: Standarisasi seluruh ikon aplikasi menggunakan Tabler Icons (inline SVG) untuk tampilan yang lebih modern, konsisten, dan ringan.
- [x] Fitur Akademik: Penambahan kolom `semester` (Ganjil/Genap) pada kelas dan jadwal beserta filter pada halaman index dan form request.
- [x] Fitur Ekspor: Format ekspor Excel multi-sheet matriks per pertemuan (P1..Pn) per pasangan kelas-mapel untuk guru dan admin.
- [x] Fitur Admin: Pengaturan Periode aktif (AB-11) melalui antarmuka web, migrasi tabel pengaturan (kunci-nilai), saran otomatis berbasis tanggal kalender, modal konfirmasi pergantian, banner pengingat dashboard admin, serta pemakaian seragam di seluruh dashboard guru, dashboard siswa, riwayat guru, rekapitulasi, dan ekspor laporan.
- [x] Fitur Ekspor: Pembatasan jumlah sheet Excel admin ('batas_sheet_ekspor' => 50) dan batas baris PDF ('batas_baris_pdf' => 2000) di config/absensi.php dengan pesan error ramah, penambahan filter Jurusan pada laporan admin, penanganan kombinasi jurusan/kelas tidak cocok, dan eliminasi N+1 query pada pembentukan sheet ekspor.

### Tahap Improvement / Fase 11: Refactor Arsitektur & Dokumentasi
- [x] Pemisahan Tanggung Jawab (Separation of Concerns): Controller tipis (*Thin Controller*), FormRequest khusus validasi input, Policy khusus otorisasi hak akses, Service sebagai pusat seluruh *business logic*, Model khusus relasi & persistensi data, dan View khusus layer presentasi.
- [x] Pembersihan Fat Controller: Memindahkan query agregasi rekap per mapel dan status harian siswa dari `Siswa\DashboardController` ke `AbsensiService`.
- [x] Implementasi Service Pattern: Memusatkan logika statistik guru dan penyiapan form absensi dari `Guru\AbsensiController` ke `AbsensiService`.
- [x] Pemusatan Otorisasi ke Policy: Menegakkan otorisasi absensi jadwal secara terpusat melalui `JadwalPolicy::absen()`, diintegrasikan langsung pada `StoreAbsensiRequest`.
- [x] Eliminasi Duplikasi Logika: Sentralisasi penentuan nama hari server berbasis `Asia/Jakarta` melalui method statis tunggal `AbsensiService::getHariServer()`.
- [x] Standarisasi Timezone: Menyelaraskan seluruh pencatatan waktu dan instansiasi Carbon mutlak menggunakan `Asia/Jakarta`.
- [x] Pencegahan N+1 Query & Eager Loading pada seluruh relasi domain siswa, guru, kelas, dan jadwal.
- [x] Otorisasi Riwayat Guru via Policy: Memindahkan otorisasi detail riwayat guru (`guru.riwayat.detail`) dari `abort(403)` manual di controller ke `JadwalPolicy::viewRiwayat()` dan melengkapi pengujian Feature Test (AB-08).
- [x] FormRequest untuk Import Siswa: Memindahkan validasi manual `$request->validate()` di `SiswaController::import` ke kelas FormRequest tersendiri (`ImportSiswaRequest`) dengan otorisasi khusus admin.
- [x] Sentralisasi Parser `kelas_mapel`: Menyatukan logika pembuatan, pemecahan, dan validasi format `"{kelas_id}-{mapel_id}"` ke dalam class pembantu tunggal `App\Support\KelasMapel` serta aturan validasi `regex:/^[1-9]\d*-[1-9]\d*$/`.
- [x] Pembersihan Dead Code `LaporanAbsensiGuruExport`: Menghapus class export `app/Exports/LaporanAbsensiGuruExport.php` yang tidak pernah diinstansiasi.
- [x] Rapikan Dependensi Tailwind di `package.json`: Menghapus paket `@tailwindcss/vite` yang tidak terpakai agar konsisten dengan `tailwindcss: ^3.1.0` (ukuran bundle CSS `npm run build` tetap ~83 kB).
- [x] Perlindungan FK `diabsen_oleh` dan `diubah_oleh`: Migrasi baru mengubah foreign key pada tabel `sesi_absensi` menjadi `restrictOnDelete` demi melindungi histori absensi, dilengkapi penanganan pesan ramah pada controller admin.
- [x] Penghapusan Ekspor PDF Guru: Menghapus route `guru.laporan.exportPdf`, method controller, dan test terkait atas keputusan pemilik proyek (guru hanya mengekspor Excel; admin tetap Excel dan PDF).
- [x] Menambahkan aset dokumentasi visual dan memperbarui README.md untuk presentasi GitHub.

## Catatan dan hambatan
Tulis satu baris per catatan dengan format: `tanggal | fase | catatan`.
2026-09-21 | 3,6,7,8 | Fase 3, 6, 7, 8 ditunda untuk fokus MVP/BETA (Fase 1, 2, 4, 5). Master data digenerate via Seeder.
2026-09-27 | 3,5,6,7,9 | Implementasi layout Sidebar SMK Mandiri 02 Balaraja, form login rate-limiting Alpine.js, ekspor data awal, hardening N+1, lokalisasi Bahasa Indonesia, font Inter, dan perombakan dashboard siswa dan guru.
2026-09-29 | 2 | Fase 2 (Refactor Dokumen & Test ke Keputusan Bisnis Aktual) telah selesai dilakukan berdasarkan hasil temuan Audit. PRD dan Test diselaraskan dengan aturan bisnis aktual: AB-03 (hak koreksi historis admin), AB-05 (perlindungan soft delete histori master data), AB-07 (kalkulasi persentase kehadiran: Hadir, Izin, dan Sakit dihitung positif; Alpa sebagai pengurang), dan standar timezone Asia/Jakarta. Seluruh 100 pengujian otomatis lulus (PASS).
2026-09-29 | 3 | Tahap Improvement (Refactor Arsitektur) selesai: Pemisahan tegas tanggung jawab Controller-Service-Policy-FormRequest, eliminasi duplikasi hari/tanggal, standarisasi mutlak Asia/Jakarta, dan pembersihan Fat Controllers. Seluruh 100 test lulus (PASS).
2026-10-02 | 3,6,7 | Catatan 2026-09-21 yang menunda Fase 3, 6, 7 sudah tidak berlaku (dikerjakan kemudian, lihat catatan 2026-09-27).
2026-10-02 | 2,3,4,5,7 | Perlu audit: test untuk AB-05, 06, 07, 09, 10 dan rate limiting login sisi server belum terverifikasi.
2026-10-03 | 7 | chore: rapikan batas versi PHP dan maatwebsite/excel di composer.json (PR #10 oleh iwp10).
2026-10-03 | 2 | test: tambah test login NIP/NIS dan soft delete guru (PR #11 oleh iwp10).
2026-10-03 | 4,9 | feat: koreksi absensi 7 hari untuk guru, halaman jadwal interaktif, dan menu koreksi absensi admin (PR #12 oleh iwp10).
2026-10-03 | 4,9 | feat: menu sidebar guru "Jadwal & Koreksi Absensi" (7 hari terakhir dari hari ini s.d. H-6) urut terbaru (PR #13 oleh iwp10).
2026-10-03 | 6 | refactor: satukan logika ekspor laporan ke LaporanService, FormRequest ExportLaporanRequest, dan konsolidasi view PDF (PR #14 oleh iwp10).
2026-10-03 | 6,9 | feat: inisiasi format ekspor Excel multi-sheet matriks per pertemuan (LaporanAbsensiPerKelasSheet) dan draf redesign riwayat guru (oleh lat's play).
2026-10-04 | 4,9 | feat: finalisasi redesign riwayat guru dua tingkat (pemilihan kartu Kelas-Mapel lalu matriks pertemuan P1..Pn di guru.riwayat.detail) dan pembersihan stub export (oleh lat's play).
2026-10-04 | 6,9 | feat: migrasi LaporanAbsensiExport admin ke format multi-sheet per kelas-mapel dan penambahan konfirmasi modal logout (oleh lat's play).
2026-10-04 | 1,3,9 | feat: penambahan kolom dan konsep semester (Ganjil/Genap) pada tabel kelas dan jadwal via migrasi baru serta filter pada index dan request (oleh lat's play).
2026-10-05 | 1,3,4,5,6,9 | feat: implementasi Pengaturan Periode aktif (AB-11) oleh admin via web dan database (tabel pengaturan), saran kalender otomatis, banner pengingat dashboard admin, penyelarasan default periode di dashboard guru, dashboard siswa, riwayat guru, rekap dan ekspor laporan, validasi tahun ajaran YYYY/YYYY (tahun kedua = pertama+1), serta 8 pengujian otomatis AB-11 lulus (PASS).
2026-10-05 | 1,3,6,9 | fix: perbaikan filter periode terpadu master data admin (Kelas dan Jadwal default Semua Periode), standarisasi dark mode form admin (termasuk color-scheme:dark), pembentukan sheet ekspor admin multi-jurusan (TKJ, TBSM, dll) dengan sanitasi nama sheet <= 31 karakter, perbaikan kedipan modal pengaturan periode (type=button, intercept Enter, dan x-cloak global), serta 12 feature test AB-11 lulus (PASS).
2026-10-05 | 6,9 | feat: pembatasan ekspor laporan admin (maks. 50 sheet Excel dan 2000 baris PDF via config/absensi.php), filter jurusan opsional, optimasi eliminasi N+1 pada LaporanAbsensiExport, dan 17 test LaporanTest lulus (PASS).
2026-10-05 | 3,4,8 | refactor: otorisasi riwayat detail guru via JadwalPolicy (AB-08), FormRequest ImportSiswaRequest untuk import siswa, sentralisasi parser dan validasi kelas_mapel ke App\Support\KelasMapel, serta penambahan 7 feature test riwayat guru dan 4 test import siswa (seluruh 155 test PASS).
2026-10-05 | 6,7,9 | chore: pembersihan dead code LaporanAbsensiGuruExport, pencabutan @tailwindcss/vite, migrasi FK restrict sesi_absensi, pemindahan test koreksi admin, serta penghapusan ekspor PDF guru atas keputusan pemilik proyek (guru hanya Excel, admin tetap Excel dan PDF).
2026-10-05 | 2,7,9 | fix: pemblokiran akses login dan pemutusan sesi berjalan untuk akun guru dan siswa yang profilnya telah di-soft-delete (AB-05 & AB-10) tanpa mengubah rate limiting, akun admin, maupun alur soft delete, disertai 5 feature test AB-05.
2026-10-05 | 4,6,7,9 | fix: standardisasi tampilan siswa terhapus (soft-delete) pada seluruh laporan (riwayat guru, Excel, PDF) dengan tanda "(nonaktif)" jika memiliki riwayat absensi pada cakupan filter, header guru terhapus di Excel tetap tampil nama, dan 7 automated feature test AB-05 lulus (PASS).
2026-10-05 | 3,7,9 | feat: implementasi menu admin "Data Terhapus" (Arsip) untuk memulihkan master data yang di-soft-delete (Guru, Siswa, Kelas, Mapel) dengan validasi dependensi dan pencegahan bentrok, pesan validasi edukatif saat input NIP/NIS/kode duplikat dengan data terhapus, serta 9 feature test AB-05 lulus (PASS).
2026-10-06 | 1,3,9 | fix: validasi periode jadwal wajib sama dengan kelasnya pada Store & Update (AB-06), perlindungan route /profile dan /password dengan middleware role:admin,guru,siswa (AB-05), serta penambahan feature test AB-06 dan AB-05 (189 test PASS).
2026-10-06 | 8 | feat: implementasi akun siap produksi: isolasi DemoSeeder untuk non-produksi, AdminSeeder idempotent di produksi, sentralisasi password awal aman (config/absensi.php), migrasi flag must_change_password pada users, middleware EnsurePasswordChanged untuk kewajiban ganti password profil saat login pertama, validasi penolakan password baru yang lemah/sama dengan awal (AB-10), serta 6 feature test AB-10 lulus (195 test PASS).
2026-10-08 | 11 | docs: menambahkan bagian Visual Preview di README.md (dashboard admin, guru, siswa) dan sinkronisasi checklist progress.

## Backlog teknis (belum dikerjakan)
Tugas pemeliharaan dan perbaikan teknis yang perlu dikerjakan pada fase berikutnya:
- [ ] hapus permanen dari menu Data Terhapus (belum dibuat sengaja)
- [ ] validasi saat mengubah periode kelas yang sudah punya jadwal
- [ ] **Pilihan Periode di Riwayat Guru dan Siswa:** Tambahkan dropdown pemilihan tahun ajaran dan semester pada halaman riwayat guru dan riwayat siswa agar pengguna dapat meninjau histori kehadiran periode terdahulu tanpa harus mengubah periode aktif.
- [ ] **Audit Aturan Bisnis Lanjutan (dari `docs/AUDIT_MYABSEN.md`):**
  - Penyempurnaan pembagi persentase untuk siswa pindah kelas/siswa baru agar tidak bias (AB-07).
  - Tabel riwayat keanggotaan kelas per tahun ajaran (`anggota_kelas`) agar riwayat kelas siswa tidak tertimpa saat naik kelas.
  - Audit log koreksi absensi (pencatatan nilai sebelum dan sesudah koreksi beserta alasan).
  - Antrean ekspor latar belakang (`ShouldQueue`) untuk laporan berskala besar.



