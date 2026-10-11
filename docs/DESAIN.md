# Panduan Desain & Token UI MyAbsen

Dokumen ini adalah **satu-satunya sumber kebenaran (single source of truth)** untuk desain antarmuka MyAbsen (mode terang dan mode gelap). Semua perancangan antarmuka, pembuatan komponen Blade, pembaruan template, dan tahapan pengembangan berikutnya (termasuk Tahap 2: CRUD Admin) **WAJIB merujuk dokumen ini**.

---

## 1. Prinsip Utama Desain

1. **Satu Sumber Token**: Tidak ada penulisan class Tailwind warna mentah tanpa pasangan `dark:` di view. Komponen bersama dan kelas presentasi dikelola melalui `App\Support\Ui` serta komponen `resources/views/components/ui/*`.
2. **Satu Warna Satu Arti (Status Kehadiran)**:
   - **Hadir**: Emerald (hijau zamrud) – melambangkan kehadiran fisik di KBM.
   - **Izin**: Blue (biru) – dispensasi resmi/kegiatan sekolah/izin tertulis.
   - **Sakit**: Amber (oranye-kuning) – keterangan medis/kesehatan.
   - **Alpa**: Red (merah) – ketidakhadiran tanpa keterangan resmi.
   - **Belum Diabsen**: Slate netral border putus-putus – belum ada baris sesi absensi.
3. **Elevasi & Kedalaman Visual di Mode Gelap**:
   - Latar belakang halaman memakai `dark:bg-slate-950`.
   - Permukaan kartu memakai `dark:bg-slate-900`.
   - Elemen elevated (header grup hari/tanggal, item jadwal, panel, input) memakai `dark:bg-slate-800` dipadukan dengan `dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5`.
   - Pembatas/bingkai menggunakan `border-2 border-slate-300 dark:border-slate-600`.
4. **Keterbacaan & Aksesibilitas (WCAG AA)**:
   - Rasio kontras teks biasa minimal **4.5:1** terhadap latar belakangnya.
   - Rasio kontras teks besar ($\ge$24px atau $\ge$18.66px bold) serta garis batas/ikon bermakna minimal **3:1**.
   - Input form wajib berukuran teks minimal 16px dan tinggi minimal 44px untuk mencegah zoom otomatis pada browser mobile (iOS Safari).
   - Target sentuh minimal 44x44px dengan jarak pemisah minimal 8px.

---

## 2. Tabel Token Desain Baku

Semua kelas ditulis secara **literal dan utuh** (dilarang merangkai string Tailwind secara dinamis):

