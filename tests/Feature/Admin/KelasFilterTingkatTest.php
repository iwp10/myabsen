<?php

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->siswa = User::factory()->create(['role' => 'siswa']);

    $this->jurusan = Jurusan::create(['nama' => 'Teknik Komputer Jaringan', 'kode' => 'TKJ']);

    $this->k10 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'TKJ 1',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->k11 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'TKJ 2',
        'tingkat' => 11,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->k12 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'TKJ 3',
        'tingkat' => 12,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);
});

test('kelas: filter tingkat menyaring data kelas dengan benar', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['tingkat' => '10']));

    $response->assertStatus(200);
    $response->assertSee('TKJ 1');
    $response->assertDontSee('TKJ 2');
    $response->assertDontSee('TKJ 3');
    $response->assertSee('Menampilkan 1 kelas');
});

test('kelas: filter tingkat bekerja bersama pencarian dan filter periode', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.kelas.index', [
        'periode' => '2026/2027|Ganjil',
        'tingkat' => '11',
        'search' => 'TKJ',
    ]));

    $response->assertStatus(200);
    $response->assertSee('TKJ 2');
    $response->assertDontSee('TKJ 1');
    $response->assertDontSee('TKJ 3');
    $response->assertSee('Menampilkan 1 kelas');
});

test('kelas: nilai tingkat tidak valid memberi error validasi', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['tingkat' => 'invalid_tingkat']));

    $response->assertSessionHasErrors('tingkat');
});

test('kelas: urutan bawaan adalah periode terbaru, tingkat, lalu nama', function () {
    // Tambahkan kelas dengan variasi periode dan nama
    $kLama = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'A Lama',
        'tingkat' => 10,
        'tahun_ajaran' => '2024/2025',
        'semester' => 'Ganjil',
    ]);

    $kBaruGenap = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'B Baru Genap',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.kelas.index'));
    $response->assertStatus(200);

    $items = $response->viewData('kelas');
    $first = $items->first();
    $last = $items->last();

    // 2026/2027 Genap harus lebih awal daripada 2024/2025 Ganjil
    expect($first->tahun_ajaran)->toBe('2026/2027');
    expect($last->tahun_ajaran)->toBe('2024/2025');
});
