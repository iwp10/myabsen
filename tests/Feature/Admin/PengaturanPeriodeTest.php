<?php

use App\Exports\LaporanAbsensiExport;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Pengaturan;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AbsensiService;
use App\Services\PeriodeService;
use Carbon\Carbon;

test('AB-11: tanpa data di tabel pengaturan, periode aktif = saran dari tanggal', function () {
    Pengaturan::truncate();
    $service = app(PeriodeService::class);

    // Juli (Bulan 7): Ganjil, year / (year + 1)
    Carbon::setTestNow('2026-07-15 10:00:00');
    $periodeJuli = $service->getActivePeriode();
    expect($periodeJuli['semester'])->toBe('Ganjil');
    expect($periodeJuli['tahun_ajaran'])->toBe('2026/2027');

    // Desember (Bulan 12): Ganjil, year / (year + 1)
    Carbon::setTestNow('2026-12-20 10:00:00');
    $periodeDes = $service->getActivePeriode();
    expect($periodeDes['semester'])->toBe('Ganjil');
    expect($periodeDes['tahun_ajaran'])->toBe('2026/2027');

    // Januari (Bulan 1): Genap, (year - 1) / year
    Carbon::setTestNow('2027-01-10 10:00:00');
    $periodeJan = $service->getActivePeriode();
    expect($periodeJan['semester'])->toBe('Genap');
    expect($periodeJan['tahun_ajaran'])->toBe('2026/2027');

    // Juni (Bulan 6): Genap, (year - 1) / year
    Carbon::setTestNow('2027-06-25 10:00:00');
    $periodeJun = $service->getActivePeriode();
    expect($periodeJun['semester'])->toBe('Genap');
    expect($periodeJun['tahun_ajaran'])->toBe('2026/2027');
});

test('AB-11: admin bisa mengganti periode aktif, guru dan siswa tidak bisa mengakses halaman pengaturan (403), tamu dialihkan ke login', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $guru = User::factory()->create(['role' => 'guru']);
    $siswa = User::factory()->create(['role' => 'siswa']);

    // Tamu dialihkan ke login
    $this->get(route('admin.pengaturan-periode.index'))
        ->assertRedirect(route('login'));
    $this->post(route('admin.pengaturan-periode.update'), [
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ])->assertRedirect(route('login'));

    // Guru dilarang (403)
    $this->actingAs($guru)
        ->get(route('admin.pengaturan-periode.index'))
        ->assertStatus(403);
    $this->actingAs($guru)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Genap',
        ])->assertStatus(403);

    // Siswa dilarang (403)
    $this->actingAs($siswa)
        ->get(route('admin.pengaturan-periode.index'))
        ->assertStatus(403);
    $this->actingAs($siswa)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Genap',
        ])->assertStatus(403);

    // Admin bisa akses halaman
    $this->actingAs($admin)
        ->get(route('admin.pengaturan-periode.index'))
        ->assertStatus(200)
        ->assertSee('Pengaturan Periode Aktif');

    // Admin bisa mengganti periode aktif
    $response = $this->actingAs($admin)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2027/2028',
            'semester' => 'Genap',
        ]);

    $response->assertRedirect(route('admin.pengaturan-periode.index'));
    $response->assertSessionHas('status', 'Periode aktif berhasil diperbarui.');

    $this->assertDatabaseHas('pengaturan', [
        'kunci' => 'tahun_ajaran_aktif',
        'nilai' => '2027/2028',
    ]);
    $this->assertDatabaseHas('pengaturan', [
        'kunci' => 'semester_aktif',
        'nilai' => 'Genap',
    ]);

    $active = app(PeriodeService::class)->getActivePeriode();
    expect($active['tahun_ajaran'])->toBe('2027/2028');
    expect($active['semester'])->toBe('Genap');
});

