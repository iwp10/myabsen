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
    $view = $this->view('admin.laporan.pdf', [
        'data' => collect(),
        'filters' => ['bulan' => '2026-09'],
        'periode' => 'September 2026',
    ]);

    $view->assertSee('Periode:');
    $view->assertSee('September 2026');
    $view->assertDontSee('SeptemberSeptember');
});
