<?php

use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin can see guru list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.guru.index'));
    $response->assertStatus(200);
});

test('admin can create guru and auto generate user', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.guru.store'), [
        'name' => 'Budi Santoso',
        'nip' => '198001012005011001',
    ]);

    $response->assertRedirect(route('admin.guru.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'Budi Santoso',
        'username' => '198001012005011001',
        'role' => 'guru',
    ]);

    $user = User::where('username', '198001012005011001')->first();

    $this->assertDatabaseHas('guru', [
        'user_id' => $user->id,
        'nip' => '198001012005011001',
    ]);
});

test('admin can update guru and user is updated', function () {
    $user = User::factory()->create(['role' => 'guru', 'username' => 'oldnip']);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => 'oldnip']);

    $response = $this->actingAs($this->admin)->put(route('admin.guru.update', $guru), [
        'name' => 'Nama Baru',
        'nip' => 'newnip123',
    ]);

    $response->assertRedirect(route('admin.guru.index'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Nama Baru',
        'username' => 'newnip123',
    ]);

    $this->assertDatabaseHas('guru', [
        'id' => $guru->id,
        'nip' => 'newnip123',
    ]);
});

test('admin can hard delete guru if no attendance history', function () {
    $user = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => '12345']);

    $response = $this->actingAs($this->admin)->delete(route('admin.guru.destroy', $guru));

    $response->assertRedirect(route('admin.guru.index'));

    $this->assertDatabaseMissing('guru', ['id' => $guru->id]);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin can reset guru password', function () {
    $user = User::factory()->create(['role' => 'guru', 'password' => Hash::make('oldpassword')]);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => '12345']);

    $response = $this->actingAs($this->admin)->post(route('admin.guru.reset-password', $guru));

    $response->assertRedirect(route('admin.guru.index'));

    $user->refresh();
    expect(Hash::check('password', $user->password))->toBeTrue();
});

test('admin can search guru by nip or user name', function () {
    $user1 = User::factory()->create(['name' => 'Ahmad Dahlan', 'role' => 'guru', 'username' => '11111']);
    Guru::create(['user_id' => $user1->id, 'nip' => '11111']);

    $user2 = User::factory()->create(['name' => 'Siti Walidah', 'role' => 'guru', 'username' => '22222']);
    Guru::create(['user_id' => $user2->id, 'nip' => '22222']);

    $responseName = $this->actingAs($this->admin)->get(route('admin.guru.index', ['search' => 'Ahmad']));
    $responseName->assertStatus(200);
    $responseName->assertSee('Ahmad Dahlan');
    $responseName->assertDontSee('Siti Walidah');

    $responseNip = $this->actingAs($this->admin)->get(route('admin.guru.index', ['search' => '22222']));
    $responseNip->assertStatus(200);
    $responseNip->assertSee('Siti Walidah');
    $responseNip->assertDontSee('Ahmad Dahlan');
});

test('guru index paginates 10 items per page', function () {
    for ($i = 0; $i < 15; $i++) {
        $user = User::factory()->create(['role' => 'guru', 'username' => 'nip'.$i]);
        Guru::create(['user_id' => $user->id, 'nip' => 'nip'.$i]);
    }

    $response = $this->actingAs($this->admin)->get(route('admin.guru.index'));
    $response->assertStatus(200);
    $gurus = $response->viewData('gurus');
    expect($gurus->count())->toBe(10);
    expect($gurus->total())->toBe(15);
});

test('AB-05: guru yang punya riwayat absensi (jadwal + sesi_absensis) dihapus admin: baris Guru tetap ada di database dengan deleted_at terisi, dan riwayat absensinya tetap utuh serta masih bisa dibaca', function () {
    $user = User::factory()->create(['role' => 'guru', 'name' => 'Guru Riwayat']);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => '198001012005011005']);

    $jadwal = Jadwal::factory()->create([
        'guru_id' => $guru->id,
    ]);

    $sesi = SesiAbsensi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'diabsen_oleh' => $user->id,
    ]);

    $detail = DetailAbsensi::factory()->create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => Siswa::factory()->create(['kelas_id' => $jadwal->kelas_id])->id,
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.guru.destroy', $guru));

    $response->assertRedirect(route('admin.guru.index'));
    $response->assertSessionHas('success', 'Guru di-soft-delete karena memiliki riwayat absensi.');

    // Baris Guru tetap ada di database dengan deleted_at terisi (soft delete)
    $this->assertSoftDeleted('guru', ['id' => $guru->id]);
    $this->assertDatabaseHas('guru', ['id' => $guru->id]);
    expect($guru->fresh()->trashed())->toBeTrue();
    expect($guru->fresh()->deleted_at)->not->toBeNull();

    // Akun User milik guru tetap ada
    $this->assertDatabaseHas('users', ['id' => $user->id]);

    // Riwayat absensinya tetap utuh serta masih bisa dibaca
    $this->assertDatabaseHas('jadwal', ['id' => $jadwal->id]);
    $this->assertDatabaseHas('sesi_absensi', ['id' => $sesi->id]);
    $this->assertDatabaseHas('detail_absensi', ['id' => $detail->id]);

    $sesiDb = SesiAbsensi::with('jadwal', 'detailAbsensi')->find($sesi->id);
    expect($sesiDb)->not->toBeNull();
    expect($sesiDb->jadwal)->not->toBeNull();
    expect($sesiDb->jadwal->guru_id)->toBe($guru->id);
    expect($sesiDb->detailAbsensi)->toHaveCount(1);
    expect($sesiDb->detailAbsensi->first()->id)->toBe($detail->id);
});

test('AB-05: guru tanpa riwayat absensi dihapus admin: ikuti perilaku yang sudah ada di GuruController', function () {
    $user = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => '123456']);

    $response = $this->actingAs($this->admin)->delete(route('admin.guru.destroy', $guru));

    $response->assertRedirect(route('admin.guru.index'));
    $response->assertSessionHas('success', 'Guru beserta akun berhasil dihapus.');

    $this->assertDatabaseMissing('guru', ['id' => $guru->id]);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
