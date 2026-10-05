<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup data dasar
    $this->guruUser = User::factory()->create(['role' => 'guru']);
    $this->guru = Guru::create(['user_id' => $this->guruUser->id, 'nip' => '123456']);

    $this->lainUser = User::factory()->create(['role' => 'guru']);
    $this->lainGuru = Guru::create(['user_id' => $this->lainUser->id, 'nip' => '654321']);

    $this->adminUser = User::factory()->create(['role' => 'admin']);

    $this->jurusan = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);

    $this->kelas = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X RPL 1', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027']);
    $this->kelasLain = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X RPL 2', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027']);

    $this->mapel = Mapel::create(['nama' => 'Pemrograman Web', 'kode' => 'PW']);

    // Buat siswa di kelas
    $this->siswa1User = User::factory()->create(['role' => 'siswa']);
    $this->siswa1 = Siswa::create(['user_id' => $this->siswa1User->id, 'kelas_id' => $this->kelas->id, 'nis' => 'S01']);

    $this->siswa2User = User::factory()->create(['role' => 'siswa']);
    $this->siswa2 = Siswa::create(['user_id' => $this->siswa2User->id, 'kelas_id' => $this->kelas->id, 'nis' => 'S02']);

    $this->siswaLainUser = User::factory()->create(['role' => 'siswa']);
    $this->siswaLain = Siswa::create(['user_id' => $this->siswaLainUser->id, 'kelas_id' => $this->kelasLain->id, 'nis' => 'S03']);

    // Jadwal untuk hari Senin
    $this->jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);
});

test('dashboard hanya menampilkan jadwal hari ini milik guru itu', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Jadwal milik guru lain
    Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->lainGuru->id,
        'hari' => 'senin',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    // Jadwal guru ini tapi hari lain
    Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    // Assertion array properties pass via View
    $response->assertViewHas('jadwalHariIni', function ($jadwalHariIni) {
        return $jadwalHariIni->count() === 1 && $jadwalHariIni->first()->id === $this->jadwal->id;
    });
});

test('dashboard menampilkan banner sapaan dan statistik mengajar guru', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    $kelas2 = Kelas::factory()->create();
    $mapel2 = Mapel::factory()->create();

    // Buat jadwal kedua untuk guru ini dengan kelas berbeda dan mapel berbeda
    Jadwal::create([
        'kelas_id' => $kelas2->id,
        'mapel_id' => $mapel2->id,
        'guru_id' => $this->guru->id,
        'hari' => 'rabu',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Halo, '.$this->guruUser->name.'!');
    $response->assertSee('Selamat datang di Dashboard Guru MyAbsen');
    $response->assertViewHas('total_kelas', 2);
    $response->assertViewHas('total_mapel', 2);
    $response->assertViewHas('total_jadwal', 2);
});

test('dashboard menampilkan tombol pintar lihat riwayat ketika jadwal hari ini kosong', function () {
    Carbon::setTestNow('2026-09-27 08:00:00'); // Minggu (tidak ada jadwal)

    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Tidak ada jadwal mengajar hari ini');
    $response->assertSee('Lihat Riwayat Absensi');
    $response->assertSee(route('guru.riwayat'));
});

test('AB-02: sebelum disimpan tidak ada baris detail, dan dashboard menampilkan Belum diabsen', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));
    $response->assertSee('Belum diabsen');

    $this->assertDatabaseEmpty('sesi_absensi');
    $this->assertDatabaseEmpty('detail_absensi');
});

test('AB-03: guru bisa mengabsen/koreksi jadwalnya sendiri hari ini', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Show form di tanggal hari ini
    $response = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', [
        'jadwal' => $this->jadwal->id,
        'tanggal' => '2026-09-21',
    ]));
    $response->assertStatus(200);

    // Simpan absensi pertama
    $responseStore = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'tanggal' => '2026-09-21',
        'siswa' => [
            $this->siswa1->id => ['status' => 'hadir'],
        ],
    ]);
    $responseStore->assertRedirect(route('guru.dashboard'));

    $this->assertDatabaseHas('sesi_absensi', [
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-09-21',
        'diabsen_oleh' => $this->guruUser->id,
        'diubah_oleh' => null,
    ]);

    // Koreksi absensi di hari yang sama
    $responseKoreksi = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'tanggal' => '2026-09-21',
        'siswa' => [
            $this->siswa1->id => ['status' => 'sakit', 'keterangan' => 'Demam'],
        ],
    ]);
    $responseKoreksi->assertRedirect(route('guru.dashboard'));

    $sesi = SesiAbsensi::where('jadwal_id', $this->jadwal->id)->where('tanggal', '2026-09-21')->first();
    $this->assertEquals($this->guruUser->id, $sesi->diubah_oleh);
});

