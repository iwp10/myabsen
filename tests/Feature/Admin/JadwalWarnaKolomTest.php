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

    $this->kelas = Kelas::factory()->create(['nama' => 'X RPL 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->mapel = Mapel::factory()->create(['nama' => 'Matematika']);
    $this->guruUser = User::factory()->create(['name' => 'Pak Budi']);
    $this->guru = Guru::factory()->create(['user_id' => $this->guruUser->id]);

    // Jadwal hari Senin
    $this->jSenin = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal hari Selasa
    $this->jSelasa = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);
});

test('jadwal: halaman memuat class warna per kolom dan badge hari yang berbeda untuk tiap hari', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index'));

    $response->assertStatus(200);

    // 1. Periode: teks abu-abu netral
    $response->assertSee('text-gray-600');
    $response->assertSee('dark:text-gray-400');

    // 2. Semester: Ganjil biru dan Genap ungu
    $response->assertSee('bg-blue-100 text-blue-800');
    $response->assertSee('dark:bg-blue-900/40 dark:text-blue-300');
    $response->assertSee('bg-purple-100 text-purple-800');
    $response->assertSee('dark:bg-purple-900/40 dark:text-purple-300');

    // 3. Hari: badge warna berbeda (Senin: sky, Selasa: emerald)
    $response->assertSee('bg-sky-100 text-sky-800');
    $response->assertSee('dark:bg-sky-900/40 dark:text-sky-300');
    $response->assertSee('bg-emerald-100 text-emerald-800');
    $response->assertSee('dark:bg-emerald-900/40 dark:text-emerald-300');

    // 4. Jam: teks teal monospasi/tabular
    $response->assertSee('font-mono tabular-nums');
    $response->assertSee('text-teal-700');
    $response->assertSee('dark:text-teal-400');

    // 5. Kelas: badge indigo
    $response->assertSee('bg-indigo-100 text-indigo-800');
    $response->assertSee('dark:bg-indigo-900/40 dark:text-indigo-300');

    // 6. Mata Pelajaran: teks hijau (emerald) semi-tebal
    $response->assertSee('font-semibold text-emerald-700');
    $response->assertSee('dark:text-emerald-400');

    // 7. Guru: teks oranye/amber
    $response->assertSee('text-amber-800');
    $response->assertSee('dark:text-amber-300');
});

test('jadwal: filter dan paginasi yang ada tetap bekerja normal', function () {
    $responseFilter = $this->actingAs($this->admin)->get(route('admin.jadwal.index', [
        'hari' => 'senin',
    ]));

    $responseFilter->assertStatus(200);
    $responseFilter->assertSee('07:00 - 08:30');
    $responseFilter->assertDontSee('09:00 - 10:30');
});
