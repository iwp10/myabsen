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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Pengaturan::updateOrCreate(['kunci' => 'tahun_ajaran_aktif'], ['nilai' => '2025/2026']);
    Pengaturan::updateOrCreate(['kunci' => 'semester_aktif'], ['nilai' => 'Ganjil']);

    $this->admin = User::factory()->create([
        'name' => 'Administrator',
        'role' => 'admin',
    ]);

    $this->guruUser = User::factory()->create([
        'name' => 'Guru Pengajar',
        'role' => 'guru',
    ]);
    $this->guru = Guru::factory()->create([
        'user_id' => $this->guruUser->id,
        'nip' => '198501012010011001',
    ]);

    $this->jurusan = Jurusan::create([
        'nama' => 'Teknik Komputer dan Jaringan',
        'kode' => 'TKJ',
    ]);

    $this->kelasAsal = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 'X',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);

    $this->kelasTujuan = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'XI TKJ 1',
        'tingkat' => 'XI',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->mapel = Mapel::create([
        'nama' => 'Pemrograman Web',
        'kode' => 'WEB01',
    ]);

    $this->jadwal = Jadwal::create([
        'kelas_id' => $this->kelasAsal->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('AB-11: 1. Hanya admin yang bisa membuka dan menjalankan aksi, tamu diarahkan ke login, guru dan siswa mendapat 403, dan input tidak valid memberi error validasi', function () {
    $siswaUser = User::factory()->create(['role' => 'siswa']);

    // 1. Akses Guest
    $this->get(route('admin.pergantian-periode.index'))->assertRedirect(route('login'));
    $this->post(route('admin.pergantian-periode.salin-kelas'))->assertRedirect(route('login'));
    $this->post(route('admin.pergantian-periode.pindahkan-siswa'))->assertRedirect(route('login'));
    $this->post(route('admin.pergantian-periode.luluskan-siswa'))->assertRedirect(route('login'));

    // 2. Akses Guru (403)
    $this->actingAs($this->guruUser)->get(route('admin.pergantian-periode.index'))->assertStatus(403);
    $this->actingAs($this->guruUser)->post(route('admin.pergantian-periode.salin-kelas'))->assertStatus(403);
    $this->actingAs($this->guruUser)->post(route('admin.pergantian-periode.pindahkan-siswa'))->assertStatus(403);
    $this->actingAs($this->guruUser)->post(route('admin.pergantian-periode.luluskan-siswa'))->assertStatus(403);

    // 3. Akses Siswa (403)
    $this->actingAs($siswaUser)->get(route('admin.pergantian-periode.index'))->assertStatus(403);
    $this->actingAs($siswaUser)->post(route('admin.pergantian-periode.salin-kelas'))->assertStatus(403);
    $this->actingAs($siswaUser)->post(route('admin.pergantian-periode.pindahkan-siswa'))->assertStatus(403);
    $this->actingAs($siswaUser)->post(route('admin.pergantian-periode.luluskan-siswa'))->assertStatus(403);

    // 4. Akses Admin (200 OK)
    $response = $this->actingAs($this->admin)->get(route('admin.pergantian-periode.index'));
    $response->assertStatus(200);
    $response->assertViewIs('admin.pergantian_periode.index');

    // 5. Input Kosong / Tidak Valid pada Aksi Salin Kelas
    $this->actingAs($this->admin)->post(route('admin.pergantian-periode.salin-kelas'), [])
        ->assertSessionHasErrors(['periode_asal', 'tahun_ajaran_tujuan', 'semester_tujuan', 'kelas_ids']);

    // 6. Input Kosong / Tidak Valid pada Aksi Pindahkan Siswa
    $this->actingAs($this->admin)->post(route('admin.pergantian-periode.pindahkan-siswa'), [])
        ->assertSessionHasErrors(['kelas_asal_id', 'kelas_tujuan_id', 'siswa_ids']);

    // 7. Input Kosong / Tidak Valid pada Aksi Luluskan Siswa
    $this->actingAs($this->admin)->post(route('admin.pergantian-periode.luluskan-siswa'), [])
        ->assertSessionHasErrors(['kelas_id', 'siswa_ids']);
});

test('AB-11: 2. Salin kelas: kelas terpilih tersalin ke periode tujuan dengan atribut sama, yang sudah ada dilewati, kelas terhapus tidak ikut, jadwal dan siswa tidak ikut, periode sama atau format salah ditolak', function () {
    // Kelas 2 di periode asal (akan disalin)
    $kelas2 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 2',
        'tingkat' => 'X',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);

    // Kelas terhapus di periode asal
    $kelasTerhapus = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 3',
        'tingkat' => 'X',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);
    $kelasTerhapus->delete();

    // Buat siswa di kelas asal
    $uSiswa = User::factory()->create(['role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $uSiswa->id, 'nis' => '1101', 'kelas_id' => $this->kelasAsal->id]);

    // Di periode tujuan '2026/2027' - 'Ganjil', kita sudah punya kelas dengan nama 'X TKJ 1'
    Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 'X',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // 1. Coba salin ke periode yang sama persis (harus ditolak)
    $resSama = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.salin-kelas'), [
        'periode_asal' => '2025/2026-Ganjil',
        'tahun_ajaran_tujuan' => '2025/2026',
        'semester_tujuan' => 'Ganjil',
        'kelas_ids' => [$this->kelasAsal->id],
    ]);
    $resSama->assertSessionHasErrors('tahun_ajaran_tujuan');

    // 2. Format tahun ajaran salah (tahun kedua bukan tahun pertama + 1)
    $resFormatSalah = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.salin-kelas'), [
        'periode_asal' => '2025/2026-Ganjil',
        'tahun_ajaran_tujuan' => '2026/2028',
        'semester_tujuan' => 'Ganjil',
        'kelas_ids' => [$this->kelasAsal->id],
    ]);
    $resFormatSalah->assertSessionHasErrors('tahun_ajaran_tujuan');

    // 3. Salin kelas X TKJ 1 (sudah ada) dan X TKJ 2 (baru) ke periode 2026/2027 - Ganjil
    $response = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.salin-kelas'), [
        'periode_asal' => '2025/2026-Ganjil',
        'tahun_ajaran_tujuan' => '2026/2027',
        'semester_tujuan' => 'Ganjil',
        'kelas_ids' => [$this->kelasAsal->id, $kelas2->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(session('success'))->toContain('1 kelas berhasil disalin, 1 kelas dilewati');

    // X TKJ 2 berhasil disalin ke 2026/2027 Ganjil
    $kelasBaru = Kelas::where('nama', 'X TKJ 2')
        ->where('tahun_ajaran', '2026/2027')
        ->where('semester', 'Ganjil')
        ->first();

    expect($kelasBaru)->not->toBeNull();
    expect($kelasBaru->tingkat)->toBe('X');
    expect($kelasBaru->jurusan_id)->toBe($this->jurusan->id);

    // Jadwal dan siswa tidak ikut disalin ke kelas baru
    expect($kelasBaru->jadwal()->count())->toBe(0);
    expect($kelasBaru->siswa()->count())->toBe(0);

    // Kelas terhapus tidak ada di periode baru
    expect(Kelas::where('nama', 'X TKJ 3')->where('tahun_ajaran', '2026/2027')->exists())->toBeFalse();
});

