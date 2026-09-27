<?php

namespace Tests\Unit;

use App\Enums\StatusKehadiran;
use App\Models\DetailAbsensi;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AbsensiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AbsensiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AbsensiService::class);
    }

    public function test_hitung_persentase_kehadiran_mengembalikan_nol_jika_total_sesi_nol(): void
    {
        $persentase = $this->service->hitungPersentaseKehadiran(0, 0, 0, 0);
        $this->assertEquals(0.0, $persentase);
    }

    public function test_hitung_persentase_kehadiran_menghitung_izin_dan_sakit_sebagai_kehadiran_positif(): void
    {
        // 2 Hadir, 1 Izin, 1 Sakit, 0 Alpa dari 4 pertemuan -> 100%
        $persentase = $this->service->hitungPersentaseKehadiran(2, 1, 1, 4);
        $this->assertEquals(100.0, $persentase);
    }

    public function test_hitung_persentase_kehadiran_hanya_alpa_yang_mengurangi_persentase(): void
    {
        // 1 Hadir, 1 Izin, 1 Sakit, 1 Alpa dari 4 pertemuan -> (3 / 4) * 100 = 75%
        $persentase = $this->service->hitungPersentaseKehadiran(1, 1, 1, 4);
        $this->assertEquals(75.0, $persentase);

        // 2 Hadir, 0 Izin, 0 Sakit, 1 Alpa dari 3 pertemuan -> (2 / 3) * 100 = 66.67%
        $persentase2 = $this->service->hitungPersentaseKehadiran(2, 0, 0, 3);
        $this->assertEquals(66.67, $persentase2);
    }

    public function test_get_ringkasan_kehadiran_siswa_mengembalikan_rincian_lengkap(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $kelas = Kelas::factory()->create();
        $siswa = Siswa::factory()->create([
            'user_id' => $user->id,
            'kelas_id' => $kelas->id,
        ]);

        $mapel = Mapel::factory()->create();
        $jadwal = Jadwal::factory()->create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]);

        $sesi1 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-01']);
        $sesi2 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-02']);
        $sesi3 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-03']);
        $sesi4 = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'tanggal' => '2026-09-04']);

        DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $siswa->id, 'status' => StatusKehadiran::HADIR]);
        DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswa->id, 'status' => StatusKehadiran::IZIN]);
        DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi3->id, 'siswa_id' => $siswa->id, 'status' => StatusKehadiran::SAKIT]);
        DetailAbsensi::factory()->create(['sesi_absensi_id' => $sesi4->id, 'siswa_id' => $siswa->id, 'status' => StatusKehadiran::ALPA]);

        $ringkasan = $this->service->getRingkasanKehadiranSiswa($siswa->id);

        $this->assertEquals(1, $ringkasan['total_hadir']);
        $this->assertEquals(1, $ringkasan['total_izin']);
        $this->assertEquals(1, $ringkasan['total_sakit']);
        $this->assertEquals(1, $ringkasan['total_alpa']);
        $this->assertEquals(4, $ringkasan['total_sesi']);
        $this->assertEquals(75.0, $ringkasan['persentase']);
    }
}
