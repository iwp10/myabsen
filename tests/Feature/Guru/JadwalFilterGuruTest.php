<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guruUser = User::factory()->create(['role' => 'guru', 'name' => 'Budi Santoso']);
    $this->guru = Guru::create(['user_id' => $this->guruUser->id, 'nip' => '198501012010011001']);

    $this->guruLainUser = User::factory()->create(['role' => 'guru', 'name' => 'Siti Rahma']);
    $this->guruLain = Guru::create(['user_id' => $this->guruLainUser->id, 'nip' => '198802022012012002']);

    $this->jurusanRPL = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);
    $this->jurusanTKJ = Jurusan::create(['nama' => 'Teknik Komputer Jaringan', 'kode' => 'TKJ']);

    $this->kelasA = Kelas::create(['jurusan_id' => $this->jurusanRPL->id, 'nama' => 'X RPL 1', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->kelasB = Kelas::create(['jurusan_id' => $this->jurusanRPL->id, 'nama' => 'XI RPL 1', 'tingkat' => 11, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->kelasLain = Kelas::create(['jurusan_id' => $this->jurusanTKJ->id, 'nama' => 'XII TKJ 1', 'tingkat' => 12, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

    $this->mapel1 = Mapel::create(['nama' => 'Pemrograman Berorientasi Objek', 'kode' => 'PBO']);
    $this->mapel2 = Mapel::create(['nama' => 'Basis Data', 'kode' => 'BD']);
    $this->mapelLain = Mapel::create(['nama' => 'Jaringan Nirkabel Khusus', 'kode' => 'JNK']);

    // Jadwal 1: Kelas A, Mapel 1, Senin
    $this->jadwal1 = Jadwal::create([
        'kelas_id' => $this->kelasA->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:30:00',
        'jam_selesai' => '09:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal 2: Kelas A, Mapel 2, Selasa
    $this->jadwal2 = Jadwal::create([
        'kelas_id' => $this->kelasA->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:15:00',
        'jam_selesai' => '10:45:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal 3: Kelas B, Mapel 1, Senin
    $this->jadwal3 = Jadwal::create([
        'kelas_id' => $this->kelasB->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '11:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal guru lain
    Jadwal::create([
        'kelas_id' => $this->kelasLain->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guruLain->id,
        'hari' => 'senin',
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '09:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('jadwal mengajar: filter kelas dan mapel menyaring dan bekerja bersama hari', function () {
    // Filter hari=senin dan kelas_id=kelasA
    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal', [
        'hari' => 'senin',
        'kelas_id' => $this->kelasA->id,
    ]));

    $response->assertStatus(200);
    // Jadwal 1 tampil
    $response->assertSee('Pemrograman Berorientasi Objek');
    $response->assertSee('X RPL 1');

    // Jadwal 2 (selasa 09:15) dan Jadwal 3 (kelas B 10:00) tidak muncul di daftar jadwal
    $response->assertDontSee('10:00 - 11:30');
    $response->assertDontSee('09:15 - 10:45');
});

test('jadwal mengajar: item yang cocok bertanda sorotan hijau dan label filter aktif tampil', function () {
    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal', [
        'kelas_id' => $this->kelasA->id,
    ]));

    $response->assertStatus(200);

    // Label filter aktif tampil
    $response->assertSee('Filter aktif:');
    $response->assertSee('Kelas: X RPL 1');

    // Mengandung kelas sorotan hijau
    $response->assertSee('border-l-emerald-600');
    $response->assertSee('bg-emerald-50/70');
});

test('jadwal mengajar: kelas_id dan mapel_id milik guru lain ditolak dengan error validasi', function () {
    // Mencoba memfilter kelas milik guru lain
    $responseKelas = $this->actingAs($this->guruUser)->get(route('guru.jadwal', [
        'kelas_id' => $this->kelasLain->id,
    ]));

    $responseKelas->assertStatus(302);
    $responseKelas->assertSessionHasErrors(['kelas_id']);

    // Mencoba memfilter mapel milik guru lain
    $responseMapel = $this->actingAs($this->guruUser)->get(route('guru.jadwal', [
        'mapel_id' => $this->mapelLain->id,
    ]));

    $responseMapel->assertStatus(302);
    $responseMapel->assertSessionHasErrors(['mapel_id']);
});

test('jadwal mengajar: tombol reset mengosongkan semua filter', function () {
    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal', [
        'hari' => 'senin',
        'kelas_id' => $this->kelasA->id,
    ]));

    $response->assertStatus(200);
    $response->assertSee('Reset Semua Filter');
    $response->assertSee(route('guru.jadwal'));
});
