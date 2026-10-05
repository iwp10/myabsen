<?php

use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('AB-05: 1. Hanya admin yang bisa membuka dan memulihkan (guru/siswa 403, tamu ke login). jenis tidak valid = 404; id yang tidak terhapus atau tidak ada = 404', function () {
    $guruUser = User::factory()->create(['role' => 'guru']);
    $siswaUser = User::factory()->create(['role' => 'siswa']);

    $trashedGuru = Guru::factory()->create();
    $trashedGuru->delete();

    // Tamu -> login
    $this->get(route('admin.arsip.index'))->assertRedirect(route('login'));
    $this->post(route('admin.arsip.pulihkan', ['jenis' => 'guru', 'id' => $trashedGuru->id]))->assertRedirect(route('login'));

    // Guru -> 403
    $this->actingAs($guruUser)->get(route('admin.arsip.index'))->assertForbidden();
    $this->actingAs($guruUser)->post(route('admin.arsip.pulihkan', ['jenis' => 'guru', 'id' => $trashedGuru->id]))->assertForbidden();

    // Siswa -> 403
    $this->actingAs($siswaUser)->get(route('admin.arsip.index'))->assertForbidden();
    $this->actingAs($siswaUser)->post(route('admin.arsip.pulihkan', ['jenis' => 'guru', 'id' => $trashedGuru->id]))->assertForbidden();

    // Admin -> 200
    $this->actingAs($this->admin)->get(route('admin.arsip.index'))->assertOk();

    // Jenis tidak valid -> 404
    $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'invalid']))->assertNotFound();
    $this->actingAs($this->admin)->post('/admin/arsip/invalid/'.$trashedGuru->id.'/pulihkan')->assertNotFound();

    // ID tidak terhapus (aktif) -> 404
    $activeGuru = Guru::factory()->create();
    $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'guru', 'id' => $activeGuru->id]))->assertNotFound();

    // ID tidak ada -> 404
    $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'guru', 'id' => 999999]))->assertNotFound();
});

test('AB-05: 2. Tiap tab hanya menampilkan data terhapus sesuai jenis, dengan pencarian dan paginasi', function () {
    // Buat data trashed dan aktif untuk guru, siswa, kelas, mapel
    $guruTrashed = Guru::factory()->create(['nip' => '19800001']);
    $guruTrashed->delete();
    $guruAktif = Guru::factory()->create(['nip' => '19800002']);

    $jurusan = Jurusan::factory()->create();
    $kelasTrashed = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'X RPL Trashed']);
    $kelasTrashed->delete();
    $kelasAktif = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'X RPL Aktif']);

    $siswaTrashed = Siswa::factory()->create(['kelas_id' => $kelasAktif->id, 'nis' => '11111']);
    $siswaTrashed->delete();
    $siswaAktif = Siswa::factory()->create(['kelas_id' => $kelasAktif->id, 'nis' => '22222']);

    $mapelTrashed = Mapel::factory()->create(['nama' => 'Mapel Terhapus', 'kode' => 'MTRH']);
    $mapelTrashed->delete();
    $mapelAktif = Mapel::factory()->create(['nama' => 'Mapel Aktif', 'kode' => 'MAKT']);

    // Tab guru: menampilkan guru trashed, tidak menampilkan guru aktif atau jenis lain
    $resGuru = $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'guru']));
    $resGuru->assertOk();
    $resGuru->assertSee($guruTrashed->nip);
    $resGuru->assertDontSee($guruAktif->nip);
    $resGuru->assertDontSee($siswaTrashed->nis);

    // Tab siswa
    $resSiswa = $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'siswa']));
    $resSiswa->assertOk();
    $resSiswa->assertSee($siswaTrashed->nis);
    $resSiswa->assertDontSee($siswaAktif->nis);

    // Tab kelas
    $resKelas = $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'kelas']));
    $resKelas->assertOk();
    $resKelas->assertSee('X RPL Trashed');
    $resKelas->assertDontSee('X RPL Aktif');

    // Tab mapel
    $resMapel = $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'mapel']));
    $resMapel->assertOk();
    $resMapel->assertSee('Mapel Terhapus');
    $resMapel->assertDontSee('Mapel Aktif');

    // Pencarian di tab guru
    $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'guru', 'search' => '19800001']))
        ->assertSee($guruTrashed->nip);
    $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'guru', 'search' => '99999999']))
        ->assertSee('Tidak ada data terhapus.');

    // Paginasi 10 item
    for ($i = 1; $i <= 11; $i++) {
        $g = Guru::factory()->create(['nip' => 'GURU_PAGI_'.$i]);
        $g->delete();
    }
    $resPaginasi = $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'guru']));
    $resPaginasi->assertOk();
    $resPaginasi->assertSee('page=2');
});

