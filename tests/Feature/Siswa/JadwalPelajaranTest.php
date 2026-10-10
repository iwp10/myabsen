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

    $this->mapel1 = Mapel::factory()->create(['nama' => 'Matematika']);
    $this->mapel2 = Mapel::factory()->create(['nama' => 'Bahasa Inggris']);
    $this->mapelLain = Mapel::factory()->create(['nama' => 'Fisika TKJ']);

    // Jadwal kelas siswa (periode aktif)
    $this->j1 = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->j2 = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '08:45:00',
        'jam_selesai' => '10:15:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->j3 = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:30:00',
        'jam_selesai' => '09:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal kelas lain (tidak boleh terlihat siswa)
    $this->jLain = Jadwal::factory()->create([
        'kelas_id' => $this->kelasLain->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('AB-08: siswa hanya melihat jadwal kelasnya sendiri pada periode aktif', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal'));

    $response->assertStatus(200);
    $response->assertSee('Matematika');
    $response->assertSee('Bahasa Inggris');
    $response->assertSee('Budi Guru');
    $response->assertSee('X RPL 1');

    // Jadwal kelas lain tidak tampil
    $response->assertDontSee('Fisika TKJ');
    $response->assertDontSee('X TKJ 1');
});

test('AB-08: jadwal dikelompokkan per hari dan diurutkan sesuai jam pelajaran', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal'));

    $response->assertStatus(200);
    $content = $response->getContent();

    // Jam 07:00 muncul sebelum 08:45 pada hari Senin
    $posisiJam1 = strpos($content, '07:00');
    $posisiJam2 = strpos($content, '08:45');

    expect($posisiJam1)->toBeLessThan($posisiJam2);
});

test('AB-08: filter hari bekerja pada jadwal pelajaran siswa', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal', ['hari' => 'selasa']));

    $response->assertStatus(200);
    $response->assertSee('Hari Selasa');
    $response->assertSee('07:30');
    // Mapel Senin tidak muncul
    $response->assertDontSee('Bahasa Inggris');
});

test('AB-08: siswa tanpa kelas melihat empty state ramah', function () {
    $userTanpaKelas = User::factory()->create(['role' => 'siswa']);

    $response = $this->actingAs($userTanpaKelas)->get(route('siswa.jadwal'));

    $response->assertStatus(200);
    $response->assertSee('Belum Terdaftar di Kelas');
});

test('AB-08: kelas tanpa jadwal melihat empty state ramah', function () {
    $kelasKosong = Kelas::factory()->create(['nama' => 'XII RPL 3', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $userSiswaKosong = User::factory()->create(['role' => 'siswa']);
    Siswa::factory()->create(['user_id' => $userSiswaKosong->id, 'kelas_id' => $kelasKosong->id]);

    $response = $this->actingAs($userSiswaKosong)->get(route('siswa.jadwal'));

    $response->assertStatus(200);
    $response->assertSee('Belum Ada Jadwal Pelajaran');
});

test('AB-08: guru dan admin ditolak dengan 403 saat mengakses jadwal pelajaran siswa', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('siswa.jadwal'))->assertStatus(403);

    $this->actingAs($this->userGuru)->get(route('siswa.jadwal'))->assertStatus(403);
});

test('AB-08: tamu dialihkan ke halaman login', function () {
    $this->get(route('siswa.jadwal'))->assertRedirect(route('login'));
});

test('AB-05 & AB-08: akun siswa nonaktif ditolak mengakses jadwal pelajaran', function () {
    $this->siswa->delete(); // soft delete

    $response = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal'));
    // Middleware role memblokir akun soft delete
    $response->assertRedirect(route('login'));
});

test('sidebar siswa menampilkan menu Jadwal Pelajaran', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Jadwal Pelajaran');
    $response->assertSee(route('siswa.jadwal'));
});

test('performa: jumlah query jadwal siswa tidak bertambah proporsional terhadap jumlah jadwal (tanpa N+1)', function () {
    // Tambah 10 jadwal lagi di kelas siswa
    for ($i = 0; $i < 10; $i++) {
        $m = Mapel::factory()->create(['nama' => "Mapel Extra {$i}"]);
        Jadwal::factory()->create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $m->id,
            'guru_id' => $this->guru->id,
            'hari' => 'rabu',
            'jam_mulai' => sprintf('%02d:00:00', 7 + $i),
            'jam_selesai' => sprintf('%02d:45:00', 7 + $i),
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);
    }

    DB::enableQueryLog();

    $this->actingAs($this->userSiswa)->get(route('siswa.jadwal'))->assertStatus(200);

    $queries = DB::getQueryLog();
    // Eager loading mapel, guru.user, kelas.jurusan harus tetap dalam jumlah query tetap
    expect(count($queries))->toBeLessThan(15);
});
