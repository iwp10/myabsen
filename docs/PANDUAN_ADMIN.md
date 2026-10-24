# Panduan Pengguna Administrator - MyAbsen

Selamat datang di Panduan Pengguna Administrator MyAbsen. Dokumen ini disusun untuk membantu operator dan staf tata usaha sekolah dalam mengelola data master, jadwal pembelajaran, periode akademik, koreksi absensi, dan laporan kehadiran siswa SMK Mandiri 02 Balaraja dengan mudah dan teratur.

---

## 1. Pengantar Sistem

### Apa itu MyAbsen?
MyAbsen adalah sistem informasi absensi per mata pelajaran berbasis web untuk sekolah menengah kejuruan (SMK). Sistem ini mencatat kehadiran siswa pada setiap jam pelajaran yang diajarkan oleh guru, menyajikan rekapitulasi kehadiran secara otomatis, serta memudahkan pelaporan resmi ke pihak sekolah dan orang tua siswa.

### Siapa Melakukan Apa?
Aplikasi MyAbsen memiliki tiga peran pengguna:
1. **Administrator (Operator / Tata Usaha):** Mengelola seluruh data sekolah (jurusan, kelas, mata pelajaran, guru, siswa, jadwal pelajaran), menentukan periode akademik aktif, mengoreksi data absensi jika diperlukan, memulihkan data terhapus, serta mengunduh rekapitulasi laporan absensi sekolah.
2. **Guru Pengajar:** Mengisi absensi siswa pada setiap jam mengajar, mengoreksi data absensi dalam batas waktu 7 hari, serta memantau riwayat kehadiran siswa pada kelas yang diajarnya.
3. **Siswa:** Melihat jadwal pelajaran mingguan, memantau daftar mata pelajaran, serta memeriksa catatan riwayat dan persentase kehadirannya sendiri.

### Cara Masuk dan Ganti Password Pertama Kali
1. Buka peramban (browser) di komputer atau laptop Anda, lalu kunjungi alamat web MyAbsen sekolah: `[ISI: Alamat web resmi MyAbsen sekolah]`.
2. Pada halaman **Masuk**, masukkan nama pengguna (**Username**) dan kata sandi (**Password**) yang telah diberikan oleh pengelola sistem.
3. Klik tombol **Masuk**.
4. Jika Anda baru pertama kali masuk atau kata sandi Anda baru saja disetel ulang oleh sistem, Anda akan langsung diarahkan ke halaman **Profil**. Sistem mewajibkan Anda membuat kata sandi baru demi keamanan akun.
5. Pada bagian **Perbarui Password**:
   - Ketikkan kata sandi lama pada isian **Password Saat Ini**.
   - Ketikkan kata sandi baru pada isian **Password Baru** (minimal 8 karakter).
   - Ketikkan kembali kata sandi baru pada isian **Konfirmasi Password Baru**.
   - Klik tombol **Simpan Password**.
6. Setelah muncul pemberitahuan berwarna hijau *"Password berhasil diganti"*, Anda dapat membuka seluruh menu administrator pada bilah sisi kiri (sidebar).

[GAMBAR: Halaman login MyAbsen dan formulir ganti password pertama kali di halaman profil]

### Arti Warna Status Kehadiran
Aplikasi MyAbsen menerapkan prinsip satu warna satu arti pada seluruh halaman:

| Status Kehadiran | Warna Lencana | Arti |
|---|---|---|
| **Hadir** | Hijau | Siswa hadir di kelas mengikuti pelajaran. |
| **Izin** | Biru | Siswa tidak hadir dengan izin resmi atau surat dispensasi sekolah. |
| **Sakit** | Kuning / Amber | Siswa tidak hadir karena kondisi kesehatan dan dilengkapi surat keterangan. |
| **Alpa** | Merah | Siswa tidak hadir tanpa keterangan yang sah. |
| **Belum diabsen** | Abu-abu garis putus-putus | Jadwal pelajaran pada hari ini belum diisi absensinya oleh guru. |

---

## 2. Persiapan Awal Berurutan

Saat awal tahun ajaran baru atau saat pertama kali mengoperasikan aplikasi, lakukan penyiapan data master sekolah dengan urutan berurutan berikut agar data saling terhubung dengan benar:

```
[1. Jurusan] ──> [2. Kelas] ──> [3. Mata Pelajaran] ──> [4. Guru] ──> [5. Siswa] ──> [6. Jadwal] ──> [7. Pengaturan Periode]
```

### Langkah 1: Menambah Data Jurusan
1. Klik menu **Jurusan** pada bilah samping.
2. Klik tombol **Tambah Jurusan**.
3. Masukkan **Kode Jurusan** (contoh: `TKRO`, `TBSM`, `RPL`) dan **Nama Jurusan** (contoh: `Teknik Kendaraan Ringan Otomotif`).
4. Klik tombol **Simpan**.

### Langkah 2: Menambah Data Kelas
1. Klik menu **Kelas** pada bilah samping.
2. Klik tombol **Tambah Kelas**.
3. Tentukan data kelas:
   - **Tingkat**: Pilih `10`, `11`, atau `12`.
   - **Nama Kelas**: Tuliskan nama rombongan belajar (contoh: `X TKRO 1`, `XI TBSM 2`).
   - **Jurusan**: Pilih jurusan yang menaungi kelas ini.
   - **Tahun Ajaran**: Pilih tahun ajaran (contoh: `2026/2027`).
   - **Semester**: Pilih `Ganjil` atau `Genap`.