test('AB-05: 3. Memulihkan guru, siswa, kelas, dan mapel mengembalikan ke status aktif dan akun bisa login lagi', function () {
    // 1. Guru
    $guruUser = User::factory()->create(['role' => 'guru', 'username' => 'GURUPULIH', 'password' => Hash::make('password')]);
    $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => 'GURUPULIH']);
    $guru->delete();

    // Sebelum restore: login guru ditolak (AB-05)
    $loginRes = $this->post(route('login'), ['username' => 'GURUPULIH', 'password' => 'password']);
    $loginRes->assertSessionHasErrors('username');

    // Pulihkan guru
    $res = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'guru', 'id' => $guru->id]));
    $res->assertRedirect(route('admin.arsip.index', ['jenis' => 'guru']));
    $res->assertSessionHas('success');
    expect($guru->fresh()->trashed())->toBeFalse();

    // Tampil di daftar guru admin
    $this->actingAs($this->admin)->get(route('admin.guru.index'))->assertSee('GURUPULIH');

    // Akun guru bisa login lagi
    $this->post(route('logout'));
    $loginSuccess = $this->post(route('login'), ['username' => 'GURUPULIH', 'password' => 'password']);
    $loginSuccess->assertRedirect(route('guru.dashboard'));

    // Logout guru sebelum mencoba login siswa
    $this->post(route('logout'));

    // 2. Siswa (login lewat NIS)
    $jurusan = Jurusan::factory()->create();
    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
    $siswaUser = User::factory()->create(['role' => 'siswa', 'username' => 'SISWAPULIH', 'password' => Hash::make('password')]);
    $siswa = Siswa::create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id, 'nis' => 'SISWAPULIH']);
    $siswa->delete();

    // Sebelum restore: login siswa ditolak
    $loginSiswaBefore = $this->post(route('login'), ['username' => 'SISWAPULIH', 'password' => 'password']);
    $loginSiswaBefore->assertSessionHasErrors('username');

    // Pulihkan siswa
    $resSiswa = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'siswa', 'id' => $siswa->id]));
    $resSiswa->assertRedirect(route('admin.arsip.index', ['jenis' => 'siswa']));
    $resSiswa->assertSessionHas('success');
    expect($siswa->fresh()->trashed())->toBeFalse();

    // Tampil di daftar siswa admin
    $this->actingAs($this->admin)->get(route('admin.siswa.index'))->assertSee('SISWAPULIH');

    // Siswa bisa login lagi dengan NIS
    $this->post(route('logout'));
    $loginSiswa = $this->post(route('login'), ['username' => 'SISWAPULIH', 'password' => 'password']);
    $loginSiswa->assertRedirect(route('siswa.dashboard'));

    // 3. Kelas
    $kelasRestore = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'KELAS_PULIH']);
    $kelasRestore->delete();
    $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'kelas', 'id' => $kelasRestore->id]))
        ->assertRedirect(route('admin.arsip.index', ['jenis' => 'kelas']));
    expect($kelasRestore->fresh()->trashed())->toBeFalse();
    $this->actingAs($this->admin)->get(route('admin.kelas.index'))->assertSee('KELAS_PULIH');

    // 4. Mapel
    $mapel = Mapel::factory()->create(['nama' => 'Mapel Pulih', 'kode' => 'MPL_PULIH']);
    $mapel->delete();
    $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'mapel', 'id' => $mapel->id]))
        ->assertRedirect(route('admin.arsip.index', ['jenis' => 'mapel']));
    expect($mapel->fresh()->trashed())->toBeFalse();
    $this->actingAs($this->admin)->get(route('admin.mapel.index'))->assertSee('MPL_PULIH');
});