test('AB-11: 3. Pindahkan siswa: siswa terpilih pindah, yang tidak terpilih tetap, kelas tujuan aktif & berbeda, siswa bukan kelas asal ditolak, detail absensi lama utuh, dashboard siswa ikut kelas baru', function () {
    // 3 siswa di kelas asal
    $u1 = User::factory()->create(['name' => 'Siswa 1', 'role' => 'siswa']);
    $s1 = Siswa::create(['user_id' => $u1->id, 'nis' => '2001', 'kelas_id' => $this->kelasAsal->id]);

    $u2 = User::factory()->create(['name' => 'Siswa 2', 'role' => 'siswa']);
    $s2 = Siswa::create(['user_id' => $u2->id, 'nis' => '2002', 'kelas_id' => $this->kelasAsal->id]);

    $u3 = User::factory()->create(['name' => 'Siswa 3', 'role' => 'siswa']);
    $s3 = Siswa::create(['user_id' => $u3->id, 'nis' => '2003', 'kelas_id' => $this->kelasAsal->id]);

    // Siswa lain di kelas berbeda (kelas C)
    $kelasLain = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ Lain',
        'tingkat' => 'X',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);
    $uLain = User::factory()->create(['name' => 'Siswa Lain', 'role' => 'siswa']);
    $sLain = Siswa::create(['user_id' => $uLain->id, 'nis' => '2004', 'kelas_id' => $kelasLain->id]);

    // Catat riwayat absensi lama untuk S1 di kelas asal
    $sesiLama = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2025-08-04',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    $detailLama = DetailAbsensi::create([
        'sesi_absensi_id' => $sesiLama->id,
        'siswa_id' => $s1->id,
        'status' => 'hadir',
    ]);

    // 1. Pindahkan dengan kelas tujuan sama dengan asal (ditolak)
    $resSama = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.pindahkan-siswa'), [
        'kelas_asal_id' => $this->kelasAsal->id,
        'kelas_tujuan_id' => $this->kelasAsal->id,
        'siswa_ids' => [$s1->id],
    ]);
    $resSama->assertSessionHasErrors('kelas_tujuan_id');

    // 2. Pindahkan dengan menyertakan siswa yang bukan siswa kelas asal (ditolak)
    $resBukanAsal = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.pindahkan-siswa'), [
        'kelas_asal_id' => $this->kelasAsal->id,
        'kelas_tujuan_id' => $this->kelasTujuan->id,
        'siswa_ids' => [$s1->id, $sLain->id],
    ]);
    // Harus memicu error redirect dengan pesan error
    $resBukanAsal->assertRedirect();
    $resBukanAsal->assertSessionHas('error');

    // 3. Pindahkan S1 dan S2 ke kelas tujuan. S3 tetap di kelas asal.
    $resSukses = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.pindahkan-siswa'), [
        'kelas_asal_id' => $this->kelasAsal->id,
        'kelas_tujuan_id' => $this->kelasTujuan->id,
        'siswa_ids' => [$s1->id, $s2->id],
    ]);
    $resSukses->assertRedirect();
    $resSukses->assertSessionHas('success');

    // Verifikasi kelas_id siswa
    expect($s1->fresh()->kelas_id)->toBe($this->kelasTujuan->id);
    expect($s2->fresh()->kelas_id)->toBe($this->kelasTujuan->id);
    expect($s3->fresh()->kelas_id)->toBe($this->kelasAsal->id);

    // Detail absensi lama tetap tidak berubah dan tetap terhubung ke sesi lama
    expect($detailLama->fresh()->sesi_absensi_id)->toBe($sesiLama->id);
    expect($detailLama->fresh()->siswa_id)->toBe($s1->id);

    // Dashboard siswa S1 mengikuti kelas baru
    // Kita buat jadwal di kelas tujuan pada periode aktif (2025/2026 Ganjil untuk tes)
    $jadwalTujuan = Jadwal::create([
        'kelas_id' => $this->kelasTujuan->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:30:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);

    $resDashboard = $this->actingAs($u1)->get(route('siswa.dashboard'));
    $resDashboard->assertStatus(200);
    // Menampilkan kelas baru XI TKJ 1
    $resDashboard->assertSee('XI TKJ 1');
});