4. Klik tombol **Simpan**.

### Langkah 3: Menambah Data Mata Pelajaran
1. Klik menu **Mata Pelajaran** pada bilah samping.
2. Klik tombol **Tambah Mata Pelajaran**.
3. Tuliskan **Kode Mata Pelajaran** (contoh: `MTK`, `BING`, `PROG-WEB`) dan **Nama Mata Pelajaran** (contoh: `Matematika`, `Pemrograman Web`).
4. Klik tombol **Simpan**.

### Langkah 4: Menambah Data Guru
1. Klik menu **Guru** pada bilah samping.
2. Klik tombol **Tambah Guru**.
3. Tuliskan identitas guru:
   - **Nama Lengkap Guru**: Nama beserta gelar pendidik.
   - **NIP**: Nomor Induk Pegawai (opsional, kosongkan jika belum memiliki NIP).
   - **Username**: Nama pengguna untuk masuk ke aplikasi (wajib unik, misalnya nama tanpa spasi atau NIP).
   - **Email**: Alamat surel aktif (opsional).
4. Klik tombol **Simpan**.
5. Kata sandi awal guru secara otomatis dibuatkan oleh sistem (`password`). Guru wajib menggantinya saat pertama kali masuk ke aplikasi.

### Langkah 5: Menambah Data Siswa
Anda dapat menambahkan siswa satu per satu secara manual atau mengimpor banyak siswa sekaligus melalui berkas lembar kerja Excel/CSV.

#### Cara A: Tambah Siswa Satu per Satu
1. Klik menu **Siswa** pada bilah samping.
2. Klik tombol **Tambah Siswa**.
3. Masukkan **NIS** (Nomor Induk Siswa, wajib unik), **Nama Lengkap Siswa**, dan pilih **Kelas**.
4. Klik tombol **Simpan**. Kata sandi awal siswa otomatis disetel menjadi `password`.

#### Cara B: Impor Siswa Sekaligus Lewat Excel / CSV
1. Klik menu **Siswa** pada bilah samping.
2. Perhatikan kartu **Impor Data Siswa** di bagian atas daftar siswa.
3. Siapkan berkas Excel (.xlsx) atau CSV dengan struktur 2 kolom persis seperti berikut:

| nis | name |
|---|---|
| 20261001 | Achmad Pratama |
| 20261002 | Bella Anggraini |
| 20261003 | Chairul Anwar |

> Baris pertama wajib memuat judul kolom huruf kecil: `nis` dan `name`. Jangan menambahkan kolom lain pada berkas ini.

4. Pada formulir **Impor Data Siswa**:
   - Pilih kelas tujuan pada kotak pilihan **Pilih Kelas**.
   - Klik tombol **Pilih Berkas** pada isian **File Excel/CSV**, lalu pilih berkas dari komputer Anda.
   - Klik tombol hijau **Impor**.
5. Tunggu proses selesai. Sistem akan menampilkan pemberitahuan hijau jumlah siswa yang berhasil didaftarkan.

[GAMBAR: Formulir impor data siswa melalui Excel dan tabel daftar siswa]

### Langkah 6: Membuat Jadwal Pelajaran
1. Klik menu **Jadwal** pada bilah samping.
2. Klik tombol **Tambah Jadwal**.
3. Lengkapi formulir:
   - **Hari**: Pilih hari pembelajaran (`Senin` sampai `Sabtu`).
   - **Jam Mulai** dan **Jam Selesai**: Tentukan jam pelajaran (contoh: `07:15` sampai `09:15`).
   - **Kelas**: Pilih rombongan belajar yang mengikuti pembelajaran.
   - **Mata Pelajaran**: Pilih mata pelajaran yang diajarkan.
   - **Guru**: Pilih guru pengampu.
4. Klik tombol **Simpan**.

> **PERINGATAN PENTING MENGENAI PERIODE JADWAL:**
> Periode tahun ajaran dan semester pada jadwal mengikuti periode kelas yang dipilih. Pastikan kelas yang Anda pilih pada jadwal berada pada tahun ajaran dan semester yang sama dengan periode aktif sekolah saat ini. Jika Anda memilih kelas dari periode yang berbeda, jadwal tersebut tidak akan tampil di dashboard guru pengajar maupun dashboard siswa!

### Langkah 7: Memeriksa Pengaturan Periode Aktif
1. Klik menu **Pengaturan Periode** pada bilah samping.
2. Pastikan **Tahun Ajaran** dan **Semester** yang berstatus aktif sudah sesuai dengan kalender akademik sekolah saat ini.

---

## 3. Tugas Rutin Administrator

### Memantau Halaman Dashboard
Halaman **Dashboard** menyajikan ringkasan statistik terkini sekolah:
- Kartu ringkasan total **Jurusan**, **Kelas**, **Mata Pelajaran**, **Guru**, dan **Siswa**.
- Kartu **Jadwal Aktif Hari Ini**: Jumlah jam pelajaran yang dijadwalkan pada hari ini.
- Kartu **Kehadiran Hari Ini**: Persentase kehadiran akumulatif sekolah untuk hari yang sedang berjalan.
- Tabel pemantauan aktivitas absensi per kelas pada hari ini.