test('AB-05: 4. Siswa dengan kelas yang masih terhapus ditolak dipulihkan dengan pesan jelas; setelah kelasnya dipulihkan, siswa berhasil dipulihkan', function () {
    $jurusan = Jurusan::factory()->create();
    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'X TKJ 1']);
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);

    // Hapus kelas dan siswa
    $kelas->delete();
    $siswa->delete();

    // Coba pulihkan siswa saat kelas masih terhapus
    $res = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'siswa', 'id' => $siswa->id]));
    $res->assertRedirect(route('admin.arsip.index', ['jenis' => 'siswa']));
    $res->assertSessionHas('error', "Kelas {$kelas->nama} masih terhapus. Pulihkan kelas tersebut lebih dulu.");
    expect($siswa->fresh()->trashed())->toBeTrue();

    // Pulihkan kelas terlebih dahulu
    $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'kelas', 'id' => $kelas->id]))
        ->assertSessionHas('success');
    expect($kelas->fresh()->trashed())->toBeFalse();

    // Sekarang pulihkan siswa
    $resSuccess = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'siswa', 'id' => $siswa->id]));
    $resSuccess->assertSessionHas('success');
    expect($siswa->fresh()->trashed())->toBeFalse();
});

test('AB-05: 5. Bentrok NIP/NIS/kode/nama dengan data aktif ditolak dengan pesan ramah dan data tetap terhapus', function () {
    // Bentrok Guru NIP
    $guruAktif = Guru::factory()->create(['nip' => 'NIP_SAMA']);
    $trashedGuruUser = User::factory()->create(['username' => 'OLD_USER_GURU']);
    $guruTrashed = Guru::create(['user_id' => $trashedGuruUser->id, 'nip' => 'NIP_SAMA']);
    $guruTrashed->delete();

    $resGuru = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'guru', 'id' => $guruTrashed->id]));
    $resGuru->assertSessionHas('error');
    expect(session('error'))->toContain('NIP_SAMA');
    expect($guruTrashed->fresh()->trashed())->toBeTrue();

    // Bentrok Siswa NIS
    $jurusan = Jurusan::factory()->create();
    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
    $trashedSiswaUser = User::factory()->create(['username' => 'OLD_USER_SISWA']);
    $siswaTrashed = Siswa::create(['user_id' => $trashedSiswaUser->id, 'kelas_id' => $kelas->id, 'nis' => 'NIS_SAMA']);
    $siswaTrashed->delete();

    $siswaAktifUser = User::factory()->create(['username' => 'NIS_SAMA']);
    $resSiswa = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'siswa', 'id' => $siswaTrashed->id]));
    $resSiswa->assertSessionHas('error');
    expect(session('error'))->toContain('NIS_SAMA');
    expect($siswaTrashed->fresh()->trashed())->toBeTrue();

    // Bentrok Mapel Kode
    $mapelAktif = Mapel::factory()->create(['kode' => 'KD_SAMA']);
    $mapelTrashed = Mapel::create(['nama' => 'Mapel Lama', 'kode' => 'KD_SAMA']);
    $mapelTrashed->delete();

    $resMapel = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'mapel', 'id' => $mapelTrashed->id]));
    $resMapel->assertSessionHas('error');
    expect(session('error'))->toContain('KD_SAMA');
    expect($mapelTrashed->fresh()->trashed())->toBeTrue();

    // Bentrok Kelas: nama + tahun_ajaran + semester sama
    $kelasAktif = Kelas::factory()->create([
        'jurusan_id' => $jurusan->id,
        'nama' => 'X RPL Bentrok',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $kelasTrashed = Kelas::create([
        'jurusan_id' => $jurusan->id,
        'nama' => 'X RPL Bentrok',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $kelasTrashed->delete();

    $resKelas = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'kelas', 'id' => $kelasTrashed->id]));
    $resKelas->assertSessionHas('error');
    expect($kelasTrashed->fresh()->trashed())->toBeTrue();

    // Bentrok Kelas: jurusan sudah tidak ada
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    $kelasYatim = Kelas::create([
        'jurusan_id' => 99999,
        'nama' => 'Kelas Yatim',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);
    $kelasYatim->delete();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    $resYatim = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'kelas', 'id' => $kelasYatim->id]));
    $resYatim->assertSessionHas('error', 'Jurusan untuk kelas ini sudah tidak ada.');
    expect($kelasYatim->fresh()->trashed())->toBeTrue();
});

