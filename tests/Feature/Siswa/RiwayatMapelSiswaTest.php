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
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->kelas = Kelas::factory()->create([
        'nama' => 'X RPL 1',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->userSiswa1 = User::factory()->create([
        'name' => 'Siswa Utama',
        'role' => 'siswa',
    ]);
    $this->siswa1 = Siswa::factory()->create([
        'user_id' => $this->userSiswa1->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '12345',
    ]);

    $this->userSiswa2 = User::factory()->create([
        'name' => 'Siswa Teman Sekelas',
        'role' => 'siswa',
    ]);
    $this->siswa2 = Siswa::factory()->create([
        'user_id' => $this->userSiswa2->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '67890',
    ]);

    $this->userGuru = User::factory()->create([
        'name' => 'Pak Guru Budi',
        'role' => 'guru',
    ]);
    $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id]);

    $this->mapelMatematika = Mapel::factory()->create(['nama' => 'Matematika']);
    $this->mapelFisika = Mapel::factory()->create(['nama' => 'Fisika']);
    $this->mapelKimia = Mapel::factory()->create(['nama' => 'Kimia']); // Tanpa catatan absensi

    // Jadwal Matematika (2026/2027 - Ganjil)
    $this->jadwalMatematika = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapelMatematika->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // 4 Sesi Matematika: 2 Hadir, 1 Izin, 1 Alpa -> persentase: ((2+1)/4)*100 = 75%
    $this->sesiM1 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwalMatematika->id, 'tanggal' => '2026-09-01']);
    $this->sesiM2 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwalMatematika->id, 'tanggal' => '2026-09-08']);
    $this->sesiM3 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwalMatematika->id, 'tanggal' => '2026-09-15']);
    $this->sesiM4 = SesiAbsensi::factory()->create(['jadwal_id' => $this->jadwalMatematika->id, 'tanggal' => '2026-09-22']);

    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM1->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM2->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM3->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::IZIN, 'keterangan' => 'Ada acara keluarga']);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM4->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::ALPA]);

    // Siswa 2 di sesi yang sama
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM1->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM2->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM3->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $this->sesiM4->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::HADIR]);
});

test('AB-08: Tab Per Mata Pelajaran menampilkan kartu hanya untuk mapel yang punya catatan siswa pada periode terpilih', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat'));

    $response->assertStatus(200);
    // Tab per_mapel adalah default
    $response->assertSee('Per Mata Pelajaran');
    $response->assertSee('Matematika');
    $response->assertSee('Pak Guru Budi');
    // Mapel tanpa catatan absensi tidak muncul dalam kartu
    $response->assertDontSee('Kimia');
});

test('AB-07: persentase dan jumlah H/I/S/A pada kartu identik dengan rumus AB-07', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'per_mapel']));

    $response->assertStatus(200);
    // Matematika 75% (2 hadir, 1 izin, 0 sakit, 1 alpa dari 4 sesi)
    $response->assertSee('75%');
    $response->assertSee('Total 4 Sesi');
});

test('AB-08: periode lain tidak tercampur dan ganti periode mengubah kartu atau menampilkan empty state', function () {
    // Buat jadwal periode lama 2025/2026 Genap
    $kelasLama = Kelas::factory()->create([
        'nama' => 'X RPL 1 Lama',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);
    $jadwalFisikaLama = Jadwal::factory()->create([
        'kelas_id' => $kelasLama->id,
        'mapel_id' => $this->mapelFisika->id,
        'guru_id' => $this->guru->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);
    $sesiFisika = SesiAbsensi::factory()->create([
        'jadwal_id' => $jadwalFisikaLama->id,
        'tanggal' => '2026-02-10',
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesiFisika->id,
        'siswa_id' => $this->siswa1->id,
        'status' => StatusKehadiran::HADIR,
    ]);

    // Pada periode aktif (2026/2027 Ganjil), Fisika tidak muncul
    $resAktif = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['periode' => '2026/2027|Ganjil']));
    $resAktif->assertStatus(200);
    $resAktif->assertSee('Matematika');
    $resAktif->assertDontSee('Fisika');

    // Pada periode lama 2025/2026 Genap, Fisika muncul dan Matematika tidak muncul
    $resLama = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['periode' => '2025/2026|Genap']));
    $resLama->assertStatus(200);
    $resLama->assertSee('Fisika');
    $resLama->assertDontSee('Matematika');

    // Pada periode kosong yang dibuat, tampil empty state
    $resKosong = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tahun_ajaran' => '2024/2025', 'semester' => 'Ganjil']));
    $resKosong->assertStatus(200);
    $resKosong->assertSee('Belum ada data kehadiran pada periode ini');
});

