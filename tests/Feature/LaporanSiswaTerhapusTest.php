<?php

use App\Exports\LaporanAbsensiPerKelasSheet;
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
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

beforeEach(function () {
    Pengaturan::updateOrCreate(['kunci' => 'tahun_ajaran_aktif'], ['nilai' => '2026/2027']);
    Pengaturan::updateOrCreate(['kunci' => 'semester_aktif'], ['nilai' => 'Ganjil']);

    $this->admin = User::factory()->create(['role' => 'admin']);

    $this->guruUser = User::factory()->create([
        'name' => 'Pak Budi Guru',
        'role' => 'guru',
    ]);
    $this->guru = Guru::factory()->create([
        'user_id' => $this->guruUser->id,
        'nip' => '198001012005011001',
    ]);

    $this->jurusan = Jurusan::create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);
    $this->kelas = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 'X',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->mapel = Mapel::create(['nama' => 'Pemrograman Dasar', 'kode' => 'PROGDAS']);

    $this->jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('AB-05: 1. Siswa terhapus yang punya riwayat di kelas-mapel tampil di riwayat detail guru dengan tanda (nonaktif) dan kehadiran lamanya utuh; siswa terhapus tanpa riwayat di kelas-mapel itu tidak tampil', function () {
    // 1. Siswa aktif
    $userAktif = User::factory()->create(['name' => 'Siswa Aktif', 'role' => 'siswa']);
    $siswaAktif = Siswa::create(['user_id' => $userAktif->id, 'nis' => '1001', 'kelas_id' => $this->kelas->id]);

    // 2. Siswa terhapus yang punya riwayat di kelas-mapel ini
    $userTrashedRiwayat = User::factory()->create(['name' => 'Siswa Terhapus Riwayat', 'role' => 'siswa']);
    $siswaTrashedRiwayat = Siswa::create(['user_id' => $userTrashedRiwayat->id, 'nis' => '1002', 'kelas_id' => $this->kelas->id]);

    // 3. Siswa terhapus TANPA riwayat di kelas-mapel ini
    $userTrashedTanpa = User::factory()->create(['name' => 'Siswa Terhapus Tanpa Riwayat', 'role' => 'siswa']);
    $siswaTrashedTanpa = Siswa::create(['user_id' => $userTrashedTanpa->id, 'nis' => '1003', 'kelas_id' => $this->kelas->id]);

    // Buat 2 sesi
    $sesi1 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    $sesi2 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-10',
        'diabsen_oleh' => $this->guruUser->id,
    ]);

    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $siswaAktif->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswaAktif->id, 'status' => 'hadir']);

    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $siswaTrashedRiwayat->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswaTrashedRiwayat->id, 'status' => 'izin']);

    // Soft delete siswa 2 dan siswa 3
    $siswaTrashedRiwayat->delete();
    $siswaTrashedTanpa->delete();

    $response = $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));

    $response->assertStatus(200);

    // Siswa aktif tampil normal
    $response->assertSee('Siswa Aktif');
    $response->assertSee('1001');

    // Siswa terhapus dengan riwayat tampil dengan tanda (nonaktif)
    $response->assertSee('Siswa Terhapus Riwayat');
    $response->assertSee('1002');
    $response->assertSee('(nonaktif)');

    // Kehadiran lamanya utuh (P1 = H, P2 = I)
    $response->assertSee('H');
    $response->assertSee('I');

    // Siswa terhapus tanpa riwayat TIDAK tampil
    $response->assertDontSee('Siswa Terhapus Tanpa Riwayat');
    $response->assertDontSee('1003');
});

