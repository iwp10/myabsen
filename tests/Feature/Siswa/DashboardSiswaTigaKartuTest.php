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
use App\Services\SiswaDashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tahunAjaran = '2026/2027';
    $this->semester = 'Ganjil';

    // Konfigurasi periode aktif
    config([
        'absensi.tahun_ajaran' => $this->tahunAjaran,
        'absensi.semester' => $this->semester,
        'absensi.batas_kehadiran_rendah' => 75,
        'absensi.batas_koreksi_hari' => 7,
    ]);

    $this->user = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Penguji']);
    $this->kelas = Kelas::factory()->create([
        'nama' => '10 RPL 1',
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);
    $this->siswa = Siswa::factory()->create([
        'user_id' => $this->user->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '102030',
    ]);

    $this->userGuru = User::factory()->create(['role' => 'guru', 'name' => 'Bapak Guru']);
    $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id]);
});

test('jendela 7 hari: hari ini sebelum jam mulai tidak dihitung, sesudahnya dihitung, masa depan tidak, H-6 dihitung dan H-7 tidak', function () {
    // Tetapkan waktu simulasi: Rabu, 4 November 2026 pukul 10:00:00 WIB
    Carbon::setTestNow('2026-11-04 10:00:00');

    $mapel = Mapel::factory()->create(['nama' => 'Matematika']);

    // 1. Jadwal Rabu (hari ini) jam 08:00 - 09:30 (jam mulai sudah lewat) -> HARUS MASUK
    $jadwalRabuPagi = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'rabu',
        'jam_mulai' => '08:00',
        'jam_selesai' => '09:30',
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);

    // 2. Jadwal Rabu (hari ini) jam 11:00 - 12:30 (jam mulai belum lewat) -> TIDAK BOLEH MASUK
    $jadwalRabuSiang = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'rabu',
        'jam_mulai' => '11:00',
        'jam_selesai' => '12:30',
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);

    // 3. Jadwal Kamis (H-6 dari Rabu adalah Kamis pekan lalu, 29 Oktober 2026) -> HARUS MASUK
    $jadwalKamis = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'kamis',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);

    // 4. Jadwal Rabu pekan lalu (H-7: 28 Oktober 2026) -> TIDAK BOLEH MASUK (di luar batas 7 hari)
    // Jadwal Rabu sudah ada ($jadwalRabuPagi), tapi tanggal 2026-10-28 berada di luar rentang H s.d. H-6.

    $service = app(SiswaDashboardService::class);
    $result = $service->getJadwalBelumDiabsen($this->siswa, Carbon::now('Asia/Jakarta'));

    // Yang masuk harus persis jadwal Rabu pagi (2026-11-04) dan Kamis pekan lalu (2026-10-29)
    $tanggalMasuk = $result['items']->pluck('tanggal')->all();
    expect($tanggalMasuk)->toContain('2026-11-04');
    expect($tanggalMasuk)->toContain('2026-10-29');
    expect($tanggalMasuk)->not->toContain('2026-10-28'); // H-7 tidak masuk
    expect($tanggalMasuk)->not->toContain('2026-11-05'); // Masa depan tidak masuk

    // Pastikan jadwal siang hari ini tidak masuk
    $jamMasuk = $result['items']->where('tanggal', '2026-11-04')->pluck('jam_mulai')->all();
    expect($jamMasuk)->toContain('08:00');
    expect($jamMasuk)->not->toContain('11:00');

    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Belum Diabsen (7 Hari Terakhir)');
    $response->assertSee('Guru belum mencatat kehadiran. Ini bukan berarti kamu alpa.');
    $response->assertSee('Belum dicatat guru');
    $response->assertSee('08:00');
    $response->assertSee('2 Pertemuan');
});