test('AB-03: guru bisa koreksi tanggal 6 hari lalu yang hari-nya cocok', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin (21 Sept 2026)
    // 6 hari lalu adalah Selasa, 15 Sept 2026
    $jadwalSelasa = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    // Buka form absensi pada 6 hari lalu
    $response = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', [
        'jadwal' => $jadwalSelasa->id,
        'tanggal' => '2026-09-15',
    ]));
    $response->assertStatus(200);

    // Simpan absensi koreksi 6 hari lalu
    $responseStore = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $jadwalSelasa->id), [
        'tanggal' => '2026-09-15',
        'siswa' => [
            $this->siswa1->id => ['status' => 'hadir'],
        ],
    ]);
    $responseStore->assertRedirect(route('guru.dashboard'));

    $this->assertDatabaseHas('sesi_absensi', [
        'jadwal_id' => $jadwalSelasa->id,
        'tanggal' => '2026-09-15',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
});

test('AB-03: guru ditolak untuk 8 hari lalu atau lebih lama, tanggal masa depan, hari tidak cocok, dan jadwal guru lain', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin (21 Sept 2026)

    // 1. 8 hari lalu atau lebih lama (14 hari lalu: 2026-09-07, Senin)
    $res8HariShow = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', [
        'jadwal' => $this->jadwal->id,
        'tanggal' => '2026-09-07',
    ]));
    $res8HariShow->assertStatus(403);

    $res8HariStore = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'tanggal' => '2026-09-07',
        'siswa' => [$this->siswa1->id => ['status' => 'hadir']],
    ]);
    $res8HariStore->assertStatus(403);

    // 2. Tanggal masa depan (2026-09-28)
    $resFutureShow = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', [
        'jadwal' => $this->jadwal->id,
        'tanggal' => '2026-09-28',
    ]));
    $resFutureShow->assertStatus(403);

    $resFutureStore = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'tanggal' => '2026-09-28',
        'siswa' => [$this->siswa1->id => ['status' => 'hadir']],
    ]);
    $resFutureStore->assertStatus(403);

    // 3. Hari pada tanggal tidak cocok dengan hari jadwal (Jumat 2026-09-18 vs Senin)
    $resBedaHariShow = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', [
        'jadwal' => $this->jadwal->id,
        'tanggal' => '2026-09-18',
    ]));
    $resBedaHariShow->assertStatus(403);

    $resBedaHariStore = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'tanggal' => '2026-09-18',
        'siswa' => [$this->siswa1->id => ['status' => 'hadir']],
    ]);
    $resBedaHariStore->assertStatus(403);

    // 4. Jadwal milik guru lain
    $jadwalLain = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->lainGuru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $resJadwalLainShow = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', [
        'jadwal' => $jadwalLain->id,
        'tanggal' => '2026-09-21',
    ]));
    $resJadwalLainShow->assertStatus(403);

    $resJadwalLainStore = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $jadwalLain->id), [
        'tanggal' => '2026-09-21',
        'siswa' => [$this->siswa1->id => ['status' => 'hadir']],
    ]);
    $resJadwalLainStore->assertStatus(403);
});

test('AB-03: guru bisa mengisi susulan jadwal yang belum diabsen dalam batas', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin (21 Sept 2026)
    // Jadwal Selasa belum pernah diabsen sama sekali
    $jadwalSelasa = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    // Buka form susulan 6 hari lalu (Selasa, 15 Sept 2026)
    $response = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', [
        'jadwal' => $jadwalSelasa->id,
        'tanggal' => '2026-09-15',
    ]));
    $response->assertStatus(200);
    $response->assertSee('Belum ada data absensi untuk tanggal ini');

    // Simpan absensi susulan
    $responseStore = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $jadwalSelasa->id), [
        'tanggal' => '2026-09-15',
        'catatan' => 'Absensi susulan oleh guru',
        'siswa' => [
            $this->siswa1->id => ['status' => 'hadir'],
            $this->siswa2->id => ['status' => 'izin', 'keterangan' => 'Lomba'],
        ],
    ]);
    $responseStore->assertRedirect(route('guru.dashboard'));

    $this->assertDatabaseHas('sesi_absensi', [
        'jadwal_id' => $jadwalSelasa->id,
        'tanggal' => '2026-09-15',
        'diabsen_oleh' => $this->guruUser->id,
        'catatan' => 'Absensi susulan oleh guru',
    ]);

    $this->assertDatabaseHas('detail_absensi', [
        'siswa_id' => $this->siswa2->id,
        'status' => 'izin',
        'keterangan' => 'Lomba',
    ]);
});