test('AB-05: 2. Excel memuat baris siswa terhapus dengan tanda (nonaktif) dan persentase yang benar; siswa terhapus tanpa riwayat tidak ada', function () {
    $userAktif = User::factory()->create(['name' => 'Siswa Aktif', 'role' => 'siswa']);
    $siswaAktif = Siswa::create(['user_id' => $userAktif->id, 'nis' => '1001', 'kelas_id' => $this->kelas->id]);

    $userTrashedRiwayat = User::factory()->create(['name' => 'Siswa Terhapus Riwayat', 'role' => 'siswa']);
    $siswaTrashedRiwayat = Siswa::create(['user_id' => $userTrashedRiwayat->id, 'nis' => '1002', 'kelas_id' => $this->kelas->id]);

    $userTrashedTanpa = User::factory()->create(['name' => 'Siswa Terhapus Tanpa Riwayat', 'role' => 'siswa']);
    $siswaTrashedTanpa = Siswa::create(['user_id' => $userTrashedTanpa->id, 'nis' => '1003', 'kelas_id' => $this->kelas->id]);

    // 4 sesi: Hadir (1), Izin (1), Sakit (1), Alpa (1) -> 75%
    $sesis = [];
    foreach (['2026-08-03', '2026-08-10', '2026-08-17', '2026-08-24'] as $tgl) {
        $sesis[] = SesiAbsensi::create([
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => $tgl,
            'diabsen_oleh' => $this->guruUser->id,
        ]);
    }

    // Detail siswa aktif (4 Hadir = 100%)
    foreach ($sesis as $sesi) {
        DetailAbsensi::create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $siswaAktif->id, 'status' => 'hadir']);
    }

    // Detail siswa terhapus (H, I, S, A)
    DetailAbsensi::create(['sesi_absensi_id' => $sesis[0]->id, 'siswa_id' => $siswaTrashedRiwayat->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesis[1]->id, 'siswa_id' => $siswaTrashedRiwayat->id, 'status' => 'izin']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesis[2]->id, 'siswa_id' => $siswaTrashedRiwayat->id, 'status' => 'sakit']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesis[3]->id, 'siswa_id' => $siswaTrashedRiwayat->id, 'status' => 'alpa']);

    $siswaTrashedRiwayat->delete();
    $siswaTrashedTanpa->delete();

    $sheet = new LaporanAbsensiPerKelasSheet($this->guru->id, $this->kelas->id, $this->mapel->id, [
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $data = $sheet->array();

    // 1. Cek baris siswa aktif
    $rowAktif = collect($data)->first(fn ($r) => is_array($r) && isset($r[2]) && $r[2] === 'Siswa Aktif');
    expect($rowAktif)->not->toBeNull();
    expect(end($rowAktif))->toBe(1.0); // 100%

    // 2. Cek baris siswa terhapus (nama memuat (nonaktif) polos)
    $rowTrashed = collect($data)->first(fn ($r) => is_array($r) && isset($r[2]) && $r[2] === 'Siswa Terhapus Riwayat (nonaktif)');
    expect($rowTrashed)->not->toBeNull();
    // Persentase = 3/4 = 75%
    expect(end($rowTrashed))->toBe(0.75);

    // 3. Siswa terhapus tanpa riwayat TIDAK ada di data
    $rowTanpa = collect($data)->first(fn ($r) => is_array($r) && isset($r[2]) && str_contains(strval($r[2]), 'Siswa Terhapus Tanpa Riwayat'));
    expect($rowTanpa)->toBeNull();
});