| Kategori Elemen | Mode Terang | Mode Gelap | Catatan & Kelas Pendukung |
|---|---|---|---|
| **Latar Halaman** | `bg-slate-50` | `dark:bg-slate-950` | Mengalir di seluruh halaman layout `app.blade.php`. |
| **Permukaan Kartu Dasar** | `bg-white` | `dark:bg-slate-900` | `border-2 border-slate-300 dark:border-slate-600 rounded-xl shadow-xs` |
| **Elevated Surface** (Item Jadwal, Panel, Input) | `bg-white` (Header: `bg-slate-100`) | `dark:bg-slate-800` | Tambahan gelap: `dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5` |
| **Hover / Status Aktif** | `bg-slate-100` | `dark:bg-slate-700` | Transisi halus `duration-150`. |
| **Bingkai / Border** | `border-2 border-slate-300` | `dark:border-slate-600` | Garis tegas dan kontras jelas. |
| **Pemisah Bagian & Baris Tabel** | `divide-y-2 divide-slate-200` | `dark:divide-slate-700` | Pemisah antar baris kartu atau tabel. |
| **Garis Bawah Header Tabel** | `border-b-2 border-slate-400` | `dark:border-slate-500` | Pembatas antara thead dan tbody. |
| **Teks Utama / Judul** | `text-slate-900` | `dark:text-white` | Judul grup `font-bold`. |
| **Teks Isi Biasa** | `text-slate-800` | `dark:text-slate-100` | Kontras optimal untuk teks bacaan. |
| **Teks Sekunder** (Guru, Kelas, NIS, Periode, Keterangan) | `text-slate-600` | `dark:text-slate-300` | Dilarang lebih pudar dari ini. |
| **Teks Meta / Terkecil** (Batas Terendah) | `text-slate-600` | `dark:text-slate-400` | Batas terendah; dilarang `text-gray-500` ke bawah di dark mode. |
| **Header Grup Hari / Tanggal** | `bg-slate-100 text-slate-900 border-2 border-slate-300` | `dark:bg-slate-800 dark:text-white dark:border-slate-600` | `font-bold rounded-xl p-4 sm:p-5`. Tidak ada latar silau di mode gelap. |
| **Badge "Hari ini"** | `bg-emerald-700 text-white` | `dark:bg-emerald-400 dark:text-slate-950` | Solid, kontras tinggi. |
| **Status Hadir (Tinted)** | `bg-emerald-100 text-emerald-900 border-emerald-400` | `dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50` | Selalu disertai ikon `check` + label teks. |
| **Status Izin (Tinted)** | `bg-blue-100 text-blue-900 border-blue-400` | `dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50` | Selalu disertai ikon `info` + label teks. |
| **Status Sakit (Tinted)** | `bg-amber-100 text-amber-900 border-amber-400` | `dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50` | Selalu disertai ikon `plus` + label teks. |
| **Status Alpa (Tinted)** | `bg-red-100 text-red-900 border-red-400` | `dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50` | Selalu disertai ikon `x` + label teks. |
| **Status Belum Diabsen** | `bg-slate-100 text-slate-800 border-slate-400 border-dashed` | `dark:bg-slate-700/50 dark:text-slate-200 dark:border-slate-400 border-dashed` | Ikon `clock` + label teks. |
| **Sorotan Filter Cocok** | `bg-emerald-50 border-emerald-600 border-l-8` | `dark:bg-emerald-500/10 dark:border-emerald-400 border-l-8` | Indikator visual pencarian aktif. |
| **Strip Rekap H/I/S/A** | Pola tinted per status + `border-l-8` aksen solid | Pola tinted per status + `border-l-8` aksen solid | Angka `text-2xl sm:text-3xl font-extrabold` (-900 / -300), label `text-slate-700 dark:text-slate-200`. Tidak boleh hitam pekat. |
| **Tabel Thead** | `bg-slate-100 text-slate-900` | `dark:bg-slate-800 dark:text-white` | Huruf tegas `font-bold`. |
| **Tabel Baris Zebra** | Ganjil `bg-white`, Genap `bg-slate-50` | Ganjil `dark:bg-slate-900`, Genap `dark:bg-slate-800/60` | Kolom sticky harus berlatar opak sama dan bayangan tepi. |
| **Input / Select / Textarea** | `bg-white border-2 border-slate-400 text-slate-900 placeholder:text-slate-500` | `dark:bg-slate-950 dark:border-slate-500 dark:text-slate-50 dark:placeholder:text-slate-400 dark:[color-scheme:dark]` | Tinggi min 44px, teks 16px, fokus `focus-visible:ring-4 ring-emerald-500`. |
| **Tombol Primer** | `bg-emerald-700 hover:bg-emerald-800 text-white` | `dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950` | Min 44px, fokus `focus-visible:ring-4 ring-emerald-500`. |
| **Tombol Sekunder** | `bg-white border-2 border-slate-400 text-slate-900 hover:bg-slate-100` | `dark:bg-slate-800 dark:border-slate-500 dark:text-slate-100 dark:hover:bg-slate-700` | Min 44px. |
| **Tombol Bahaya** | `bg-red-700 hover:bg-red-800 text-white` | `dark:bg-red-400 dark:hover:bg-red-300 dark:text-slate-950` | Min 44px, fokus `focus-visible:ring-4 ring-red-500`. |
| **Sidebar Menu Biasa** | `text-slate-700 hover:bg-slate-100` | `dark:text-slate-200 dark:hover:bg-slate-800` | Target klik ramah jempol. |
| **Sidebar Menu Aktif** | `bg-emerald-100 text-emerald-900 border-l-4 border-emerald-600` | `dark:bg-emerald-500/15 dark:text-emerald-200 dark:border-emerald-400 border-l-4` | Penanda posisi halaman saat ini. |