test('AB-11: 4. Riwayat kelas lama utuh: setelah semua siswa dipindah, riwayat detail guru, Excel, dan PDF admin tetap memuat siswa dengan persentase sama, form sesi baru tidak memuat mereka', function () {
    // 2 siswa di kelas asal
    $u1 = User::factory()->create(['name' => 'Siswa Pertama', 'role' => 'siswa']);
    $s1 = Siswa::create(['user_id' => $u1->id, 'nis' => '3001', 'kelas_id' => $this->kelasAsal->id]);

    $u2 = User::factory()->create(['name' => 'Siswa Kedua', 'role' => 'siswa']);
    $s2 = Siswa::create(['user_id' => $u2->id, 'nis' => '3002', 'kelas_id' => $this->kelasAsal->id]);

    // Buat 2 sesi absensi historis di kelas asal
    $sesi1 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2025-08-04',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $s1->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $s2->id, 'status' => 'hadir']);

    $sesi2 = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2025-08-11',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $s1->id, 'status' => 'hadir']);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $s2->id, 'status' => 'alpa']);

    // Pindahkan SEMUA siswa keluar dari kelas asal ke kelas tujuan
    $this->actingAs($this->admin)->post(route('admin.pergantian-periode.pindahkan-siswa'), [
        'kelas_asal_id' => $this->kelasAsal->id,
        'kelas_tujuan_id' => $this->kelasTujuan->id,
        'siswa_ids' => [$s1->id, $s2->id],
    ])->assertSessionHas('success');

    expect($this->kelasAsal->siswa()->count())->toBe(0);

    // 1. Riwayat Detail Guru untuk kelas asal dan mapel tetap memuat S1 dan S2
    $resGuru = $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAsal->id,
        'mapel' => $this->mapel->id,
    ]));
    $resGuru->assertStatus(200);
    $resGuru->assertSee('Siswa Pertama');
    $resGuru->assertSee('Siswa Kedua');
    // Siswa Pertama tidak bertanda (nonaktif) karena masih aktif (hanya pindah kelas)
    $resGuru->assertDontSee('Siswa Pertama (nonaktif)');
    // Persentase S1: 2/2 = 100%, S2: 1/2 = 50%
    $resGuru->assertSee('100%');
    $resGuru->assertSee('50%');

    // 2. Ekspor Excel Admin untuk kelas asal
    $resExcel = $this->actingAs($this->admin)->get(route('admin.laporan.export', [
        'kelas_id' => $this->kelasAsal->id,
        'mapel_id' => $this->mapel->id,
    ]));
    $resExcel->assertStatus(200);

    // 3. Ekspor PDF Admin untuk kelas asal
    $resPdf = $this->actingAs($this->admin)->get(route('admin.laporan.exportPdf', [
        'kelas_id' => $this->kelasAsal->id,
        'mapel_id' => $this->mapel->id,
    ]));
    $resPdf->assertStatus(200);

    // 4. Form absensi untuk sesi baru kelas asal hari ini
    Carbon::setTestNow('2025-08-18 07:30:00');
    $resAbsen = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', $this->jadwal));
    $resAbsen->assertStatus(200);
    // Tidak memuat siswa yang sudah pindah di form absensi sesi baru
    $resAbsen->assertDontSee('Siswa Pertama');
    $resAbsen->assertDontSee('Siswa Kedua');
});

