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
use App\Services\PertemuanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->kelas = Kelas::factory()->create([
        'nama' => '10 RPL 1',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->userSiswa = User::factory()->create([
        'name' => 'Ahmad Siswa',
        'role' => 'siswa',
    ]);
    $this->siswa = Siswa::factory()->create([
        'user_id' => $this->userSiswa->id,
        'kelas_id' => $this->kelas->id,
    ]);

    $this->userGuru = User::factory()->create([
        'name' => 'Guru Pengampu',
        'role' => 'guru',
    ]);
    $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id]);

    $this->mapel = Mapel::factory()->create(['nama' => 'Matematika']);

    $this->jadwal = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:30',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Buat 3 sesi secara kronologis
    $this->sesi1 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-03',
        'status' => 'ditutup',
        'diabsen_oleh' => $this->userGuru->id,
    ]);
    $this->detail1 = DetailAbsensi::create([
        'sesi_absensi_id' => $this->sesi1->id,
        'siswa_id' => $this->siswa->id,
        'status' => StatusKehadiran::HADIR,
    ]);

    $this->sesi2 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-10',
        'status' => 'ditutup',
        'diabsen_oleh' => $this->userGuru->id,
    ]);
    $this->detail2 = DetailAbsensi::create([
        'sesi_absensi_id' => $this->sesi2->id,
        'siswa_id' => $this->siswa->id,
        'status' => StatusKehadiran::HADIR,
    ]);

    $this->sesi3 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-17',
        'status' => 'ditutup',
        'diabsen_oleh' => $this->userGuru->id,
    ]);
    $this->detail3 = DetailAbsensi::create([
        'sesi_absensi_id' => $this->sesi3->id,
        'siswa_id' => $this->siswa->id,
        'status' => StatusKehadiran::ALPA,
        'keterangan' => 'Tidak hadir tanpa kabar',
    ]);
});

test('tab semua riwayat menampilkan label pertemuan kronologis', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.riwayat', ['tab' => 'semua']));

    $response->assertStatus(200);
    $response->assertSee('data-label="Pertemuan"', false);
    $response->assertSee('Pertemuan ke-1');
    $response->assertSee('Pertemuan ke-2');
    $response->assertSee('Pertemuan ke-3');
});

test('filter status tidak menggeser nomor pertemuan', function () {
    // Siswa memfilter status=alpa (hanya sesi 3 yang alpa)
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.riwayat', [
        'tab' => 'semua',
        'status' => 'alpa',
    ]));

    $response->assertStatus(200);
    // Sesi 3 harus tetap tampil sebagai "Pertemuan ke-3", BUKAN "Pertemuan ke-1"
    $response->assertSee('Pertemuan ke-3');
    $response->assertDontSee('Pertemuan ke-1');
    $response->assertDontSee('Pertemuan ke-2');
});

test('pertemuan service menghitung nomor urut pertemuan secara efisien tanpa N+1', function () {
    $service = app(PertemuanService::class);

    // Ambil detail absensi dengan relasi eager loaded
    $items = DetailAbsensi::with('sesiAbsensi.jadwal')
        ->where('siswa_id', $this->siswa->id)
        ->get();

    DB::enableQueryLog();

    $map = $service->hitungNomorPertemuan($items);

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Hanya boleh ada 1 query batch untuk sesi
    expect(count($queries))->toBeLessThanOrEqual(1);

    expect($map[$this->detail1->id])->toBe(1);
    expect($map[$this->detail2->id])->toBe(2);
    expect($map[$this->detail3->id])->toBe(3);

    expect($service->formatNomorPertemuan(1))->toBe('Pertemuan ke-1');
    expect($service->formatNomorPertemuan(null))->toBe('-');
});