test('AB-03: admin bisa koreksi tanggal 30 hari lalu, tetapi tidak masa depan', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin (21 Sept 2026)
    // 30 hari lalu: 2026-08-22
    $responseShow = $this->actingAs($this->adminUser)->get(route('guru.absensi.show', [
        'jadwal' => $this->jadwal->id,
        'tanggal' => '2026-08-22',
    ]));
    $responseShow->assertStatus(200);

    $responseStore = $this->actingAs($this->adminUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'tanggal' => '2026-08-22',
        'siswa' => [
            $this->siswa1->id => ['status' => 'hadir'],
        ],
    ]);
    $responseStore->assertRedirect(route('guru.dashboard'));

    $this->assertDatabaseHas('sesi_absensi', [
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-08-22',
        'diabsen_oleh' => $this->adminUser->id,
    ]);

    // Admin ditolak untuk tanggal masa depan (misal: 2026-09-28)
    $responseFutureShow = $this->actingAs($this->adminUser)->get(route('guru.absensi.show', [
        'jadwal' => $this->jadwal->id,
        'tanggal' => '2026-09-28',
    ]));
    $responseFutureShow->assertStatus(403);

    $responseFutureStore = $this->actingAs($this->adminUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'tanggal' => '2026-09-28',
        'siswa' => [
            $this->siswa1->id => ['status' => 'hadir'],
        ],
    ]);
    $responseFutureStore->assertStatus(403);
});

test('AB-03: koreksi tidak membuat sesi ganda dan mengisi diubah_oleh', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Jadwal Selasa dan sesi dibuat 6 hari lalu (2026-09-15) oleh admin
    $jadwalSelasa = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $sesiLama = SesiAbsensi::create([
        'jadwal_id' => $jadwalSelasa->id,
        'tanggal' => '2026-09-15',
        'diabsen_oleh' => $this->adminUser->id,
        'catatan' => 'Sesi awal',
    ]);

    // Guru mengoreksi sesi 6 hari lalu tersebut
    $response = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $jadwalSelasa->id), [
        'tanggal' => '2026-09-15',
        'catatan' => 'Sesi dikoreksi guru',
        'siswa' => [
            $this->siswa1->id => ['status' => 'izin', 'keterangan' => 'Sakit mendadak'],
        ],
    ]);
    $response->assertRedirect(route('guru.dashboard'));

    // Sesi tidak ganda (tetap 1 baris)
    $this->assertDatabaseCount('sesi_absensi', 1);

    $sesiLama->refresh();
    $this->assertEquals($this->adminUser->id, $sesiLama->diabsen_oleh);
    $this->assertEquals($this->guruUser->id, $sesiLama->diubah_oleh);
    $this->assertEquals('Sesi dikoreksi guru', $sesiLama->catatan);

    $this->assertDatabaseHas('detail_absensi', [
        'sesi_absensi_id' => $sesiLama->id,
        'siswa_id' => $this->siswa1->id,
        'status' => 'izin',
        'keterangan' => 'Sakit mendadak',
    ]);
});

