<?php

use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Pengaturan;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use App\Support\KelasMapel;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    // Setup periode aktif: 2026/2027 Ganjil
    Pengaturan::updateOrCreate(['kunci' => 'tahun_ajaran_aktif'], ['nilai' => '2026/2027']);
    Pengaturan::updateOrCreate(['kunci' => 'semester_aktif'], ['nilai' => 'Ganjil']);

    $this->jurusan = Jurusan::create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);

    $this->kelas1 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 'X',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->kelas2 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 2',
        'tingkat' => 'X',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->kelasLama = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'XI TKJ 1',
        'tingkat' => 'XI',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    $this->mapel1 = Mapel::create(['nama' => 'Pemrograman Dasar', 'kode' => 'PROGDAS']);
    $this->mapel2 = Mapel::create(['nama' => 'Jaringan Dasar', 'kode' => 'JARDAS']);
    $this->mapelLain = Mapel::create(['nama' => 'Sistem Komputer', 'kode' => 'SISKOM']);

    // Guru 1 (Pengguna utama yang diuji)
    $this->guruUser1 = User::factory()->create([
        'name' => 'Guru Penguji 1',
        'username' => 'guru1',
        'role' => 'guru',
    ]);
    $this->guru1 = Guru::create([
        'user_id' => $this->guruUser1->id,
        'nip' => '198001012005011001',
    ]);

    // Guru 2 (Guru lain)
    $this->guruUser2 = User::factory()->create([
        'name' => 'Guru Penguji 2',
        'username' => 'guru2',
        'role' => 'guru',
    ]);
    $this->guru2 = Guru::create([
        'user_id' => $this->guruUser2->id,
        'nip' => '198001012005011002',
    ]);

    // Jadwal Guru 1 pada periode aktif
    $this->jadwal1 = Jadwal::create([
        'kelas_id' => $this->kelas1->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->jadwal2 = Jadwal::create([
        'kelas_id' => $this->kelas2->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal Guru 1 pada periode TIDAK aktif (2025/2026 Genap)
    $this->jadwalLama = Jadwal::create([
        'kelas_id' => $this->kelasLama->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    // Jadwal Guru 2 pada periode aktif
    $this->jadwalGuruLain = Jadwal::create([
        'kelas_id' => $this->kelas1->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guru2->id,
        'hari' => 'kamis',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('AB-08: 1. Guru melihat halaman riwayat berisi kartu kelas-mapel miliknya pada periode aktif saja dan kelas-mapel guru lain tidak muncul', function () {
    $response = $this->actingAs($this->guruUser1)->get(route('guru.riwayat'));

    $response->assertStatus(200);

    // Memuat kartu jadwal kelas-mapel milik Guru 1 pada periode aktif
    $response->assertSee('X TKJ 1');
    $response->assertSee('Pemrograman Dasar');
    $response->assertSee('X TKJ 2');
    $response->assertSee('Jaringan Dasar');

    // Tidak memuat kelas dari periode lama
    $response->assertDontSee('XI TKJ 1');

    // Tidak memuat mapel yang diajar guru lain
    $response->assertDontSee('Sistem Komputer');
});

test('AB-08: 2. Detail riwayat kelas-mapel yang diajar tampil dengan matriks pertemuan dan daftar siswa dengan data benar', function () {
    // Buat siswa di kelas 1
    $siswaUser1 = User::factory()->create(['name' => 'Ahmad Fauzi', 'role' => 'siswa']);
    $siswa1 = Siswa::create(['user_id' => $siswaUser1->id, 'nis' => '1001', 'kelas_id' => $this->kelas1->id]);

    $siswaUser2 = User::factory()->create(['name' => 'Budi Santoso', 'role' => 'siswa']);
    $siswa2 = Siswa::create(['user_id' => $siswaUser2->id, 'nis' => '1002', 'kelas_id' => $this->kelas1->id]);

    // Buat 2 sesi absensi pada periode aktif
    $sesi1 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal1->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $this->guruUser1->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $siswa1->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $siswa2->id, 'status' => 'izin']);

    $sesi2 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal1->id,
        'tanggal' => '2026-08-10',
        'diabsen_oleh' => $this->guruUser1->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswa1->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswa2->id, 'status' => 'alpa']);

    $response = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas1->id,
        'mapel' => $this->mapel1->id,
    ]));

    $response->assertStatus(200);

    // Periksa header & informasi kelas-mapel
    $response->assertSee('Riwayat Absensi: X TKJ 1 - Pemrograman Dasar');
    $response->assertSee('2 siswa');
    $response->assertSee('2 pertemuan');

    // Periksa daftar siswa
    $response->assertSee('Ahmad Fauzi');
    $response->assertSee('1001');
    $response->assertSee('Budi Santoso');
    $response->assertSee('1002');

    // Periksa label pertemuan matriks horizontal P1 dan P2
    $response->assertSee('P1');
    $response->assertSee('P2');
});

test('AB-08: 3. Detail riwayat kelas-mapel yang TIDAK diajar guru itu mendapat 403', function () {
    // Guru 1 mencoba mengakses kelas 1 dan mapel lain yang diajar Guru 2
    $response = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas1->id,
        'mapel' => $this->mapelLain->id,
    ]));

    // Otorisasi melalui Policy menolak dengan 403 Forbidden
    $response->assertStatus(403);
});

