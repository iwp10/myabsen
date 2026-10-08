<?php

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test Notifikasi Ganti Password, Header No-Cache, Logout Idempotent, dan 419
|--------------------------------------------------------------------------
*/

test('ganti password sukses menampilkan banner hijau; pengguna sukarela tetap di profil', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('PasswordLama123!'),
        'must_change_password' => false,
    ]);

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordLama123!',
        'password' => 'PasswordBaru999!',
        'password_confirmation' => 'PasswordBaru999!',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('status', 'Password berhasil diganti.');
    $this->assertTrue(Hash::check('PasswordBaru999!', $user->refresh()->password));

    // Follow redirect dan pastikan banner hijau muncul
    $page = $this->actingAs($user)->get(route('profile.edit'));
    $page->assertSee('Password berhasil diganti.');
});

test('ganti password sukses pengguna wajib-ganti mengarahkan ke dashboard role-nya dengan banner hijau', function ($role, $dashboardRoute) {
    $user = User::factory()->create([
        'role' => $role,
        'password' => Hash::make('PasswordAwal123!'),
        'must_change_password' => true,
    ]);

    if ($role === 'guru') {
        Guru::factory()->create(['user_id' => $user->id]);
    } elseif ($role === 'siswa') {
        Siswa::factory()->create(['user_id' => $user->id]);
    }

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordAwal123!',
        'password' => 'KunciBaruSuper2026!',
        'password_confirmation' => 'KunciBaruSuper2026!',
    ]);

    $response->assertRedirect(route($dashboardRoute));
    $response->assertSessionHas('status', 'Password berhasil diganti.');

    $user->refresh();
    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('KunciBaruSuper2026!', $user->password))->toBeTrue();

    // Pastikan dashboard menampilkan pesan sukses
    $page = $this->actingAs($user)->get(route($dashboardRoute));
    $page->assertSee('Password berhasil diganti.');
})->with([
    ['admin', 'admin.dashboard'],
    ['guru', 'guru.dashboard'],
    ['siswa', 'siswa.dashboard'],
]);

test('ganti password gagal bila password lama salah: banner merah dan pesan kolom muncul, password tidak berubah', function () {
    $user = User::factory()->create([
        'role' => 'guru',
        'password' => Hash::make('PasswordBenar123!'),
    ]);
    Guru::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordSalahTotal!',
        'password' => 'PasswordBaru999!',
        'password_confirmation' => 'PasswordBaru999!',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrorsIn('updatePassword', [
        'current_password' => 'Password lama salah.',
    ]);

    $user->refresh();
    expect(Hash::check('PasswordBenar123!', $user->password))->toBeTrue();

    // Follow redirect ke halaman profil dan pastikan banner merah tampil
    $page = $this->actingAs($user)->get(route('profile.edit'));
    $page->assertSee('Password gagal diganti. Periksa isian di bawah.');
    $page->assertSee('Password lama salah.');
});

test('ganti password gagal bila password baru kurang dari 8 karakter: pesan kolom muncul dan password tidak berubah', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'password' => Hash::make('PasswordBenar123!'),
    ]);
    Siswa::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordBenar123!',
        'password' => 'pendek',
        'password_confirmation' => 'pendek',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrorsIn('updatePassword', [
        'password' => 'Password baru kurang dari 8 karakter.',
    ]);

    $user->refresh();
    expect(Hash::check('PasswordBenar123!', $user->password))->toBeTrue();
});

test('ganti password gagal bila password baru sama dengan password lama: pesan kolom muncul dan password tidak berubah', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('PasswordLamaSama123!'),
    ]);

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordLamaSama123!',
        'password' => 'PasswordLamaSama123!',
        'password_confirmation' => 'PasswordLamaSama123!',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrorsIn('updatePassword', [
        'password' => 'Password baru tidak boleh sama dengan password lama.',
    ]);

    $errors = session('errors')->getBag('updatePassword')->get('password');
    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toBe('Password baru tidak boleh sama dengan password lama.');

    $user->refresh();
    expect(Hash::check('PasswordLamaSama123!', $user->password))->toBeTrue();
});

test('pengguna wajib ganti password mengisi password baru sama dengan password awal: tepat satu pesan password lama dan teks password awal tidak muncul', function () {
    config(['absensi.password_awal' => 'PasswordAwal123!']);

    $user = User::factory()->create([
        'role' => 'siswa',
        'password' => Hash::make('PasswordAwal123!'),
        'must_change_password' => true,
    ]);
    Siswa::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordAwal123!',
        'password' => 'PasswordAwal123!',
        'password_confirmation' => 'PasswordAwal123!',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrorsIn('updatePassword', [
        'password' => 'Password baru tidak boleh sama dengan password lama.',
    ]);

    $errors = session('errors')->getBag('updatePassword')->get('password');
    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toBe('Password baru tidak boleh sama dengan password lama.')
        ->and(session('errors')->getBag('updatePassword')->first('password'))->not->toContain('password awal');

    $user->refresh();
    expect($user->must_change_password)->toBeTrue()
        ->and(Hash::check('PasswordAwal123!', $user->password))->toBeTrue();
});

