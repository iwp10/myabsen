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
| 12 | Pembaruan Tampilan Frontend (Tahap 1) | Agent | feat/desain-mobile-konsisten | selesai |

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
- [x] Riwayat Siswa Dua Tingkat: Tab Per Mata Pelajaran (grid kartu dengan nama mapel, guru, persentase AB-07, H/I/S/A, total sesi) dan Tab Semua Riwayat, serta Tingkat 2 Detail Per Mapel (matriks presensi P1..Pn satu baris milik siswa login dengan catatan keterangan, hanya-baca, isolasi AB-08, tanpa N+1).

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
- [x] Notifikasi ganti password ramah pengguna (banner hijau sukses dengan auto-dismiss, banner merah gagal dengan highlight kolom, pengalihan pengguna must_change_password ke dashboard role-nya) (AB-10)
- [x] Penguatan keamanan sesi dan logout: header anti-cache no-store pada seluruh halaman terautentikasi (mencegah akses via tombol Back setelah logout), logout idempotent tanpa 419/500 bagi guest, penanganan global TokenMismatchException (419) dengan pesan ramah, dan view error 419 terpadu
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
- [x] Fitur Admin: Filter terpadu jadwal admin (hari, kelas, guru, mapel, rentang jam format H:i divalidasi JadwalFilterRequest, urutan bawaan hari lalu jam, tombol reset, dan paginasi query string).
- [x] Fitur Guru: Filter hari pada jadwal mengajar guru dan sorotan visual pada hari ini (badge "Hari ini" dan latar lembut).
- [x] Fitur Siswa: Menu sidebar dan route "Jadwal Pelajaran" (siswa.jadwal) kelas siswa pada periode aktif dikelompokkan per hari urut jam dengan filter hari dan empty state ramah.
- [x] Fitur Guru: Kartu pengingat jadwal belum diabsen dalam 7 hari terakhir pada dashboard guru dengan tautan langsung dan pengecekan jam mulai hari ini.
- [x] Fitur Guru: Penanda kehadiran rendah ("Perlu perhatian") pada siswa di bawah batas konfigurasi (config/absensi.batas_kehadiran_rendah) di riwayat detail guru.
- [x] Fitur Siswa: Filter status kehadiran (Hadir, Izin, Sakit, Alpa) pada riwayat siswa dengan validasi FormRequest tanpa mengubah rekapitulasi persentase per mapel.
- [x] Fitur Guru: Kartu Total Kelas dan Total Mata Pelajaran interaktif di dashboard guru dengan modal Alpine daftar kelas/mapel periode aktif dan tautan filter jadwal.
- [x] Fitur Guru: Filter Kelas dan Mata Pelajaran pada Jadwal Mengajar guru (divalidasi JadwalGuruFilterRequest), sorotan hijau pada jadwal yang cocok, label filter aktif dengan tombol hapus filter, dan tombol reset.
- [x] Fitur Siswa: Menu sidebar dan route "Mata Pelajaran" (siswa.mapel) menampilkan ringkasan kartu mapel, guru pengampu, jadwal sesi, empty state, dan tautan filter ke Jadwal Pelajaran.
- [x] Fitur Siswa: Filter Mata Pelajaran pada Jadwal Pelajaran siswa (divalidasi JadwalSiswaFilterRequest), sorotan hijau sesuai filter, label filter aktif, dan eliminasi N+1 query.
- [x] Fitur Siswa: Kartu Hadir, Izin, Sakit, Alpa di dashboard siswa interaktif tertaut ke tab Semua Riwayat dengan status dan parameter periode=semua yang sama.
- [x] Fitur Siswa: Filter bulan (input type="month" format Y-m), opsi 'Semua Periode', header ringkas "Menampilkan {n} catatan {status}", tombol reset, dan paginasi pada tab Semua Riwayat.

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
- [x] Pembersihan File dan Dead Code Tak Terpakai: Pelepasan dump SQL (database/*.sql) dari git tracking, penghapusan view Blade dan komponen sisa Breeze yang tidak dirujuk (welcome, dashboard, guest layout, application-logo, auth-session-status, nav-link, responsive-nav-link), penghapusan ProfileUpdateRequest dan GuestLayout, serta eliminasi duplikasi rute password.update di routes/auth.php.

### Tahap Pembaruan Tampilan Frontend (Tahap 1 / Fase 12)
- [x] Sumber desain tunggal: panduan token dan WCAG di `docs/DESAIN.md`, helper `App\Support\Ui` dengan nama class literal utuh, dan 9 komponen Blade UI (`resources/views/components/ui/`: `card`, `stat-card`, `status-badge`, `table`, `filter-bar`, `empty-state`, `section-title`, `page-header`, `button`).
- [x] Standardisasi warna status ("Satu Warna Satu Arti") pola tinted berikon Tabler & teks (Hadir emerald, Izin blue, Sakit amber, Alpa red, Belum diabsen slate border-dashed) yang lolos uji kontras WCAG AA (>= 4.5:1).
- [x] Perbaikan mode gelap: bar tanggal/hari berlatar elevated `dark:bg-slate-800` (bebas latar silau), teks sekunder/meta tegas (`dark:text-slate-300` / `dark:text-slate-400`), kartu jadwal berelevasi `dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5`, dan strip rekap H/I/S/A beraksen tinted cerah.
- [x] Aturan mobile responsif: teks dasar & input minimal 16px (cegah zoom otomatis iOS), target sentuh >= 44x44px dengan jarak 8px, ramah layar hingga 360px.
- [x] Tabel transformatif `responsive-stack-table` dengan atribut `data-label` bertumpuk di HP tanpa duplikasi markup; matriks P1..Pn bergeser horizontal dengan kolom identitas sticky opak berbayang.
- [x] Form absensi guru: tombol Simpan melekat (`sticky bottom-0`) dengan padding safe-area, pilihan status berukuran besar, dan ringkasan H/I/S/A terlihat langsung.
- [x] Penerapan menyeluruh pada layout app (sidebar, topbar), login, tiga dashboard (admin, guru, siswa), seluruh halaman guru dan siswa.
- [x] Refaktor matriks presensi mobile (< md / 768px): bertransformasi menjadi daftar kartu vertikal responsif dengan chip pertemuan P{n} (label, tanggal, ikon + huruf status lewat `App\Support\Ui::pertemuanChip` dan `x-ui.pertemuan-chip`) pada riwayat guru dan siswa.
- [x] Bilah filter mobile: 1 kolom penuh (`grid-cols-1`, `w-full min-w-0`), label di atas kontrol, tinggi minimal 44px, padding kanan pencegah bentrok chevron/teks pada layar 360px.
- [x] Komponen `x-ui.file-input`: tombol `file:` bertema emerald (`file:bg-emerald-100`, `dark:file:bg-emerald-500/15`) dengan tinggi minimal 44px dan teks bantu format/ukuran.
- [x] Navigasi tab pergantian periode: bilah scroll-snap horizontal, Alpine `$nextTick` auto-scroll ke panel dengan offset topbar `scroll-mt-24`, sinkronisasi URL hash (`#salin`, `#pindah`, `#lulus`, `#panduan`), fokus judul panel `tabindex="-1"`, dan tautan "Kembali ke daftar langkah".
- [x] Pengujian otomatis: `UiTest` (10 test), `TampilanKonsistenTest` (14 test penegak R1-R5 konsistensi token, SVG ukuran eksplisit & komponen baru), dan `MobileLayoutInteraksiTest` (7 feature test komprehensif); seluruh test proyek PASS.
- [x] Standarisasi ukuran eksplisit seluruh ikon (`x-ui.icon`) sm/md/lg dan penegakan aturan R5 (seluruh SVG di view Blade memiliki class `w-*` & `h-*` atau atribut `width`/`height`).
- [x] Penggantian input `type="month"` pada riwayat siswa menjadi komponen `<x-ui.bulan-select>` berdasarkan periode semester dengan 6 opsi berlabel Indonesia dan pencegahan validasi error pada nilai kosong.
- [x] Standardisasi pemilihan massal terpusat via modul Javascript murni `resources/js/pilihan-massal.js` (`pilihanMassal`), komponen header `<x-ui.pilih-semua>`, komponen baris `<x-ui.pilih-baris>`, normalisasi ID string, pembersihan otomatis ID tersembunyi filter pencarian (`setTersedia`), dan 10 unit test JS murni (`npm run test:js`).
- [x] Koreksi bug inversi status checkbox header pada tab Luluskan Siswa dan Pindahkan Siswa (`admin/pergantian_periode`) serta migrasi konsisten pada Kelola Siswa (`admin/siswa`) dan Data Terhapus (`admin/arsip`).
- [x] Komponen pembungkus seragam `<x-ui.select>` (w-full, min-w-0, pr-10, appearance-none, min-h-[44px], truncate, ikon panah chevron-down) dan penegakan aturan konsistensi R6 di `TampilanKonsistenTest`.
- [x] Pencegahan tabrakan teks periode & lencana status `(aktif)` pada mobile (320–484px) via pembungkus fleksibel `flex-wrap gap-x-2 gap-y-1` dan lencana `shrink-0 whitespace-nowrap`.
- [x] Rancang ulang pengalaman mobile Guru dan Siswa (layar 320px–412px):
  - Komponen bilah navigasi bawah `<x-ui.bottom-nav>` (4 tab konsisten dari `Ui::bottomNavLinks()`, tinggi min 56px, sentuh min 44px, safe-area-inset-bottom, disembunyikan otomatis pada form absensi via `:hide="$hideBottomNav"`).
  - Komponen panel filter terlipat `<x-ui.filter-panel>` (tombol pemicu lencana jumlah aktif, drawer Alpine, dan chip filter aktif yang bisa dihapus per parameter tanpa kehilangan parameter lain; otomatis inline di desktop).
  - Komponen pilihan status form absensi `<x-ui.segmented-status>` (4 tombol besar H/I/S/A, min 44px, warna solid saat terpilih + centang, tint border-2 saat tidak terpilih, di atas 4 input radio `siswa[id][status]` asli ramah aksesibilitas fieldset + legend sr-only).
  - Komponen bilah ringkasan sticky `<x-ui.ringkasan-sticky>` (rekapitulasi dinamis Hadir, Izin, Sakit, Alpa melekat di atas saat gulir, tombol cepat "Semua Hadir", modul JS murni `resources/js/absensi-form.js` dengan 4 unit test Node runner).
  - Restrukturisasi 8 view Guru & Siswa: dashboard ramah layar pertama, jadwal mengajar/pelajaran & koreksi absensi berbasis accordion per hari (hari ini terbuka bawaan dengan lencana khusus), form absensi tombol simpan `sticky bottom-0`, riwayat kartu informatif, dan eliminasi geser horizontal pada layar 320px–412px.
  - Penegak otomatis konsistensi R9 (bottom nav guru & siswa, kesamaan rute dengan menu sidebar) dan R10 (filter panel lipat & pelestarian nama parameter) di `TampilanKonsistenTest` serta feature test `GuruSiswaMobileTest`.

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
2026-10-08 | 7 | chore: pembersihan file tidak terpakai (dump SQL dilepas dari git tracking, hapus view/component/request/layout sisa default Breeze, eliminasi duplikasi route password.update di routes/auth.php, seluruh 195 test PASS).
2026-10-08 | 8 | feat: perbaikan notifikasi ganti password (banner hijau sukses dengan auto-dismiss, banner merah gagal dengan highlight kolom), pengalihan pengguna must_change_password ke dashboard role-nya, penguatan keamanan logout & back history dengan header no-store, logout idempotent untuk tamu tanpa 419/500, penanganan global TokenMismatchException (419) dengan pesan ramah, view error 419 terpadu, serta penambahan 18 feature test (seluruh 213 test PASS).
2026-10-08 | 3,4,5,6,9,11 | feat: implementasi dropdown pilihan periode di riwayat guru (kartu dan detail matriks) serta riwayat siswa (AB-11), pencarian kelas/mapel & siswa (AB-08), ekspor Excel guru membawa periode terpilih, serta validasi penolakan ubah periode kelas berjadwal (AB-06).
2026-10-08 | 3,7,9,11 | feat: implementasi fitur Pergantian Periode admin (salin kelas, pindahkan siswa, luluskan siswa massal via soft delete), perbaikan proteksi hapus kelas aktif, serta keutuhan riwayat kelas lama tanpa tabel baru (AB-13, 231 test PASS).
2026-10-10 | 3,4,5,9 | feat: implementasi empat penambahan ringan antarmuka & penyaringan tanpa migrasi baru: (1) filter jadwal admin (hari, kelas, guru, mapel, rentang jam) dan filter hari jadwal guru dengan sorotan hari ini; (2) menu & route Jadwal Pelajaran siswa (siswa.jadwal); (3) kartu pengingat jadwal belum diabsen 7 hari terakhir di dashboard guru; (4) penanda kehadiran rendah di riwayat guru (config absensi.batas_kehadiran_rendah) dan filter status riwayat siswa.
2026-10-10 | 4,5,9 | feat: interaksi navigasi guru dan siswa: kartu dashboard interaktif dengan modal Alpine untuk guru, filter kelas & mapel jadwal mengajar guru dengan sorotan hijau, menu Mata Pelajaran siswa di sidebar (siswa.mapel), filter mapel jadwal pelajaran siswa, navigasi kartu dashboard siswa ke Semua Riwayat dengan status dan periode=semua yang sama, filter bulan (Y-m), dan header ringkas catatan kehadiran.
2026-10-11 | 3,9 | feat: tiga penambahan tampilan admin: (1) filter kelas optgroup per periode dan pengurutan nama A-Z/Z-A/NIS pada daftar siswa (SiswaFilterRequest) dengan kolom nomor urut dan header ringkas; (2) filter tingkat pada daftar kelas (KelasFilterRequest) bersama pencarian & periode dengan urutan bawaan periode terbaru, tingkat, nama; (3) pewarnaan per kolom di tabel Kelola Jadwal (badge semester biru/ungu, badge hari 6 warna berbeda, jam monospasi teal, badge kelas indigo, teks mapel emerald, teks guru amber).
2026-10-11 | 3,7,8,9 | feat: implementasi aksi massal admin (AB-14) pada Kelola Siswa (reset password massal, hapus massal dengan pemisahan status nonaktif vs permanen via withExists) dan Data Terhapus (pemulihan massal Guru, Siswa, Kelas, Mapel), seleksi per halaman dengan status indeterminate, floating action bar responsif, modal konfirmasi dengan rincian aman, transaksi mandiri per baris, audit log bebas data sensitif, dan 8 feature test komprehensif (113 assertions).
2026-10-11 | 12 | feat: pembaruan tampilan frontend tahap 1 (App\Support\Ui, 9 komponen Blade x-ui.*, standardisasi warna status WCAG AA >= 4.5:1, aturan mobile teks 16px dan target sentuh 44px, tabel daftar bertumpuk via data-label, form absensi sticky bar, peremajaan dashboard, halaman guru & siswa; seluruh 330 test PASS).
2026-10-11 | 12 | feat: perbaikan desain tahap 1 (koreksi mode gelap bar tanggal/hari ke dark:bg-slate-800, penguatan kontras teks sekunder/meta, elevasi kartu jadwal berbayang dan ber-ring, peremajaan strip rekap H/I/S/A beraksen tinted tanpa latar kusam, sticky kolom matriks opak berbayang, single source docs/DESAIN.md, penegakan aturan R1-R4 via TampilanKonsistenTest; seluruh 331 test PASS).
2026-10-11 | 12 | feat: perbaikan tampilan dan interaksi mobile (matriks presensi kartu/chip < md di guru & siswa, filter bar 1 kolom bebas tumpang tindih di 360px, standarisasi x-ui.file-input token emerald 44px, navigasi tab pergantian periode auto-scroll & sinkronisasi URL hash; seluruh pengujian otomatis lulus).
2026-10-11 | 12 | feat: perbaikan ukuran ikon eksplisit seluruh aplikasi (x-ui.icon, penegakan R5) dan standarisasi filter bulan siswa via x-ui.bulan-select berbasis periode (seluruh 348 test PASS).
2026-10-11 | 12 | fix: perbaikan bug checkbox pilih-semua terbalik (modul murni pilihanMassal, x-ui.pilih-semua, x-ui.pilih-baris, test JS node:test) dan tabrakan header periode/dropdown di HP (x-ui.select, flex-wrap + badge whitespace-nowrap, penegakan R6; seluruh test PASS).
2026-10-24 | 4,11 | fix: pembersihan penanda konflik merge Git yang tertinggal dari commit 1e18dd7 pada view riwayat detail guru (resources/views/guru/riwayat_detail.blade.php), pemulihan struktur tunggal thead tabel matriks desktop dan kartu mobile, serta penambahan penegak otomatis TanpaPenandaKonflikTest.
2026-10-24 | 7,11 | chore: pembersihan hasil audit (pencabutan axios dan dependensi yatim follow-redirects, pencabutan laravel/sail dan symfony/yaml via composer, eliminasi method tak terpakai AbsensiService::getRiwayatSesi, pembersihan tests/Unit/ExampleTest.php, konfigurasi SESSION_SECURE_COOKIE & komentar produksi di .env.example, penambahan FormAdminSmokeTest & EnvExampleTest; audit composer: 0 advisories, npm audit: dari 8 kerentanan turun menjadi 3 moderate (PostCSS/Tailwind v3), bundle JS terpangkas dari 108.46 kB menjadi 56.65 kB).
2026-10-24 | 7,12 | feat: implementasi halaman galat kustom 403 (Akses Ditolak) dan 503 (Sistem Sedang Dalam Pemeliharaan tanpa Auth/CSRF dengan fallback style inline), penyelarasan halaman Profil dan form Ganti Password sesuai token DESAIN.md (border-2, input 16px min-h-[44px], tombol primer emerald), peremajaan komponen Breeze (text-input, input-label, input-error, dropdown, dropdown-link, modal), perluasan cakupan penegak desain TampilanKonsistenTest (R1-R6), serta penambahan feature test HalamanGalatDanProfilTest.
2026-10-24 | 7,11 | chore: pembersihan hasil audit (pencabutan axios dan dependensi yatim follow-redirects, pencabutan laravel/sail dan symfony/yaml via composer, eliminasi method tak terpakai AbsensiService::getRiwayatSesi, pembersihan tests/Unit/ExampleTest.php, konfigurasi SESSION_SECURE_COOKIE & komentar produksi di .env.example, penambahan FormAdminSmokeTest & EnvExampleTest; audit composer: 0 advisories, npm audit: dari 8 kerentanan turun menjadi 3 moderate (PostCSS/Tailwind v3), bundle JS terpangkas dari 108.46 kB menjadi 56.65 kB).
2026-10-24 | 7,12 | feat: implementasi halaman galat kustom 403 (Akses Ditolak) dan 503 (Sistem Sedang Dalam Pemeliharaan tanpa Auth/CSRF dengan fallback style inline), penyelarasan halaman Profil dan form Ganti Password sesuai token DESAIN.md (border-2, input 16px min-h-[44px], tombol primer emerald), peremajaan komponen Breeze (text-input, input-label, input-error, dropdown, dropdown-link, modal), perluasan cakupan penegak desain TampilanKonsistenTest (R1-R6), serta penambahan feature test HalamanGalatDanProfilTest.
2026-10-24 | 3,12 | feat: pembaruan tampilan mobile master data admin Tahap 2A (Siswa, Guru, Kelas, Mapel, Jurusan, Jadwal) mencakup daftar, filter-bar 1 kolom, form create & edit, SATU markup tabel-menjadi-kartu (responsive-stack-table dengan data-label), bilah x-ui.pilih-semua di HP, floating bottom action bar dengan safe-area, helper badge Ui::badgeHari & Ui::badgeSemester, penegak R1-R6 diperluas ke 18 view admin, serta penegak baru R7 (tabel bertumpuk) dan R8 (anti-duplikasi checkbox) (seluruh 379 test PHP dan 10 test JS PASS).
2026-10-24 | 4,5,12 | feat: rancang ulang pengalaman mobile untuk role Guru dan Siswa (layar 320px–412px): bilah navigasi bawah x-ui.bottom-nav (Ui::bottomNavLinks(), safe-area, auto-hide di form absensi), panel filter lipat x-ui.filter-panel dengan chip aktif, segmented status form absensi x-ui.segmented-status 4 radio ramah jempol, ringkasan sticky x-ui.ringkasan-sticky dan tombol Semua Hadir (modul murni JS absensi-form.js + unit test Node), accordion jadwal mingguan & koreksi per hari, peremajaan 8 view guru & siswa bebas geser horizontal, penegak R9 & R10 di TampilanKonsistenTest, serta feature test GuruSiswaMobileTest.
2026-10-25 | 4,5,12 | fix: koreksi tiga bug mobile guru & siswa (feat/mobile-guru-siswa): (1) kelengkapan 21 pustaka ikon x-ui.icon (penanganan exception di testing dan fallback lingkaran netral di produksi) dan pembuatan komponen x-ui.back-button untuk seluruh halaman turunan; (2) eliminasi escape ganda entitas HTML (&RARR;, &rarr;, dll.) di {{ }} dan prop komponen diganti Unicode/ikon, disertai penegak baru R11 dan test render bebas escape ganda; (3) standarisasi tombol status absensi kata penuh ("Hadir", "Izin", "Sakit", "Alpa") pada x-ui.segmented-status (grid 2 di <360px dan 4 di >=360px) serta ringkasan melekat x-ui.ringkasan-sticky; seluruh 392 test PHP dan 14 test JS PASS.

## Backlog teknis (belum dikerjakan)
Tugas pemeliharaan dan perbaikan teknis yang perlu dikerjakan pada fase berikutnya:
- [x] **Pembaruan Tampilan Frontend Tahap 2A (Master Data Admin Mobile):** Standardisasi seluruh tabel CRUD dan form master data admin (Siswa, Guru, Kelas, Mapel, Jurusan, Jadwal) ke pola satu markup tabel-menjadi-kartu (`responsive-stack-table` + `data-label`), `x-ui.table`, `x-ui.filter-bar` 1 kolom, `x-ui.select`, floating action bar aman di HP, dan penegak otomatis R7 serta R8.
- [ ] **Pembaruan Tampilan Frontend Tahap 2B (Fitur Operasional & Laporan Admin Mobile):** Standardisasi tampilan mobile untuk halaman Laporan, Koreksi Absensi, Data Terhapus (Arsip), Pengaturan Periode, Pergantian Periode, dan Dashboard admin.
- [ ] hapus permanen dari menu Data Terhapus (belum dibuat sengaja)
- [ ] **Batasan dan Pertimbangan Arsitektur:**
  - Pembagi persentase siswa pindah: Siswa yang baru pindah kelas di tengah periode memiliki pembagi persentase berbasis sesi yang ia ikuti (AB-07).
  - Skema keanggotaan kelas tanpa tabel `anggota_kelas`: Keanggotaan kelas siswa dikelola via pembaruan `siswa.kelas_id` langsung demi menjaga kesederhanaan skema basis data MVP tanpa migrasi baru; batasan yang diketahui: tidak ada pencatatan timestamp tanggal mutasi siswa dan jadwal pelajaran diisi manual tiap periode baru.
- [ ] Audit log koreksi absensi (pencatatan nilai sebelum dan sesudah koreksi beserta alasan).
- [ ] Antrean ekspor latar belakang (`ShouldQueue`) untuk laporan berskala besar.