test('AB-05: 3. PDF admin memuat siswa terhapus yang punya riwayat dengan tanda yang sama', function () {
    $userAktif = User::factory()->create(['name' => 'Siswa Aktif PDF', 'role' => 'siswa']);
    $siswaAktif = Siswa::create(['user_id' => $userAktif->id, 'nis' => '2001', 'kelas_id' => $this->kelas->id]);

    $userTrashedRiwayat = User::factory()->create(['name' => 'Siswa Terhapus PDF', 'role' => 'siswa']);
    $siswaTrashedRiwayat = Siswa::create(['user_id' => $userTrashedRiwayat->id, 'nis' => '2002', 'kelas_id' => $this->kelas->id]);

    $userTrashedTanpa = User::factory()->create(['name' => 'Siswa Terhapus Tanpa PDF', 'role' => 'siswa']);
    $siswaTrashedTanpa = Siswa::create(['user_id' => $userTrashedTanpa->id, 'nis' => '2003', 'kelas_id' => $this->kelas->id]);

    $sesi = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $this->guruUser->id,
    ]);

    DetailAbsensi::create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $siswaAktif->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $siswaTrashedRiwayat->id, 'status' => 'hadir']);

    $siswaTrashedRiwayat->delete();
    $siswaTrashedTanpa->delete();

    $absensiService = app(AbsensiService::class);
    $data = $absensiService->getRekapLaporan([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Data query memuat siswa terhapus dengan flag is_nonaktif = 1
    $itemTrashed = $data->firstWhere('siswa_id', $siswaTrashedRiwayat->id);
    expect($itemTrashed)->not->toBeNull();
    expect((int) $itemTrashed->is_nonaktif)->toBe(1);

    // Data query TIDAK memuat siswa terhapus tanpa riwayat
    $itemTanpa = $data->firstWhere('siswa_id', $siswaTrashedTanpa->id);
    expect($itemTanpa)->toBeNull();

    // Render view PDF memuat tanda (nonaktif)
    $view = $this->view('laporan.pdf', [
        'data' => $data,
        'filters' => [],
        'periode' => 'TA 2026/2027 Semester Ganjil',
    ]);

    $view->assertSee('Siswa Terhapus PDF');
    $view->assertSee('(nonaktif)');
    $view->assertDontSee('Siswa Terhapus Tanpa PDF');

    // Endpoint download PDF admin berhasil dengan HTTP 200
    $response = $this->actingAs($this->admin)->get(route('admin.laporan.exportPdf', [
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('AB-05: 4. Persentase siswa terhapus identik di riwayat, Excel, dan PDF untuk data yang sama', function () {
    $user = User::factory()->create(['name' => 'Siswa Hitung Identik', 'role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $user->id, 'nis' => '3001', 'kelas_id' => $this->kelas->id]);

    // 4 sesi: 2 Hadir, 1 Izin, 1 Alpa -> persentase = (2+1)/4 * 100 = 75%
    $sesi1 = SesiAbsensi::create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-08-03', 'diabsen_oleh' => $this->guruUser->id]);
    $sesi2 = SesiAbsensi::create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-08-10', 'diabsen_oleh' => $this->guruUser->id]);
    $sesi3 = SesiAbsensi::create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-08-17', 'diabsen_oleh' => $this->guruUser->id]);
    $sesi4 = SesiAbsensi::create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-08-24', 'diabsen_oleh' => $this->guruUser->id]);

    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $siswa->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswa->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi3->id, 'siswa_id' => $siswa->id, 'status' => 'izin']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi4->id, 'siswa_id' => $siswa->id, 'status' => 'alpa']);

    $siswa->delete();

    // 1. Riwayat Detail Guru: cek persentase pada tampilan HTML
    $resRiwayat = $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));
    $resRiwayat->assertStatus(200);
    $resRiwayat->assertSee('75%');

    // 2. Excel: cek nilai numerik kolom %
    $sheet = new LaporanAbsensiPerKelasSheet($this->guru->id, $this->kelas->id, $this->mapel->id, [
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $dataExcel = $sheet->array();
    $rowExcel = collect($dataExcel)->first(fn ($r) => is_array($r) && isset($r[2]) && str_contains(strval($r[2]), 'Siswa Hitung Identik'));
    expect($rowExcel)->not->toBeNull();
    $percentExcel = end($rowExcel);
    expect($percentExcel)->toBe(0.75); // 75%

    // 3. PDF: cek persentase yang dihitung untuk data row
    $absensiService = app(AbsensiService::class);
    $dataPdf = $absensiService->getRekapLaporan([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $rowPdf = $dataPdf->firstWhere('siswa_id', $siswa->id);
    expect($rowPdf)->not->toBeNull();
    $persenPdf = $absensiService->hitungPersentaseKehadiran(
        (int) $rowPdf->hadir,
        (int) $rowPdf->izin,
        (int) $rowPdf->sakit,
        (int) $rowPdf->total_sesi
    );
    expect($persenPdf)->toBe(75.0);
});

test('AB-05: 5. Header Excel menampilkan nama guru yang sudah terhapus (bukan -)', function () {
    // Soft delete guru
    $this->guru->delete();

    $sheet = new LaporanAbsensiPerKelasSheet($this->guru->id, $this->kelas->id, $this->mapel->id, [
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $data = $sheet->array();

    // Baris ke-4 adalah baris Guru: ['Guru', '', ': ' . $this->guruNama]
    $rowGuru = $data[3];
    expect($rowGuru[0])->toBe('Guru');
    expect($rowGuru[2])->toBe(': Pak Budi Guru');
    expect($rowGuru[2])->not->toBe(': -');
});

test('AB-05: 6. Siswa aktif tidak berubah di ketiga jalur, dan form absensi untuk sesi baru tetap menyembunyikan siswa terhapus', function () {
    Carbon::setTestNow('2026-08-10 08:00:00'); // Senin

    $userAktif = User::factory()->create(['name' => 'Siswa Selalu Aktif', 'role' => 'siswa']);
    $siswaAktif = Siswa::create(['user_id' => $userAktif->id, 'nis' => '4001', 'kelas_id' => $this->kelas->id]);

    $userTrashed = User::factory()->create(['name' => 'Siswa Disembunyikan Form', 'role' => 'siswa']);
    $siswaTrashed = Siswa::create(['user_id' => $userTrashed->id, 'nis' => '4002', 'kelas_id' => $this->kelas->id]);
    $siswaTrashed->delete();

    // Buat 1 sesi absensi
    $sesi = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $siswaAktif->id, 'status' => 'hadir']);

    // 1. Form absensi sesi baru: siswa terhapus tidak muncul
    $resForm = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', $this->jadwal->id));
    $resForm->assertStatus(200);
    $resForm->assertSee('Siswa Selalu Aktif');
    $resForm->assertSee('4001');
    $resForm->assertDontSee('Siswa Disembunyikan Form');
    $resForm->assertDontSee('4002');

    // 2. Di riwayat detail guru: siswa aktif tetap tampil normal tanpa tanda (nonaktif)
    $resRiwayat = $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));
    $resRiwayat->assertStatus(200);
    $resRiwayat->assertSee('Siswa Selalu Aktif');
    $resRiwayat->assertDontSee('Siswa Selalu Aktif (nonaktif)');
    $resRiwayat->assertDontSee('Siswa Disembunyikan Form');

    // 3. Di Excel: siswa aktif tampil tanpa tanda (nonaktif)
    $sheet = new LaporanAbsensiPerKelasSheet($this->guru->id, $this->kelas->id, $this->mapel->id, [
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $dataExcel = $sheet->array();
    $rowAktif = collect($dataExcel)->first(fn ($r) => is_array($r) && isset($r[2]) && $r[2] === 'Siswa Selalu Aktif');
    expect($rowAktif)->not->toBeNull();
    $rowTrashed = collect($dataExcel)->first(fn ($r) => is_array($r) && isset($r[2]) && str_contains(strval($r[2]), 'Siswa Disembunyikan Form'));
    expect($rowTrashed)->toBeNull();
});

test('AB-05: 7. Filter bulan: siswa terhapus hanya muncul jika punya detail pada bulan yang difilter', function () {
    $userAgustus = User::factory()->create(['name' => 'Siswa Trashed Agustus', 'role' => 'siswa']);
    $siswaAgustus = Siswa::create(['user_id' => $userAgustus->id, 'nis' => '5001', 'kelas_id' => $this->kelas->id]);

    $userSeptember = User::factory()->create(['name' => 'Siswa Trashed September', 'role' => 'siswa']);
    $siswaSeptember = Siswa::create(['user_id' => $userSeptember->id, 'nis' => '5002', 'kelas_id' => $this->kelas->id]);

    // Sesi bulan Agustus (2026-08-03)
    $sesiAgustus = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesiAgustus->id, 'siswa_id' => $siswaAgustus->id, 'status' => 'hadir']);

    // Sesi bulan September (2026-09-07)
    $sesiSeptember = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-09-07',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesiSeptember->id, 'siswa_id' => $siswaSeptember->id, 'status' => 'hadir']);

    // Soft delete kedua siswa
    $siswaAgustus->delete();
    $siswaSeptember->delete();

    // 1. Filter Excel bulan 2026-08: hanya Siswa Trashed Agustus yang muncul
    $sheetAgustus = new LaporanAbsensiPerKelasSheet($this->guru->id, $this->kelas->id, $this->mapel->id, [
        'bulan' => '2026-08',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $dataAgustus = $sheetAgustus->array();
    $foundAgustusInAgustus = collect($dataAgustus)->contains(fn ($r) => is_array($r) && isset($r[2]) && str_contains(strval($r[2]), 'Siswa Trashed Agustus'));
    $foundSeptemberInAgustus = collect($dataAgustus)->contains(fn ($r) => is_array($r) && isset($r[2]) && str_contains(strval($r[2]), 'Siswa Trashed September'));
    expect($foundAgustusInAgustus)->toBeTrue();
    expect($foundSeptemberInAgustus)->toBeFalse();

    // 2. Filter Excel bulan 2026-09: hanya Siswa Trashed September yang muncul
    $sheetSeptember = new LaporanAbsensiPerKelasSheet($this->guru->id, $this->kelas->id, $this->mapel->id, [
        'bulan' => '2026-09',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $dataSeptember = $sheetSeptember->array();
    $foundAgustusInSeptember = collect($dataSeptember)->contains(fn ($r) => is_array($r) && isset($r[2]) && str_contains(strval($r[2]), 'Siswa Trashed Agustus'));
    $foundSeptemberInSeptember = collect($dataSeptember)->contains(fn ($r) => is_array($r) && isset($r[2]) && str_contains(strval($r[2]), 'Siswa Trashed September'));
    expect($foundAgustusInSeptember)->toBeFalse();
    expect($foundSeptemberInSeptember)->toBeTrue();

    // 3. Filter PDF (getRekapLaporan) bulan 2026-08
    $absensiService = app(AbsensiService::class);
    $dataPdfAgustus = $absensiService->getRekapLaporan([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'bulan' => '2026-08',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    expect($dataPdfAgustus->contains('siswa_id', $siswaAgustus->id))->toBeTrue();
    expect($dataPdfAgustus->contains('siswa_id', $siswaSeptember->id))->toBeFalse();
});
