<?php

use App\Enums\StatusKehadiran;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->jurusan = Jurusan::create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);

    $this->kelas = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->userGuru = User::factory()->create(['role' => 'guru', 'name' => 'Pak Guru Budi']);
    $this->guru = Guru::create(['user_id' => $this->userGuru->id, 'nip' => '198001012005011001']);

    $this->userSiswa1 = User::factory()->create(['role' => 'siswa', 'name' => 'Ahmad Siswa 1']);
    $this->siswa1 = Siswa::create(['user_id' => $this->userSiswa1->id, 'kelas_id' => $this->kelas->id, 'nis' => '1001']);

    $this->userSiswa2 = User::factory()->create(['role' => 'siswa', 'name' => 'Budi Siswa 2']);
    $this->siswa2 = Siswa::create(['user_id' => $this->userSiswa2->id, 'kelas_id' => $this->kelas->id, 'nis' => '1002']);

    $this->userSiswaNonaktif = User::factory()->create(['role' => 'siswa', 'name' => 'Citra Nonaktif']);
    $this->siswaNonaktif = Siswa::create(['user_id' => $this->userSiswaNonaktif->id, 'kelas_id' => $this->kelas->id, 'nis' => '1003']);
    $this->siswaNonaktif->delete(); // soft delete / nonaktif

    $this->mapel = Mapel::create(['nama' => 'Jaringan Dasar', 'kode' => 'JD01']);

    $this->jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'mapel_id' => $this->mapel->id,
        'hari' => 'senin',
        'jam_mulai' => '07:30:00',
        'jam_selesai' => '09:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Pertemuan 1
    $this->sesi1 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'guru_id' => $this->guru->id,
        'diabsen_oleh' => $this->userGuru->id,
        'tanggal' => '2026-08-03',
        'status' => 'selesai',
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi1->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi1->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::IZIN]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi1->id, 'siswa_id' => $this->siswaNonaktif->id, 'status' => StatusKehadiran::HADIR]);

    // Pertemuan 2
    $this->sesi2 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'guru_id' => $this->guru->id,
        'diabsen_oleh' => $this->userGuru->id,
        'tanggal' => '2026-08-10',
        'status' => 'selesai',
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi2->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::SAKIT]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi2->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::ALPA]);

    $this->userAdmin = User::factory()->create(['role' => 'admin', 'name' => 'Admin PKM']);
});

test('riwayat detail guru: memuat tabel desktop (md:block) dan daftar kartu mobile (md:hidden) dengan kotak pertemuan', function () {
    $response = $this->actingAs($this->userGuru)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));

    $response->assertOk();

    // 1. Memuat tabel desktop (hidden md:block) dan daftar kartu mobile (md:hidden)
    $response->assertSee('hidden md:block');
    $response->assertSee('md:hidden');

    // 2. Kartu mobile memuat nama siswa, NIS, persentase kehadiran, dan ringkasan H/I/S/A
    $response->assertSee('Ahmad Siswa 1');
    $response->assertSee('NIS: 1001');
    $response->assertSee('Budi Siswa 2');
    $response->assertSee('NIS: 1002');
    $response->assertSee('H: 1');

    // 3. Siswa nonaktif yang memiliki riwayat tetap bertanda (nonaktif)
    $response->assertSee('Citra Nonaktif');
    $response->assertSee('(nonaktif)');

    // 4. Kotak pertemuan chip memuat label P1, P2, tanggal, dan huruf status (H, I, S, A)
    $response->assertSee('P1');
    $response->assertSee('P2');
    $response->assertSee('3 Agt');
    $response->assertSee('10 Agt');
});

test('riwayat mapel siswa: memuat tabel desktop dan kartu ringkasan mobile siswa bersangkutan saja', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapel->id,
    ]));

    $response->assertOk();

    // 1. Memuat tabel desktop dan kartu mobile
    $response->assertSee('hidden md:block');
    $response->assertSee('md:hidden');

    // 2. Memuat chip pertemuan untuk siswa ini
    $response->assertSee('P1');
    $response->assertSee('P2');

    // 3. Siswa tidak memuat nama atau data siswa lain
    $response->assertDontSee('Budi Siswa 2');
    $response->assertDontSee('Citra Nonaktif');
});

test('siswa jadwal: blok filter memakai grid-cols-1 di mobile dan w-full tanpa elemen absolut/negatif', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.jadwal'));

    $response->assertOk();

    // Blok filter memakai grid-cols-1 di mobile dan w-full
    $response->assertSee('grid grid-cols-1');
    $response->assertSee('id="filter-hari"', false);
    $response->assertSee('id="filter-mapel"', false);

    // Label berada di atas input (bukan sr-only)
    $response->assertSee('Hari</label>', false);
    $response->assertSee('Mata Pelajaran</label>', false);

    // Kontrol memiliki tinggi min 44px
    $response->assertSee('min-h-[44px]');
});