test('AB-05: 6. Membuat guru/siswa/mapel baru dengan NIP/NIS/kode milik data terhapus ditolak dengan pesan yang menyebut menu Data Terhapus dan nama data itu; duplikat dengan data aktif tetap ditolak dengan pesan lama', function () {
    // 1. Guru
    $guruUser = User::factory()->create(['name' => 'Guru Trashed Budi', 'username' => 'NIP_TRASHED']);
    $guruTrashed = Guru::create(['user_id' => $guruUser->id, 'nip' => 'NIP_TRASHED']);
    $guruTrashed->delete();

    $activeGuruUser = User::factory()->create(['name' => 'Guru Aktif', 'username' => 'NIP_AKTIF']);
    $activeGuru = Guru::create(['user_id' => $activeGuruUser->id, 'nip' => 'NIP_AKTIF']);

    // Coba buat guru baru dengan NIP terhapus
    $resCreateGuruTrashed = $this->actingAs($this->admin)->post(route('admin.guru.store'), [
        'name' => 'Guru Baru',
        'nip' => 'NIP_TRASHED',
    ]);
    $resCreateGuruTrashed->assertSessionHasErrors('nip');
    $errMsgGuru = session('errors')->first('nip');
    expect($errMsgGuru)->toContain('Data Terhapus')->toContain('Guru Trashed Budi');

    // Coba buat guru baru dengan NIP aktif -> pesan lama
    $resCreateGuruAktif = $this->actingAs($this->admin)->post(route('admin.guru.store'), [
        'name' => 'Guru Baru 2',
        'nip' => 'NIP_AKTIF',
    ]);
    $resCreateGuruAktif->assertSessionHasErrors(['nip' => 'NIP sudah terdaftar.']);

    // 2. Siswa
    $jurusan = Jurusan::factory()->create();
    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
    $siswaUser = User::factory()->create(['name' => 'Siswa Trashed Ani', 'username' => 'NIS_TRASHED']);
    $siswaTrashed = Siswa::create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id, 'nis' => 'NIS_TRASHED']);
    $siswaTrashed->delete();

    $activeSiswaUser = User::factory()->create(['name' => 'Siswa Aktif', 'username' => 'NIS_AKTIF']);
    $activeSiswa = Siswa::create(['user_id' => $activeSiswaUser->id, 'kelas_id' => $kelas->id, 'nis' => 'NIS_AKTIF']);

    // Coba buat siswa baru dengan NIS terhapus
    $resCreateSiswaTrashed = $this->actingAs($this->admin)->post(route('admin.siswa.store'), [
        'name' => 'Siswa Baru',
        'nis' => 'NIS_TRASHED',
        'kelas_id' => $kelas->id,
    ]);
    $resCreateSiswaTrashed->assertSessionHasErrors('nis');
    $errMsgSiswa = session('errors')->first('nis');
    expect($errMsgSiswa)->toContain('Data Terhapus')->toContain('Siswa Trashed Ani');

    // Coba buat siswa baru dengan NIS aktif -> pesan lama
    $resCreateSiswaAktif = $this->actingAs($this->admin)->post(route('admin.siswa.store'), [
        'name' => 'Siswa Baru 2',
        'nis' => 'NIS_AKTIF',
        'kelas_id' => $kelas->id,
    ]);
    $resCreateSiswaAktif->assertSessionHasErrors(['nis' => 'NIS sudah terdaftar.']);

    // 3. Mapel
    $mapelTrashed = Mapel::create(['nama' => 'Fisika Trashed', 'kode' => 'FIS_TRASHED']);
    $mapelTrashed->delete();

    $mapelAktif = Mapel::create(['nama' => 'Fisika Aktif', 'kode' => 'FIS_AKTIF']);

    // Coba buat mapel baru dengan kode terhapus
    $resCreateMapelTrashed = $this->actingAs($this->admin)->post(route('admin.mapel.store'), [
        'nama' => 'Fisika Baru',
        'kode' => 'FIS_TRASHED',
    ]);
    $resCreateMapelTrashed->assertSessionHasErrors('kode');
    $errMsgMapel = session('errors')->first('kode');
    expect($errMsgMapel)->toContain('Data Terhapus')->toContain('Fisika Trashed');

    // Coba buat mapel baru dengan kode aktif -> pesan lama
    $resCreateMapelAktif = $this->actingAs($this->admin)->post(route('admin.mapel.store'), [
        'nama' => 'Fisika Baru 2',
        'kode' => 'FIS_AKTIF',
    ]);
    $resCreateMapelAktif->assertSessionHasErrors('kode');
    expect(session('errors')->first('kode'))->not->toContain('Data Terhapus');
});

