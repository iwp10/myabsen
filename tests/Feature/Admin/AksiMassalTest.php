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
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->jurusan = Jurusan::create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);
    $this->kelas = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => '10',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $this->mapel = Mapel::create(['nama' => 'Pemrograman Web', 'kode' => 'WEB01']);
});

// TEST 1: Hak akses & validasi input
test('AB-05 & AB-10: 1. Hanya admin yang bisa aksi massal; guru/siswa 403, tamu ke login. Validasi ids ketat tanpa perubahan data', function () {
    $guruUser = User::factory()->create(['role' => 'guru']);
    $siswaUser = User::factory()->create(['role' => 'siswa']);

    $user1 = User::factory()->create(['role' => 'siswa']);
    $siswa1 = Siswa::create(['user_id' => $user1->id, 'nis' => '1001', 'kelas_id' => $this->kelas->id]);

    $trashedGuru = Guru::factory()->create();
    $trashedGuru->delete();

    // Tamu -> login
    $this->post(route('admin.siswa.massal.hapus'), ['ids' => [$siswa1->id]])->assertRedirect(route('login'));
    $this->post(route('admin.siswa.massal.reset-password'), ['ids' => [$siswa1->id]])->assertRedirect(route('login'));
    $this->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'guru']), ['ids' => [$trashedGuru->id]])->assertRedirect(route('login'));

    // Guru -> 403
    $this->actingAs($guruUser)->post(route('admin.siswa.massal.hapus'), ['ids' => [$siswa1->id]])->assertForbidden();
    $this->actingAs($guruUser)->post(route('admin.siswa.massal.reset-password'), ['ids' => [$siswa1->id]])->assertForbidden();
    $this->actingAs($guruUser)->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'guru']), ['ids' => [$trashedGuru->id]])->assertForbidden();

    // Siswa -> 403
    $this->actingAs($siswaUser)->post(route('admin.siswa.massal.hapus'), ['ids' => [$siswa1->id]])->assertForbidden();
    $this->actingAs($siswaUser)->post(route('admin.siswa.massal.reset-password'), ['ids' => [$siswa1->id]])->assertForbidden();
    $this->actingAs($siswaUser)->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'guru']), ['ids' => [$trashedGuru->id]])->assertForbidden();

    // Validasi: ids kosong
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), ['ids' => []])
        ->assertSessionHasErrors('ids');

    // Validasi: ids lebih dari 100
    $overIds = range(1, 101);
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), ['ids' => $overIds])
        ->assertSessionHasErrors('ids');

    // Validasi: ids tidak unik (duplikat)
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), ['ids' => [$siswa1->id, $siswa1->id]])
        ->assertSessionHasErrors('ids.0');

    // Validasi: bukan integer
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), ['ids' => ['bukan_angka']])
        ->assertSessionHasErrors('ids.0');

    // Validasi: id tidak ada di tabel siswa
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), ['ids' => [999999]])
        ->assertSessionHasErrors('ids.0');

    // Validasi: id siswa yang sudah terhapus
    $siswa1->delete();
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), ['ids' => [$siswa1->id]])
        ->assertSessionHasErrors('ids.0');

    // Validasi arsip: id bukan data terhapus (masih aktif)
    $activeGuru = Guru::factory()->create();
    $this->actingAs($this->admin)->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'guru']), ['ids' => [$activeGuru->id]])
        ->assertSessionHasErrors('ids.0');
});

