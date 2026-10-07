<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin can view jadwal index', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index'));
    $response->assertStatus(200);
});

test('admin can create jadwal', function () {
    $kelas = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response->assertRedirect(route('admin.jadwal.index'));
    $this->assertDatabaseHas('jadwal', [
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
    ]);
});

test('cannot create jadwal with overlap for guru', function () {
    $kelas1 = Kelas::factory()->create();
    $kelas2 = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();

    Jadwal::factory()->create([
        'kelas_id' => $kelas1->id,
        'guru_id' => $guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
        'kelas_id' => $kelas2->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:00', // overlap
        'jam_selesai' => '11:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response->assertSessionHasErrors(['guru_id']);
});

test('cannot create jadwal with overlap for kelas', function () {
    $kelas = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $guru1 = Guru::factory()->create();
    $guru2 = Guru::factory()->create();

    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru1->id,
        'hari' => 'rabu',
        'jam_mulai' => '10:00',
        'jam_selesai' => '12:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru2->id,
        'hari' => 'rabu',
        'jam_mulai' => '09:00', // overlap
        'jam_selesai' => '10:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response->assertSessionHasErrors(['kelas_id']);
});

test('admin can update jadwal', function () {
    $jadwal = Jadwal::factory()->create([
        'hari' => 'kamis',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '09:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
        'kelas_id' => $jadwal->kelas_id,
        'mapel_id' => $jadwal->mapel_id,
        'guru_id' => $jadwal->guru_id,
        'hari' => 'jumat',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response->assertRedirect(route('admin.jadwal.index'));
    $this->assertDatabaseHas('jadwal', [
        'id' => $jadwal->id,
        'hari' => 'jumat',
        'jam_mulai' => '08:00',
    ]);
});

test('admin can delete jadwal without attendance history', function () {
    $jadwal = Jadwal::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.jadwal.destroy', $jadwal));

    $response->assertRedirect(route('admin.jadwal.index'));
    $this->assertDatabaseMissing('jadwal', ['id' => $jadwal->id]);
});

test('admin cannot delete jadwal with attendance history', function () {
    $jadwal = Jadwal::factory()->create();
    SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.jadwal.destroy', $jadwal));

    $response->assertRedirect(route('admin.jadwal.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('jadwal', ['id' => $jadwal->id]);
});

test('AB-06: Membuat jadwal dengan periode berbeda dari kelasnya ditolak dengan pesan di atas dan tidak ada data tersimpan', function () {
    $kelas = Kelas::factory()->create([
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();

    // 1. Tahun ajaran beda
    $responseTa = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);

    $responseTa->assertSessionHasErrors([
        'tahun_ajaran' => 'Periode jadwal harus sama dengan periode kelas (Ganjil 2026/2027).',
    ]);
    $this->assertDatabaseMissing('jadwal', [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
    ]);

    // 2. Semester beda
    $responseSem = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);

    $responseSem->assertSessionHasErrors([
        'semester' => 'Periode jadwal harus sama dengan periode kelas (Ganjil 2026/2027).',
    ]);
    $this->assertDatabaseMissing('jadwal', [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
    ]);

    // 3. Keduanya beda
    $responseBoth = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    $responseBoth->assertSessionHasErrors([
        'tahun_ajaran' => 'Periode jadwal harus sama dengan periode kelas (Ganjil 2026/2027).',
        'semester' => 'Periode jadwal harus sama dengan periode kelas (Ganjil 2026/2027).',
    ]);
    $this->assertDatabaseMissing('jadwal', [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
    ]);
});

test('AB-06: Membuat jadwal dengan periode sama berhasil', function () {
    $kelas = Kelas::factory()->create([
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response->assertRedirect(route('admin.jadwal.index'));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('jadwal', [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('AB-06: Mengubah jadwal ke periode yang berbeda dari kelasnya ditolak; mengubah hal lain (mis. jam) pada jadwal yang periodenya sudah sama berhasil', function () {
    $kelas = Kelas::factory()->create([
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // 1. Mengubah semester ke Genap ditolak
    $responseSem = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);

    $responseSem->assertSessionHasErrors([
        'semester' => 'Periode jadwal harus sama dengan periode kelas (Ganjil 2026/2027).',
    ]);
    expect($jadwal->fresh()->semester)->toBe('Ganjil');

    // 2. Mengubah tahun ajaran ke 2025/2026 ditolak
    $responseTa = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);

    $responseTa->assertSessionHasErrors([
        'tahun_ajaran' => 'Periode jadwal harus sama dengan periode kelas (Ganjil 2026/2027).',
    ]);
    expect($jadwal->fresh()->tahun_ajaran)->toBe('2026/2027');

    // 3. Mengubah hal lain (jam mulai dan jam selesai) pada periode yang sama berhasil
    $responseJam = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '09:00',
        'jam_selesai' => '10:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $responseJam->assertRedirect(route('admin.jadwal.index'));
    $responseJam->assertSessionHasNoErrors();
    expect($jadwal->fresh()->jam_mulai)->toBe('09:00:00');
    expect($jadwal->fresh()->jam_selesai)->toBe('10:30:00');
});

test('AB-06: Mengganti kelas pada jadwal ke kelas berperiode berbeda ditolak', function () {
    $kelas1 = Kelas::factory()->create([
        'nama' => '10 RPL 1',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $kelas2 = Kelas::factory()->create([
        'nama' => '10 RPL 2',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas1->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Update kelas_id ke $kelas2 tapi tetap mengirim tahun_ajaran/semester $kelas1
    $response = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
        'kelas_id' => $kelas2->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response->assertSessionHasErrors([
        'tahun_ajaran' => 'Periode jadwal harus sama dengan periode kelas (Genap 2025/2026).',
        'semester' => 'Periode jadwal harus sama dengan periode kelas (Genap 2025/2026).',
    ]);
    expect($jadwal->fresh()->kelas_id)->toBe($kelas1->id);
});
