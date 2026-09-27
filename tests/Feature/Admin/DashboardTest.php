<?php

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin can access dashboard and view master data statistics', function () {
    $jurusan = Jurusan::create(['nama' => 'Teknik Komputer', 'kode' => 'TKJ']);
    $kelas = Kelas::create(['jurusan_id' => $jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027']);
    $mapel = Mapel::create(['nama' => 'Matematika', 'kode' => 'MTK']);

    $userGuru = User::factory()->create(['role' => 'guru']);
    Guru::create(['user_id' => $userGuru->id, 'nip' => '1987654321']);

    $userSiswa = User::factory()->create(['role' => 'siswa']);
    Siswa::create(['user_id' => $userSiswa->id, 'kelas_id' => $kelas->id, 'nis' => '10293847']);

    $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.dashboard');
    $response->assertViewHas('total_siswa', 1);
    $response->assertViewHas('total_guru', 1);
    $response->assertViewHas('total_kelas', 1);
    $response->assertViewHas('total_mapel', 1);

    // Memastikan banner sapaan dan identitas sekolah ditampilkan
    $response->assertSee('Halo, Administrator!');
    $response->assertSee('Selamat datang di Pusat Kendali Utama MyAbsen SMK Mandiri 02 Balaraja');

    // Memastikan kartu statistik & aksi cepat dirender
    $response->assertSee('Total Siswa');
    $response->assertSee('Total Guru');
    $response->assertSee('Total Kelas');
    $response->assertSee('Total Mapel');
    $response->assertSee('Aksi Cepat');
    $response->assertSee(route('admin.laporan.index'));
    $response->assertSee(route('admin.siswa.index'));
});

test('guru cannot access admin dashboard', function () {
    $guru = User::factory()->create(['role' => 'guru']);

    $response = $this->actingAs($guru)->get(route('admin.dashboard'));
    $response->assertStatus(403);
});

test('siswa cannot access admin dashboard', function () {
    $siswa = User::factory()->create(['role' => 'siswa']);

    $response = $this->actingAs($siswa)->get(route('admin.dashboard'));
    $response->assertStatus(403);
});

test('guest is redirected to login when accessing admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});