test('pengguna yang sudah pernah mengganti password lalu mencoba memakai password awal: muncul pesan password awal saja', function () {
    config(['absensi.password_awal' => 'KunciDefaultSekolah!']);

    $user = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('PasswordLain123!'),
        'must_change_password' => false,
    ]);

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordLain123!',
        'password' => 'KunciDefaultSekolah!',
        'password_confirmation' => 'KunciDefaultSekolah!',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrorsIn('updatePassword', [
        'password' => 'Password baru tidak boleh sama dengan password awal.',
    ]);

    $errors = session('errors')->getBag('updatePassword')->get('password');
    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toBe('Password baru tidak boleh sama dengan password awal.');

    $user->refresh();
    expect(Hash::check('PasswordLain123!', $user->password))->toBeTrue();
});

test('ganti password gagal bila konfirmasi tidak cocok: pesan kolom muncul dan password tidak berubah', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('PasswordBenar123!'),
    ]);

    $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'PasswordBenar123!',
        'password' => 'PasswordBaruA123!',
        'password_confirmation' => 'PasswordBaruB999!',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrorsIn('updatePassword', [
        'password' => 'Konfirmasi password tidak cocok.',
    ]);

    $user->refresh();
    expect(Hash::check('PasswordBenar123!', $user->password))->toBeTrue();
});

test('halaman terautentikasi mengirim header no-store sedangkan login tidak mengirim no-store', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $guru = User::factory()->create(['role' => 'guru']);
    Guru::factory()->create(['user_id' => $guru->id]);
    $siswa = User::factory()->create(['role' => 'siswa']);
    Siswa::factory()->create(['user_id' => $siswa->id]);

    // Admin dashboard
    $respAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
    $respAdmin->assertOk();
    $cacheControlAdmin = (string) $respAdmin->headers->get('Cache-Control');
    expect($cacheControlAdmin)
        ->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('must-revalidate')
        ->toContain('max-age=0');
    $respAdmin->assertHeader('Pragma', 'no-cache');
    $respAdmin->assertHeader('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

    // Guru dashboard
    $respGuru = $this->actingAs($guru)->get(route('guru.dashboard'));
    $respGuru->assertOk();
    expect((string) $respGuru->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('must-revalidate')
        ->toContain('max-age=0');

    // Siswa dashboard
    $respSiswa = $this->actingAs($siswa)->get(route('siswa.dashboard'));
    $respSiswa->assertOk();
    expect((string) $respSiswa->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('must-revalidate')
        ->toContain('max-age=0');

    // Profil
    $respProfile = $this->actingAs($admin)->get(route('profile.edit'));
    $respProfile->assertOk();
    expect((string) $respProfile->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('must-revalidate')
        ->toContain('max-age=0');

    // Laporan admin
    $respLaporan = $this->actingAs($admin)->get(route('admin.laporan.index'));
    $respLaporan->assertOk();
    expect((string) $respLaporan->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('must-revalidate')
        ->toContain('max-age=0');

});

test('halaman login tamu tidak mengirim header no-store', function () {
    $this->assertGuest();
    $respLogin = $this->get(route('login'));
    $respLogin->assertOk();
    $cacheControlLogin = (string) $respLogin->headers->get('Cache-Control');
    expect(str_contains($cacheControlLogin, 'no-store'))->toBeFalse();
});

test('unduhan berkas excel tidak terpengaruh header no-store middleware', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.laporan.export'));
    $response->assertOk();

    // Verifikasi respons unduhan berkas biner
    $contentDisposition = (string) $response->headers->get('Content-Disposition');
    expect(str_contains($contentDisposition, 'attachment'))->toBeTrue();
});

test('POST /logout saat pengguna sudah guest diarahkan ke login secara idempotent tanpa 419/500', function () {
    $this->assertGuest();

    $response = $this->post(route('logout'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Anda sudah keluar.');
});

test('POST /logout saat terautentikasi menghapus sesi dan mengarahkan ke halaman root', function () {
    $user = User::factory()->create(['role' => 'guru']);
    Guru::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('penanganan TokenMismatchException: request json membalas 419 JSON', function () {
    Route::post('/test-csrf-exception', function () {
        throw new TokenMismatchException('CSRF token mismatch.');
    });

    $response = $this->postJson('/test-csrf-exception');
    $response->assertStatus(419);
    $response->assertJson(['message' => 'CSRF token mismatch.']);
});

test('penanganan TokenMismatchException: request tamu diarahkan ke login dengan pesan sesi berakhir', function () {
    Route::middleware('web')->post('/test-csrf-guest', function () {
        throw new TokenMismatchException('CSRF token mismatch.');
    });

    $this->assertGuest();
    $response = $this->post('/test-csrf-guest');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Sesi Anda sudah berakhir. Silakan masuk kembali.');
});

test('penanganan TokenMismatchException: request pengguna terautentikasi diarahkan kembali dengan pesan halaman kedaluwarsa', function () {
    Route::middleware('web')->post('/test-csrf-auth', function () {
        throw new TokenMismatchException('CSRF token mismatch.');
    });

    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->from('/admin/dashboard')->post('/test-csrf-auth');

    $response->assertRedirect('/admin/dashboard');
    $response->assertSessionHas('error', 'Halaman kedaluwarsa, silakan ulangi.');
});

test('penanganan TokenMismatchException pada rute logout diarahkan ke login dengan pesan Anda sudah keluar', function () {
    Route::middleware('web')->post('/test-csrf-logout', function () {
        throw new TokenMismatchException('CSRF token mismatch.');
    });

    $response = $this->post('/logout');
    $response->assertRedirect(route('login'));
});
