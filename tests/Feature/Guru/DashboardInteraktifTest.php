<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guruUser = User::factory()->create(['role' => 'guru', 'name' => 'Budi Santoso']);
    $this->guru = Guru::create(['user_id' => $this->guruUser->id, 'nip' => '198501012010011001']);

    $this->guruLainUser = User::factory()->create(['role' => 'guru', 'name' => 'Siti Rahma']);
    $this->guruLain = Guru::create(['user_id' => $this->guruLainUser->id, 'nip' => '198802022012012002']);

    $this->jurusanRPL = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);
    $this->jurusanTKJ = Jurusan::create(['nama' => 'Teknik Komputer Jaringan', 'kode' => 'TKJ']);

    $this->kelasA = Kelas::create(['jurusan_id' => $this->jurusanRPL->id, 'nama' => 'X RPL 1', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->kelasB = Kelas::create(['jurusan_id' => $this->jurusanRPL->id, 'nama' => 'XI RPL 1', 'tingkat' => 11, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->kelasLain = Kelas::create(['jurusan_id' => $this->jurusanTKJ->id, 'nama' => 'XII TKJ 1', 'tingkat' => 12, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

    $this->mapel1 = Mapel::create(['nama' => 'Pemrograman Berorientasi Objek', 'kode' => 'PBO']);
    $this->mapel2 = Mapel::create(['nama' => 'Basis Data', 'kode' => 'BD']);
    $this->mapelLain = Mapel::create(['nama' => 'Jaringan Nirkabel Khusus', 'kode' => 'JNK']);

    // Jadwal guru Budi (2 kelas, 2 mapel)
    Jadwal::create([
        'kelas_id' => $this->kelasA->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:30:00',
        'jam_selesai' => '09:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    Jadwal::create([
        'kelas_id' => $this->kelasA->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:15:00',
        'jam_selesai' => '10:45:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    Jadwal::create([
        'kelas_id' => $this->kelasB->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:30:00',
        'jam_selesai' => '09:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal guru lain (tidak boleh masuk modal dan statistik Budi)
    Jadwal::create([
        'kelas_id' => $this->kelasLain->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guruLain->id,
        'hari' => 'kamis',
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '09:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('dashboard guru: kartu Total Kelas dan Total Mapel memuat pemicu modal dan menampilkan data yang sesuai', function () {
    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);

    // Memuat pemicu Alpine untuk kedua modal
    $response->assertSee('modal-daftar-kelas');
    $response->assertSee('modal-daftar-mapel');
    $response->assertSee('role="button"', false);
    $response->assertSee('tabindex="0"', false);

    // Kartu menampilkan angka yang sesuai (2 kelas, 2 mapel)
    $response->assertSee('Daftar Kelas yang Diajar');
    $response->assertSee('Daftar Mata Pelajaran yang Diampu');

    // Modal kelas: memuat kelas guru login, bukan kelas guru lain
    $response->assertSee('X RPL 1');
    $response->assertSee('XI RPL 1');
    $response->assertDontSee('XII TKJ 1');

    // Modal mapel: memuat mapel guru login, bukan mapel guru lain
    $response->assertSee('Pemrograman Berorientasi Objek');
    $response->assertSee('Basis Data');
    $response->assertDontSee('Jaringan Nirkabel Khusus');

    // Tautan mengarah ke Jadwal Mengajar dengan parameter yang tepat
    $response->assertSee(route('guru.jadwal', ['kelas_id' => $this->kelasA->id]));
    $response->assertSee(route('guru.jadwal', ['mapel_id' => $this->mapel1->id]));
});

test('dashboard guru: data modal hanya memuat kelas dan mapel guru itu saja pada periode aktif', function () {
    $response = $this->actingAs($this->guruLainUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);

    // Guru lain hanya melihat kelas & mapel miliknya
    $response->assertSee('XII TKJ 1');
    $response->assertSee('Jaringan Nirkabel Khusus');
    $response->assertDontSee('PBO');
});
