<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin update jadwal mengarahkan ke url kembali yang valid dengan filter', function () {
    $kelas = Kelas::factory()->create(['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $kembaliUrl = '/admin/jadwal?hari=senin&page=2';

    $response = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'kembali' => $kembaliUrl,
    ]);

    $response->assertRedirect($kembaliUrl);
    $response->assertSessionHas('success');
});

test('admin update jadwal mengabaikan url kembali yang berbahaya', function () {
    $kelas = Kelas::factory()->create(['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'kembali' => 'https://evil.com/phishing',
    ]);

    $response->assertRedirect(route('admin.jadwal.index'));
});

test('admin hapus jadwal mengarahkan ke url kembali yang valid dengan filter', function () {
    $jadwal = Jadwal::factory()->create();
    $kembaliUrl = '/admin/jadwal?hari=rabu&page=1';

    $response = $this->actingAs($this->admin)->delete(route('admin.jadwal.destroy', [
        'jadwal' => $jadwal->id,
        'kembali' => $kembaliUrl,
    ]));

    $response->assertRedirect($kembaliUrl);
    $response->assertSessionHas('success');
});

test('admin update kelas mengarahkan ke url kembali yang valid dengan filter', function () {
    $kelas = Kelas::factory()->create(['tingkat' => '10', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $kembaliUrl = '/admin/kelas?tingkat=10&page=2';

    $response = $this->actingAs($this->admin)->put(route('admin.kelas.update', $kelas), [
        'nama' => '10 RPL 1 Baru',
        'tingkat' => 10,
        'jurusan_id' => $kelas->jurusan_id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'kembali' => $kembaliUrl,
    ]);

    $response->assertRedirect($kembaliUrl);
    $response->assertSessionHas('success');
});

test('admin hapus kelas mengarahkan ke url kembali yang valid dengan filter', function () {
    $kelas = Kelas::factory()->create();
    $kembaliUrl = '/admin/kelas?periode=2026%2F2027%7CGanjil';

    $response = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', [
        'kelas' => $kelas->id,
        'kembali' => $kembaliUrl,
    ]));

    $response->assertRedirect($kembaliUrl);
    $response->assertSessionHas('success');
});

test('index jadwal mengarahkan ke halaman terakhir yang valid jika halaman saat ini kosong', function () {
    // Buat 11 jadwal (page size 10)
    Jadwal::factory()->count(11)->create([
        'hari' => 'senin',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Buka halaman 5 yang tidak memiliki data (halaman terakhir sebenarnya adalah halaman 2)
    $response = $this->actingAs($this->admin)->get('/admin/jadwal?hari=senin&page=5');

    // Harusnya redirect ke page=2 dengan parameter filter tetap ada
    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');
    expect($targetUrl)->toContain('hari=senin');
    expect($targetUrl)->toContain('page=2');
});

test('index kelas mengarahkan ke halaman terakhir yang valid jika halaman saat ini kosong', function () {
    // Buat 11 kelas (page size 10)
    Kelas::factory()->count(11)->create();

    // Buka halaman 10 yang kosong
    $response = $this->actingAs($this->admin)->get('/admin/kelas?page=10');

    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');
    expect($targetUrl)->toContain('page=2');
});