test('AB-08: 4. Admin dan siswa tidak bisa mengakses halaman riwayat guru (403) dan tamu dialihkan ke login', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $siswaUser = User::factory()->create(['role' => 'siswa']);

    // Admin mencoba akses riwayat guru
    $this->actingAs($admin)->get(route('guru.riwayat'))->assertStatus(403);
    $this->actingAs($admin)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas1->id,
        'mapel' => $this->mapel1->id,
    ]))->assertStatus(403);

    // Siswa mencoba akses riwayat guru
    $this->actingAs($siswaUser)->get(route('guru.riwayat'))->assertStatus(403);
    $this->actingAs($siswaUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas1->id,
        'mapel' => $this->mapel1->id,
    ]))->assertStatus(403);

    // Tamu (guest) dialihkan ke halaman login
    auth()->logout();
    $this->get(route('guru.riwayat'))->assertRedirect(route('login'));
    $this->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas1->id,
        'mapel' => $this->mapel1->id,
    ]))->assertRedirect(route('login'));
});

test('AB-08: 5. Tombol ekspor di detail mengirim kelas_mapel berformat benar dan ekspor Excel dari detail tetap berhasil dengan nama file lama', function () {
    Excel::fake();

    $expectedParam = KelasMapel::make($this->kelas1->id, $this->mapel1->id);

    // Pastikan tombol ekspor di view detail memiliki format kelas_mapel yang benar
    $detailResponse = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelas1->id,
        'mapel' => $this->mapel1->id,
    ]));
    $detailResponse->assertStatus(200);
    $detailResponse->assertSee(route('guru.laporan.export', ['kelas_mapel' => $expectedParam]));

    // Eksekusi download Excel via route ekspor guru
    $exportResponse = $this->actingAs($this->guruUser1)->get(route('guru.laporan.export', [
        'kelas_mapel' => $expectedParam,
    ]));

    $exportResponse->assertStatus(200);

    $expectedFilename = 'rekap_absensi_guru_'.date('Y-m-d').'.xlsx';
    Excel::assertDownloaded($expectedFilename);
});

test('AB-08: 6. kelas_mapel tidak valid ("abc", "1-", "1-2-3") menghasilkan error validasi pada ekspor guru, bukan error 500', function () {
    // Uji string non-numerik "abc"
    $responseAbc = $this->actingAs($this->guruUser1)->get(route('guru.laporan.export', [
        'kelas_mapel' => 'abc',
    ]));
    $responseAbc->assertSessionHasErrors('kelas_mapel');
    $responseAbc->assertStatus(302);

    // Uji string "1-"
    $responseDash = $this->actingAs($this->guruUser1)->get(route('guru.laporan.export', [
        'kelas_mapel' => '1-',
    ]));
    $responseDash->assertSessionHasErrors('kelas_mapel');
    $responseDash->assertStatus(302);

    // Uji string "1-2-3"
    $responseTriple = $this->actingAs($this->guruUser1)->get(route('guru.laporan.export', [
        'kelas_mapel' => '1-2-3',
    ]));
    $responseTriple->assertSessionHasErrors('kelas_mapel');
    $responseTriple->assertStatus(302);

    // Uji format JSON / AJAX (memastikan HTTP 422, bukan 500)
    $responseJson = $this->actingAs($this->guruUser1)->json('GET', route('guru.laporan.export'), [
        'kelas_mapel' => 'abc',
    ]);
    $responseJson->assertStatus(422);
    $responseJson->assertJsonValidationErrors(['kelas_mapel']);
});

