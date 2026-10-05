<?php

use App\Exports\LaporanAbsensiExport;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\AssertionFailedError;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guruUser = User::factory()->create(['role' => 'guru']);
    $this->guru = Guru::factory()->create(['user_id' => $this->guruUser->id]);

    $this->kelas = Kelas::factory()->create();
    $this->mapel = Mapel::factory()->create();
    $this->siswa = Siswa::factory()->create(['kelas_id' => $this->kelas->id]);

    $this->jadwal = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->sesi = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => now()->toDateString(),
        'diabsen_oleh' => $this->guruUser->id,
    ]);

    DetailAbsensi::create([
        'sesi_absensi_id' => $this->sesi->id,
        'siswa_id' => $this->siswa->id,
        'status' => 'hadir',
    ]);
});

test('admin dapat melihat halaman rekap laporan', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.laporan.index'));

    $response->assertStatus(200);
    $response->assertSee('Rekap Laporan Absensi');
});

test('guru tidak dapat melihat halaman rekap laporan admin', function () {
    $response = $this->actingAs($this->guruUser)
        ->get(route('admin.laporan.index'));

    $response->assertStatus(403);
});

test('admin dapat mengunduh laporan excel', function () {
    Excel::fake();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $response->assertStatus(200);

    Excel::assertDownloaded('rekap_absensi_2026-2027_Ganjil.xlsx');
});

test('guru dapat mengunduh laporan excel', function () {
    Excel::fake();

    $response = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $response->assertStatus(200);

    Excel::assertDownloaded('rekap_absensi_guru_2026-2027_Ganjil.xlsx');
});

test('admin dapat mengunduh laporan pdf', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('guru dapat mengunduh laporan pdf', function () {
    $response = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.exportPdf', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('view pdf menampilkan periode dengan benar dan tidak berulang', function () {
    $view = $this->view('laporan.pdf', [
        'data' => collect(),
        'filters' => ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil'],
        'periode' => 'TA 2026/2027 Semester Ganjil',
    ]);

    $view->assertSee('Periode:');
    $view->assertSee('TA 2026/2027 Semester Ganjil');
});

test('filter tidak valid ditolak dengan error validasi', function () {
    // 1. kelas_id tidak ada di database
    $resKelas = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['kelas_id' => 99999]));
    $resKelas->assertSessionHasErrors(['kelas_id']);

    // 2. tahun_ajaran format salah (bukan format YYYY/YYYY)
    $resBulan = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['tahun_ajaran' => '2026-2027']));
    $resBulan->assertSessionHasErrors(['tahun_ajaran']);

    // 3. untuk pdf juga ditolak jika tahun_ajaran format salah
    $resPdf = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['tahun_ajaran' => 'bukan-tahun']));
    $resPdf->assertSessionHasErrors(['tahun_ajaran']);
});

test('guru tidak bisa mengekspor kelas yang bukan diampunya', function () {
    $kelasLain = Kelas::factory()->create();

    // Guru mencoba ekspor excel untuk kelas yang bukan diampunya
    $resExcel = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export', ['kelas_id' => $kelasLain->id]));
    $resExcel->assertStatus(403);

    // Guru mencoba ekspor pdf untuk kelas yang bukan diampunya
    $resPdf = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.exportPdf', ['kelas_id' => $kelasLain->id]));
    $resPdf->assertStatus(403);

    // Guru berhasil mengekspor kelas yang diampunya
    Excel::fake();
    $resOk = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export', ['kelas_id' => $this->kelas->id]));
    $resOk->assertStatus(200);
});

test('nama file hasil ekspor tetap sesuai format sebelum refactor', function () {
    Excel::fake();

    $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));
    Excel::assertDownloaded('rekap_absensi_2026-2027_Ganjil.xlsx');

    $this->actingAs($this->admin)
        ->get(route('admin.laporan.export'));
    Excel::assertDownloaded('rekap_absensi_'.date('Y-m-d').'.xlsx');

    $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));
    Excel::assertDownloaded('rekap_absensi_guru_2026-2027_Ganjil.xlsx');

    $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export'));
    Excel::assertDownloaded('rekap_absensi_guru_'.date('Y-m-d').'.xlsx');

    $resAdminPdf = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));
    $resAdminPdf->assertHeader('content-disposition', 'attachment; filename=rekap_absensi_2026-2027_Ganjil.pdf');

    $resGuruPdf = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.exportPdf', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));
    $resGuruPdf->assertHeader('content-disposition', 'attachment; filename=rekap_absensi_guru_2026-2027_Ganjil.pdf');
});

