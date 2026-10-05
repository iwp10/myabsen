<?php

use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->kelas = Kelas::factory()->create();
    $this->mapel = Mapel::factory()->create();
});

test('menghapus User yang menjadi diabsen_oleh pada sesi absensi gagal karena FK restrict dan data riwayat tetap utuh', function () {
    $guruUser = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '198501012010011001']);

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

    $petugasUser = User::factory()->create(['role' => 'guru']);
    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $siswaUser->id, 'nis' => '8888', 'kelas_id' => $this->kelas->id]);

    $sesi = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $petugasUser->id,
    ]);

    $detail = DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswa->id,
        'status' => 'hadir',
    ]);

    // Eksekusi hard delete User harus memicu QueryException karena FK restrict
    $caught = false;
    try {
        $petugasUser->delete();
    } catch (QueryException $e) {
        $caught = true;
    }

    expect($caught)->toBeTrue('Penghapusan user harus ditolak oleh foreign key restrict constraint.');

    // Verifikasi data User, sesi_absensi, dan detail_absensi tetap utuh
    $this->assertDatabaseHas('users', ['id' => $petugasUser->id]);
    $this->assertDatabaseHas('sesi_absensi', ['id' => $sesi->id]);
    $this->assertDatabaseHas('detail_absensi', ['id' => $detail->id]);
});

test('menghapus User yang menjadi diubah_oleh pada sesi absensi gagal karena FK restrict dan data riwayat tetap utuh', function () {
    $creatorUser = User::factory()->create(['role' => 'guru']);
    $guruCreator = Guru::create(['user_id' => $creatorUser->id, 'nip' => '198501012010011002']);

    $editorUser = User::factory()->create(['role' => 'guru']);
    $guruEditor = Guru::create(['user_id' => $editorUser->id, 'nip' => '198501012010011003']);

    $jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $guruCreator->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $siswaUser->id, 'nis' => '8889', 'kelas_id' => $this->kelas->id]);

    $sesi = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $creatorUser->id,
        'diubah_oleh' => $editorUser->id,
    ]);

    $detail = DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswa->id,
        'status' => 'hadir',
    ]);

    // Eksekusi hard delete editorUser harus memicu QueryException karena FK restrict
    $caught = false;
    try {
        $editorUser->delete();
    } catch (QueryException $e) {
        $caught = true;
    }

    expect($caught)->toBeTrue('Penghapusan user pengubah harus ditolak oleh foreign key restrict constraint.');

    $this->assertDatabaseHas('users', ['id' => $editorUser->id]);
    $this->assertDatabaseHas('sesi_absensi', ['id' => $sesi->id]);
    $this->assertDatabaseHas('detail_absensi', ['id' => $detail->id]);
});

test('hard delete akun user tanpa riwayat absensi tetap berhasil', function () {
    $user = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => '99990001']);

    $user->delete();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertDatabaseMissing('guru', ['id' => $guru->id]);
});

test('alur hapus guru lewat admin controller: soft delete bila ada riwayat, hard delete bila tidak', function () {
    // 1. Guru dengan riwayat absensi -> soft delete
    $guruUserWithHistory = User::factory()->create(['role' => 'guru']);
    $guruWithHistory = Guru::create(['user_id' => $guruUserWithHistory->id, 'nip' => '77770001']);
    $jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $guruWithHistory->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $guruUserWithHistory->id,
    ]);

    $res1 = $this->actingAs($this->admin)->delete(route('admin.guru.destroy', $guruWithHistory));
    $res1->assertRedirect(route('admin.guru.index'));
    $res1->assertSessionHas('success', 'Guru di-soft-delete karena memiliki riwayat absensi.');
    $this->assertSoftDeleted('guru', ['id' => $guruWithHistory->id]);
    $this->assertDatabaseHas('users', ['id' => $guruUserWithHistory->id]);

    // 2. Guru tanpa riwayat absensi -> hard delete
    $guruUserClean = User::factory()->create(['role' => 'guru']);
    $guruClean = Guru::create(['user_id' => $guruUserClean->id, 'nip' => '77770002']);

    $res2 = $this->actingAs($this->admin)->delete(route('admin.guru.destroy', $guruClean));
    $res2->assertRedirect(route('admin.guru.index'));
    $res2->assertSessionHas('success', 'Guru beserta akun berhasil dihapus.');
    $this->assertDatabaseMissing('guru', ['id' => $guruClean->id]);
    $this->assertDatabaseMissing('users', ['id' => $guruUserClean->id]);
});