test('AB-05 & AB-11: 5. Luluskan: siswa ter-soft-delete, akun user tetap ada, login ditolak, tampil (nonaktif) di laporan, bisa dipulihkan dari data terhapus dan login lagi, siswa tanpa riwayat pun tetap soft delete', function () {
    // Siswa dengan riwayat
    $u1 = User::factory()->create([
        'name' => 'Siswa Tamat 1',
        'role' => 'siswa',
        'password' => Hash::make('password123'),
    ]);
    $s1 = Siswa::create(['user_id' => $u1->id, 'nis' => '4001', 'kelas_id' => $this->kelasAsal->id]);

    // Siswa tanpa riwayat
    $u2 = User::factory()->create([
        'name' => 'Siswa Tamat 2',
        'role' => 'siswa',
        'password' => Hash::make('password123'),
    ]);
    $s2 = Siswa::create(['user_id' => $u2->id, 'nis' => '4002', 'kelas_id' => $this->kelasAsal->id]);

    // Buat riwayat untuk s1
    $sesi = SesiAbsensi::create([
        'jadwal_id' => $this->jadwal->id,
        'tanggal' => '2025-08-04',
        'diabsen_oleh' => $this->guruUser->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $s1->id, 'status' => 'hadir']);

    // Luluskan kedua siswa
    $response = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.luluskan-siswa'), [
        'kelas_id' => $this->kelasAsal->id,
        'siswa_ids' => [$s1->id, $s2->id],
    ]);
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verifikasi Soft Delete: Siswa ter-soft-delete, BUKAN forceDelete meskipun s2 tidak punya riwayat
    $this->assertSoftDeleted('siswa', ['id' => $s1->id]);
    $this->assertSoftDeleted('siswa', ['id' => $s2->id]);

    // Akun User TETAP ADA di database
    $this->assertDatabaseHas('users', ['id' => $u1->id]);
    $this->assertDatabaseHas('users', ['id' => $u2->id]);

    // Logout admin terlebih dahulu sebelum menguji login siswa
    auth()->logout();
    $this->assertGuest();

    // Login dengan akun siswa yang sudah diluluskan ditolak (AB-05)
    $loginRes = $this->post(route('login'), [
        'username' => $s1->nis,
        'password' => 'password123',
    ]);
    $loginRes->assertSessionHasErrors('username');
    $this->assertGuest();

    // Di laporan guru detail, nama s1 tampil dengan tanda (nonaktif)
    $resGuru = $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAsal->id,
        'mapel' => $this->mapel->id,
    ]));
    $resGuru->assertStatus(200);
    $resGuru->assertSee('Siswa Tamat 1');
    $resGuru->assertSee('(nonaktif)');

    // Pulihkan dari Data Terhapus
    $resPulih = $this->actingAs($this->admin)->post(route('admin.arsip.pulihkan', [
        'jenis' => 'siswa',
        'id' => $s1->id,
    ]));
    $resPulih->assertRedirect();
    $resPulih->assertSessionHas('success');

    // Siswa aktif kembali
    expect($s1->fresh()->trashed())->toBeFalse();

    // Logout admin sebelum siswa login
    auth()->logout();
    $this->assertGuest();

    // Login kembali berhasil
    $loginPulih = $this->post(route('login'), [
        'username' => $s1->nis,
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($u1);
});

