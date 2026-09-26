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
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'siswa']);
    $this->kelas = Kelas::factory()->create();
    $this->siswa = Siswa::factory()->create([
        'user_id' => $this->user->id,
        'kelas_id' => $this->kelas->id,
    ]);
});

test('siswa dapat melihat dashboard dengan jadwal hari ini dan status', function () {
    $hariIni = strtolower(Carbon::now()->locale('id')->isoFormat('dddd'));
    $tanggalHariIni = Carbon::today()->format('Y-m-d');

    $mapel = Mapel::factory()->create(['nama' => 'Matematika']);
    $guru = Guru::factory()->create();

    // Jadwal hari ini
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => $hariIni,
    ]);

    // Belum diabsen
    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Matematika');
    $response->assertSee('Belum diabsen');

    // Sudah diabsen (Hadir)
    $sesi = SesiAbsensi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => $tanggalHariIni,
    ]);

    DetailAbsensi::factory()->create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $this->siswa->id,
        'status' => StatusKehadiran::HADIR,
    ]);

    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Hadir');
    $response->assertDontSee('Belum diabsen');
});

test('siswa dapat melihat riwayat dan memfilter berdasarkan tanggal dan mapel', function () {
    $mapel1 = Mapel::factory()->create(['nama' => 'Fisika']);
    $mapel2 = Mapel::factory()->create(['nama' => 'Biologi']);

    $jadwal1 = Jadwal::factory()->create(['kelas_id' => $this->kelas->id, 'mapel_id' => $mapel1->id]);
    $jadwal2 = Jadwal::factory()->create(['kelas_id' => $this->kelas->id, 'mapel_id' => $mapel2->id]);

    $sesi1 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal1->id, 'tanggal' => '2026-09-01']);
    $sesi2 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal2->id, 'tanggal' => '2026-09-02']);

    DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $this->siswa->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $this->siswa->id, 'status' => StatusKehadiran::IZIN]);

    // Tanpa filter
    $response = $this->actingAs($this->user)->get(route('siswa.riwayat'));
    $response->assertStatus(200);
    $response->assertSee('Fisika');
    $response->assertSee('Biologi');

    // Filter by mapel
    $response = $this->actingAs($this->user)->get(route('siswa.riwayat', ['mapel_id' => $mapel1->id]));
    $response->assertStatus(200);
    $response->assertSee('01/09/2026');
    $response->assertDontSee('02/09/2026');

    // Filter by tanggal
    $response = $this->actingAs($this->user)->get(route('siswa.riwayat', ['tanggal' => '2026-09-02']));
    $response->assertStatus(200);
    $response->assertSee('02/09/2026');
    $response->assertDontSee('01/09/2026');
});

test('siswa dapat melihat persentase kehadiran dengan benar (AB-07)', function () {
    $mapel = Mapel::factory()->create(['nama' => 'Kimia']);
    $jadwal = Jadwal::factory()->create(['kelas_id' => $this->kelas->id, 'mapel_id' => $mapel->id]);

    // 3 Sesi: 2 Hadir, 1 Sakit -> 66.67%
    $sesi1 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-01']);
    $sesi2 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-02']);
    $sesi3 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-03']);

    DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $this->siswa->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $this->siswa->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi3->id, 'siswa_id' => $this->siswa->id, 'status' => StatusKehadiran::SAKIT]);

    $response = $this->actingAs($this->user)->get(route('siswa.riwayat'));
    $response->assertStatus(200);
    $response->assertSee('Kimia');
    $response->assertSee('66.67%');
});

test('siswa hanya dapat melihat datanya sendiri (AB-08)', function () {
    $siswaLain = Siswa::factory()->create(['kelas_id' => $this->kelas->id]);
    $mapel = Mapel::factory()->create(['nama' => 'Sejarah']);
    $jadwal = Jadwal::factory()->create(['kelas_id' => $this->kelas->id, 'mapel_id' => $mapel->id]);

    $sesi = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-01']);

    // Data siswa ini
    DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $this->siswa->id, 'status' => StatusKehadiran::HADIR, 'keterangan' => 'KetSiswaIni']);

    // Data siswa lain
    DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $siswaLain->id, 'status' => StatusKehadiran::ALPA, 'keterangan' => 'KetSiswaLain']);

    $response = $this->actingAs($this->user)->get(route('siswa.riwayat'));
    $response->assertStatus(200);
    $response->assertSee('KetSiswaIni');
    $response->assertDontSee('KetSiswaLain');
});
