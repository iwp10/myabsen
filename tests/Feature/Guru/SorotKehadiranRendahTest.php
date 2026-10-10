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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->userGuru = User::factory()->create(['role' => 'guru', 'name' => 'Pak Guru']);
    $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id]);

    $this->kelas = Kelas::factory()->create(['nama' => 'X RPL 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->mapel = Mapel::factory()->create(['nama' => 'Algoritma']);

    $this->jadwal = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Siswa 1: Kehadiran 50% (di bawah batas 75%) -> 1 Hadir, 1 Alpa dari 2 sesi
    $this->userS1 = User::factory()->create(['name' => 'Siswa Rendah']);
    $this->siswa1 = Siswa::factory()->create(['user_id' => $this->userS1->id, 'kelas_id' => $this->kelas->id]);

    // Siswa 2: Kehadiran 100% (di atas batas 75%) -> 2 Hadir dari 2 sesi
    $this->userS2 = User::factory()->create(['name' => 'Siswa Tinggi']);
    $this->siswa2 = Siswa::factory()->create(['user_id' => $this->userS2->id, 'kelas_id' => $this->kelas->id]);

    // Siswa 3: Kehadiran 75% (tepat di batas 75%) -> 3 Hadir, 1 Alpa dari 4 sesi
    $this->userS3 = User::factory()->create(['name' => 'Siswa Pas']);
    $this->siswa3 = Siswa::factory()->create(['user_id' => $this->userS3->id, 'kelas_id' => $this->kelas->id]);

    // Siswa 4: Belum punya sesi sama sekali
    $this->userS4 = User::factory()->create(['name' => 'Siswa Tanpa Sesi']);
    $this->siswa4 = Siswa::factory()->create(['user_id' => $this->userS4->id, 'kelas_id' => $this->kelas->id]);

    // Buat 4 sesi
    $this->sesi1 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-09-01']);
    $this->sesi2 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-09-02']);
    $this->sesi3 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-09-03']);
    $this->sesi4 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwal->id, 'tanggal' => '2026-09-04']);

    // Detail Siswa 1 (2 sesi: 1 Hadir, 1 Alpa => 50%)
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi1->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi2->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::ALPA]);

    // Detail Siswa 2 (2 sesi: 2 Hadir => 100%)
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi1->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi2->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::HADIR]);

    // Detail Siswa 3 (4 sesi: 3 Hadir, 1 Alpa => 75%)
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi1->id, 'siswa_id' => $this->siswa3->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi2->id, 'siswa_id' => $this->siswa3->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi3->id, 'siswa_id' => $this->siswa3->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesi4->id, 'siswa_id' => $this->siswa3->id, 'status' => StatusKehadiran::ALPA]);
});

test('AB-07: siswa di bawah batas kehadiran rendah disorot dengan badge Perlu perhatian dan latar lembut', function () {
    Config::set('absensi.batas_kehadiran_rendah', 75);

    $response = $this->actingAs($this->userGuru)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));

    $response->assertStatus(200);

    $content = $response->getContent();

    // Siswa 1 (50%) harus memiliki badge "Perlu perhatian"
    expect($content)->toContain('Siswa Rendah');
    expect($content)->toContain('Perlu perhatian');

    // Persentase tetap identik
    expect($content)->toContain('50%');
    expect($content)->toContain('100%');
    expect($content)->toContain('75%');
});

test('AB-07: siswa tepat di batas (75%) dan di atas batas tidak disorot', function () {
    Config::set('absensi.batas_kehadiran_rendah', 75);

    $response = $this->actingAs($this->userGuru)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));

    $content = $response->getContent();

    // Hanya ada 1 badge "Perlu perhatian" pada baris tabel (selain di legend)
    $countBadge = substr_count($content, 'Perlu perhatian</span>');
    // Di tabel ada 1 siswa yang disorot + 1 di legend keterangan bawah
    expect($countBadge)->toBe(2);
});

test('AB-07: siswa tanpa sesi tidak disorot', function () {
    Config::set('absensi.batas_kehadiran_rendah', 75);

    // Ambil baris HTML untuk siswa 4
    $response = $this->actingAs($this->userGuru)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));

    $content = $response->getContent();
    expect($content)->toContain('Siswa Tanpa Sesi');

    // Pastikan siswa 4 menampilkan 0% dan tidak ada badge di dekat namanya
    $posisiSiswa4 = strpos($content, 'Siswa Tanpa Sesi');
    $subContent = substr($content, $posisiSiswa4, 200);
    expect($subContent)->not->toContain('Perlu perhatian');
});

test('AB-07: batas kehadiran rendah dapat diubah lewat config', function () {
    // Ubah config batas menjadi 60%
    Config::set('absensi.batas_kehadiran_rendah', 60);

    $response = $this->actingAs($this->userGuru)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));

    $content = $response->getContent();

    // Siswa 1 (50%) < 60% tetap disorot
    expect($content)->toContain('Perlu perhatian');

    // Sekarang ubah batas ke 40% (sehingga siswa 1 dengan 50% tidak lagi di bawah batas)
    Config::set('absensi.batas_kehadiran_rendah', 40);

    $response40 = $this->actingAs($this->userGuru)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas->id,
        'mapel' => $this->mapel->id,
    ]));

    $content40 = $response40->getContent();
    // Pada tabel tidak ada siswa yang disorot (hanya di legend)
    $countBadge = substr_count($content40, 'Perlu perhatian</span>');
    expect($countBadge)->toBe(1); // Hanya di legend bawah
});

test('AB-07: fitur ekspor Excel guru tidak terpengaruh oleh penanda kehadiran rendah', function () {
    $response = $this->actingAs($this->userGuru)->get(route('guru.laporan.export', [
        'kelas_mapel' => "{$this->kelas->id}-{$this->mapel->id}",
    ]));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
});