// TEST 2: Hapus massal
test('AB-05: 2. Hapus massal: siswa berriwayat di-soft-delete (akun ada, login diblokir), tanpa riwayat dihapus permanen, penanganan FK restrict', function () {
    // 1. Siswa dengan riwayat absensi
    $user1 = User::factory()->create(['role' => 'siswa', 'username' => 'nis_history']);
    $siswa1 = Siswa::create(['user_id' => $user1->id, 'nis' => 'nis_history', 'kelas_id' => $this->kelas->id]);

    $guru = Guru::factory()->create();
    $jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $sesi = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $this->admin->id,
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswa1->id,
        'status' => 'hadir',
    ]);

    // 2. Siswa tanpa riwayat absensi
    $user2 = User::factory()->create(['role' => 'siswa', 'username' => 'nis_clean']);
    $siswa2 = Siswa::create(['user_id' => $user2->id, 'nis' => 'nis_clean', 'kelas_id' => $this->kelas->id]);

    // Eksekusi hapus massal campuran siswa1 dan siswa2
    $response = $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), [
        'ids' => [$siswa1->id, $siswa2->id],
    ]);

    $response->assertRedirect(route('admin.siswa.index'));
    $response->assertSessionHas('success', '1 dinonaktifkan, 1 dihapus permanen');

    // Siswa1 di-soft-delete, akun user tetap ada, tapi login diblokir sesuai AB-05
    $this->assertSoftDeleted('siswa', ['id' => $siswa1->id]);
    $this->assertDatabaseHas('users', ['id' => $user1->id]);

    // Siswa2 terhapus permanen beserta akun user
    $this->assertDatabaseMissing('siswa', ['id' => $siswa2->id]);
    $this->assertDatabaseMissing('users', ['id' => $user2->id]);

    // 3. Uji kasus satu siswa gagal karena FK restrict (misal usernya tercatat sebagai diabsen_oleh)
    $user3 = User::factory()->create(['role' => 'siswa', 'username' => 'nis_fk']);
    $siswa3 = Siswa::create(['user_id' => $user3->id, 'nis' => 'nis_fk', 'kelas_id' => $this->kelas->id]);

    $otherSiswa = Siswa::factory()->create(['kelas_id' => $this->kelas->id]);
    $sesiWithFk = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-10',
        'diabsen_oleh' => $user3->id, // FK restrict ke user3
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesiWithFk->id,
        'siswa_id' => $otherSiswa->id,
        'status' => 'hadir',
    ]);

    $user4 = User::factory()->create(['role' => 'siswa', 'username' => 'nis_clean_2']);
    $siswa4 = Siswa::create(['user_id' => $user4->id, 'nis' => 'nis_clean_2', 'kelas_id' => $this->kelas->id]);

    $resPartial = $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), [
        'ids' => [$siswa3->id, $siswa4->id],
    ]);

    $resPartial->assertRedirect(route('admin.siswa.index'));
    $resPartial->assertSessionHas('warning');
    $warningMsg = session('warning');
    expect($warningMsg)->toContain('1 berhasil, 1 ditolak');
    expect($warningMsg)->toContain('Akun tidak dapat dihapus karena memiliki riwayat absensi.');

    // Siswa 3 tetap utuh (tidak terhapus karena rollback)
    $this->assertDatabaseHas('siswa', ['id' => $siswa3->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('users', ['id' => $user3->id]);

    // Siswa 4 tetap berhasil dihapus permanen
    $this->assertDatabaseMissing('siswa', ['id' => $siswa4->id]);
    $this->assertDatabaseMissing('users', ['id' => $user4->id]);
});

// TEST 3: Modal konfirmasi & performa query
test('AB-05: 3. Rincian data di modal konfirmasi akurat dari server dan jumlah query tidak bertambah proporsional (tanpa N+1)', function () {
    // Buat siswa berriwayat
    $userWithHist = User::factory()->create(['name' => 'Siswa Riwayat', 'role' => 'siswa']);
    $siswaWithHist = Siswa::create(['user_id' => $userWithHist->id, 'nis' => 'NIS_H', 'kelas_id' => $this->kelas->id]);

    $guru = Guru::factory()->create();
    $jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $sesi = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $this->admin->id,
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswaWithHist->id,
        'status' => 'hadir',
    ]);

    // Buat siswa tanpa riwayat
    $userClean = User::factory()->create(['name' => 'Siswa Bersih', 'role' => 'siswa']);
    $siswaClean = Siswa::create(['user_id' => $userClean->id, 'nis' => 'NIS_C', 'kelas_id' => $this->kelas->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
    $response->assertOk();

    $siswas = $response->viewData('siswas');
    $siswaH = $siswas->firstWhere('id', $siswaWithHist->id);
    $siswaC = $siswas->firstWhere('id', $siswaClean->id);

    expect((bool) $siswaH->detail_absensi_exists)->toBeTrue();
    expect((bool) $siswaC->detail_absensi_exists)->toBeFalse();

    // Verifikasi view memuat pageItems dengan hasHistory yang tepat
    $content = $response->getContent();
    expect($content)->toContain("{ id: {$siswaWithHist->id}, name: 'Siswa Riwayat', hasHistory: true }");
    expect($content)->toContain("{ id: {$siswaClean->id}, name: 'Siswa Bersih', hasHistory: false }");

    // Uji performa query (tidak bertambah proporsional dengan jumlah siswa)
    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->actingAs($this->admin)->get(route('admin.siswa.index'));
    $queryCountBaseline = count(DB::getQueryLog());

    // Tambah 5 siswa lagi
    for ($i = 0; $i < 5; $i++) {
        $u = User::factory()->create(['role' => 'siswa']);
        Siswa::create(['user_id' => $u->id, 'nis' => 'NIS_EXTRA_'.$i, 'kelas_id' => $this->kelas->id]);
    }

    DB::flushQueryLog();
    $this->actingAs($this->admin)->get(route('admin.siswa.index'));
    $queryCountAfter = count(DB::getQueryLog());

    // Jumlah query harus sama persis (tanpa N+1)
    expect($queryCountAfter)->toBe($queryCountBaseline);
});