test('AB-05: 7. Setelah pemulihan, laporan dan riwayat menampilkan siswa itu sebagai siswa aktif (tanpa "(nonaktif)") dan dashboard admin menghitungnya lagi', function () {
    $jurusan = Jurusan::factory()->create();
    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
    $mapel = Mapel::factory()->create();
    $guru = Guru::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
    ]);
    $siswaUser = User::factory()->create(['name' => 'Asep Subagja', 'username' => 'NIS_ASEP']);
    $siswa = Siswa::create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id, 'nis' => 'NIS_ASEP']);

    $sesi = SesiAbsensi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => now()->toDateString(),
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswa->id,
        'status' => 'hadir',
    ]);

    // Hapus siswa
    $siswa->delete();
    expect($siswa->fresh()->trashed())->toBeTrue();

    // Sebelum dipulihkan:
    // Format nama laporan siswa terhapus memuat "(nonaktif)"
    expect($siswa->fresh()->nama_laporan)->toContain('(nonaktif)');
    // Dashboard admin tidak menghitung siswa terhapus
    $countBefore = Siswa::count();

    // Pulihkan siswa
    $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'siswa', 'id' => $siswa->id]));
    expect($siswa->fresh()->trashed())->toBeFalse();

    // Setelah dipulihkan:
    // 1. Dashboard admin menghitungnya lagi (+1)
    expect(Siswa::count())->toBe($countBefore + 1);

    // 2. Format nama laporan tidak memuat "(nonaktif)"
    expect($siswa->fresh()->nama_laporan)->not->toContain('(nonaktif)');
    expect($siswa->fresh()->nama_laporan)->toBe('Asep Subagja');
});

test('AB-05: 8. Pemulihan atomik: bila pengecekan gagal, tidak ada perubahan data', function () {
    $jurusan = Jurusan::factory()->create();
    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'X TKJ Atom']);
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);

    $kelas->delete();
    $siswa->delete();

    $deletedAtBefore = $siswa->fresh()->deleted_at;

    // Gagal karena kelas masih terhapus
    $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'siswa', 'id' => $siswa->id]));

    // Tidak ada perubahan data
    $siswaFresh = $siswa->fresh();
    expect($siswaFresh->trashed())->toBeTrue();
    expect($siswaFresh->deleted_at->toDateTimeString())->toBe($deletedAtBefore->toDateTimeString());
});

test('AB-05: impor siswa dengan NIS milik siswa terhapus memuat pesan yang menolong', function () {
    $jurusan = Jurusan::factory()->create();
    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);

    $siswaUser = User::factory()->create(['name' => 'Siswa Impor Trashed', 'username' => 'NIS_IMPORT_TRASHED']);
    $siswa = Siswa::create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id, 'nis' => 'NIS_IMPORT_TRASHED']);
    $siswa->delete();

    $csvContent = "nis,nama\nNIS_IMPORT_TRASHED,Siswa Baru\n";
    $file = UploadedFile::fake()->createWithContent('siswa.csv', $csvContent);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.siswa.index'))
        ->post(route('admin.siswa.import'), [
            'kelas_id' => $kelas->id,
            'file' => $file,
        ]);

    $response->assertRedirect(route('admin.siswa.index'));
    $response->assertSessionHas('error');
    $importError = session('error');
    expect($importError)->toContain('Data Terhapus')->toContain('Siswa Impor Trashed');
});