### Tambah, Ubah, dan Hapus Data Master
Pada setiap tabel data master (Jurusan, Kelas, Mata Pelajaran, Guru, Siswa, Jadwal):
- Untuk mengubah data: Klik tombol **Ubah** (ikon pensil berwarna biru) pada baris yang diinginkan, perbarui isian, lalu klik tombol **Simpan**.
- Untuk menghapus data: Klik tombol **Hapus** (ikon tempat sampah berwarna merah). Sistem akan meminta konfirmasi sebelum data dihapus.

### Arti Status "Dihapus" (Perlindungan Histori dan Pemulihan)
Aplikasi MyAbsen menerapkan perlindungan data ketat agar rekam jejak kehadiran siswa tidak hilang tanpa sengaja:
- **Data yang Belum Pernah Dipakai Absensi:** Jika siswa, guru, kelas, atau mapel belum pernah memiliki catatan absensi sama sekali, data akan dihapus permanen dari sistem.
- **Data yang Sudah Memiliki Riwayat Absensi:** Jika data sudah pernah tercatat dalam sesi absensi lampau, sistem **tidak menghapus data secara permanen**. Data tersebut dipindahkan ke arsip (**Data Terhapus**), akunnya dinonaktifkan sehingga tidak bisa masuk ke aplikasi, dan namanya pada rekaman absensi lama diberi tanda **(nonaktif)**. Anda dapat memulihkan kembali data ini kapan saja melalui menu **Data Terhapus**.

### Reset Password Pengguna (Guru / Siswa)
Jika ada guru atau siswa yang lupa kata sandinya:
1. Buka menu **Guru** atau **Siswa**.
2. Cari nama pengguna yang bersangkutan lewat kotak pencarian.
3. Klik tombol **Reset Password** pada baris pengguna tersebut.
4. Klik tombol konfirmasi pada jendela yang muncul.
5. Kata sandi pengguna tersebut akan dikembalikan ke kata sandi standar sekolah (`password`). Beritahukan kepada yang bersangkutan untuk segera masuk dan mengganti kata sandinya di menu **Profil**.

### Menggunakan Fitur Aksi Massal (Batas 100 Data)
Untuk mempercepat pekerjaan pada halaman **Siswa** dan **Data Terhapus**, Anda dapat melakukan tindakan sekaligus terhadap banyak data:
1. Centang kotak centang di sebelah kiri nama siswa yang ingin Anda pilih satu per satu, atau centang kotak **Pilih semua di halaman ini** di bagian atas tabel untuk memilih seluruh data pada halaman tersebut (maksimal 100 baris per halaman paginasi).
2. Setelah ada data yang dicentang, sebuah **bilah tindakan melayang** akan muncul di bagian bawah layar komputer atau ponsel Anda:
   - Menampilkan jumlah data yang dipilih (contoh: *"25 dipilih"*).
   - Tautan **Batalkan pilihan** untuk membatalkan seluruh centang.
   - Tombol **Reset password yang dipilih** (warna kuning/amber).
   - Tombol **Hapus yang dipilih** (warna merah).
3. Klik tombol tindakan yang Anda inginkan.
4. Jendela konfirmasi akan menampilkan daftar nama yang akan diproses beserta peringatan jika ada data yang memiliki riwayat absensi.
5. Klik tombol konfirmasi (misalnya **Reset Password** atau **Hapus Siswa**).
6. Cara membaca hasil:
   - Jika berhasil: Muncul pemberitahuan hijau di bagian atas halaman.
   - Jika terdapat sebagian data yang tidak dapat diproses: Muncul kotak kuning/merah yang merinci baris mana saja yang dilewati sesuai aturan perlindungan data.

[GAMBAR: Bilah aksi massal melayang di bagian bawah layar dan modal konfirmasi tindakan]

### Filter, Pencarian, dan Pengurutan Data
Pada halaman daftar siswa dan master data lainnya:
- **Pilihan Kelas**: Saring siswa berdasarkan rombongan belajar tertentu.
- **Pilihan Urutan**: Urutkan data berdasarkan **Nama A-Z (bawaan)**, **Nama Z-A**, atau **NIS**.
- **Kotak Pencarian**: Ketikkan sebagian nama atau nomor induk, lalu klik tombol **Cari**.
- Klik tombol **Reset** untuk menghapus seluruh saringan dan menampilkan kembali semua data.

### Menggunakan Aplikasi di Ponsel (HP)
Seluruh halaman administrator dirancang responsif. Khusus tabel master data (Siswa, Guru, Kelas, Jadwal), saat dibuka melalui layar ponsel pintar, tabel secara otomatis bertransformasi menjadi susunan kartu vertikal tanpa perlu menggeser layar ke kanan dan ke kiri. Tombol aksi dan kotak centang tetap berukuran besar dan nyaman disentuh. Untuk pekerjaan administrasi berkas besar (seperti impor Excel atau pergantian periode), sangat disarankan menggunakan komputer atau laptop agar lebih leluasa.

---

## 4. Laporan Absensi

Laporan absensi digunakan untuk merekap kehadiran siswa dalam jangka waktu tertentu guna keperluan rapat dewan guru, laporan wali kelas, atau arsip kurikulum.