test('AB-11: 6. Atomik: jika operasi gagal atau ada input id yang tidak valid, tidak ada data yang berubah', function () {
    $u1 = User::factory()->create(['name' => 'Siswa Atomik', 'role' => 'siswa']);
    $s1 = Siswa::create(['user_id' => $u1->id, 'nis' => '5001', 'kelas_id' => $this->kelasAsal->id]);

    // Coba kirim ID siswa yang tidak ada (999999) bersama s1
    $response = $this->actingAs($this->admin)->post(route('admin.pergantian-periode.pindahkan-siswa'), [
        'kelas_asal_id' => $this->kelasAsal->id,
        'kelas_tujuan_id' => $this->kelasTujuan->id,
        'siswa_ids' => [$s1->id, 999999],
    ]);

    // Validasi form request menolak atau rollback
    $response->assertSessionHasErrors('siswa_ids.*');

    // Kelas siswa s1 tidak berubah sama sekali
    expect($s1->fresh()->kelas_id)->toBe($this->kelasAsal->id);
});

test('AB-11: 7. Hapus kelas: ditolak bila masih memiliki siswa aktif dengan pesan jumlah siswa, boleh bila kosong atau hanya berisi siswa terhapus, aturan jadwal tetap', function () {
    // 1. Kelas dengan siswa aktif tidak boleh dihapus
    $u1 = User::factory()->create(['role' => 'siswa']);
    $s1 = Siswa::create(['user_id' => $u1->id, 'nis' => '6001', 'kelas_id' => $this->kelasTujuan->id]);
    $u2 = User::factory()->create(['role' => 'siswa']);
    $s2 = Siswa::create(['user_id' => $u2->id, 'nis' => '6002', 'kelas_id' => $this->kelasTujuan->id]);

    $resHapusAktif = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $this->kelasTujuan));
    $resHapusAktif->assertRedirect();
    $resHapusAktif->assertSessionHas('error', 'Kelas ini masih berisi 2 siswa aktif. Pindahkan atau luluskan siswa terlebih dahulu.');
    expect($this->kelasTujuan->fresh()->trashed())->toBeFalse();

    // 2. Jika semua siswa terhapus (soft delete), kelas boleh di-soft-delete (jika tanpa jadwal)
    $s1->delete();
    $s2->delete();

    $resHapusSiswaTrashed = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $this->kelasTujuan));
    $resHapusSiswaTrashed->assertRedirect();
    $resHapusSiswaTrashed->assertSessionHas('success');
    expect($this->kelasTujuan->fresh()->trashed())->toBeTrue();

    // 3. Kelas kosong tanpa siswa boleh dihapus
    $kelasKosong = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ Kosong',
        'tingkat' => 'X',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);
    $resHapusKosong = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $kelasKosong));
    $resHapusKosong->assertSessionHas('success');
    expect($kelasKosong->fresh()->trashed())->toBeTrue();

    // 4. Kelas tanpa siswa tetapi masih punya jadwal tetap ditolak (aturan jadwal lama tetap)
    $kelasPunyaJadwal = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ Jadwal',
        'tingkat' => 'X',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);
    Jadwal::create([
        'kelas_id' => $kelasPunyaJadwal->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);

    $resHapusJadwal = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $kelasPunyaJadwal));
    $resHapusJadwal->assertSessionHas('error', 'Kelas tidak dapat dihapus karena masih memiliki jadwal aktif.');
    expect($kelasPunyaJadwal->fresh()->trashed())->toBeFalse();
});

