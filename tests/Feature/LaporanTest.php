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
        ->get(route('admin.laporan.export', ['bulan' => date('Y-m')]));

    $response->assertStatus(200);

    Excel::assertDownloaded('rekap_absensi_'.date('Y-m').'.xlsx', function (LaporanAbsensiExport $export) {
        return true;
    });
});

test('guru dapat mengunduh laporan excel', function () {
    Excel::fake();

    $response = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export', ['bulan' => date('Y-m')]));

    $response->assertStatus(200);

    Excel::assertDownloaded('rekap_absensi_guru_'.date('Y-m').'.xlsx', function (LaporanAbsensiExport $export) {
        return true;
    });
});

test('admin dapat mengunduh laporan pdf', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['bulan' => date('Y-m')]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('guru dapat mengunduh laporan pdf', function () {
    $response = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.exportPdf', ['bulan' => date('Y-m')]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('view pdf menampilkan periode dengan benar dan tidak berulang', function () {
    $view = $this->view('laporan.pdf', [
        'data' => collect(),
        'filters' => ['bulan' => '2026-09'],
        'periode' => 'September 2026',
    ]);

    $view->assertSee('Periode:');
    $view->assertSee('September 2026');
    $view->assertDontSee('SeptemberSeptember');
});

test('filter tidak valid ditolak dengan error validasi', function () {
    // 1. kelas_id tidak ada di database
    $resKelas = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['kelas_id' => 99999]));
    $resKelas->assertSessionHasErrors(['kelas_id']);

    // 2. bulan format salah (bukan format Y-m)
    $resBulan = $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['bulan' => '2026-13']));
    $resBulan->assertSessionHasErrors(['bulan']);

    // 3. untuk pdf juga ditolak jika bulan format salah
    $resPdf = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['bulan' => 'bukan-bulan']));
    $resPdf->assertSessionHasErrors(['bulan']);
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

    // 1. Admin dengan bulan
    $this->actingAs($this->admin)
        ->get(route('admin.laporan.export', ['bulan' => '2026-08']));
    Excel::assertDownloaded('rekap_absensi_2026-08.xlsx');

    // 2. Admin tanpa bulan (default tahun-bulan saat ini)
    $this->actingAs($this->admin)
        ->get(route('admin.laporan.export'));
    Excel::assertDownloaded('rekap_absensi_'.date('Y-m').'.xlsx');

    // 3. Guru dengan bulan
    $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export', ['bulan' => '2026-08']));
    Excel::assertDownloaded('rekap_absensi_guru_2026-08.xlsx');

    // 4. Guru tanpa bulan
    $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.export'));
    Excel::assertDownloaded('rekap_absensi_guru_'.date('Y-m').'.xlsx');

    // 5. PDF Admin dengan bulan
    $resAdminPdf = $this->actingAs($this->admin)
        ->get(route('admin.laporan.exportPdf', ['bulan' => '2026-08']));
    $resAdminPdf->assertHeader('content-disposition', 'attachment; filename=rekap_absensi_2026-08.pdf');

    // 6. PDF Guru dengan bulan
    $resGuruPdf = $this->actingAs($this->guruUser)
        ->get(route('guru.laporan.exportPdf', ['bulan' => '2026-08']));
    $resGuruPdf->assertHeader('content-disposition', 'attachment; filename=rekap_absensi_guru_2026-08.pdf');
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
    $collection = $export->collection();
    $rowSiswaBaru = $collection->firstWhere('siswa_id', $siswaBaru->id);

    expect($rowSiswaBaru)->not->toBeNull();
    expect((int) $rowSiswaBaru->hadir)->toBe(1);
    expect((int) $rowSiswaBaru->izin)->toBe(1);
    expect((int) $rowSiswaBaru->sakit)->toBe(1);
    expect((int) $rowSiswaBaru->alpa)->toBe(1);
    expect((int) $rowSiswaBaru->total_sesi)->toBe(4);

    $mapped = $export->map($rowSiswaBaru);
    // Indeks ke-9 adalah persentase kehadiran: ((1 + 1 + 1) / 4) * 100 = 75.0%
    expect($mapped[9])->toBe(75.0);
});