// TEST 4: Reset password massal
test('AB-10: 4. Reset massal: akun terpilih bisa login dengan password awal, must_change_password true, akun lain tidak berubah, dan penolakan di produksi', function () {
    $user1 = User::factory()->create(['role' => 'siswa', 'password' => Hash::make('oldpass1'), 'must_change_password' => false]);
    $siswa1 = Siswa::create(['user_id' => $user1->id, 'nis' => '2001', 'kelas_id' => $this->kelas->id]);

    $user2 = User::factory()->create(['role' => 'siswa', 'password' => Hash::make('oldpass2'), 'must_change_password' => false]);
    $siswa2 = Siswa::create(['user_id' => $user2->id, 'nis' => '2002', 'kelas_id' => $this->kelas->id]);

    $userUnselected = User::factory()->create(['role' => 'siswa', 'password' => Hash::make('oldpass_stay'), 'must_change_password' => false]);
    $siswaUnselected = Siswa::create(['user_id' => $userUnselected->id, 'nis' => '2003', 'kelas_id' => $this->kelas->id]);

    $response = $this->actingAs($this->admin)->post(route('admin.siswa.massal.reset-password'), [
        'ids' => [$siswa1->id, $siswa2->id],
    ]);

    $response->assertRedirect(route('admin.siswa.index'));
    $response->assertSessionHas('success', '2 siswa berhasil direset');

    $user1->refresh();
    $user2->refresh();
    $userUnselected->refresh();

    // Default dev environment: password awal adalah 'password'
    expect(Hash::check('password', $user1->password))->toBeTrue();
    expect($user1->must_change_password)->toBeTrue();

    expect(Hash::check('password', $user2->password))->toBeTrue();
    expect($user2->must_change_password)->toBeTrue();

    // Siswa yang tidak dipilih tidak berubah
    expect(Hash::check('oldpass_stay', $userUnselected->password))->toBeTrue();
    expect($userUnselected->must_change_password)->toBeFalse();

    // Uji proteksi di environment produksi dengan password awal tidak aman
    app()['env'] = 'production';
    config(['absensi.password_awal' => 'password']);

    try {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $resProd = $this->actingAs($this->admin)->post(route('admin.siswa.massal.reset-password'), [
            'ids' => [$siswaUnselected->id],
        ]);

        $resProd->assertSessionHas('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');

        $userUnselected->refresh();
        expect(Hash::check('oldpass_stay', $userUnselected->password))->toBeTrue();
        expect($userUnselected->must_change_password)->toBeFalse();
    } finally {
        app()['env'] = 'testing';
    }
});

