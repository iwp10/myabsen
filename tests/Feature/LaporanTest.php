<?php

use App\Exports\LaporanAbsensiExport;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

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