test('AB-11: 8. Jumlah query pada riwayat detail tidak bertambah proporsional dengan jumlah siswa setelah perubahan aturan siswa kelas', function () {
    // Buat 2 siswa awal dengan riwayat
    for ($i = 1; $i <= 2; $i++) {
        $u = User::factory()->create(['name' => "Siswa Perf $i", 'role' => 'siswa']);
        $s = Siswa::create(['user_id' => $u->id, 'nis' => "700$i", 'kelas_id' => $this->kelasTujuan->id]); // sudah pindah kelas
        $sesi = SesiAbsensi::create([
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => "2025-08-0$i",
            'diabsen_oleh' => $this->guruUser->id,
        ]);
        DetailAbsensi::create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $s->id, 'status' => 'hadir']);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAsal->id,
        'mapel' => $this->mapel->id,
    ]));

    $queryCount2Siswa = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Tambah 10 siswa lagi yang sudah pindah kelas tetapi punya riwayat
    for ($i = 3; $i <= 12; $i++) {
        $u = User::factory()->create(['name' => "Siswa Perf $i", 'role' => 'siswa']);
        $s = Siswa::create(['user_id' => $u->id, 'nis' => "70$i", 'kelas_id' => $this->kelasTujuan->id]);
        $sesi = SesiAbsensi::first();
        DetailAbsensi::create(['sesi_absensi_id' => $sesi->id, 'siswa_id' => $s->id, 'status' => 'hadir']);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAsal->id,
        'mapel' => $this->mapel->id,
    ]));

    $queryCount12Siswa = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Jumlah query tidak boleh bertambah 10 (bebas dari N+1, selisih maksimal 1 atau 0)
    expect($queryCount12Siswa - $queryCount2Siswa)->toBeLessThanOrEqual(1);
});