test('excel hasil ekspor berisi persentase yang sama dengan rumus AB-07 (Hadir+Izin+Sakit)', function () {
    // Siapkan data dengan 1 Hadir, 1 Izin, 1 Sakit, 1 Alpa (total 4 sesi)
    $siswaBaru = Siswa::factory()->create(['kelas_id' => $this->kelas->id]);

    $sesi2 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => now()->subDays(1)->toDateString(),
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    $sesi3 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => now()->subDays(2)->toDateString(),
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    $sesi4 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => now()->subDays(3)->toDateString(),
        'diabsen_oleh' => $this->guruUser->id,
    ]);

    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi->id, 'siswa_id' => $siswaBaru->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswaBaru->id, 'status' => 'izin']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi3->id, 'siswa_id' => $siswaBaru->id, 'status' => 'sakit']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi4->id, 'siswa_id' => $siswaBaru->id, 'status' => 'alpa']);

    $export = new LaporanAbsensiExport(['kelas_id' => $this->kelas->id]);
    $sheets = $export->sheets();
    $this->assertNotEmpty($sheets);

    $sheet = $sheets[0];
    $data = $sheet->array();

    // Find the row for $siswaBaru
    $found = false;
    foreach ($data as $row) {
        if (is_array($row) && count($row) > 3 && str_contains(strval($row[2]), $siswaBaru->user->name)) {
            $found = true;
            // Percent is the last column
            $percent = end($row);
            expect($percent)->toBe(0.75); // 3/4 = 75%
            break;
        }
    }

    expect($found)->toBeTrue();
});