### Cara Menyaring dan Mengunduh Laporan
1. Klik menu **Laporan** pada bilah samping.
2. Tentukan saringan data sesuai kebutuhan Anda:
   - **Jurusan (Opsional)**: Pilih jurusan tertentu atau biarkan `-- Semua Jurusan --`.
   - **Kelas (Opsional)**: Pilih kelas tertentu atau biarkan `-- Semua Kelas --`.
   - **Mata Pelajaran (Opsional)**: Pilih mapel tertentu atau biarkan `-- Semua Mata Pelajaran --`.
   - **Bulan**: Pilih bulan tertentu jika ingin merekap bulanan (contoh: `2026-10`). Kosongkan jika ingin merekap satu semester penuh.
   - **Tahun Ajaran** dan **Semester**: Pilih periode yang ingin direkap (otomatis terisi periode aktif).
3. Klik salah satu tombol ekspor:
   - Tombol hijau **Ekspor Excel**: Mengunduh berkas lembar kerja spreadsheet (.xlsx) di mana setiap kelas otomatis terpisah dalam lembar kerja (sheet) tersendiri.
   - Tombol merah **Ekspor PDF**: Mengunduh dokumen resmi siap cetak (.pdf) lengkap dengan tanda tangan pengesahan sekolah.

[GAMBAR: Halaman penyaringan dan tombol ekspor Laporan Absensi]

### Batas Ekspor dan Pesan Peringatan
Demi menjaga kinerja server sekolah agar tidak berhenti bekerja akibat beban memori berlebih, sistem memberlakukan batas keamanan ekspor:
1. **Batas Ekspor Excel:** Maksimal mencakup **50 sheet (kelas)** dalam sekali unduh. Jika pilihan Anda melebihi 50 kelas, sistem menolak proses dan memunculkan pesan galat persis:
   > *"Ekspor mencakup [jumlah] sheet, melebihi batas 50. Silakan pilih jurusan atau kelas tertentu."*
2. **Batas Ekspor PDF:** Maksimal mencakup **2000 baris rekapan** dalam sekali cetak. Jika pilihan Anda melebihi 2000 baris, sistem memunculkan pesan galat persis:
   > *"Ekspor mencakup [jumlah] baris, melebihi batas 2000. Silakan pilih jurusan atau kelas tertentu."*

**Solusi jika terkena batas:** Jangan mengunduh seluruh sekolah sekaligus. Pilih salah satu **Jurusan** atau tentukan **Kelas** dan **Bulan** tertentu, lalu unduh secara bertahap per jurusan atau per tingkat.

### Rumus dan Arti Persentase Kehadiran
Aplikasi MyAbsen menghitung persentase kehadiran dengan aturan baku:
$$\text{Persentase Kehadiran} = \left( \frac{\text{Hadir} + \text{Izin} + \text{Sakit}}{\text{Total Pertemuan yang Diabsen}} \right) \times 100\%$$

- Status **Hadir**, **Izin**, dan **Sakit** dinilai sebagai kehadiran yang sah/positif dan dihitung sebagai hadir dalam persentase.
- Status **Alpa** adalah satu-satunya status yang dinilai sebagai ketidakhadiran tanpa izin dan menurunkan nilai persentase siswa.
- Jam pelajaran yang **Belum diabsen** oleh guru tidak dihitung sebagai Alpa dan sama sekali tidak mengurangi nilai persentase siswa.

---

## 5. Koreksi Absensi

### Kapan Menu Ini Digunakan?
Menu **Koreksi Absensi** digunakan saat terdapat kesalahan pencatatan status oleh guru yang baru disadari di kemudian hari, atau saat guru mata pelajaran berhalangan hadir sehingga absensi pada hari lampau perlu diisikan oleh staf tata usaha/guru piket.

### Perbedaan Wewenang Koreksi: Guru vs Administrator
- **Guru Pengajar:** Hanya memiliki jendela waktu koreksi maksimal **7 hari terakhir** (hari ini sampai 6 hari ke belakang yang harinya cocok dengan jadwal). Jadwal yang telah lewat lebih dari 7 hari otomatis terkunci bagi guru.
- **Administrator:** Memiliki wewenang **bebas tanpa batas waktu** untuk meninjau dan mengoreksi data absensi pada tanggal lampau mana pun di masa lalu sepanjang tahun ajaran berjalan.

### Langkah-langkah Melakukan Koreksi Absensi
1. Klik menu **Koreksi Absensi** pada bilah samping.
2. Pada isian **Tanggal Sesi Absensi**, tentukan tanggal historis yang ingin Anda periksa (sistem membatasi maksimal tanggal hari ini; tidak dapat memilih tanggal di masa depan).
3. Pada isian **Pilih Kelas (Opsional)**, pilih kelas yang ingin ditinjau atau pilih `Semua Kelas`.
4. Klik tombol biru **Tampilkan Jadwal**.
5. Sistem menampilkan daftar jadwal pelajaran yang terjadwal pada hari tersebut:
   - Jika sesi **sudah pernah diabsen**: Kartu menampilkan status hijau `Sudah diabsen`, informasi jam dan pembuat absensi, serta tombol hijau bertuliskan **Koreksi Absensi**.
   - Jika sesi **belum pernah diabsen**: Kartu menampilkan lencana kuning `Belum diabsen` dan tombol biru bertuliskan **Isi Absensi**.