test('AB-11: validasi tahun ajaran format salah atau tahun kedua bukan pertama+1, dan semester selain Ganjil/Genap, ditolak', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Format tahun ajaran bukan YYYY/YYYY
    $this->actingAs($admin)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2026-2027',
            'semester' => 'Ganjil',
        ])
        ->assertSessionHasErrors(['tahun_ajaran']);

    // Tahun kedua bukan pertama + 1 (misal 2026/2028)
    $this->actingAs($admin)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2026/2028',
            'semester' => 'Ganjil',
        ])
        ->assertSessionHasErrors(['tahun_ajaran']);

    // Tahun kedua sama dengan pertama (misal 2026/2026)
    $this->actingAs($admin)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2026/2026',
            'semester' => 'Ganjil',
        ])
        ->assertSessionHasErrors(['tahun_ajaran']);

    // Semester selain Ganjil/Genap (misal 'Pendek')
    $this->actingAs($admin)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Pendek',
        ])
        ->assertSessionHasErrors(['semester']);

    // Semester kosong
    $this->actingAs($admin)
        ->post(route('admin.pengaturan-periode.update'), [
            'tahun_ajaran' => '2026/2027',
            'semester' => '',
        ])
        ->assertSessionHasErrors(['semester']);
});

test('AB-11: setelah periode diganti ke Genap, dashboard guru dan siswa hanya menampilkan jadwal Genap, jadwal Ganjil tetap ada di database', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Hari Senin

    $guruUser = User::factory()->create(['role' => 'guru']);
    $guru = Guru::factory()->create(['user_id' => $guruUser->id]);

    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $kelas = Kelas::factory()->create();
    $siswa = Siswa::factory()->create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id]);

    $mapelGanjil = Mapel::factory()->create(['nama' => 'Mapel Ganjil']);
    $mapelGenap = Mapel::factory()->create(['nama' => 'Mapel Genap']);

    $jadwalGanjil = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapelGanjil->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $jadwalGenap = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapelGenap->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);

    // Ganti periode aktif ke Genap
    app(PeriodeService::class)->setPeriodeAktif('2026/2027', 'Genap');

    // Dashboard guru
    $resGuru = $this->actingAs($guruUser)->get(route('guru.dashboard'));
    $resGuru->assertStatus(200);
    $resGuru->assertSee('Mapel Genap');
    $resGuru->assertDontSee('Mapel Ganjil');

    // Dashboard siswa
    $resSiswa = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
    $resSiswa->assertStatus(200);
    $resSiswa->assertSee('Mapel Genap');
    $resSiswa->assertDontSee('Mapel Ganjil');

    // Jadwal ganjil tetap ada di database
    $this->assertDatabaseHas('jadwal', ['id' => $jadwalGanjil->id]);
    $this->assertDatabaseHas('jadwal', ['id' => $jadwalGenap->id]);
});

test('AB-11: menambah jadwal baru semester lain TIDAK mengubah periode aktif (regresi dari bug tebakan jadwal terakhir)', function () {
    app(PeriodeService::class)->setPeriodeAktif('2026/2027', 'Ganjil');

    $kelas = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();

    // Buat jadwal baru dengan semester Genap dan ID lebih besar
    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'tahun_ajaran' => '2027/2028',
        'semester' => 'Genap',
    ]);

    $active = app(PeriodeService::class)->getActivePeriode();
    expect($active['tahun_ajaran'])->toBe('2026/2027');
    expect($active['semester'])->toBe('Ganjil');

    $activeFromAbsensi = app(AbsensiService::class)->getActivePeriode();
    expect($activeFromAbsensi['tahun_ajaran'])->toBe('2026/2027');
    expect($activeFromAbsensi['semester'])->toBe('Ganjil');
});

