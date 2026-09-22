<?php

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\DetailAbsensi;
use App\Models\SesiAbsensi;
use App\Models\Jadwal;
use App\Models\Mapel;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->jurusan = Jurusan::create(['nama' => 'RPL', 'kode' => 'RPL']);
    $this->kelas = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X RPL 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027']);
});

test('admin can see siswa list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
    $response->assertStatus(200);
});

test('admin can create siswa and auto generate user', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.siswa.store'), [
        'name' => 'Andi',
        'nis' => '12345678',
        'kelas_id' => $this->kelas->id,
    ]);

    $response->assertRedirect(route('admin.siswa.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'Andi',
        'username' => '12345678',
        'role' => 'siswa',
    ]);

    $user = User::where('username', '12345678')->first();

    $this->assertDatabaseHas('siswa', [
        'user_id' => $user->id,
        'nis' => '12345678',
        'kelas_id' => $this->kelas->id,
    ]);
});

test('admin can update siswa and user is updated', function () {
    $user = User::factory()->create(['role' => 'siswa', 'username' => 'oldnis']);
    $siswa = Siswa::create(['user_id' => $user->id, 'nis' => 'oldnis', 'kelas_id' => $this->kelas->id]);

    $kelasBaru = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X RPL 2', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027']);

    $response = $this->actingAs($this->admin)->put(route('admin.siswa.update', $siswa), [
        'name' => 'Nama Baru Siswa',
        'nis' => 'newnis123',
        'kelas_id' => $kelasBaru->id,
    ]);

    $response->assertRedirect(route('admin.siswa.index'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Nama Baru Siswa',
        'username' => 'newnis123',
    ]);

    $this->assertDatabaseHas('siswa', [
        'id' => $siswa->id,
        'nis' => 'newnis123',
        'kelas_id' => $kelasBaru->id,
    ]);
});

test('admin can hard delete siswa if no attendance history', function () {
    $user = User::factory()->create(['role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $user->id, 'nis' => '9999', 'kelas_id' => $this->kelas->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.siswa.destroy', $siswa));

    $response->assertRedirect(route('admin.siswa.index'));

    $this->assertDatabaseMissing('siswa', ['id' => $siswa->id]);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin can reset siswa password', function () {
    $user = User::factory()->create(['role' => 'siswa', 'password' => Hash::make('oldpassword')]);
    $siswa = Siswa::create(['user_id' => $user->id, 'nis' => '9999', 'kelas_id' => $this->kelas->id]);

    $response = $this->actingAs($this->admin)->post(route('admin.siswa.reset-password', $siswa));

    $response->assertRedirect(route('admin.siswa.index'));

    $user->refresh();
    expect(Hash::check('password', $user->password))->toBeTrue();
});

test('admin soft deletes siswa if has history', function () {
    $user = User::factory()->create(['role' => 'siswa']);
    $siswa = Siswa::create(['user_id' => $user->id, 'nis' => '9999', 'kelas_id' => $this->kelas->id]);
    
    $mapel = Mapel::create(['nama' => 'Math', 'kode' => 'MTK']);
    $guruUser = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create(['user_id' => $guruUser->id, 'nip' => 'g1']);
    
    $jadwal = Jadwal::create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $mapel->id,
        'guru_id' => $guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
    ]);
    
    $sesi = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => now()->toDateString(),
        'diabsen_oleh' => $guruUser->id,
    ]);
    
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswa->id,
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.siswa.destroy', $siswa));

    $response->assertRedirect(route('admin.siswa.index'));
    $response->assertSessionHas('success', 'Siswa di-soft-delete karena memiliki riwayat absensi.');

    $this->assertSoftDeleted('siswa', ['id' => $siswa->id]);
    $this->assertDatabaseHas('users', ['id' => $user->id]); // user remains
});