test('jadwal yang sudah punya sesi tidak muncul; kelas lain dan periode lain tidak muncul', function () {
    Carbon::setTestNow('2026-11-04 14:00:00'); // Rabu

    $mapel = Mapel::factory()->create();

    // Jadwal kelas ini pada hari Selasa (H-1: 2026-11-03)
    $jadwalKelasIni = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:30',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);

    // Sudah diabsen pada Selasa 2026-11-03
    SesiAbsensi::factory()->create([
        'jadwal_id' => $jadwalKelasIni->id,
        'tanggal' => '2026-11-03',
    ]);

    // Jadwal kelas lain pada hari yang sama (Selasa)
    $kelasLain = Kelas::factory()->create(['tahun_ajaran' => $this->tahunAjaran, 'semester' => $this->semester]);
    Jadwal::factory()->create([
        'kelas_id' => $kelasLain->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:30',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);

    // Jadwal periode lain (Genap)
    Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:30',
        'jam_selesai' => '11:00',
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => 'Genap',
    ]);

    $service = app(SiswaDashboardService::class);
    $result = $service->getJadwalBelumDiabsen($this->siswa, Carbon::now('Asia/Jakarta'));

    expect($result['total'])->toBe(0);
    expect($result['items'])->toBeEmpty();

    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Semua pertemuan 7 hari terakhir sudah dicatat.');
});

test('batas maksimal 7 item dan penyingkatan dan n lainnya bila lebih', function () {
    Carbon::setTestNow('2026-11-04 15:00:00'); // Rabu

    $mapel = Mapel::factory()->create();

    // Buat 9 jadwal berbeda pada hari Selasa yang belum diabsen
    for ($i = 1; $i <= 9; $i++) {
        $jamMulai = sprintf('%02d:00', $i + 6);
        $jamSelesai = sprintf('%02d:45', $i + 6);
        Jadwal::factory()->create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $this->guru->id,
            'hari' => 'selasa',
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);
    }

    $service = app(SiswaDashboardService::class);
    $result = $service->getJadwalBelumDiabsen($this->siswa, Carbon::now('Asia/Jakarta'));

    expect($result['total'])->toBe(9);
    expect($result['items']->count())->toBe(7);
    expect($result['sisa'])->toBe(2);

    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('9 Pertemuan');
    $response->assertSee('dan 2 lainnya');
});

test('ringkasan minggu ini dan persentase cocok dengan rumus AB-07', function () {
    Carbon::setTestNow('2026-11-04 12:00:00'); // Rabu

    $mapel = Mapel::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);

    // Buat sesi dalam 7 hari terakhir:
    // 2 Hadir, 1 Izin, 1 Sakit, 1 Alpa -> Total 5 sesi, (2+1+1)/5 * 100 = 80.0%
    $statuses = [
        ['tgl' => '2026-11-04', 'status' => StatusKehadiran::HADIR],
        ['tgl' => '2026-11-03', 'status' => StatusKehadiran::HADIR],
        ['tgl' => '2026-11-02', 'status' => StatusKehadiran::IZIN],
        ['tgl' => '2026-11-01', 'status' => StatusKehadiran::SAKIT],
        ['tgl' => '2026-10-31', 'status' => StatusKehadiran::ALPA],
    ];

    foreach ($statuses as $s) {
        $sesi = SesiAbsensi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'tanggal' => $s['tgl'],
        ]);
        DetailAbsensi::factory()->create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $this->siswa->id,
            'status' => $s['status'],
        ]);
    }

    // Catatan di luar 7 hari (H-8: 2026-10-27) tidak boleh masuk ke ringkasan minggu ini
    $sesiLama = SesiAbsensi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-10-27',
    ]);
    DetailAbsensi::factory()->create([
        'sesi_absensi_id' => $sesiLama->id,
        'siswa_id' => $this->siswa->id,
        'status' => StatusKehadiran::ALPA,
    ]);

    $service = app(SiswaDashboardService::class);
    $ringkasan = $service->getRingkasanMingguIni($this->siswa, Carbon::now('Asia/Jakarta'));

    expect($ringkasan['total_hadir'])->toBe(2);
    expect($ringkasan['total_izin'])->toBe(1);
    expect($ringkasan['total_sakit'])->toBe(1);
    expect($ringkasan['total_alpa'])->toBe(1);
    expect($ringkasan['total_sesi'])->toBe(5);
    expect($ringkasan['persentase'])->toBe(80.0);

    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Ringkasan Minggu Ini');
    $response->assertSee('80%');
    $response->assertSee('dari 5 pertemuan');
    $response->assertSee('Hadir');
    $response->assertSee('Izin');
    $response->assertSee('Sakit');
    $response->assertSee('Alpa');
});