6. Klik tombol **Koreksi Absensi** atau **Isi Absensi** tersebut untuk membuka lembar absensi kelas.
7. Sesuaikan status siswa (pilih Hadir, Izin, Sakit, atau Alpa) dan lengkapi catatan keterangan jika ada.
8. Klik tombol **Simpan Koreksi** (atau **Simpan Absensi**) pada bilah yang melekat di bawah layar. Perubahan akan langsung tersimpan ke riwayat siswa.

[GAMBAR: Halaman Koreksi Absensi dengan filter tanggal dan daftar kartu jadwal]

---

## 6. Data Terhapus (Arsip dan Pemulihan)

Aplikasi MyAbsen menyimpan data yang pernah dihapus tetapi memiliki keterkaitan riwayat di menu arsip data.

### Cara Memulihkan Data
1. Klik menu **Data Terhapus** pada bilah samping.
2. Pilih salah satu tab: **Guru**, **Siswa**, **Kelas**, atau **Mata Pelajaran**.
3. Cari nama atau nomor induk data yang ingin dipulihkan lewat kotak pencarian.
4. Lakukan pemulihan:
   - Satu data: Klik tombol **Pulihkan** pada baris data terkait.
   - Banyak data sekaligus: Centang baris-baris data, lalu pada bilah melayang di bawah klik tombol biru **Pulihkan yang dipilih**, kemudian klik konfirmasi **Pulihkan**.

### Alasan Penolakan Pemulihan dan Solusinya
Jika pemulihan ditolak oleh sistem, periksa pesan galat yang muncul dan lakukan solusi berikut:

1. **Siswa ditolak pulih karena kelas induknya masih terhapus:**
   - Pesan galat: `"Kelas [nama_kelas] masih terhapus. Pulihkan kelas tersebut lebih dulu."`
   - **Solusi:** Buka tab **Kelas** di menu Data Terhapus, cari dan pulihkan kelas yang bersangkutan terlebih dahulu, baru kembali ke tab **Siswa** untuk memulihkan siswanya.
2. **Nomor induk siswa (NIS) bentrok dengan data aktif:**
   - Pesan galat: `"NIS [nomor] sudah digunakan oleh siswa aktif lain."`
   - **Solusi:** Periksa menu **Siswa** aktif. Pastikan tidak ada siswa baru yang tidak sengaja didaftarkan dengan NIS yang sama. Perbaiki NIS siswa aktif terlebih dahulu, lalu pulihkan siswa lama dari arsip.
3. **Nomor induk pegawai (NIP) guru bentrok:**
   - Pesan galat: `"NIP [nomor] sudah digunakan oleh guru aktif lain."`
   - **Solusi:** Periksa data guru aktif di menu **Guru**, sesuaikan NIP yang bentrok, lalu pulihkan guru dari arsip.
4. **Kode mata pelajaran bentrok:**
   - Pesan galat: `"Kode [kode] sudah digunakan oleh mata pelajaran aktif lain."`
   - **Solusi:** Pastikan tidak ada mata pelajaran aktif yang memakai kode yang sama.
5. **Kelas bentrok pada periode yang sama:**
   - Pesan galat: `"Kelas [nama] untuk periode [tahun] semester [semester] sudah ada dan aktif."`
   - **Solusi:** Kelas dengan nama dan periode tersebut sudah terdaftar di menu Kelas aktif.
6. **Jurusan induk kelas sudah tidak ada:**
   - Pesan galat: `"Jurusan untuk kelas ini sudah tidak ada."`
   - **Solusi:** Tambahkan kembali jurusan yang sesuai pada menu **Jurusan**, baru pulihkan kelas tersebut.

[GAMBAR: Halaman Data Terhapus dan modal konfirmasi pemulihan massal]

---

## 7. Panduan Pergantian Semester dan Tahun Ajaran

Pergantian periode akademik adalah prosedur paling krusial bagi administrator. Ikuti daftar periksa berurutan berikut tanpa melompati langkah agar data sekolah tetap rapi dan tidak terjadi gangguan pada akun siswa maupun guru:

```
[Langkah 1: Selesaikan Koreksi Periode Lama]
                     │
                     ▼
[Langkah 2: Tab 1. Salin Struktur Kelas]
                     │
                     ▼
[Langkah 3: Menu Jadwal - Buat Jadwal Periode Baru]
                     │
                     ▼
[Langkah 4: Tab 2. Pindahkan Siswa ke Kelas Baru]
                     │
                     ▼ ⚠️ SEGERA DI HARI YANG SAMA!
[Langkah 5: Menu Pengaturan Periode - Ganti Periode Aktif]
                     │
                     ▼
[Langkah 6: Tab 3. Luluskan Siswa Kelas Akhir]
                     │
                     ▼
[Langkah 7: Verifikasi Dashboard & Laporan Lama]
```

### Kalender Waktu yang Disarankan:
- **Semester Ganjil:** Berjalan antara bulan **Juli sampai Desember**.
- **Semester Genap:** Berjalan antara bulan **Januari sampai Juni**.

---

### DAFTAR PERIKSA LANGKAH KERJA BERURUTAN:

