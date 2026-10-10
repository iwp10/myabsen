<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->kelas = Kelas::factory()->create(['nama' => 'X RPL 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->kelasLain = Kelas::factory()->create(['nama' => 'X TKJ 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

    $this->userSiswa = User::factory()->create(['role' => 'siswa', 'name' => 'Ahmad Siswa']);
    $this->siswa = Siswa::factory()->create([
        'user_id' => $this->userSiswa->id,
        'kelas_id' => $this->kelas->id,
    ]);

    $this->userGuru = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru']);
    $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id]);

    $this->mapel1 = Mapel::factory()->create(['nama' => 'Matematika', 'kode' => 'MTK']);
    $this->mapel2 = Mapel::factory()->create(['nama' => 'Bahasa Inggris', 'kode' => 'BIG']);
    $this->mapelLain = Mapel::factory()->create(['nama' => 'Fisika TKJ', 'kode' => 'FIS']);

    // Jadwal kelas siswa (periode aktif)
    Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:45:00',
        'jam_selesai' => '10:15:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal kelas lain
    Jadwal::factory()->create([
        'kelas_id' => $this->kelasLain->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guru->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('jadwal pelajaran siswa: filter mapel_id menyaring, menyorot hijau, dan menampilkan label filter aktif', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal', [
        'mapel_id' => $this->mapel1->id,
    ]));

    $response->assertStatus(200);

    // Label filter aktif tampil
    $response->assertSee('Filter aktif:');
    $response->assertSee('Mata pelajaran: Matematika');

    // Terdapat sorotan hijau
    $response->assertSee('border-l-emerald-600');
    $response->assertSee('Sesuai filter');

    // Jadwal mapel 1 tampil dengan jamnya, jam mapel 2 tidak ada di kartu jadwal
    $response->assertSee('Matematika');
    $response->assertSee('07:00 - 08:30');
    $response->assertDontSee('08:45 - 10:15');
});

test('jadwal pelajaran siswa: mapel_id yang bukan milik kelas siswa ditolak dengan error validasi', function () {
    $response = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal', [
        'mapel_id' => $this->mapelLain->id,
    ]));

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['mapel_id']);
});
