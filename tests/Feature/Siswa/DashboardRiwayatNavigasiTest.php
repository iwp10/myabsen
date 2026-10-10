<?php

use App\Enums\StatusKehadiran;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->userSiswa1 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Satu']);
    $this->kelas = Kelas::factory()->create(['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->siswa1 = Siswa::factory()->create([
        'user_id' => $this->userSiswa1->id,
        'kelas_id' => $this->kelas->id,
    ]);

    $this->userSiswa2 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Dua']);
    $this->siswa2 = Siswa::factory()->create([
        'user_id' => $this->userSiswa2->id,
        'kelas_id' => $this->kelas->id,
    ]);

    $this->mapel = Mapel::factory()->create(['nama' => 'Matematika']);
    $this->guru = Guru::factory()->create();

    $this->jadwal = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Buat data absensi siswa 1: 3 Hadir, 2 Izin, 1 Sakit, 1 Alpa
    // Tanggal 2026-09-01 sampai 2026-09-07
    $dates = [
        '2026-09-01' => StatusKehadiran::HADIR,
        '2026-09-02' => StatusKehadiran::HADIR,
        '2026-09-03' => StatusKehadiran::HADIR,
        '2026-09-04' => StatusKehadiran::IZIN,
        '2026-09-05' => StatusKehadiran::IZIN,
        '2026-09-06' => StatusKehadiran::SAKIT,
        '2026-09-07' => StatusKehadiran::ALPA,
    ];

    foreach ($dates as $tgl => $st) {
        $sesi = SesiAbsensi::create([
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => $tgl,
            'diabsen_oleh' => $this->guru->user_id,
        ]);

        DetailAbsensi::create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $this->siswa1->id,
            'status' => $st,
            'keterangan' => 'Catatan '.$tgl,
        ]);

        // Siswa 2 punya catatan sendiri pada sesi yang sama
        DetailAbsensi::create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $this->siswa2->id,
            'status' => StatusKehadiran::HADIR,
            'keterangan' => 'Siswa 2 '.$tgl,
        ]);
    }
});

test('kartu dashboard siswa memuat tautan menuju Semua Riwayat dengan status dan periode yang sama', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.dashboard'));

    $response->assertStatus(200);

    // Memuat link kartu untuk Hadir, Izin, Sakit, Alpa dengan parameter periode=semua
    $response->assertSee(route('siswa.riwayat', ['tab' => 'semua', 'status' => 'hadir', 'periode' => 'semua']));
    $response->assertSee(route('siswa.riwayat', ['tab' => 'semua', 'status' => 'izin', 'periode' => 'semua']));
    $response->assertSee(route('siswa.riwayat', ['tab' => 'semua', 'status' => 'sakit', 'periode' => 'semua']));
    $response->assertSee(route('siswa.riwayat', ['tab' => 'semua', 'status' => 'alpa', 'periode' => 'semua']));
});

test('jumlah catatan di Semua Riwayat sama persis dengan angka kartu dashboard untuk setiap status', function () {
    // 1. Hadir: 3
    $resHadir = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'status' => 'hadir',
        'periode' => 'semua',
    ]));
    $resHadir->assertStatus(200);
    $resHadir->assertSee('Menampilkan 3 catatan hadir');

    // 2. Izin: 2
    $resIzin = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'status' => 'izin',
        'periode' => 'semua',
    ]));
    $resIzin->assertStatus(200);
    $resIzin->assertSee('Menampilkan 2 catatan izin');

    // 3. Sakit: 1
    $resSakit = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'status' => 'sakit',
        'periode' => 'semua',
    ]));
    $resSakit->assertStatus(200);
    $resSakit->assertSee('Menampilkan 1 catatan sakit');

    // 4. Alpa: 1
    $resAlpa = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'status' => 'alpa',
        'periode' => 'semua',
    ]));
    $resAlpa->assertStatus(200);
    $resAlpa->assertSee('Menampilkan 1 catatan alpa');
});

test('filter bulan bekerja pada Semua Riwayat dan bulan tidak valid seperti 2026-13 ditolak', function () {
    // Filter bulan yang cocok (2026-09)
    $resBulan = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'bulan' => '2026-09',
        'status' => 'hadir',
        'periode' => 'semua',
    ]));
    $resBulan->assertStatus(200);
    $resBulan->assertSee('Menampilkan 3 catatan hadir');

    // Filter bulan yang tidak ada data (2026-10)
    $resBulanKosong = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'bulan' => '2026-10',
        'status' => 'hadir',
        'periode' => 'semua',
    ]));
    $resBulanKosong->assertStatus(200);
    $resBulanKosong->assertSee('Menampilkan 0 catatan hadir');

    // Bulan tidak valid (2026-13) ditolak dengan error validasi
    $resBulanSalah = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'bulan' => '2026-13',
    ]));
    $resBulanSalah->assertStatus(302);
    $resBulanSalah->assertSessionHasErrors(['bulan']);
});

test('AB-08: siswa lain tidak bisa melihat data riwayat kehadiran siswa ini', function () {
    // Siswa 2 melihat riwayat alpa: siswa 2 tidak punya alpa, alpa milik siswa 1 tidak boleh muncul
    $res = $this->actingAs($this->userSiswa2)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'status' => 'alpa',
        'periode' => 'semua',
    ]));

    $res->assertStatus(200);
    $res->assertSee('Menampilkan 0 catatan alpa');
    $res->assertDontSee('Catatan 2026-09-07');
});
