<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->kelas = Kelas::factory()->create(['nama' => 'X RPL 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->kelasLain = Kelas::factory()->create(['nama' => 'X TKJ 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

    $this->userSiswa = User::factory()->create(['role' => 'siswa', 'name' => 'Ahmad Siswa']);
    $this->siswa = Siswa::factory()->create([
        'user_id' => $this->userSiswa->id,
        'kelas_id' => $this->kelas->id,
    ]);

    $this->userGuru = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru']);
    $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id]);

    $this->mapel1 = Mapel::factory()->create(['nama' => 'Matematika', 'kode' => 'MTK']);
    $this->mapel2 = Mapel::factory()->create(['nama' => 'Bahasa Inggris', 'kode' => 'BIG']);
    $this->mapelLain = Mapel::factory()->create(['nama' => 'Fisika TKJ', 'kode' => 'FIS']);

    // Jadwal kelas siswa (periode aktif)
    Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:45:00',
        'jam_selesai' => '10:15:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal kelas lain
    Jadwal::factory()->create([
        'kelas_id' => $this->kelasLain->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guru->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('sidebar siswa: menu Mata Pelajaran muncul di antara Jadwal Pelajaran dan Riwayat', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Mata Pelajaran');
    $response->assertSee(route('siswa.mapel'));
});

test('AB-08: daftar mata pelajaran hanya memuat mapel kelas siswa itu pada periode aktif', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.mapel'));

    $response->assertStatus(200);
    $response->assertSee('Matematika');
    $response->assertSee('MTK');
    $response->assertSee('Bahasa Inggris');
    $response->assertSee('BIG');
    $response->assertSee('Budi Guru');

    // Mapel kelas lain tidak boleh muncul
    $response->assertDontSee('Fisika TKJ');
    $response->assertDontSee('FIS');

    // Tautan kartu menuju jadwal dengan mapel_id
    $response->assertSee(route('siswa.jadwal', ['mapel_id' => $this->mapel1->id]));
});

test('AB-08: empty state ramah saat siswa belum memiliki kelas atau jadwal', function () {
    $userTanpaKelas = User::factory()->create(['role' => 'siswa']);
    $response = $this->actingAs($userTanpaKelas)->get(route('siswa.mapel'));

    $response->assertStatus(200);
    $response->assertSee('Belum Terdaftar di Kelas');

    $kelasKosong = Kelas::factory()->create(['nama' => 'X Kosong', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $userSiswaKosong = User::factory()->create(['role' => 'siswa']);
    Siswa::factory()->create(['user_id' => $userSiswaKosong->id, 'kelas_id' => $kelasKosong->id]);

    $responseKosong = $this->actingAs($userSiswaKosong)->get(route('siswa.mapel'));
    $responseKosong->assertStatus(200);
    $responseKosong->assertSee('Belum Ada Mata Pelajaran');
});

test('AB-08: guru dan admin ditolak 403 saat mengakses mata pelajaran siswa', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('siswa.mapel'))->assertStatus(403);

    $this->actingAs($this->userGuru)->get(route('siswa.mapel'))->assertStatus(403);
});

test('AB-08: tamu dialihkan ke login', function () {
    $this->get(route('siswa.mapel'))->assertRedirect(route('login'));
});

test('AB-05 & AB-08: akun siswa nonaktif ditolak mengakses menu mata pelajaran', function () {
    $this->siswa->delete(); // soft delete

    $response = $this->actingAs($this->userSiswa)->get(route('siswa.mapel'));
    $response->assertRedirect(route('login'));
});

test('performa: jumlah query mata pelajaran siswa tidak bertambah proporsional dengan jumlah jadwal (tanpa N+1)', function () {
    for ($i = 0; $i < 10; $i++) {
        $m = Mapel::factory()->create(['nama' => "Mapel Extra {$i}", 'kode' => "EX{$i}"]);
        Jadwal::factory()->create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $m->id,
            'guru_id' => $this->guru->id,
            'hari' => 'kamis',
            'jam_mulai' => sprintf('%02d:00:00', 7 + $i),
            'jam_selesai' => sprintf('%02d:45:00', 7 + $i),
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);
    }

    DB::enableQueryLog();

    $this->actingAs($this->userSiswa)->get(route('siswa.mapel'))->assertStatus(200);

    $queries = DB::getQueryLog();
    expect(count($queries))->toBeLessThan(15);
});