test('AB-08: Tab Semua Riwayat berperilaku persis seperti sebelumnya', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'semua']));

    $response->assertStatus(200);
    $response->assertSee('Riwayat Kehadiran');
    $response->assertSee('Matematika');
    $response->assertSee('01/09/2026');
    $response->assertSee('Hadir');
    $response->assertSee('Ada acara keluarga');
});

test('AB-08: parameter tab tidak valid ditolak dengan error validasi', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat', ['tab' => 'tab_ilegal']));
    $response->assertSessionHasErrors(['tab']);
});

test('AB-08: detail mapel menampilkan satu baris milik siswa itu dengan status tiap pertemuan dan persentase identik', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Matematika');
    $response->assertSee('Pak Guru Budi');
    $response->assertSee('75%');
    $response->assertSee('Siswa Utama');
    $response->assertSee('12345');
    // Header pertemuan P1, P2, P3, P4
    $response->assertSee('P1');
    $response->assertSee('P2');
    $response->assertSee('P3');
    $response->assertSee('P4');
    // Keterangan izin muncul
    $response->assertSee('Ada acara keluarga');
});

test('AB-08: data siswa lain TIDAK bocor pada response detail mapel', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]));

    $response->assertStatus(200);
    $content = $response->getContent();

    // Data Siswa 1 harus ada
    expect($content)->toContain('Siswa Utama');
    expect($content)->toContain('12345');

    // Data Siswa 2 (sekelas) TIDAK boleh ada sama sekali di seluruh response
    expect($content)->not->toContain('Siswa Teman Sekelas');
    expect($content)->not->toContain('67890');
});

test('AB-08: mapel tanpa catatan absensi menghasilkan 404', function () {
    // Mapel Kimia belum pernah ada absensi
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelKimia->id,
        'periode' => '2026/2027|Ganjil',
    ]));

    $response->assertStatus(404);
});

test('AB-08: guru dan admin ditolak 403 saat mengakses detail riwayat mapel siswa', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]))->assertStatus(403);

    $this->actingAs($this->userGuru)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]))->assertStatus(403);
});

test('AB-08: tamu dialihkan ke halaman login', function () {
    $this->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
    ]))->assertRedirect(route('login'));
});

test('AB-05 & AB-08: akun siswa nonaktif ditolak mengakses riwayat mapel', function () {
    $this->siswa1->delete(); // Soft delete siswa

    $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]))->assertRedirect(route('login'));
});

test('AB-08: siswa yang pernah pindah kelas tetap melihat riwayat dari kelas lamanya', function () {
    // Pindahkan siswa1 ke kelas baru
    $kelasBaru = Kelas::factory()->create([
        'nama' => 'X RPL 2',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $this->siswa1->update(['kelas_id' => $kelasBaru->id]);

    // Siswa tetap dapat melihat riwayat Matematika yang dicatat saat di kelas lama
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Matematika');
    $response->assertSee('75%');
});

test('AB-08: periode tidak valid pada detail mapel ditolak dengan error validasi', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'tahun_ajaran' => '2026/2028', // tidak berurutan
        'semester' => 'Ganjil',
    ]));

    $response->assertSessionHasErrors(['tahun_ajaran']);
});

test('halaman detail mapel adalah hanya-baca tanpa form aksi dan tombol ekspor', function () {
    $response = $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]));

    $response->assertStatus(200);
    $content = $response->getContent();

    expect($content)->not->toContain('<form method="POST"');
    expect($content)->not->toContain('method="DELETE"');
    expect($content)->not->toContain('export');
    expect($content)->not->toContain('Ekspor');
    expect($content)->not->toContain('Unduh');
});

test('performa: jumlah query pada detail mapel tidak bertambah proporsional dengan jumlah pertemuan (tanpa N+1)', function () {
    // 1. Query count awal (dengan 4 sesi bawaan)
    DB::enableQueryLog();
    $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]))->assertStatus(200);

    $countAwal = count(DB::getQueryLog());

    // 2. Tambah 15 sesi baru (total 19 sesi)
    for ($i = 1; $i <= 15; $i++) {
        $sesi = SesiAbsensi::factory()->create([
            'jadwal_id' => $this->jadwalMatematika->id,
            'tanggal' => sprintf('2026-10-%02d', $i),
        ]);
        DetailAbsensi::create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $this->siswa1->id,
            'status' => StatusKehadiran::HADIR,
        ]);
    }

    DB::flushQueryLog();

    // 3. Query count setelah penambahan 15 sesi
    $this->actingAs($this->userSiswa1)->get(route('siswa.riwayat.mapel', [
        'mapel' => $this->mapelMatematika->id,
        'periode' => '2026/2027|Ganjil',
    ]))->assertStatus(200);

    $countAkhir = count(DB::getQueryLog());

    // Membuktikan O(1): jumlah query tidak bertambah proporsional terhadap jumlah sesi (bebas N+1)
    expect($countAkhir)->toBeLessThanOrEqual($countAwal);
});