// TEST 5: Pulihkan massal pada tiap jenis arsip
test('AB-05: 5. Pulihkan massal (guru, siswa, kelas, mapel): kembali aktif, kelas terhapus / bentrok ditolak dengan alasan, jenis invalid 404', function () {
    // 1. Guru trashed
    $guruUser = User::factory()->create(['role' => 'guru']);
    $guruTrashed = Guru::create(['user_id' => $guruUser->id, 'nip' => '19850001']);
    $guruTrashed->delete();

    // 2. Mapel trashed
    $mapelTrashed = Mapel::create(['nama' => 'Fisika', 'kode' => 'FIS01']);
    $mapelTrashed->delete();

    // 3. Kelas trashed
    $kelasTrashed = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'XI TKJ 1',
        'tingkat' => '11',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $kelasTrashed->delete();

    // 4. Siswa trashed dengan kelas aktif
    $siswaUser1 = User::factory()->create(['role' => 'siswa']);
    $siswaTrashed1 = Siswa::create(['user_id' => $siswaUser1->id, 'nis' => '3001', 'kelas_id' => $this->kelas->id]);
    $siswaTrashed1->delete();

    // 5. Siswa trashed dengan kelas masih terhapus
    $siswaUser2 = User::factory()->create(['role' => 'siswa']);
    $siswaTrashed2 = Siswa::create(['user_id' => $siswaUser2->id, 'nis' => '3002', 'kelas_id' => $kelasTrashed->id]);
    $siswaTrashed2->delete();

    // Pulihkan guru massal
    $resGuru = $this->actingAs($this->admin)->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'guru']), [
        'ids' => [$guruTrashed->id],
    ]);
    $resGuru->assertRedirect(route('admin.arsip.index', ['jenis' => 'guru']));
    $resGuru->assertSessionHas('success', '1 data Guru berhasil dipulihkan.');
    $this->assertDatabaseHas('guru', ['id' => $guruTrashed->id, 'deleted_at' => null]);

    // Pulihkan mapel massal
    $resMapel = $this->actingAs($this->admin)->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'mapel']), [
        'ids' => [$mapelTrashed->id],
    ]);
    $resMapel->assertRedirect(route('admin.arsip.index', ['jenis' => 'mapel']));
    $resMapel->assertSessionHas('success', '1 data Mapel berhasil dipulihkan.');
    $this->assertDatabaseHas('mapel', ['id' => $mapelTrashed->id, 'deleted_at' => null]);

    // Pulihkan siswa massal: siswa1 berhasil, siswa2 ditolak karena kelasnya masih terhapus
    $resSiswa = $this->actingAs($this->admin)->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'siswa']), [
        'ids' => [$siswaTrashed1->id, $siswaTrashed2->id],
    ]);
    $resSiswa->assertRedirect(route('admin.arsip.index', ['jenis' => 'siswa']));
    $resSiswa->assertSessionHas('warning');
    $warningMsg = session('warning');
    expect($warningMsg)->toContain('1 berhasil, 1 ditolak');
    expect($warningMsg)->toContain('masih terhapus. Pulihkan kelas tersebut lebih dulu.');

    $this->assertDatabaseHas('siswa', ['id' => $siswaTrashed1->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('siswa', ['id' => $siswaTrashed2->id]);
    expect(Siswa::onlyTrashed()->find($siswaTrashed2->id))->not->toBeNull();

    // Uji bentrok: pulihkan kelas bentrok
    $kelasBentrok = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1', // sama dengan $this->kelas
        'tingkat' => '10',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $kelasBentrok->delete();

    $resKelasBentrok = $this->actingAs($this->admin)->post(route('admin.arsip.massal.pulihkan', ['jenis' => 'kelas']), [
        'ids' => [$kelasBentrok->id],
    ]);
    $resKelasBentrok->assertSessionHas('error');
    expect(session('error'))->toContain('sudah ada dan aktif');

    // Route jenis tidak valid -> 404
    $this->actingAs($this->admin)->post('/admin/arsip/bukan_jenis/massal/pulihkan', ['ids' => [1]])
        ->assertNotFound();
});