#### Langkah 1: Selesaikan Semua Koreksi Absensi Periode Lama
> **PERINGATAN TEBAL & MUTLAK:**
> Pastikan seluruh guru telah menyelesaikan input absensi dan Anda telah menuntaskan seluruh koreksi absensi pada semester lama sebelum memulai mutasi siswa. Begitu siswa dipindahkan ke kelas baru, rombongan belajar kelas lama telah berganti. Jangan memindahkan siswa jika absensi semester lama masih ada yang tertinggal!

#### Langkah 2: Menyalin Struktur Kelas ke Periode Baru
1. Buka menu **Pergantian Periode** pada bilah samping.
2. Klik tab **1. Salin Kelas**.
3. Pada formulir saringan:
   - **Pilih Periode Asal (Sumber Kelas)**: Pilih periode yang menjadi sumber susunan kelas (misalnya semester lalu).
   - **Tahun Ajaran Tujuan**: Pilih tahun ajaran baru (contoh: `2027/2028`).
   - **Semester Tujuan**: Pilih semester baru (`Ganjil` atau `Genap`).
4. Centang kelas-kelas yang ingin Anda gunakan kembali pada periode baru (atau centang kotak di kepala tabel untuk memilih semua kelas).
5. Klik tombol biru **Salin Kelas Terpilih**.
6. Pada jendela konfirmasi, periksa rincian kelas lalu klik tombol **Ya, Salin Kelas**.
7. *Catatan:* Tindakan ini menyalin nama kelas, tingkat, dan jurusan ke periode tujuan. Data jadwal pelajaran dan daftar siswa tidak ikut disalin. Kelas yang sudah ada di periode tujuan akan otomatis dilewati sehingga tidak akan terjadi duplikasi.

#### Langkah 3: Membuat Jadwal Pelajaran untuk Periode Baru
1. Buka menu **Jadwal** pada bilah samping.
2. Klik tombol **Tambah Jadwal**.
3. Susun jadwal belajar mengajar untuk kelas-kelas yang baru saja disalin pada Langkah 2.

#### Langkah 4: Memindahkan Siswa ke Kelas Baru (Kenaikan Kelas / Mutasi)
1. Buka kembali menu **Pergantian Periode**, lalu klik tab **2. Pindahkan Siswa**.
2. Pada bagian atas:
   - **Pilih Kelas Asal**: Pilih kelas lama siswa saat ini (contoh: kelas `X TKRO 1` tahun lalu).
   - **Pilih Kelas Tujuan**: Pilih kelas baru tempat siswa naik kelas (contoh: kelas `XI TKRO 1` periode baru).
3. Centang siswa yang naik kelas ke rombongan belajar tersebut. Anda dapat menggunakan kotak pencarian nama/NIS atau centang semua siswa sekaligus.
4. Klik tombol biru **Pindahkan Siswa Terpilih**.
5. Pada jendela konfirmasi, klik tombol **Ya, Pindahkan Siswa**.
6. Ulangi langkah ini untuk seluruh rombongan belajar yang naik kelas.

> **PERINGATAN SANGAT PENTING (JANGAN DITUNDA):**
> Setelah seluruh siswa selesai dipindahkan ke kelas baru, **ANDA HARUS SEGERA MELANJUTKAN KE LANGKAH 5 DAN MENGGANTI PERIODE AKTIF PADA HARI YANG SAMA!**
> Jika Anda menunda pergantian periode aktif ke hari berikutnya, dashboard siswa akan menjadi kosong dan siswa tidak dapat melihat jadwal pelajarannya karena data kelas siswa sudah berpindah ke periode baru sementara sistem sekolah masih menganggap periode lama yang berjalan.

#### Langkah 5: Mengganti Periode Aktif Sekolah
1. Buka menu **Pengaturan Periode** pada bilah samping.
2. Pada formulir **Ubah Periode Akademik Aktif**:
   - Pilih **Tahun Ajaran** yang baru.
   - Pilih **Semester** yang baru.
   - Anda juga dapat mengeklik tombol **Gunakan Saran Sistem** jika kalender tanggal saat ini sudah cocok dengan semester baru.
3. Klik tombol biru **Simpan Perubahan Periode**.
4. Pada jendela konfirmasi, klik tombol **Ya, Ubah Periode Aktif**.
5. Sekarang, jadwal baru dan absensi harian sekolah secara resmi berjalan pada periode baru.

#### Langkah 6: Meluluskan Siswa Kelas Akhir
1. Buka kembali menu **Pergantian Periode**, lalu klik tab **3. Luluskan Siswa**.
2. Pada isian **Pilih Kelas Akhir**, pilih kelas tingkat 12 yang telah menyelesaikan masa studi.
3. Centang siswa yang dinyatakan tamat/lulus.
4. Klik tombol merah **Luluskan Siswa Terpilih**.
5. Pada jendela konfirmasi, perhatikan peringatan sistem:
   - Akun siswa yang diluluskan otomatis dinonaktifkan sehingga tidak dapat masuk lagi.
   - Nama siswa tetap tersimpan utuh di riwayat absensi lama sekolah dengan penanda `(nonaktif)`.
   - Data siswa dapat dipulihkan sewaktu-waktu di menu Data Terhapus jika ada kekeliruan penetapan kelulusan.
6. Klik tombol **Ya, Luluskan Siswa**.