test('AB-11: rekap/ekspor/PDF memakai periode aktif sebagai default dan filter eksplisit bisa memilih periode lain', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $guruUser = User::factory()->create(['role' => 'guru']);
    $guru = Guru::factory()->create(['user_id' => $guruUser->id]);

    $kelas = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);

    // Jadwal periode aktif (2026/2027 Ganjil)
    $jadwalGanjil = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $sesiGanjil = SesiAbsensi::create([
        'jadwal_id' => $jadwalGanjil->id,
        'tanggal' => '2026-09-15',
        'diabsen_oleh' => $guruUser->id,
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesiGanjil->id,
        'siswa_id' => $siswa->id,
        'status' => 'hadir',
    ]);

    // Jadwal periode lain (2025/2026 Genap)
    $jadwalGenapLama = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);
    $sesiGenapLama = SesiAbsensi::create([
        'jadwal_id' => $jadwalGenapLama->id,
        'tanggal' => '2026-02-10',
        'diabsen_oleh' => $guruUser->id,
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesiGenapLama->id,
        'siswa_id' => $siswa->id,
        'status' => 'alpa',
    ]);

    // Set periode aktif = 2026/2027 Ganjil
    app(PeriodeService::class)->setPeriodeAktif('2026/2027', 'Ganjil');

    // getRekapLaporan tanpa filter periode -> default periode aktif (hanya sesiGanjil yang masuk)
    $rekapDefault = app(AbsensiService::class)->getRekapLaporan(['kelas_id' => $kelas->id]);
    expect($rekapDefault)->toHaveCount(1);
    expect((int) $rekapDefault->first()->hadir)->toBe(1);
    expect((int) $rekapDefault->first()->alpa)->toBe(0);

    // getRekapLaporan dengan filter eksplisit periode lain (2025/2026 Genap)
    $rekapLain = app(AbsensiService::class)->getRekapLaporan([
        'kelas_id' => $kelas->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);
    expect($rekapLain)->toHaveCount(1);
    expect((int) $rekapLain->first()->hadir)->toBe(0);
    expect((int) $rekapLain->first()->alpa)->toBe(1);

    // Ekspor Excel admin: default periode aktif
    $exportDefault = new LaporanAbsensiExport(['kelas_id' => $kelas->id, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $sheetsDefault = $exportDefault->sheets();
    expect($sheetsDefault)->toHaveCount(1);

    // Ekspor PDF: jika filter eksplisit dioper, view PDF menampilkan periode tersebut
    $resPdf = $this->actingAs($admin)->get(route('admin.laporan.exportPdf', [
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]));
    $resPdf->assertStatus(200);
    $resPdf->assertHeader('content-type', 'application/pdf');
});

test('AB-11: dashboard admin menampilkan label periode dan pengingat saat periode yang diset beda dengan saran tanggal', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Di bulan Oktober 2026, saran tanggal = Ganjil 2026/2027
    Carbon::setTestNow('2026-10-05 10:00:00');

    // 1. Kasus SAMA dengan saran: diset Ganjil 2026/2027
    app(PeriodeService::class)->setPeriodeAktif('2026/2027', 'Ganjil');

    $resSama = $this->actingAs($admin)->get(route('admin.dashboard'));
    $resSama->assertStatus(200);
    $resSama->assertSee('Periode aktif: Ganjil 2026/2027');
    $resSama->assertDontSee('Pengingat Periode Akademik');

    // 2. Kasus BERBEDA dengan saran: diset Genap 2025/2026
    app(PeriodeService::class)->setPeriodeAktif('2025/2026', 'Genap');

    $resBeda = $this->actingAs($admin)->get(route('admin.dashboard'));
    $resBeda->assertStatus(200);
    $resBeda->assertSee('Periode aktif: Genap 2025/2026');
    $resBeda->assertSee('Pengingat Periode Akademik');
    $resBeda->assertSee('Sepertinya sudah masuk semester Ganjil');
    $resBeda->assertSee(route('admin.pengaturan-periode.index'));
});