test('AB-03: halaman jadwal guru memuat link ke tanggal yang benar dan badge status yang sesuai', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin (21 Sept 2026)

    // $this->jadwal adalah hari Senin (target tanggal: hari ini 2026-09-21), belum diabsen
    // Jadwal Selasa (target tanggal: 6 hari lalu 2026-09-15), belum diabsen
    $jadwalSelasa = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    // Jadwal Rabu (target tanggal: 5 hari lalu 2026-09-16), SUDAH diabsen
    $mapelRabu = Mapel::create(['nama' => 'Matematika Terapan', 'kode' => 'MTK']);
    $jadwalRabu = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapelRabu->id,
        'guru_id' => $this->guru->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    SesiAbsensi::create([
        'jadwal_id' => $jadwalRabu->id,
        'tanggal' => '2026-09-16',
        'diabsen_oleh' => $this->guruUser->id,
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal'));
    $response->assertStatus(200);

    // Jadwal Senin: link ke tanggal 2026-09-21 dan badge Hari ini
    $response->assertSee(route('guru.absensi.show', ['jadwal' => $this->jadwal->id, 'tanggal' => '2026-09-21']));
    $response->assertSee('Hari ini');

    // Jadwal Selasa: link ke tanggal 2026-09-15 dan badge Belum diabsen
    $response->assertSee(route('guru.absensi.show', ['jadwal' => $jadwalSelasa->id, 'tanggal' => '2026-09-15']));
    $response->assertSee('Belum diabsen');

    // Jadwal Rabu: link ke tanggal 2026-09-16 dan badge Sudah diabsen
    $response->assertSee(route('guru.absensi.show', ['jadwal' => $jadwalRabu->id, 'tanggal' => '2026-09-16']));
});

test('AB-04 dan AB-09: semua siswa kelas mendapat baris detail, dan field log terisi', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    $response = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'catatan' => 'Sesi pertama',
        'siswa' => [
            $this->siswa1->id => [
                'status' => 'hadir',
            ],
            $this->siswa2->id => [
                'status' => 'izin',
                'keterangan' => 'Acara keluarga',
            ],
        ],
    ]);

    $response->assertRedirect(route('guru.dashboard'));

    // Cek Database
    $this->assertDatabaseHas('sesi_absensi', [
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-09-21',
        'catatan' => 'Sesi pertama',
        'diabsen_oleh' => $this->guruUser->id,
        'diubah_oleh' => null,
    ]);

    $sesi = SesiAbsensi::first();

    $this->assertDatabaseHas('detail_absensi', [
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $this->siswa1->id,
        'status' => 'hadir',
    ]);

    $this->assertDatabaseHas('detail_absensi', [
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $this->siswa2->id,
        'status' => 'izin',
        'keterangan' => 'Acara keluarga',
    ]);
});

test('AB-01: membuka dan menyimpan dua kali tidak membuat sesi ganda', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Simpan pertama
    $res1 = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswa1->id => ['status' => 'hadir'],
        ],
    ]);
    $res1->assertSessionHasNoErrors();
    $res1->assertRedirect();
    $this->assertDatabaseCount('sesi_absensi', 1);

    // Simpan kedua
    $res2 = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswa1->id => ['status' => 'sakit'],
        ],
    ]);
    $res2->assertSessionHasNoErrors();
    $res2->assertRedirect();

    $this->assertDatabaseCount('sesi_absensi', 1);
    $this->assertDatabaseCount('detail_absensi', 2); // Karena ada 2 siswa di kelas

    $sesi = SesiAbsensi::first();
    $this->assertEquals($this->guruUser->id, $sesi->diubah_oleh);

    $this->assertDatabaseHas('detail_absensi', [
        'siswa_id' => $this->siswa1->id,
        'status' => 'sakit',
    ]);
});

test('status tidak valid dan siswa dari kelas lain ditolak', function () {
    Carbon::setTestNow('2026-09-21 08:00:00'); // Senin

    // Status tidak valid
    $response = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswa1->id => ['status' => 'tidak_valid'],
        ],
    ]);

    $response->assertSessionHasErrors(['siswa.'.$this->siswa1->id.'.status']);

    // Siswa beda kelas
    $response2 = $this->actingAs($this->guruUser)->post(route('guru.absensi.store', $this->jadwal->id), [
        'siswa' => [
            $this->siswaLain->id => ['status' => 'hadir'],
        ],
    ]);

    $response2->assertSessionHasErrors(['siswa.'.$this->siswaLain->id]);
});

test('AB-03: guru melihat halaman jadwal & koreksi absensi dan hanya jadwal miliknya dalam 7 hari terakhir tanpa tanggal masa depan atau lewat batas', function () {
    Carbon::setTestNow('2026-10-03 10:00:00'); // Sabtu

    // Jadwal guru ini pada hari Sabtu (jatuh pada hari ini 2026-10-03)
    $jadwalSabtu = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'sabtu',
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '09:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    // Jadwal guru LAIN pada hari Senin
    $jadwalGuruLain = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->lainGuru->id,
        'hari' => 'senin',
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '11:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('guru.koreksi-absensi'));

    $response->assertStatus(200);
    $response->assertViewHas('daftarHari', function ($daftarHari) use ($jadwalSabtu, $jadwalGuruLain) {
        if (count($daftarHari) !== 7) {
            return false;
        }

        // Tanggal teratas adalah hari ini (2026-10-03)
        if ($daftarHari[0]['tanggal'] !== '2026-10-03' || ! $daftarHari[0]['is_hari_ini']) {
            return false;
        }

        // Tanggal paling akhir adalah H-6 (2026-09-27)
        if ($daftarHari[6]['tanggal'] !== '2026-09-27') {
            return false;
        }

        // Tidak boleh ada tanggal di masa depan atau lebih lama dari H-6
        foreach ($daftarHari as $item) {
            if ($item['tanggal'] > '2026-10-03' || $item['tanggal'] < '2026-09-27') {
                return false;
            }
        }

        // Jadwal Sabtu ada di hari Sabtu
        $hariSabtuItem = $daftarHari[0];
        $jadwalIdsSabtu = $hariSabtuItem['jadwals']->pluck('id')->all();
        if (! in_array($jadwalSabtu->id, $jadwalIdsSabtu)) {
            return false;
        }

        // Jadwal Senin milik guru ada di hari Senin (2026-09-28, index 5)
        $hariSeninItem = $daftarHari[5];
        $jadwalIdsSenin = $hariSeninItem['jadwals']->pluck('id')->all();
        if (! in_array($this->jadwal->id, $jadwalIdsSenin)) {
            return false;
        }

        // Jadwal milik guru lain TIDAK boleh ada
        if (in_array($jadwalGuruLain->id, $jadwalIdsSenin)) {
            return false;
        }

        return true;
    });

    // Tanggal tanpa jadwal menampilkan "Tidak ada jadwal"
    $response->assertSee('Tidak ada jadwal');
});