test('perlu perhatian: tepat di batas tidak masuk, di bawah batas masuk, urut paling rendah dan maksimal 5', function () {
    // Buat 7 mapel dengan persentase berbeda
    // Batas config = 75%
    // Mapel 1: 50% (di bawah)
    // Mapel 2: 60% (di bawah)
    // Mapel 3: 70% (di bawah)
    // Mapel 4: 72% (di bawah)
    // Mapel 5: 74% (di bawah)
    // Mapel 6: 74.5% (di bawah)
    // Mapel 7: 75% (tepat di batas -> TIDAK MASUK)
    // Mapel 8: 100% (aman -> TIDAK MASUK)

    $daftarMapelData = [
        ['nama' => 'Mapel 20', 'hadir' => 1, 'alpa' => 4], // 1/5 = 20%
        ['nama' => 'Mapel 40', 'hadir' => 2, 'alpa' => 3], // 2/5 = 40%
        ['nama' => 'Mapel 50', 'hadir' => 1, 'alpa' => 1], // 1/2 = 50%
        ['nama' => 'Mapel 60', 'hadir' => 3, 'alpa' => 2], // 3/5 = 60%
        ['nama' => 'Mapel 70', 'hadir' => 7, 'alpa' => 3], // 7/10 = 70%
        ['nama' => 'Mapel 72', 'hadir' => 18, 'alpa' => 7], // 18/25 = 72%
        ['nama' => 'Mapel 75', 'hadir' => 3, 'alpa' => 1], // 3/4 = 75% -> TIDAK MASUK
        ['nama' => 'Mapel 100', 'hadir' => 5, 'alpa' => 0], // 5/5 = 100% -> TIDAK MASUK
    ];

    foreach ($daftarMapelData as $d) {
        $mapel = Mapel::factory()->create(['nama' => $d['nama']]);
        $jadwal = Jadwal::factory()->create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $this->guru->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $total = $d['hadir'] + $d['alpa'];
        for ($k = 0; $k < $total; $k++) {
            $status = ($k < $d['hadir']) ? StatusKehadiran::HADIR : StatusKehadiran::ALPA;
            $sesi = SesiAbsensi::factory()->create([
                'jadwal_id' => $jadwal->id,
                'tanggal' => Carbon::parse('2026-08-01')->addDays($k)->toDateString(),
            ]);
            DetailAbsensi::factory()->create([
                'sesi_absensi_id' => $sesi->id,
                'siswa_id' => $this->siswa->id,
                'status' => $status,
            ]);
        }
    }

    $service = app(SiswaDashboardService::class);
    $perluPerhatian = $service->getMapelPerluPerhatian($this->siswa);

    // Maksimal 5 item
    expect($perluPerhatian->count())->toBe(5);

    // Urut dari paling rendah
    expect($perluPerhatian[0]->mapel)->toBe('Mapel 20');
    expect($perluPerhatian[1]->mapel)->toBe('Mapel 40');
    expect($perluPerhatian[2]->mapel)->toBe('Mapel 50');
    expect($perluPerhatian[3]->mapel)->toBe('Mapel 60');
    expect($perluPerhatian[4]->mapel)->toBe('Mapel 70');

    // Mapel 72 (ke-6 terendah), Mapel 75 (tepat batas), dan Mapel 100 tidak boleh ada
    $namaMapel = $perluPerhatian->pluck('mapel')->all();
    expect($namaMapel)->not->toContain('Mapel 72');
    expect($namaMapel)->not->toContain('Mapel 75');
    expect($namaMapel)->not->toContain('Mapel 100');

    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Perlu Perhatian');
    $response->assertSee('Mapel 20');
    $response->assertSee('20%');
    $response->assertDontSee('Mapel 75%');
});