test('alur hapus siswa lewat admin controller: soft delete bila ada riwayat, hard delete bila tidak', function () {
    // 1. Siswa dengan riwayat absensi -> soft delete
    $siswaUserWithHistory = User::factory()->create(['role' => 'siswa']);
    $siswaWithHistory = Siswa::create(['user_id' => $siswaUserWithHistory->id, 'nis' => '66660001', 'kelas_id' => $this->kelas->id]);
    $jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => Guru::factory()->create()->id,
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
        'siswa_id' => $siswaWithHistory->id,
        'status' => 'hadir',
    ]);

    $res1 = $this->actingAs($this->admin)->delete(route('admin.siswa.destroy', $siswaWithHistory));
    $res1->assertRedirect(route('admin.siswa.index'));
    $res1->assertSessionHas('success', 'Siswa di-soft-delete karena memiliki riwayat absensi.');
    $this->assertSoftDeleted('siswa', ['id' => $siswaWithHistory->id]);
    $this->assertDatabaseHas('users', ['id' => $siswaUserWithHistory->id]);

    // 2. Siswa tanpa riwayat absensi -> hard delete
    $siswaUserClean = User::factory()->create(['role' => 'siswa']);
    $siswaClean = Siswa::create(['user_id' => $siswaUserClean->id, 'nis' => '66660002', 'kelas_id' => $this->kelas->id]);

    $res2 = $this->actingAs($this->admin)->delete(route('admin.siswa.destroy', $siswaClean));
    $res2->assertRedirect(route('admin.siswa.index'));
    $res2->assertSessionHas('success', 'Siswa beserta akun berhasil dihapus.');
    $this->assertDatabaseMissing('siswa', ['id' => $siswaClean->id]);
    $this->assertDatabaseMissing('users', ['id' => $siswaUserClean->id]);
});

test('guru tanpa jadwal bersesi tetapi user-nya tercatat sebagai diabsen_oleh pada sesi jadwal guru lain: gagal dihapus karena FK restrict dan rollback menjaga baris guru dan user tetap utuh', function () {
    // Guru A: tidak punya jadwal bersesi
    $userA = User::factory()->create(['role' => 'guru']);
    $guruA = Guru::create(['user_id' => $userA->id, 'nip' => '55550001']);

    // Guru B: punya jadwal
    $userB = User::factory()->create(['role' => 'guru']);
    $guruB = Guru::create(['user_id' => $userB->id, 'nip' => '55550002']);

    $jadwalB = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $guruB->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Sesi dibuat pada jadwal guru B, tetapi diabsen oleh userA (Guru A)
    $sesi = SesiAbsensi::create([
        'jadwal_id' => $jadwalB->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $userA->id,
    ]);

    $siswa = Siswa::factory()->create(['kelas_id' => $this->kelas->id]);
    $detail = DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswa->id,
        'status' => 'hadir',
    ]);

    // Admin menghapus Guru A
    $response = $this->actingAs($this->admin)->delete(route('admin.guru.destroy', $guruA));

    $response->assertRedirect(route('admin.guru.index'));
    $response->assertSessionHas('error', 'Akun tidak dapat dihapus karena memiliki riwayat absensi.');

    // Karena rollback transaksi DB, baris Guru A TETAP ada (bukan soft delete, deleted_at null)
    $this->assertDatabaseHas('guru', ['id' => $guruA->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('users', ['id' => $userA->id]);

    // Sesi dan detail absensi tetap utuh
    $this->assertDatabaseHas('sesi_absensi', ['id' => $sesi->id, 'diabsen_oleh' => $userA->id]);
    $this->assertDatabaseHas('detail_absensi', ['id' => $detail->id]);
});

test('siswa tanpa riwayat detail absensi tetapi user-nya tercatat sebagai diabsen_oleh pada sesi absensi: gagal dihapus karena FK restrict dan rollback menjaga baris siswa dan user tetap utuh', function () {
    // Siswa tanpa detail absensi
    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $siswaUser->id, 'nis' => '44440001', 'kelas_id' => $this->kelas->id]);

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

    // Anomali relasi di mana siswaUser tercatat sebagai diabsen_oleh pada sebuah sesi
    $otherSiswa = Siswa::factory()->create(['kelas_id' => $this->kelas->id]);
    $sesi = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-03',
        'diabsen_oleh' => $siswaUser->id,
    ]);
    $detail = DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $otherSiswa->id,
        'status' => 'hadir',
    ]);

    // Admin menghapus siswa tersebut
    $response = $this->actingAs($this->admin)->delete(route('admin.siswa.destroy', $siswa));

    $response->assertRedirect(route('admin.siswa.index'));
    $response->assertSessionHas('error', 'Akun tidak dapat dihapus karena memiliki riwayat absensi.');

    // Karena rollback transaksi DB, baris Siswa TETAP ada (bukan soft delete, deleted_at null)
    $this->assertDatabaseHas('siswa', ['id' => $siswa->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('users', ['id' => $siswaUser->id]);

    // Sesi dan detail absensi tetap utuh
    $this->assertDatabaseHas('sesi_absensi', ['id' => $sesi->id, 'diabsen_oleh' => $siswaUser->id]);
    $this->assertDatabaseHas('detail_absensi', ['id' => $detail->id]);
});
