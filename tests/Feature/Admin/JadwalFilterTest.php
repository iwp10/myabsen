<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);

    $this->kelas1 = Kelas::factory()->create(['nama' => 'X RPL 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->kelas2 = Kelas::factory()->create(['nama' => 'XI RPL 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap']);

    $this->mapel1 = Mapel::factory()->create(['nama' => 'Matematika']);
    $this->mapel2 = Mapel::factory()->create(['nama' => 'Bahasa Indonesia']);

    $this->userGuru1 = User::factory()->create(['name' => 'Guru Satu']);
    $this->guru1 = Guru::factory()->create(['user_id' => $this->userGuru1->id]);

    $this->userGuru2 = User::factory()->create(['name' => 'Guru Dua']);
    $this->guru2 = Guru::factory()->create(['user_id' => $this->userGuru2->id]);

    // Buat jadwal untuk pengujian filter
    $this->j1 = Jadwal::factory()->create([
        'kelas_id' => $this->kelas1->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->j2 = Jadwal::factory()->create([
        'kelas_id' => $this->kelas1->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru2->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->j3 = Jadwal::factory()->create([
        'kelas_id' => $this->kelas2->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru2->id,
        'hari' => 'rabu',
        'jam_mulai' => '11:00:00',
        'jam_selesai' => '12:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);
});

test('AB-06: filter hari menyaring daftar jadwal admin', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index', ['hari' => 'senin']));

    $response->assertStatus(200);
    $response->assertSee(route('admin.jadwal.edit', $this->j1));
    $response->assertDontSee(route('admin.jadwal.edit', $this->j2));
    $response->assertDontSee('09:00 - 10:30');
    expect($response->viewData('jadwals')->pluck('id'))->toContain($this->j1->id)->not->toContain($this->j2->id);
});

test('AB-06: filter kelas menyaring daftar jadwal admin', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index', ['kelas_id' => $this->kelas2->id]));

    $response->assertStatus(200);
    $response->assertSee(route('admin.jadwal.edit', $this->j3));
    $response->assertDontSee(route('admin.jadwal.edit', $this->j1));
    $response->assertDontSee('07:00 - 08:30');
    expect($response->viewData('jadwals')->pluck('id'))->toContain($this->j3->id)->not->toContain($this->j1->id);
});

test('AB-06: filter guru menyaring daftar jadwal admin', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index', ['guru_id' => $this->guru1->id]));

    $response->assertStatus(200);
    $response->assertSee(route('admin.jadwal.edit', $this->j1));
    $response->assertDontSee(route('admin.jadwal.edit', $this->j2));
    $response->assertDontSee('09:00 - 10:30');
    expect($response->viewData('jadwals')->pluck('id'))->toContain($this->j1->id)->not->toContain($this->j2->id);
});

test('AB-06: filter mapel menyaring daftar jadwal admin', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index', ['mapel_id' => $this->mapel2->id]));

    $response->assertStatus(200);
    $response->assertSee(route('admin.jadwal.edit', $this->j2));
    $response->assertDontSee(route('admin.jadwal.edit', $this->j1));
    $response->assertDontSee('07:00 - 08:30');
    expect($response->viewData('jadwals')->pluck('id'))->toContain($this->j2->id)->not->toContain($this->j1->id);
});

test('AB-06: filter rentang jam jam_mulai_dari dan jam_mulai_sampai bekerja', function () {
    // Cari jadwal dengan jam mulai antara 08:00 sampai 10:00 (hanya j2 yang mulai 09:00)
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index', [
        'jam_mulai_dari' => '08:00',
        'jam_mulai_sampai' => '10:00',
    ]));

    $response->assertStatus(200);
    $response->assertSee(route('admin.jadwal.edit', $this->j2));
    $response->assertDontSee(route('admin.jadwal.edit', $this->j1));
    $response->assertDontSee('07:00 - 08:30');
    expect($response->viewData('jadwals')->pluck('id'))->toContain($this->j2->id)->not->toContain($this->j1->id);
});

test('AB-06: filter bekerja bersamaan dengan filter periode', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index', [
        'periode' => '2026/2027|Ganjil',
        'hari' => 'senin',
        'kelas_id' => $this->kelas1->id,
        'guru_id' => $this->guru1->id,
        'mapel_id' => $this->mapel1->id,
        'jam_mulai_dari' => '06:00',
        'jam_mulai_sampai' => '08:00',
    ]));

    $response->assertStatus(200);
    $response->assertSee(route('admin.jadwal.edit', $this->j1));
    $response->assertDontSee(route('admin.jadwal.edit', $this->j2));
    $response->assertDontSee('09:00 - 10:30');
    expect($response->viewData('jadwals')->pluck('id'))->toContain($this->j1->id)->not->toContain($this->j2->id);
});

test('AB-06: urutan jadwal bawaan adalah hari Senin-Sabtu lalu jam mulai', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index'));

    $response->assertStatus(200);
    $content = $response->getContent();

    $posisiSenin = strpos($content, 'senin');
    $posisiSelasa = strpos($content, 'selasa');
    $posisiRabu = strpos($content, 'rabu');

    expect($posisiSenin)->toBeLessThan($posisiSelasa);
    expect($posisiSelasa)->toBeLessThan($posisiRabu);
});

test('AB-06: paginasi membawa query string filter', function () {
    // Buat lebih dari 10 jadwal agar muncul paginasi
    Jadwal::factory()->count(12)->create([
        'kelas_id' => $this->kelas1->id,
        'hari' => 'senin',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index', ['hari' => 'senin']));

    $response->assertStatus(200);
    $response->assertSee('page=2');
    $response->assertSee('hari=senin');
});

test('AB-06: nilai parameter filter tidak valid memberi error validasi bukan 500', function () {
    // Hari tidak valid
    $responseHari = $this->actingAs($this->admin)->get(route('admin.jadwal.index', ['hari' => 'minggu']));
    $responseHari->assertSessionHasErrors(['hari']);

    // Jam awal lebih besar dari jam akhir
    $responseJam = $this->actingAs($this->admin)->get(route('admin.jadwal.index', [
        'jam_mulai_dari' => '10:00',
        'jam_mulai_sampai' => '08:00',
    ]));
    $responseJam->assertSessionHasErrors(['jam_mulai_sampai']);

    // ID kelas tidak ada
    $responseKelas = $this->actingAs($this->admin)->get(route('admin.jadwal.index', ['kelas_id' => 999999]));
    $responseKelas->assertSessionHasErrors(['kelas_id']);
});