test('AB-08: 7. Unit test parser kelas_mapel (valid, tidak valid, pembentukan)', function () {
    // Valid cases
    expect(KelasMapel::isValid('1-2'))->toBeTrue();
    expect(KelasMapel::isValid('10-25'))->toBeTrue();
    expect(KelasMapel::isValid('999-1234'))->toBeTrue();

    $parsed = KelasMapel::parse('12-34');
    expect($parsed)->not->toBeNull();
    expect($parsed['kelas_id'])->toBe(12);
    expect($parsed['mapel_id'])->toBe(34);
    expect($parsed[0])->toBe(12);
    expect($parsed[1])->toBe(34);

    // Destructuring support
    [$kelasId, $mapelId] = KelasMapel::parse('45-67');
    expect($kelasId)->toBe(45);
    expect($mapelId)->toBe(67);

    // Invalid cases
    expect(KelasMapel::isValid('abc'))->toBeFalse();
    expect(KelasMapel::isValid('1-'))->toBeFalse();
    expect(KelasMapel::isValid('-2'))->toBeFalse();
    expect(KelasMapel::isValid('1-2-3'))->toBeFalse();
    expect(KelasMapel::isValid('0-1'))->toBeFalse();
    expect(KelasMapel::isValid('1-0'))->toBeFalse();
    expect(KelasMapel::isValid(''))->toBeFalse();
    expect(KelasMapel::isValid(null))->toBeFalse();

    expect(KelasMapel::parse('abc'))->toBeNull();
    expect(KelasMapel::parse('1-'))->toBeNull();
    expect(KelasMapel::parse('1-2-3'))->toBeNull();
    expect(KelasMapel::parse(null))->toBeNull();

    // Formation (make)
    expect(KelasMapel::make(1, 2))->toBe('1-2');
    expect(KelasMapel::make('10', '20'))->toBe('10-20');
});

test('AB-08: dua guru mengajar kelas-mapel yang sama pada periode aktif, jadwal guru B dibuat lebih dulu, guru A dan B mendapat 200, guru C mendapat 403, kelas-mapel tanpa jadwal mendapat 404', function () {
    $kelasBersama = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ Bersama',
        'tingkat' => 'X',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $mapelBersama = Mapel::create(['nama' => 'Kewirausahaan', 'kode' => 'KWU']);

    // Guru A, Guru B, Guru C
    $userA = User::factory()->create(['name' => 'Guru A', 'role' => 'guru', 'username' => 'guru_a']);
    $guruA = Guru::create(['user_id' => $userA->id, 'nip' => '11111111']);

    $userB = User::factory()->create(['name' => 'Guru B', 'role' => 'guru', 'username' => 'guru_b']);
    $guruB = Guru::create(['user_id' => $userB->id, 'nip' => '22222222']);

    $userC = User::factory()->create(['name' => 'Guru C', 'role' => 'guru', 'username' => 'guru_c']);
    $guruC = Guru::create(['user_id' => $userC->id, 'nip' => '33333333']);

    // Jadwal Guru B dibuat LEBIH DULU
    Jadwal::create([
        'kelas_id' => $kelasBersama->id,
        'mapel_id' => $mapelBersama->id,
        'guru_id' => $guruB->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal Guru A dibuat SETELAHNYA
    Jadwal::create([
        'kelas_id' => $kelasBersama->id,
        'mapel_id' => $mapelBersama->id,
        'guru_id' => $guruA->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // 1. Guru A mendapat 200 dan melihat data kelas itu
    $responseA = $this->actingAs($userA)->get(route('guru.riwayat.detail', [
        'kelas' => $kelasBersama->id,
        'mapel' => $mapelBersama->id,
    ]));
    $responseA->assertStatus(200);
    $responseA->assertSee('X TKJ Bersama');
    $responseA->assertSee('Kewirausahaan');

    // 2. Guru B mendapat 200 dan melihat data kelas itu
    $responseB = $this->actingAs($userB)->get(route('guru.riwayat.detail', [
        'kelas' => $kelasBersama->id,
        'mapel' => $mapelBersama->id,
    ]));
    $responseB->assertStatus(200);
    $responseB->assertSee('X TKJ Bersama');
    $responseB->assertSee('Kewirausahaan');

    // 3. Guru C yang tidak mengajar mendapat 403
    $responseC = $this->actingAs($userC)->get(route('guru.riwayat.detail', [
        'kelas' => $kelasBersama->id,
        'mapel' => $mapelBersama->id,
    ]));
    $responseC->assertStatus(403);

    // 4. Kelas-mapel tanpa jadwal mendapat 404
    $mapelTanpaJadwal = Mapel::create(['nama' => 'Pendidikan Agama', 'kode' => 'PAI']);
    $response404 = $this->actingAs($userA)->get(route('guru.riwayat.detail', [
        'kelas' => $kelasBersama->id,
        'mapel' => $mapelTanpaJadwal->id,
    ]));
    $response404->assertStatus(404);
});