#### Langkah 7: Memeriksa Dashboard dan Laporan Lama
1. Buka menu **Dashboard** untuk memastikan jadwal dan statistik kelas periode baru sudah muncul dengan benar.
2. Buka menu **Laporan**, pilih tahun ajaran dan semester lama pada dropdown periode, lalu pastikan laporan kehadiran semester lama tetap dapat diunduh utuh tanpa ada data yang hilang.

[GAMBAR: Alur navigasi tab pada pusat Pergantian Periode]

---

## 8. Tabel Masalah Umum (Gejala, Penyebab, dan Solusi)

Gunakan tabel pemecahan masalah berikut jika menemukan kendala operasional:

| Gejala Masalah | Penyebab Utama | Langkah Solusi Tepat |
|---|---|---|
| **Guru / Siswa lupa password dan tidak bisa login** | Pengguna lupa kata sandi baru yang pernah dibuatnya. | Buka menu **Guru** atau **Siswa**, cari akun terkait, lalu klik tombol **Reset Password**. Kata sandi akan kembali ke standar (`password`). Pengguna diminta menggantinya saat masuk kembali. |
| **Muncul pesan: *"Akun Anda telah dinonaktifkan. Hubungi admin sekolah."*** | Akun pengguna berada dalam status terhapus atau pernah diluluskan. | Buka menu **Data Terhapus**, pilih tab yang sesuai (Guru atau Siswa), cari nama/nomor induknya, lalu klik tombol **Pulihkan**. |
| **Dashboard guru atau siswa kosong (tidak ada jadwal)** | Periode tahun ajaran dan semester pada jadwal/kelas tidak cocok dengan periode aktif. | Buka menu **Pengaturan Periode** untuk memeriksa periode aktif. Pastikan jadwal mengajar dan kelas siswa terdaftar pada tahun ajaran dan semester yang persis sama dengan periode aktif tersebut. |
| **Layar Galat 419: *"Sesi Telah Kedaluwarsa"*** | Formulir dibiarkan terbuka terlalu lama tanpa aktivitas sehingga sesi peramban habis. | Klik tombol **Kembali ke Halaman Login**, masukkan kembali nama pengguna dan password Anda, lalu ulangi tindakan yang tertunda. |
| **Layar Galat 403: *"Akses Ditolak"*** | Pengguna mencoba membuka halaman yang bukan hak akses perannya (misal guru membuka halaman admin). | Pastikan Anda masuk menggunakan akun dengan peran yang sesuai. Klik tombol kembali ke dashboard. |
| **Layar Galat 503: *"Sistem Sedang Dalam Pemeliharaan"*** | Pengelola teknis sedang melakukan perawatan server atau pembaruan kode aplikasi. | Tunggu beberapa menit hingga proses pemeliharaan selesai, kemudian klik tombol **Muat Ulang**. |
| **Data kelas/mapel tidak bisa dihapus** | Muncul pesan galat merah: *"Kelas/Mapel tidak dapat dihapus karena masih memiliki jadwal aktif / siswa aktif."* | Selesaikan atau hapus data turunannya terlebih dahulu (pindahkan siswa dari kelas tersebut, atau hapus jadwalnya terlebih dahulu di menu Jadwal). |
| **Data guru/jadwal tidak bisa dihapus** | Muncul pesan galat merah: *"Akun/Jadwal tidak dapat dihapus karena sudah memiliki riwayat absensi."* | Demi integritas dokumen sekolah, jadwal atau guru yang sudah pernah dipakai absensi tidak boleh dihapus. Cukup nonaktifkan guru melalui tombol Hapus (otomatis masuk ke arsip Data Terhapus). |
| **Impor siswa Excel gagal** | Muncul pesan galat: *"Gagal impor: [daftar baris]"* atau *"Terjadi kesalahan saat mengimpor data."* | Pastikan berkas berformat .xlsx atau .csv, baris pertama memuat kolom `nis` dan `name` (huruf kecil), tidak ada NIS yang kosong atau ganda, dan NIS belum pernah dipakai oleh siswa lain. |
| **Ekspor Laporan melebihi batas sheet / baris** | Muncul pesan galat: *"Ekspor mencakup X sheet, melebihi batas 50"* atau *"melebihi batas 2000 baris"*. | Saring laporan lebih spesifik. Pilih satu **Jurusan** tertentu atau tentukan **Kelas** dan **Bulan** tertentu sebelum mengeklik tombol Ekspor Excel atau PDF. |
| **Persentase kehadiran di laporan tampak janggal** | Kesalahpahaman rumus kehadiran. | Jelaskan bahwa persentase dihitung dari **pertemuan yang sudah diabsen**, bukan dari seluruh hari di kalender. Status Hadir, Izin, dan Sakit dinilai hadir (positif); hanya Alpa yang mengurangi persentase. Pertemuan yang belum diabsen tidak mengurangi nilai siswa. |
| **Tampilan aplikasi tampak berantakan atau tombol macet setelah pembaruan** | Peramban di komputer/ponsel masih menyimpan salinan berkas tampilan versi lama (cache browser). | Tekan tombol perpaduan `Ctrl + F5` pada keyboard komputer untuk memuat ulang halaman secara menyeluruh, atau bersihkan riwayat peramban di ponsel Anda. |

---

