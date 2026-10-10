<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guruUser = User::factory()->create(['role' => 'guru', 'name' => 'Budi Santoso']);
    $this->guru = Guru::create(['user_id' => $this->guruUser->id, 'nip' => '198501012010011001']);

    $this->lainUser = User::factory()->create(['role' => 'guru', 'name' => 'Siti Rahma']);
    $this->lainGuru = Guru::create(['user_id' => $this->lainUser->id, 'nip' => '198802022012012002']);

    $this->siswaUser = User::factory()->create(['role' => 'siswa']);

    $this->jurusan = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);

    $this->kelasA = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X RPL 1', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027']);
    $this->kelasB = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'XI RPL 1', 'tingkat' => 11, 'tahun_ajaran' => '2026/2027']);

    $this->mapel1 = Mapel::create(['nama' => 'Pemrograman Berorientasi Objek', 'kode' => 'PBO']);
    $this->mapel2 = Mapel::create(['nama' => 'Basis Data', 'kode' => 'BD']);
    $this->mapelLain = Mapel::create(['nama' => 'Matematika Terapan', 'kode' => 'MTK']);

    // Jadwal guru login
    $this->jadwal1 = Jadwal::create([
        'kelas_id' => $this->kelasA->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $this->jadwal2 = Jadwal::create([
        'kelas_id' => $this->kelasB->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:30:00',
        'jam_selesai' => '09:00:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    // Jadwal guru lain
    $this->jadwalLain = Jadwal::create([
        'kelas_id' => $this->kelasA->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->lainGuru->id,
        'hari' => 'senin',
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '11:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);
});

test('guru dapat mengakses halaman jadwal mengajar mereka', function () {
    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal'));

    $response->assertStatus(200);
    $response->assertSee('Jadwal Mengajar');
    $response->assertSee('Seluruh Jadwal Mengajar Seminggu');
    $response->assertSee('Pemrograman Berorientasi Objek');
    $response->assertSee('Basis Data');
    $response->assertSee('X RPL 1');
    $response->assertSee('XI RPL 1');

    // Jadwal guru lain tidak boleh tampil
    $response->assertDontSee('Matematika Terapan');
});

test('jadwal terurut berdasarkan hari dan jam masuk', function () {
    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal'));

    $content = $response->getContent();

    // Senin (jadwal2) harus muncul sebelum Selasa (jadwal1)
    $posisiSenin = strpos($content, 'Senin');
    $posisiSelasa = strpos($content, 'Selasa');

    expect($posisiSenin)->toBeLessThan($posisiSelasa);
});

test('siswa tidak dapat mengakses halaman jadwal guru', function () {
    $response = $this->actingAs($this->siswaUser)->get(route('guru.jadwal'));

    $response->assertStatus(403);
});

test('pengguna belum login dialihkan ke halaman login', function () {
    $response = $this->get(route('guru.jadwal'));

    $response->assertRedirect(route('login'));
});

test('sidebar guru menampilkan menu jadwal mengajar', function () {
    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Jadwal Mengajar');
    $response->assertSee(route('guru.jadwal'));
});

test('AB-08: filter hari menyaring jadwal mengajar guru dan jadwal guru lain tidak muncul', function () {
    // Filter hari selasa -> hanya jadwal1 (PBO), bukan jadwal2 (BD - senin) dan bukan jadwalLain
    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal', ['hari' => 'selasa']));

    $response->assertStatus(200);
    $response->assertSee('Pemrograman Berorientasi Objek');
    $response->assertDontSee('Basis Data');
    $response->assertDontSee('Matematika Terapan');
});

test('AB-08: jadwal hari ini disorot secara visual dengan badge Hari ini', function () {
    // Set hari ini menjadi Selasa (hari jadwal1)
    Carbon::setTestNow('2026-09-22 08:00:00'); // 2026-09-22 adalah Selasa

    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal'));

    $response->assertStatus(200);
    $response->assertSee('Hari ini');

    Carbon::setTestNow(); // reset
});