test('AB-11: dashboard siswa tidak lagi mencampur jadwal lintas periode', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $kelas = Kelas::factory()->create();
    $siswa = Siswa::factory()->create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id]);

    $guru = Guru::factory()->create();
    $mapel1 = Mapel::factory()->create(['nama' => 'Mapel Ganjil']);
    $mapel2 = Mapel::factory()->create(['nama' => 'Mapel Genap']);

    // Jadwal Senin Ganjil
    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel1->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal Senin Genap di jam yang sama pada kelas yang sama
    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel2->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);

    // Set periode aktif = Ganjil
    app(PeriodeService::class)->setPeriodeAktif('2026/2027', 'Ganjil');

    $statusListGanjil = app(AbsensiService::class)->getStatusHariIniSiswa($siswa, Carbon::now('Asia/Jakarta'));
    expect($statusListGanjil)->toHaveCount(1);
    expect($statusListGanjil->first()['jadwal']->mapel->nama)->toBe('Mapel Ganjil');

    $resGanjil = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
    $resGanjil->assertSee('Mapel Ganjil');
    $resGanjil->assertDontSee('Mapel Genap');

    // Ubah ke Genap
    app(PeriodeService::class)->setPeriodeAktif('2026/2027', 'Genap');

    $statusListGenap = app(AbsensiService::class)->getStatusHariIniSiswa($siswa, Carbon::now('Asia/Jakarta'));
    expect($statusListGenap)->toHaveCount(1);
    expect($statusListGenap->first()['jadwal']->mapel->nama)->toBe('Mapel Genap');

    $resGenap = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
    $resGenap->assertSee('Mapel Genap');
    $resGenap->assertDontSee('Mapel Ganjil');
});