test('siswa tanpa profil siswa dan kelas tanpa jadwal: halaman 200 dan keadaan kosong ramah', function () {
    // Siswa tanpa profil record siswa
    $userTanpaSiswa = User::factory()->create(['role' => 'siswa']);

    $response = $this->actingAs($userTanpaSiswa)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Semua pertemuan 7 hari terakhir sudah dicatat.');
    $response->assertSee('Belum ada catatan minggu ini');
    $response->assertSee('Semua mata pelajaran aman.');

    // Siswa dengan kelas tapi kelas tanpa jadwal
    $userKelasKosong = User::factory()->create(['role' => 'siswa']);
    $kelasKosong = Kelas::factory()->create(['tahun_ajaran' => $this->tahunAjaran, 'semester' => $this->semester]);
    Siswa::factory()->create([
        'user_id' => $userKelasKosong->id,
        'kelas_id' => $kelasKosong->id,
    ]);

    $response2 = $this->actingAs($userKelasKosong)->get(route('siswa.dashboard'));
    $response2->assertStatus(200);
    $response2->assertSee('Semua pertemuan 7 hari terakhir sudah dicatat.');
    $response2->assertSee('Belum ada catatan minggu ini');
    $response2->assertSee('Semua mata pelajaran aman.');
});

test('data siswa lain tidak bocor ke ringkasan minggu ini atau perlu perhatian (AB-08)', function () {
    $userLain = User::factory()->create(['role' => 'siswa']);
    $siswaLain = Siswa::factory()->create([
        'user_id' => $userLain->id,
        'kelas_id' => $this->kelas->id,
    ]);

    $mapel = Mapel::factory()->create(['nama' => 'Fisika Rahasia']);
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => $this->tahunAjaran,
        'semester' => $this->semester,
    ]);

    // Siswa lain punya 5 sesi alpa di tanggal berbeda
    for ($k = 0; $k < 5; $k++) {
        $sesi = SesiAbsensi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'tanggal' => Carbon::parse('2026-10-01')->addDays($k)->toDateString(),
        ]);
        DetailAbsensi::factory()->create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $siswaLain->id,
            'status' => StatusKehadiran::ALPA,
        ]);
    }

    // Siswa utama kita tidak punya catatan untuk Fisika Rahasia
    $service = app(SiswaDashboardService::class);
    $perluPerhatian = $service->getMapelPerluPerhatian($this->siswa);
    $ringkasan = $service->getRingkasanMingguIni($this->siswa, Carbon::now('Asia/Jakarta'));

    expect($perluPerhatian->pluck('mapel'))->not->toContain('Fisika Rahasia');
    expect($ringkasan['total_alpa'])->toBe(0);
    expect($ringkasan['has_data'])->toBeFalse();
});

test('performa: jumlah query dashboard siswa konstan tanpa N+1', function () {
    Carbon::setTestNow('2026-11-04 12:00:00');

    // Buat beberapa mapel dan jadwal untuk kelas siswa
    $mapels = Mapel::factory()->count(4)->create();
    foreach ($mapels as $m) {
        $jadwal = Jadwal::factory()->create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $m->id,
            'guru_id' => $this->guru->id,
            'hari' => 'rabu',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:00',
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $sesi = SesiAbsensi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'tanggal' => '2026-11-04',
        ]);

        DetailAbsensi::factory()->create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $this->siswa->id,
            'status' => StatusKehadiran::HADIR,
        ]);
    }

    DB::enableQueryLog();

    $response = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response->assertStatus(200);

    $queriesBase = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Tambah 4 jadwal dan sesi baru lagi
    $mapelsBaru = Mapel::factory()->count(4)->create();
    foreach ($mapelsBaru as $m) {
        $jadwal = Jadwal::factory()->create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $m->id,
            'guru_id' => $this->guru->id,
            'hari' => 'selasa',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:00',
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $sesi = SesiAbsensi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'tanggal' => '2026-11-03',
        ]);

        DetailAbsensi::factory()->create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $this->siswa->id,
            'status' => StatusKehadiran::HADIR,
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response2 = $this->actingAs($this->user)->get(route('siswa.dashboard'));
    $response2->assertStatus(200);

    $queriesAfter = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Jumlah query harus identik atau selisih maksimal 0 (karena tidak ada perulangan query per data)
    expect($queriesAfter)->toBeLessThanOrEqual($queriesBase);
});