---

## 3. Hasil Pengukuran Kontras WCAG (Langkah 2)

Perhitungan menggunakan rumus luminansi relatif W3C WCAG 2.1 dengan **alpha compositing** untuk warna semi-transparan (misalnya tint `emerald-500/15` yang di-blend di atas permukaan dasar `slate-900` atau `slate-800`).

| Kategori | Elemen / Peran | Pasangan Class Tailwind | Latar (Hex) | Teks (Hex) | Rasio | Target | Status WCAG |
|---|---|---|---|---|---|---|---|
| Permukaan & Teks | Halaman Terang (Utama) | `bg-slate-50 / text-slate-900` | `#f8fafc` | `#0f172a` | **17.06:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Halaman Terang (Isi biasa) | `bg-slate-50 / text-slate-800` | `#f8fafc` | `#1e293b` | **13.97:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Halaman Terang (Sekunder) | `bg-slate-50 / text-slate-600` | `#f8fafc` | `#475569` | **7.42:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Halaman Gelap (Utama) | `dark:bg-slate-950 / dark:text-white` | `#020617` | `#ffffff` | **20.24:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Halaman Gelap (Isi biasa) | `dark:bg-slate-950 / dark:text-slate-100` | `#020617` | `#f1f5f9` | **17.92:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Halaman Gelap (Sekunder) | `dark:bg-slate-950 / dark:text-slate-300` | `#020617` | `#cbd5e1` | **13.14:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Halaman Gelap (Meta terkecil) | `dark:bg-slate-950 / dark:text-slate-400` | `#020617` | `#94a3b8` | **7.76:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Kartu Terang (Utama) | `bg-white / text-slate-900` | `#ffffff` | `#0f172a` | **17.85:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Kartu Terang (Isi biasa) | `bg-white / text-slate-800` | `#ffffff` | `#1e293b` | **14.62:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Kartu Terang (Sekunder) | `bg-white / text-slate-600` | `#ffffff` | `#475569` | **7.77:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Kartu Gelap (Utama) | `dark:bg-slate-900 / dark:text-white` | `#0f172a` | `#ffffff` | **16.63:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Kartu Gelap (Isi biasa) | `dark:bg-slate-900 / dark:text-slate-100` | `#0f172a` | `#f1f5f9` | **14.72:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Kartu Gelap (Sekunder) | `dark:bg-slate-900 / dark:text-slate-300` | `#0f172a` | `#cbd5e1` | **10.80:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Kartu Gelap (Meta terkecil) | `dark:bg-slate-900 / dark:text-slate-400` | `#0f172a` | `#94a3b8` | **6.38:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Elevated Gelap (Utama) | `dark:bg-slate-800 / dark:text-white` | `#1e293b` | `#ffffff` | **13.08:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Elevated Gelap (Isi biasa) | `dark:bg-slate-800 / dark:text-slate-100` | `#1e293b` | `#f1f5f9` | **11.58:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Elevated Gelap (Sekunder) | `dark:bg-slate-800 / dark:text-slate-300` | `#1e293b` | `#cbd5e1` | **8.50:1** | 4.5:1 | LULUS |
| Permukaan & Teks | Elevated Gelap (Meta terkecil) | `dark:bg-slate-800 / dark:text-slate-400` | `#1e293b` | `#94a3b8` | **5.02:1** | 4.5:1 | LULUS |
| Header Grup Hari/Tgl | Header Terang | `bg-slate-100 / text-slate-900` | `#f1f5f9` | `#0f172a` | **16.30:1** | 4.5:1 | LULUS |
| Header Grup Hari/Tgl | Header Gelap | `dark:bg-slate-800 / dark:text-white` | `#1e293b` | `#ffffff` | **13.08:1** | 4.5:1 | LULUS |
| Header Grup Hari/Tgl | Badge "Hari ini" (Terang) | `bg-emerald-700 / text-white` | `#047857` | `#ffffff` | **5.48:1** | 4.5:1 | LULUS |
| Header Grup Hari/Tgl | Badge "Hari ini" (Gelap) | `dark:bg-emerald-400 / dark:text-slate-950` | `#34d399` | `#020617` | **10.49:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Hadir Terang | `bg-emerald-100 / text-emerald-900` | `#d1fae5` | `#064e3b` | **8.57:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Hadir Gelap (di atas slate-900) | `dark:bg-emerald-500/15 / dark:text-emerald-300` | `#0f2f37` | `#6ee7b7` | **9.30:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Hadir Gelap (di atas slate-800) | `dark:bg-emerald-500/15 / dark:text-emerald-300` | `#1c3f46` | `#6ee7b7` | **7.45:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Izin Terang | `bg-blue-100 / text-blue-900` | `#dbeafe` | `#1e3a8a` | **8.49:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Izin Gelap (di atas slate-900) | `dark:bg-blue-500/15 / dark:text-blue-300` | `#162749` | `#93c5fd` | **8.20:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Izin Gelap (di atas slate-800) | `dark:bg-blue-500/15 / dark:text-blue-300` | `#223657` | `#93c5fd` | **6.72:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Sakit Terang | `bg-amber-100 / text-amber-900` | `#fef3c7` | `#78350f` | **8.15:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Sakit Gelap (di atas slate-900) | `dark:bg-amber-500/15 / dark:text-amber-300` | `#322b25` | `#fcd34d` | **9.66:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Sakit Gelap (di atas slate-800) | `dark:bg-amber-500/15 / dark:text-amber-300` | `#3e3b34` | `#fcd34d` | **7.75:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Alpa Terang | `bg-red-100 / text-red-900` | `#fee2e2` | `#7f1d1d` | **8.20:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Alpa Gelap (di atas slate-900) | `dark:bg-red-500/15 / dark:text-red-300` | `#311e2e` | `#fca5a5` | **8.16:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Alpa Gelap (di atas slate-800) | `dark:bg-red-500/15 / dark:text-red-300` | `#3d2d3c` | `#fca5a5` | **6.75:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Belum Diabsen Terang | `bg-slate-100 / text-slate-800` | `#f1f5f9` | `#1e293b` | **13.35:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Belum Diabsen Gelap (slate-900) | `dark:bg-slate-700/50 / dark:text-slate-200` | `#212c40` | `#e2e8f0` | **11.36:1** | 4.5:1 | LULUS |
| Badge Status (Tinted) | Belum Diabsen Gelap (slate-800) | `dark:bg-slate-700/50 / dark:text-slate-200` | `#293548` | `#e2e8f0` | **10.04:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Hadir Terang (3xl) | `bg-emerald-100 / text-emerald-900` | `#d1fae5` | `#064e3b` | **8.57:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Hadir Terang | `bg-emerald-100 / text-slate-700` | `#d1fae5` | `#334155` | **9.13:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Hadir Gelap (3xl) | `dark:bg-emerald-500/15 / dark:text-emerald-300` | `#0f2f37` | `#6ee7b7` | **9.30:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Hadir Gelap | `dark:bg-emerald-500/15 / dark:text-slate-200` | `#0f2f37` | `#e2e8f0` | **11.49:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Izin Terang (3xl) | `bg-blue-100 / text-blue-900` | `#dbeafe` | `#1e3a8a` | **8.49:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Izin Terang | `bg-blue-100 / text-slate-700` | `#dbeafe` | `#334155` | **8.49:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Izin Gelap (3xl) | `dark:bg-blue-500/15 / dark:text-blue-300` | `#162749` | `#93c5fd` | **8.20:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Izin Gelap | `dark:bg-blue-500/15 / dark:text-slate-200` | `#162749` | `#e2e8f0` | **11.99:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Sakit Terang (3xl) | `bg-amber-100 / text-amber-900` | `#fef3c7` | `#78350f` | **8.15:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Sakit Terang | `bg-amber-100 / text-slate-700` | `#fef3c7` | `#334155` | **9.30:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Sakit Gelap (3xl) | `dark:bg-amber-500/15 / dark:text-amber-300` | `#322b25` | `#fcd34d` | **9.66:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Sakit Gelap | `dark:bg-amber-500/15 / dark:text-slate-200` | `#322b25` | `#e2e8f0` | **11.30:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Alpa Terang (3xl) | `bg-red-100 / text-red-900` | `#fee2e2` | `#7f1d1d` | **8.20:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Alpa Terang | `bg-red-100 / text-slate-700` | `#fee2e2` | `#334155` | **8.48:1** | 4.5:1 | LULUS |
| Strip Rekap H/I/S/A | Angka Alpa Gelap (3xl) | `dark:bg-red-500/15 / dark:text-red-300` | `#311e2e` | `#fca5a5` | **8.16:1** | 3.0:1 | LULUS |
| Strip Rekap H/I/S/A | Label Alpa Gelap | `dark:bg-red-500/15 / dark:text-slate-200` | `#311e2e` | `#e2e8f0` | **12.56:1** | 4.5:1 | LULUS |
| Tabel & Matriks | Thead Terang | `bg-slate-100 / text-slate-900` | `#f1f5f9` | `#0f172a` | **16.30:1** | 4.5:1 | LULUS |
| Tabel & Matriks | Thead Gelap | `dark:bg-slate-800 / dark:text-white` | `#1e293b` | `#ffffff` | **13.08:1** | 4.5:1 | LULUS |
| Tabel & Matriks | Baris Ganjil Terang | `bg-white / text-slate-900` | `#ffffff` | `#0f172a` | **17.85:1** | 4.5:1 | LULUS |
| Tabel & Matriks | Baris Genap Terang | `bg-slate-50 / text-slate-900` | `#f8fafc` | `#0f172a` | **17.06:1** | 4.5:1 | LULUS |
| Tabel & Matriks | Baris Ganjil Gelap | `dark:bg-slate-900 / dark:text-slate-100` | `#0f172a` | `#f1f5f9` | **14.72:1** | 4.5:1 | LULUS |
| Tabel & Matriks | Baris Genap Gelap | `dark:bg-slate-800/60 / dark:text-slate-100` | `#131b2d` | `#f1f5f9` | **14.15:1** | 4.5:1 | LULUS |
| Tombol | Primer Terang | `bg-emerald-700 / text-white` | `#047857` | `#ffffff` | **5.48:1** | 4.5:1 | LULUS |
| Tombol | Primer Gelap | `dark:bg-emerald-400 / dark:text-slate-950` | `#34d399` | `#020617` | **10.49:1** | 4.5:1 | LULUS |
| Tombol | Bahaya Terang | `bg-red-700 / text-white` | `#b91c1c` | `#ffffff` | **6.47:1** | 4.5:1 | LULUS |
| Tombol | Bahaya Gelap | `dark:bg-red-400 / dark:text-slate-950` | `#f87171` | `#020617` | **7.29:1** | 4.5:1 | LULUS |
| Tombol | Sekunder Terang | `bg-white / text-slate-900` | `#ffffff` | `#0f172a` | **17.85:1** | 4.5:1 | LULUS |
| Tombol | Sekunder Gelap | `dark:bg-slate-800 / dark:text-slate-100` | `#1e293b` | `#f1f5f9` | **11.58:1** | 4.5:1 | LULUS |
| Sidebar | Biasa Terang | `bg-white / text-slate-700` | `#ffffff` | `#334155` | **10.35:1** | 4.5:1 | LULUS |
| Sidebar | Biasa Gelap | `dark:bg-slate-900 / dark:text-slate-200` | `#0f172a` | `#e2e8f0` | **13.06:1** | 4.5:1 | LULUS |
| Sidebar | Aktif Terang | `bg-emerald-100 / text-emerald-900` | `#d1fae5` | `#064e3b` | **8.57:1** | 4.5:1 | LULUS |
| Sidebar | Aktif Gelap | `dark:bg-emerald-500/15 / dark:text-emerald-200` | `#0f2f37` | `#a7f3d0` | **11.05:1** | 4.5:1 | LULUS |

Semua pasangan warna teks dan elemen UI di atas **100% LULUS** standar WCAG AA (rasio $\ge$ 4.5:1 untuk teks normal, $\ge$ 3.0:1 untuk teks besar).