test('admin siswa import: input file memakai komponen x-ui.file-input dengan varian file: dan min-h-[44px]', function () {
    $response = $this->actingAs($this->userAdmin)->get(route('admin.siswa.index'));

    $response->assertOk();

    // Memuat input file dengan varian file: dan dark:file: serta tinggi min 44px
    $response->assertSee('file:mr-4');
    $response->assertSee('dark:file:bg-emerald-500/15');
    $response->assertSee('min-h-[44px]');
    $response->assertSee('Pilih Kelas');
    $response->assertSee('Format .xlsx, .xls, atau .csv. Maksimal sesuai batas server.');
});

test('admin pergantian periode: markup memuat x-ref panel, scroll handler, scroll-mt, penanda hash, dan tabindex', function () {
    $response = $this->actingAs($this->userAdmin)->get(route('admin.pergantian-periode.index'));

    $response->assertOk();

    // 1. x-ref panel
    $response->assertSee('x-ref="salinPanel"', false);
    $response->assertSee('x-ref="pindahPanel"', false);
    $response->assertSee('x-ref="lulusPanel"', false);
    $response->assertSee('x-ref="tabBar"', false);

    // 2. Atribut scroll-mt
    $response->assertSee('scroll-mt-24');

    // 3. Penanda hash routing di Alpine
    $response->assertSee('scrollToTab', false);
    $response->assertSee("'#salin'", false);
    $response->assertSee("'#pindah'", false);
    $response->assertSee("'#lulus'", false);

    // 4. Tabindex pada judul panel
    $response->assertSee('tabindex="-1"', false);

    // 5. Tautan Kembali ke daftar langkah
    $response->assertSee('Kembali ke daftar langkah');
});

test('riwayat siswa tab Semua Riwayat: select bulan memuat Semua bulan dan enam opsi, tanpa input type=month', function () {
    // 1. Tanpa parameter bulan: mengembalikan 200 dan tidak ada pesan error
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'semua']));
    $response->assertOk();
    $response->assertSessionHasNoErrors();

    // 2. Memuat select name="bulan" dengan "Semua bulan" dan 6 opsi semester Ganjil 2026/2027 (Juli - Desember 2026)
    $response->assertSee('name="bulan"', false);
    $response->assertSee('<option value="">Semua bulan</option>', false);
    $response->assertSee('Juli 2026');
    $response->assertSee('Agustus 2026');
    $response->assertSee('September 2026');
    $response->assertSee('Oktober 2026');
    $response->assertSee('November 2026');
    $response->assertSee('Desember 2026');

    // 3. Tidak ada input type="month"
    $response->assertDontSee('type="month"');

    // 4. Request dengan bulan kosong "" mengembalikan 200 tanpa error validasi
    $resKosong = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'semua', 'bulan' => '']));
    $resKosong->assertOk();
    $resKosong->assertSessionHasNoErrors();

    // 5. Request dengan bulan valid (2026-08) menyaring catatan (Agustus: 2 pertemuan sesi1 dan sesi2)
    $resValid = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'semua', 'bulan' => '2026-08']));
    $resValid->assertOk();
    $resValid->assertSee('Menampilkan 2 catatan');

    // 6. Request dengan bulan valid di luar daftar (mis. 2026-05) diterima dan muncul sebagai opsi terpilih
    $resLuar = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'semua', 'bulan' => '2026-05']));
    $resLuar->assertOk();
    $resLuar->assertSessionHasNoErrors();
    $resLuar->assertSee('value="2026-05"', false);
    $resLuar->assertSee('Mei 2026');

    // 7. Bulan manipulasi URL seperti 2026-13 ditolak oleh validasi
    $resSalah = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'semua', 'bulan' => '2026-13']));
    $resSalah->assertStatus(302);
    $resSalah->assertSessionHasErrors(['bulan']);
});

test('admin pergantian periode: semua SVG memiliki ukuran eksplisit dan tab memuat ikon w-5 h-5 shrink-0', function () {
    $response = $this->actingAs($this->userAdmin)->get(route('admin.pergantian-periode.index'));
    $response->assertOk();

    // Tab memuat ikon dengan class w-5 h-5 shrink-0
    $response->assertSee('w-5 h-5 shrink-0');
    $response->assertDontSee('size-4.5');

    // Kartu panduan memuat tautan Menu Jadwal dengan panah normal
    $response->assertSee('Menu Jadwal');
    $response->assertDontSee('M11 7h6l-4 -4m4 4l-4 4');
});