## 9. Batasan Sistem, Keamanan, dan Kontak Bantuan

### Batasan yang Diketahui (Catatan Serah Terima Sistem):
1. **Tidak Ada Hapus Permanen di Menu Data Terhapus:** Menu Data Terhapus sengaja dirancang tanpa tombol hapus permanen. Hal ini bertujuan melindungi sekolah dari kehilangan data arsip kehadiran siswa secara tidak sengaja di kemudian hari.
2. **Riwayat Pindah Kelas Tanpa Catatan Tanggal:** Sistem mencatat rombongan belajar siswa pada kelasnya saat ini. Sistem tidak menyimpan tanggal pasti kapan seorang siswa dimutasikan dari satu kelas ke kelas lain di tengah semester berjalan.
3. **Penyusunan Jadwal Dilakukan Manual per Periode:** Jadwal pembelajaran tidak disalin otomatis saat kelas disalin ke semester baru. Administrator perlu menyusun jadwal baru di menu Jadwal agar pembagian jam mengajar guru dapat disesuaikan dengan kalender akademik terbaru.
4. **Pembaruan Komponen Utama Sebelum 24 Februari 2027:** Kerangka kerja dasar aplikasi ini (Laravel 12) menerima patch keamanan resmi dari pengembang pusat hingga tanggal 24 Februari 2027. Sebelum tanggal tersebut berakhir, pihak sekolah perlu menjadwalkan pembaruan versi oleh pengembang atau orang teknis yang berwenang.

### Praktik Keamanan yang Wajib Dipatuhi:
- Jangan pernah membagikan nama pengguna dan kata sandi akun administrator kepada guru, siswa, atau pihak luar sekolah.
- Selalu klik tombol **Keluar** pada bilah sisi kiri saat selesai bertugas, terutama bila menggunakan komputer bersama di ruang tata usaha atau laboratorium sekolah.

### Informasi Kontak dan Layanan Dukungan:
- **Alamat Web Aplikasi:** `[ISI: Alamat web resmi MyAbsen sekolah]`
- **Jadwal dan Cara Cadangan Basis Data:** `[ISI: Jadwal dan prosedur pencadangan database berkala sekolah]`
- **Kontak Layanan Bantuan Teknis:** `[ISI: Nomor telepon, email, dan jam layanan bantuan teknis]`
- **Masa Dukungan Perbaikan Sistem:** `[ISI: Lama masa garansi dan dukungan perbaikan sistem]`

---

## 10. Lampiran

### Daftar Istilah Penting Sehari-hari

- **Administrator:** Petugas operator atau tata usaha sekolah yang berwenang mengelola seluruh pengaturan dan data master aplikasi.
- **Guru Pengajar:** Guru yang memiliki jadwal pembelajaran di kelas dan bertanggung jawab mengisi lembar absensi siswa.
- **Siswa:** Peserta didik yang terdaftar pada suatu rombongan belajar kelas dan berhak melihat data kehadirannya sendiri.
- **Tahun Ajaran:** Rentang satu tahun kalender pendidikan sekolah, misalnya tahun ajaran `2026/2027`.
- **Semester:** Pembagian waktu belajar dalam satu tahun ajaran, yaitu semester `Ganjil` (Juli–Desember) dan semester `Genap` (Januari–Juni).
- **Periode Aktif:** Kombinasi tahun ajaran dan semester yang saat ini sedang berlangsung di sekolah dan menjadi acuan utama jam pelajaran harian.
- **Sesi Absensi:** Catatan pengisian kehadiran pada suatu jadwal mata pelajaran di tanggal tertentu. Sesi absensi unik per jadwal dan per tanggal.
- **Belum Diabsen:** Kondisi di mana jadwal mata pelajaran pada hari ini belum diisi oleh guru pengampu. Keadaan ini **bukan Alpa** dan tidak menurunkan persentase kehadiran siswa.
- **Koreksi Absensi:** Tindakan mengubah status absensi yang telah tersimpan jika terdapat kekeliruan informasi di masa lalu.
- **Arsip Data (Data Terhapus):** Tempat penyimpanan data guru, siswa, kelas, atau mapel yang dinonaktifkan agar rekam jejak absensi masa lalu tetap aman dan dapat dipulihkan sewaktu-waktu.
- **Aksi Massal:** Fitur untuk mencentang banyak data sekaligus guna menyetel ulang password atau menghapus data secara cepat.

### Tabel Rangkuman Warna dan Lambang Status

| Status | Tampilan Warna | Makna di Buku Absensi | Dampak pada Persentase Kehadiran |
|---|---|---|---|
| **Hadir** | Hijau cerah | Siswa masuk mengikuti pelajaran di kelas | Dihitung Hadir (Positif) |
| **Izin** | Biru cerah | Siswa tidak hadir dengan surat izin / dispensasi | Dihitung Hadir (Positif) |
| **Sakit** | Kuning / Amber cerah | Siswa tidak hadir karena sakit dengan surat keterangan | Dihitung Hadir (Positif) |
| **Alpa** | Merah cerah | Siswa tidak hadir tanpa keterangan yang jelas | Mengurangi Persentase Kehadiran |
| **Belum diabsen** | Abu-abu garis putus-putus | Guru belum menginput absensi jam pelajaran ini | Tidak Mempengaruhi Persentase Siswa |
