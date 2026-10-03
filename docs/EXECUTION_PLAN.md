# Rencana Eksekusi Audit MyAbsen

Berdasarkan hasil audit `AUDIT_MYABSEN.md` dan aturan di `AGENTS.md`, pengerjaan perombakan harus dilakukan secara bertahap dan terstruktur untuk meminimalisir regresi. Berikut adalah rencana eksekusi (Execution Plan) yang dibagi menjadi beberapa fase logis.

## Fase A: Perbaikan Kritis & Lingkungan (Hotfix)
Fase ini fokus memperbaiki lingkungan pengujian dan bug kritis yang bisa membuat aplikasi gagal berfungsi (Error 500) sebelum melakukan refaktor besar.
- **T-02**: Perbaiki lingkungan test (aktifkan driver SQLite atau set DB ke MySQL test).
- **T-27**: Tangani relasi kelas/mapel yang di-soft-delete (tambah `withTrashed()` di relasi Jadwal dan beri validasi pencegahan hapus jika masih ada jadwal aktif).
- **T-30**: Hapus file mati/stub untuk mengurangi beban pemeliharaan (`LaporanAbsensiMultiSheetExport`, `getRiwayatSesi`, dll).

## Fase B: Fondasi Struktur Akademik (Database & Migrasi)
Fase ini menjawab kekurangan struktur data yang mendasar (Q-06, Q-07, Q-09).
- **T-06**: Buat tabel `tahun_ajaran` dan `semester` (tanggal mulai/selesai, status aktif). Tambahkan `semester_id` ke `sesi_absensi`.
- **T-07**: Buat tabel pivot `anggota_kelas` untuk menyimpan riwayat siswa di kelas per semester. Hapus penimpaan langsung di `siswa.kelas_id` dan perbarui query laporan untuk join via jadwal/anggota kelas.
- **T-08**: Buat tabel `pengampu` untuk menghindari ambiguitas jadwal ganda.
- **T-11 & T-12 (Q-04, Q-05, Q-10)**: Buat tabel `log_koreksi_absensi` (nilai lama, baru, alasan). Tambahkan fitur buka kunci manual oleh admin (dengan durasi).
- **T-28 & T-29**: Perbaiki constraint FK `diabsen_oleh` menjadi `nullOnDelete` / `restrictOnDelete` dan tambahkan `softDeletes` ke tabel `jadwal`.

## Fase C: Standarisasi Logika Bisnis & Persentase
Fase ini menyamakan persepsi persentase kehadiran antara view, export, dan dashboard, serta memperjelas status sel kosong.
- **T-03**: Satukan rumus persentase di `AbsensiService` (H+I+S per data valid). Hapus perhitungan manual di Blade dan Excel.
- **T-04 (Q-02, Q-03)**: Ubah pembagi persentase menjadi jumlah pertemuan yang telah terlaksana (bukan data yang ada), sesuai jawaban rekomendasi.
- **T-05 (Q-01)**: Bedakan tampilan visual antara "Belum diabsen" dengan "Bukan anggota" / "Libur" pada matriks riwayat.

## Fase D: Optimasi Performa & Refaktor Riwayat Guru
Fase ini menangani masalah N+1 masif dan memori (T-19) pada halaman riwayat guru.
- **T-19**: Ubah halaman riwayat guru agar memuat data per satu kombinasi kelas+mapel saja (lewat dropdown atau filter), tidak memuat seluruh kombinasi sekaligus.
- **T-20**: Ganti penggunaan `whereYear` dan `whereMonth` dengan `whereBetween` agar index tanggal terpakai.
- **T-10 & T-09**: Stabilkan penomoran P1..Pn berdasarkan perhitungan pertemuan per semester, dan di Excel petakan data per kolom sesi secara berurutan, bukan per tanggal tunggal.
- **T-13**: Tambahkan filter `guru_id` pada query Excel untuk mencegah kebocoran data jika ada jadwal ganda di kelas+mapel yang sama.

## Fase E: Laporan, Ekspor, dan Antrean (Queue)
Fase ini mencegah *timeout* dan *Out of Memory* saat ekspor laporan admin.
- **T-16 (Q-14)**: Wajibkan filter bulan/semester pada form ekspor Admin. Implementasikan `ShouldQueue` untuk ekspor dalam jumlah besar. Batasi baris ekspor PDF.
- **T-17**: Buat rekap presensi per siswa per semester (lintas mapel) untuk kebutuhan rapor wali kelas.
- **T-14**: Kembalikan validasi `ExportLaporanRequest` di endpoint export guru.
- **T-18**: Format NIS secara eksplisit menjadi string di Excel admin dan bersihkan prefix spasi di Excel guru.

## Fase F: UI/UX, Penambahan Role, & Keamanan Akhir
Fase perbaikan antarmuka, role tambahan (Q-11, Q-12), dan keamanan autentikasi.
- **T-15 (Q-11, Q-12)**: Tambahkan flag atau logic untuk role Wali Kelas (melihat laporan kelas walian) dan Kepala Sekolah (read-only global).
- **T-22 & T-23**: Perbaiki nomor urut acak di riwayat dan perbaiki UI kolom sticky yang bertumpuk.
- **T-24**: Tambahkan widget analitik berbasis pengecualian (sesi kosong, peringatan Alpa) di Dashboard Admin.
- **T-25**: Perbaiki UX paska simpan absensi (redirect kembali ke halaman asal, bukan dashboard) dan tampilkan nama user di log `diubah_oleh`.
- **T-26 (Q-15)**: Terapkan password awal berbasis tanggal lahir / acak, bukan "password", dan wajib ganti saat login perdana.

---
Silakan berikan konfirmasi apakah Anda setuju dengan susunan fase ini. Jika setuju, kita akan mulai mengeksekusi **Fase A** terlebih dahulu (dikerjakan di branch baru, misalnya `fitur/audit-fase-a`).