test('AB-03: badge status sesuai kondisi sesi (sudah diabsen / belum diabsen)', function () {
    Carbon::setTestNow('2026-10-03 10:00:00'); // Sabtu (2026-10-03)

    // Sesi sudah ada untuk jadwal Senin (2026-09-28)
    SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2026-09-28',
        'diabsen_oleh' => $this->guruUser->id,
    ]);

    // Jadwal guru di hari Sabtu (2026-10-03) belum ada sesi
    $jadwalSabtu = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'sabtu',
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '09:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('guru.koreksi-absensi'));

    $response->assertStatus(200);
    $response->assertSee('Sudah diabsen');
    $response->assertSee('Belum diabsen');
    $response->assertSee('Hari ini');

    $response->assertViewHas('daftarHari', function ($daftarHari) use ($jadwalSabtu) {
        $hariSabtu = $daftarHari[0]; // 2026-10-03
        $jSabtu = $hariSabtu['jadwals']->firstWhere('id', $jadwalSabtu->id);
        if ($jSabtu->status_absensi !== 'Belum diabsen' || ! $jSabtu->is_hari_ini) {
            return false;
        }

        $hariSenin = $daftarHari[5]; // 2026-09-28
        $jSenin = $hariSenin['jadwals']->firstWhere('id', $this->jadwal->id);
        if ($jSenin->status_absensi !== 'Sudah diabsen') {
            return false;
        }

        return true;
    });
});

test('AB-03: link tiap jadwal mengarah ke tanggal yang benar', function () {
    Carbon::setTestNow('2026-10-03 10:00:00'); // Sabtu (2026-10-03)

    // Jadwal Senin jatuh pada 2026-09-28
    $expectedUrl = route('guru.absensi.show', [
        'jadwal' => $this->jadwal->id,
        'tanggal' => '2026-09-28',
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('guru.koreksi-absensi'));

    $response->assertStatus(200);
    $response->assertSee($expectedUrl, false);
});

test('AB-03: admin dan siswa tidak bisa mengakses halaman jadwal & koreksi absensi guru (403)', function () {
    // Admin mencoba akses
    $responseAdmin = $this->actingAs($this->adminUser)->get(route('guru.koreksi-absensi'));
    $responseAdmin->assertStatus(403);

    // Siswa mencoba akses
    $responseSiswa = $this->actingAs($this->siswa1User)->get(route('guru.koreksi-absensi'));
    $responseSiswa->assertStatus(403);
});

test('AB-03: menu jadwal & koreksi absensi muncul di sidebar guru tepat setelah jadwal mengajar', function () {
    $response = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Jadwal Mengajar');
    $response->assertSee('Jadwal & Koreksi Absensi', false);
    $response->assertSee('Riwayat');

    $html = $response->getContent();
    $posDashboard = strpos($html, route('guru.dashboard'));
    $posJadwal = strpos($html, route('guru.jadwal'));
    $posKoreksi = strpos($html, route('guru.koreksi-absensi'));
    $posRiwayat = strpos($html, route('guru.riwayat'));

    expect($posDashboard)->not->toBeFalse();
    expect($posJadwal)->not->toBeFalse();
    expect($posKoreksi)->not->toBeFalse();
    expect($posRiwayat)->not->toBeFalse();

    expect($posDashboard)->toBeLessThan($posJadwal);
    expect($posJadwal)->toBeLessThan($posKoreksi);
    expect($posKoreksi)->toBeLessThan($posRiwayat);
});

afterEach(function () {
    Carbon::setTestNow(); // Reset time
});