test('AB-11: kelas dan jadwal Genap 2025/2026 tampil di daftar admin pada Semua periode dan filter periode itu, tidak tampil pada filter Ganjil 2026/2027', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    app(PeriodeService::class)->setPeriodeAktif('2026/2027', 'Ganjil');

    $jurusan = Jurusan::factory()->create();
    $guru = Guru::factory()->create();
    $mapel = Mapel::factory()->create();

    $kelasGenap = Kelas::factory()->create([
        'nama' => 'TBSM 2 kelas 12',
        'jurusan_id' => $jurusan->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    $kelasGanjil = Kelas::factory()->create([
        'nama' => 'TKJ 1 kelas 12',
        'jurusan_id' => $jurusan->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $jadwalGenap = Jadwal::factory()->create([
        'kelas_id' => $kelasGenap->id,
        'guru_id' => $guru->id,
        'mapel_id' => $mapel->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    $jadwalGanjil = Jadwal::factory()->create([
        'kelas_id' => $kelasGanjil->id,
        'guru_id' => $guru->id,
        'mapel_id' => $mapel->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // A. Daftar Kelas
    // 1. Tanpa filter (bawaan: Semua periode) -> keduanya tampil
    $resKelasAll = $this->actingAs($admin)->get(route('admin.kelas.index'));
    $resKelasAll->assertStatus(200);
    $resKelasAll->assertSee('TBSM 2 kelas 12');
    $resKelasAll->assertSee('TKJ 1 kelas 12');

    // 2. Filter periode 2025/2026 Genap -> hanya kelasGenap yang tampil
    $resKelasGenap = $this->actingAs($admin)->get(route('admin.kelas.index', ['periode' => '2025/2026|Genap']));
    $resKelasGenap->assertStatus(200);
    $resKelasGenap->assertSee('TBSM 2 kelas 12');
    $resKelasGenap->assertDontSee('TKJ 1 kelas 12');

    // 3. Filter periode 2026/2027 Ganjil -> kelasGenap tidak tampil
    $resKelasGanjil = $this->actingAs($admin)->get(route('admin.kelas.index', ['periode' => '2026/2027|Ganjil']));
    $resKelasGanjil->assertStatus(200);
    $resKelasGanjil->assertDontSee('TBSM 2 kelas 12');
    $resKelasGanjil->assertSee('TKJ 1 kelas 12');

    // B. Daftar Jadwal
    // 1. Tanpa filter (bawaan: Semua periode) -> keduanya tampil
    $resJadwalAll = $this->actingAs($admin)->get(route('admin.jadwal.index'));
    $resJadwalAll->assertStatus(200);
    $resJadwalAll->assertSee($kelasGenap->nama);
    $resJadwalAll->assertSee($kelasGanjil->nama);

    // 2. Filter periode 2025/2026 Genap -> hanya jadwalGenap tampil
    $resJadwalGenap = $this->actingAs($admin)->get(route('admin.jadwal.index', ['periode' => '2025/2026|Genap']));
    $resJadwalGenap->assertStatus(200);
    $resJadwalGenap->assertSee($kelasGenap->nama);
    $resJadwalGenap->assertDontSee($kelasGanjil->nama);

    // 3. Filter periode 2026/2027 Ganjil -> jadwalGenap tidak tampil
    $resJadwalGanjil = $this->actingAs($admin)->get(route('admin.jadwal.index', ['periode' => '2026/2027|Ganjil']));
    $resJadwalGanjil->assertStatus(200);
    $resJadwalGanjil->assertDontSee($kelasGenap->nama);
    $resJadwalGanjil->assertSee($kelasGanjil->nama);
});

test('AB-11: ekspor Semua kelas memuat sheet dari lebih dari satu jurusan dan periode lain tidak ikut tercampur', function () {
    $jurusanTkj = Jurusan::factory()->create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);
    $jurusanTbsm = Jurusan::factory()->create(['nama' => 'Teknik dan Bisnis Sepeda Motor', 'kode' => 'TBSM']);

    $kelasTkj = Kelas::factory()->create(['nama' => 'XII TKJ 1', 'jurusan_id' => $jurusanTkj->id, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $kelasTbsm = Kelas::factory()->create(['nama' => 'XII TBSM 1', 'jurusan_id' => $jurusanTbsm->id, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $kelasPeriodeLain = Kelas::factory()->create(['nama' => 'X RPL 1', 'tahun_ajaran' => '2025/2026', 'semester' => 'Genap']);

    $guru = Guru::factory()->create();
    $mapel = Mapel::factory()->create(['nama' => 'Matematika']);

    // Jadwal untuk TKJ di 2026/2027 Ganjil
    Jadwal::factory()->create([
        'kelas_id' => $kelasTkj->id,
        'guru_id' => $guru->id,
        'mapel_id' => $mapel->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal untuk TBSM di 2026/2027 Ganjil
    Jadwal::factory()->create([
        'kelas_id' => $kelasTbsm->id,
        'guru_id' => $guru->id,
        'mapel_id' => $mapel->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal untuk kelasPeriodeLain di 2025/2026 Genap
    Jadwal::factory()->create([
        'kelas_id' => $kelasPeriodeLain->id,
        'guru_id' => $guru->id,
        'mapel_id' => $mapel->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    $export = new LaporanAbsensiExport([
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $sheets = $export->sheets();
    expect($sheets)->toHaveCount(2);

    $titles = array_map(fn ($s) => $s->title(), $sheets);
    // Memuat sheet TKJ dan TBSM
    expect(collect($titles)->contains(fn ($t) => str_contains($t, 'XII TKJ 1')))->toBeTrue();
    expect(collect($titles)->contains(fn ($t) => str_contains($t, 'XII TBSM 1')))->toBeTrue();
    // Periode lain tidak ikut tercampur
    expect(collect($titles)->contains(fn ($t) => str_contains($t, 'X RPL 1')))->toBeFalse();
});

test('halaman form jadwal memuat class dark: pada input dan select', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jadwal = Jadwal::factory()->create();

    // Halaman create jadwal
    $resCreate = $this->actingAs($admin)->get(route('admin.jadwal.create'));
    $resCreate->assertStatus(200);
    $resCreate->assertSee('dark:bg-gray-800');
    $resCreate->assertSee('dark:[color-scheme:dark]');
    $resCreate->assertSee('dark:text-gray-300');

    // Halaman edit jadwal
    $resEdit = $this->actingAs($admin)->get(route('admin.jadwal.edit', $jadwal));
    $resEdit->assertStatus(200);
    $resEdit->assertSee('dark:bg-gray-800');
    $resEdit->assertSee('dark:[color-scheme:dark]');
    $resEdit->assertSee('dark:text-gray-300');
});

test('AB-11: halaman Pengaturan Periode memuat modal dengan x-cloak dan tombol pembuka modal bertipe button', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.pengaturan-periode.index'));
    $response->assertStatus(200);
    $response->assertSee('x-cloak', false);
    $response->assertSee('type="button"', false);
    $response->assertSee('@submit.prevent="showConfirmModal = true"', false);
});