test('ekspor melebihi batas sheet ditolak dengan pesan error ramah dan tidak menghasilkan file, di bawah batas berhasil', function () {
    Excel::fake();

    // Buat jadwal tambahan agar total sheet ada 2 (kombinasi kelas-mapel)
    $mapel2 = Mapel::factory()->create();
    Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel2->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Set batas sheet menjadi 1
    config(['absensi.batas_sheet_ekspor' => 1]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Ekspor mencakup 2 sheet, melebihi batas 1. Silakan pilih jurusan atau kelas tertentu.');
    expect(fn () => Excel::assertDownloaded('rekap_absensi_2026-2027_Ganjil.xlsx'))
        ->toThrow(AssertionFailedError::class);

    // Kembalikan batas ke 50, ekspor harus berhasil
    config(['absensi.batas_sheet_ekspor' => 50]);

    $responseSuccess = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $responseSuccess->assertStatus(200);
    Excel::assertDownloaded('rekap_absensi_2026-2027_Ganjil.xlsx');
});

test('filter jurusan hanya memuat sheet dari jurusan itu dan kombinasi jurusan+kelas tidak cocok menghasilkan pesan kosong', function () {
    $jurusanTKJ = Jurusan::factory()->create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);
    $jurusanTBSM = Jurusan::factory()->create(['nama' => 'Teknik Bisnis Sepeda Motor', 'kode' => 'TBSM']);

    $kelasTKJ = Kelas::factory()->create(['jurusan_id' => $jurusanTKJ->id, 'nama' => 'X TKJ 1']);
    $kelasTBSM = Kelas::factory()->create(['jurusan_id' => $jurusanTBSM->id, 'nama' => 'X TBSM 1']);

    $mapel = Mapel::factory()->create(['nama' => 'Matematika']);

    Jadwal::factory()->create([
        'kelas_id' => $kelasTKJ->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    Jadwal::factory()->create([
        'kelas_id' => $kelasTBSM->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // 1. Filter hanya jurusan TKJ
    $exportTKJ = new LaporanAbsensiExport([
        'jurusan_id' => $jurusanTKJ->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $sheetsTKJ = $exportTKJ->sheets();
    expect(count($sheetsTKJ))->toBe(1);
    expect($sheetsTKJ[0]->title())->toContain('X TKJ 1');

    // 2. Filter jurusan TKJ tetapi kelas TBSM (kombinasi tidak cocok)
    $exportMismatch = new LaporanAbsensiExport([
        'jurusan_id' => $jurusanTKJ->id,
        'kelas_id' => $kelasTBSM->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $sheetsMismatch = $exportMismatch->sheets();
    expect(count($sheetsMismatch))->toBe(1);
    expect($sheetsMismatch[0]->title())->toBe('Data Kosong');
    $dataMismatch = $sheetsMismatch[0]->array();
    expect($dataMismatch[0][0])->toContain('Kelas yang dipilih tidak sesuai dengan jurusan yang dipilih');
});

test('jumlah query ekspor tidak bertambah proporsional dengan jumlah siswa', function () {
    $kelasUji = Kelas::factory()->create();
    $mapelUji = Mapel::factory()->create();
    $jadwalUji = Jadwal::factory()->create([
        'kelas_id' => $kelasUji->id,
        'mapel_id' => $mapelUji->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $sesiUji = SesiAbsensi::create([
        'jadwal_id' => $jadwalUji->id,
        'tanggal' => now()->toDateString(),
        'diabsen_oleh' => $this->guruUser->id,
    ]);

    // Buat 2 siswa pertama
    for ($i = 1; $i <= 2; $i++) {
        $u = User::factory()->create(['role' => 'siswa']);
        $s = Siswa::factory()->create(['user_id' => $u->id, 'kelas_id' => $kelasUji->id]);
        DetailAbsensi::create(['sesi_absensi_id' => $sesiUji->id, 'siswa_id' => $s->id, 'status' => 'hadir']);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $export1 = new LaporanAbsensiExport(['kelas_id' => $kelasUji->id, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $sheets1 = $export1->sheets();
    foreach ($sheets1 as $sheet) {
        $sheet->array();
    }
    $queryCount2Siswa = count(DB::getQueryLog());

    // Tambah 10 siswa baru (total 12 siswa)
    for ($i = 3; $i <= 12; $i++) {
        $u = User::factory()->create(['role' => 'siswa']);
        $s = Siswa::factory()->create(['user_id' => $u->id, 'kelas_id' => $kelasUji->id]);
        DetailAbsensi::create(['sesi_absensi_id' => $sesiUji->id, 'siswa_id' => $s->id, 'status' => 'hadir']);
    }

    DB::flushQueryLog();

    $export2 = new LaporanAbsensiExport(['kelas_id' => $kelasUji->id, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $sheets2 = $export2->sheets();
    foreach ($sheets2 as $sheet) {
        $sheet->array();
    }
    $queryCount12Siswa = count(DB::getQueryLog());

    // Jumlah query harus sama persis (tidak bertambah seiring bertambahnya siswa)
    expect($queryCount12Siswa)->toBe($queryCount2Siswa);
});

test('ekspor guru tetap hanya berisi sesi miliknya (AB-08) dan format nama file tidak berubah', function () {
    Excel::fake();

    // Buat guru lain dengan jadwal di kelas yang sama
    $guruLainUser = User::factory()->create(['role' => 'guru']);
    $guruLain = Guru::factory()->create(['user_id' => $guruLainUser->id]);
    $jadwalLain = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $guruLain->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $sesiLain = SesiAbsensi::create([
        'jadwal_id' => $jadwalLain->id,
        'tanggal' => now()->subDay()->toDateString(),
        'diabsen_oleh' => $guruLainUser->id,
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesiLain->id,
        'siswa_id' => $this->siswa->id,
        'status' => 'hadir',
    ]);

    // Guru pertama ekspor laporan
    $resGuru = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export', ['kelas_id' => $this->kelas->id, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $resGuru->assertStatus(200);
    Excel::assertDownloaded('rekap_absensi_guru_2026-2027_Ganjil.xlsx');

    // Verifikasi objek export hanya memuat sesi milik guru pertama
    $export = new LaporanAbsensiExport([
        'guru_id' => $this->guru->id,
        'kelas_id' => $this->kelas->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $sheets = $export->sheets();
    expect(count($sheets))->toBe(1);
    $data = $sheets[0]->array();
    // Baris header pertemuan (P1)
    $headerRow = $data[6]; // P1
    // Guru pertama hanya punya 1 sesi ($this->sesi), sesi guru lain tidak masuk
    expect(in_array('P1', $headerRow))->toBeTrue();
    expect(in_array('P2', $headerRow))->toBeFalse();
});

test('validasi jurusan_id tidak valid ditolak', function () {
    $resTidakAda = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['jurusan_id' => 99999]));
    $resTidakAda->assertSessionHasErrors(['jurusan_id']);

    $resBukanAngka = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['jurusan_id' => 'abc']));
    $resBukanAngka->assertSessionHasErrors(['jurusan_id']);

    $resPdf = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['jurusan_id' => 99999]));
    $resPdf->assertSessionHasErrors(['jurusan_id']);
});

test('ekspor pdf admin melebihi batas baris ditolak dengan pesan error ramah', function () {
    config(['absensi.batas_baris_pdf' => 0]); // Set batas ke 0 agar pasti terlewati

    $response = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect(session('error'))->toContain('melebihi batas 0');
});

test('halaman laporan admin memuat input filter bulan', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.laporan.index'));

    $response->assertStatus(200);
    $response->assertSee('name="bulan"', false);
    $response->assertSee('type="month"', false);
    $response->assertSee('Bulan');
});

test('filter bulan dengan bulan 13 ditolak dengan error validasi', function () {
    $resExcel = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['bulan' => '2026-13']));
    $resExcel->assertSessionHasErrors(['bulan']);

    $resPdf = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['bulan' => '2026-13']));
    $resPdf->assertSessionHasErrors(['bulan']);
});

test('filter bulan valid menghasilkan unduhan excel dan pdf', function () {
    Excel::fake();

    $resExcel = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['bulan' => '2026-10']));

    $resExcel->assertStatus(200);
    Excel::assertDownloaded('rekap_absensi_'.date('Y-m-d').'.xlsx');

    $resPdf = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['bulan' => '2026-10']));

    $resPdf->assertStatus(200);
    $resPdf->assertHeader('content-type', 'application/pdf');
});