// TEST 6: Aksi satuan tetap berfungsi persis seperti sebelumnya
test('AB-05 & AB-10: 6. Aksi satuan (hapus, reset, pulihkan) tetap berfungsi persis seperti sebelumnya', function () {
    $user = User::factory()->create(['role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $user->id, 'nis' => '4001', 'kelas_id' => $this->kelas->id]);

    // Reset password satuan
    $resReset = $this->actingAs($this->admin)->post(route('admin.siswa.reset-password', $siswa));
    $resReset->assertRedirect(route('admin.siswa.index'));
    $resReset->assertSessionHas('success', 'Password siswa berhasil direset ke password awal.');

    // Hapus satuan siswa (hard delete karena belum ada riwayat)
    $resHapus = $this->actingAs($this->admin)->delete(route('admin.siswa.destroy', $siswa));
    $resHapus->assertRedirect(route('admin.siswa.index'));
    $resHapus->assertSessionHas('success', 'Siswa beserta akun berhasil dihapus.');
    $this->assertDatabaseMissing('siswa', ['id' => $siswa->id]);

    // Pulihkan satuan arsip
    $mapel = Mapel::create(['nama' => 'Kimia', 'kode' => 'KIM01']);
    $mapel->delete();
    $resPulih = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', ['jenis' => 'mapel', 'id' => $mapel->id]));
    $resPulih->assertRedirect(route('admin.arsip.index', ['jenis' => 'mapel']));
    $resPulih->assertSessionHas('success', 'Mapel Kimia berhasil dipulihkan.');
});

// TEST 7: View render checkbox, toolbar, dan pertahankan query string
test('AB-05: 7. Tampilan daftar siswa dan arsip memuat checkbox, toolbar aksi massal, dan aksi mempertahankan query string', function () {
    $user = User::factory()->create(['role' => 'siswa', 'name' => 'Budi Santoso']);
    $siswa = Siswa::create(['user_id' => $user->id, 'nis' => '5001', 'kelas_id' => $this->kelas->id]);

    // Halaman siswa
    $resSiswa = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['kelas_id' => $this->kelas->id, 'search' => 'Budi']));
    $resSiswa->assertOk();
    $resSiswa->assertSee('Pilih semua di halaman ini');
    $resSiswa->assertSee('Reset password yang dipilih');
    $resSiswa->assertSee('Hapus yang dipilih');
    $resSiswa->assertSee('Pilih siswa Budi Santoso');

    // Eksekusi aksi massal dengan query string filter
    $resAction = $this->actingAs($this->admin)->post(route('admin.siswa.massal.reset-password', [
        'kelas_id' => $this->kelas->id,
        'search' => 'Budi',
    ]), [
        'ids' => [$siswa->id],
    ]);

    // Redirect harus membawa kembali filter yang sama
    $resAction->assertRedirect(route('admin.siswa.index', ['kelas_id' => $this->kelas->id, 'search' => 'Budi']));

    // Halaman arsip
    $guruUser = User::factory()->create(['role' => 'guru', 'name' => 'Guru Trashed']);
    $guruTrashed = Guru::create(['user_id' => $guruUser->id, 'nip' => '19870001']);
    $guruTrashed->delete();

    $resArsip = $this->actingAs($this->admin)->get(route('admin.arsip.index', ['jenis' => 'guru', 'search' => '19870001']));
    $resArsip->assertOk();
    $resArsip->assertSee('Pilih semua di halaman ini');
    $resArsip->assertSee('Pulihkan yang dipilih');

    $resArsipAction = $this->actingAs($this->admin)->post(route('admin.arsip.massal.pulihkan', [
        'jenis' => 'guru',
        'search' => '19870001',
    ]), [
        'ids' => [$guruTrashed->id],
    ]);

    $resArsipAction->assertRedirect(route('admin.arsip.index', ['jenis' => 'guru', 'search' => '19870001']));
});

// TEST 8: Log aksi massal tercatat tanpa data sensitif
test('AB-05 & AB-10: 8. Log aksi massal tercatat dengan benar tanpa data sensitif', function () {
    Log::spy();

    $user1 = User::factory()->create(['role' => 'siswa']);
    $siswa1 = Siswa::create(['user_id' => $user1->id, 'nis' => '6001', 'kelas_id' => $this->kelas->id]);

    $user2 = User::factory()->create(['role' => 'siswa']);
    $siswa2 = Siswa::create(['user_id' => $user2->id, 'nis' => '6002', 'kelas_id' => $this->kelas->id]);

    // Reset password massal
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.reset-password'), [
        'ids' => [$siswa1->id, $siswa2->id],
    ]);

    Log::shouldHaveReceived('info')->withArgs(function ($message, $context) use ($siswa1, $siswa2) {
        if ($context['aksi'] !== 'massal_reset_password_siswa') {
            return false;
        }

        expect($context['admin_id'])->toBe($this->admin->id);
        expect($context['ids'])->toBe([$siswa1->id, $siswa2->id]);
        expect($context['berhasil'])->toBe(2);
        expect($context['ditolak'])->toBe(0);

        // Tidak boleh ada key password awal atau rahasia
        expect(array_key_exists('password', $context))->toBeFalse();
        expect(array_key_exists('password_awal', $context))->toBeFalse();

        return true;
    });

    // Hapus massal
    $this->actingAs($this->admin)->post(route('admin.siswa.massal.hapus'), [
        'ids' => [$siswa1->id, $siswa2->id],
    ]);

    Log::shouldHaveReceived('info')->withArgs(function ($message, $context) use ($siswa1, $siswa2) {
        if ($context['aksi'] !== 'massal_hapus_siswa') {
            return false;
        }

        expect($context['admin_id'])->toBe($this->admin->id);
        expect($context['ids'])->toBe([$siswa1->id, $siswa2->id]);
        expect($context['berhasil'])->toBe(2);
        expect($context['ditolak'])->toBe(0);

        return true;
    });
});
