<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup data dasar
    $this->guruUser = User::factory()->create(['role' => 'guru']);
    $this->guru = Guru::create(['user_id' => $this->guruUser->id, 'nip' => '123456']);

    $this->lainUser = User::factory()->create(['role' => 'guru']);
    $this->lainGuru = Guru::create(['user_id' => $this->lainUser->id, 'nip' => '654321']);

    $this->adminUser = User::factory()->create(['role' => 'admin']);

    $this->jurusan = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);

    $this->kelas = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X RPL 1', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027']);
    $this->kelasLain = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X RPL 2', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027']);

    $this->mapel = Mapel::create(['nama' => 'Pemrograman Web', 'kode' => 'PW']);

    // Buat siswa di kelas
    $this->siswa1User = User::factory()->create(['role' => 'siswa']);
    $this->siswa1 = Siswa::create(['user_id' => $this->siswa1User->id, 'kelas_id' => $this->kelas->id, 'nis' => 'S01']);

    $this->siswa2User = User::factory()->create(['role' => 'siswa']);
    $this->siswa2 = Siswa::create(['user_id' => $this->siswa2User->id, 'kelas_id' => $this->kelas->id, 'nis' => 'S02']);

    $this->siswaLainUser = User::factory()->create(['role' => 'siswa']);
    $this->siswaLain = Siswa::create(['user_id' => $this->siswaLainUser->id, 'kelas_id' => $this->kelasLain->id, 'nis' => 'S03']);

    // Jadwal untuk hari Senin
    $this->jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);
});

test('dashboard hanya menampilkan jadwal hari ini milik guru itu', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Jadwal milik guru lain
    Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->lainGuru->id,
        'hari' => 'senin',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    // Jadwal guru ini tapi hari lain
    Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    // Assertion array properties pass via View
    $response->assertViewHas('jadwalHariIni', function ($jadwalHariIni) {
        return $jadwalHariIni->count() === 1 && $jadwalHariIni->first()->id === $this->jadwal->id;
    });
});

test('AB-02: sebelum disimpan tidak ada baris detail, dan dashboard menampilkan Belum diabsen', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));
    $response->assertSee('Belum diabsen');

    $this->assertDatabaseEmpty('sesi_absensi');
    $this->assertDatabaseEmpty('detail_absensi');
});

test('AB-03: guru lain dan jadwal hari lain mendapat 403, admin diizinkan', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Guru lain akses 403
    $response = $this->actingAs($this->lainUser)->get(route('guru.absensi.show', $this->jadwal->id));
    $response->assertStatus(403);

    // Hari selasa (hari tidak sama dengan server (senin))
    $jadwalSelasa = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $response2 = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', $jadwalSelasa->id));
    $response2->assertStatus(403);

    // Admin diizinkan
    $response3 = $this->actingAs($this->adminUser)->get(route('guru.absensi.show', $this->jadwal->id));
    $response3->assertStatus(200);
});

test('AB-04 dan AB-09: semua siswa kelas mendapat baris detail, dan field log terisi', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    $response = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'catatan' => 'Sesi pertama',
        'siswa' => [
            $this->siswa1->id => [
                'status' => 'hadir',
            ],
            $this->siswa2->id => [
                'status' => 'izin',
                'keterangan' => 'Acara keluarga',
            ],
        ],
    ]);

    $response->assertRedirect(route('guru.dashboard'));

    // Cek Database
    $this->assertDatabaseHas('sesi_absensi', [
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-09-21',
        'catatan' => 'Sesi pertama',
        'diabsen_oleh' => $this->guruUser->id,
        'diubah_oleh' => null,
    ]);

    $sesi = SesiAbsensi::first();

    $this->assertDatabaseHas('detail_absensi', [
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $this->siswa1->id,
        'status' => 'hadir',
    ]);

    $this->assertDatabaseHas('detail_absensi', [
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $this->siswa2->id,
        'status' => 'izin',
        'keterangan' => 'Acara keluarga',
    ]);
});

test('AB-01: membuka dan menyimpan dua kali tidak membuat sesi ganda', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Simpan pertama
    $res1 = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswa1->id => ['status' => 'hadir'],
        ],
    ]);
    $res1->assertSessionHasNoErrors();
    $res1->assertRedirect();
    $this->assertDatabaseCount('sesi_absensi', 1);

    // Simpan kedua
    $res2 = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswa1->id => ['status' => 'sakit'],
        ],
    ]);
    $res2->assertSessionHasNoErrors();
    $res2->assertRedirect();

    $this->assertDatabaseCount('sesi_absensi', 1);
    $this->assertDatabaseCount('detail_absensi', 2); // Karena ada 2 siswa di kelas

    $sesi = SesiAbsensi::first();
    $this->assertEquals($this->guruUser->id, $sesi->diubah_oleh);

    $this->assertDatabaseHas('detail_absensi', [
        'siswa_id' => $this->siswa1->id,
        'status' => 'sakit',
    ]);
});

test('status tidak valid dan siswa dari kelas lain ditolak', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Status tidak valid
    $response = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswa1->id => ['status' => 'tidak_valid'],
        ],
    ]);

    $response->assertSessionHasErrors(['siswa.'.$this->siswa1->id.'.status']);

    // Siswa beda kelas
    $response2 = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswaLain->id => ['status' => 'hadir'],
        ],
    ]);

    $response2->assertSessionHasErrors(['siswa.'.$this->siswaLain->id]);
});

afterEach(function () {
    Carbon::setTestNow(); // Reset time
});
