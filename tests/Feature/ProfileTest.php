<?php

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();
});

test('AB-05: Pengguna guru atau siswa yang profilnya sudah di-soft-delete tidak bisa membuka /profile (diarahkan ke login dengan pesan akun tidak aktif); admin, guru aktif, dan siswa aktif tetap bisa membuka /profile dan mengganti password; tamu diarahkan ke login', function () {
    // 1. Tamu (guest) diarahkan ke login
    $this->get('/profile')->assertRedirect(route('login'));
    $this->put('/password', [])->assertRedirect(route('login'));

    // 2. Guru yang profilnya di-soft-delete tidak bisa membuka /profile dan /password
    $guruUser = User::factory()->create(['role' => 'guru', 'password' => Hash::make('password')]);
    $guru = Guru::factory()->create(['user_id' => $guruUser->id]);
    $guru->delete();

    $respGuruProfile = $this->actingAs($guruUser)->get('/profile');
    $respGuruProfile->assertRedirect(route('login'));
    $respGuruProfile->assertSessionHas('error', 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.');
    $this->assertGuest();

    $respGuruPassword = $this->actingAs($guruUser)->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);
    $respGuruPassword->assertRedirect(route('login'));
    $respGuruPassword->assertSessionHas('error', 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.');
    $this->assertGuest();

    // 3. Siswa yang profilnya di-soft-delete tidak bisa membuka /profile dan /password
    $siswaUser = User::factory()->create(['role' => 'siswa', 'password' => Hash::make('password')]);
    $siswa = Siswa::factory()->create(['user_id' => $siswaUser->id]);
    $siswa->delete();

    $respSiswaProfile = $this->actingAs($siswaUser)->get('/profile');
    $respSiswaProfile->assertRedirect(route('login'));
    $respSiswaProfile->assertSessionHas('error', 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.');
    $this->assertGuest();

    $respSiswaPassword = $this->actingAs($siswaUser)->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);
    $respSiswaPassword->assertRedirect(route('login'));
    $respSiswaPassword->assertSessionHas('error', 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.');
    $this->assertGuest();

    // 4. Admin aktif tetap bisa membuka /profile dan mengganti password
    $adminUser = User::factory()->create(['role' => 'admin', 'password' => Hash::make('password')]);
    $this->actingAs($adminUser)->get('/profile')->assertOk();
    $respAdminPass = $this->actingAs($adminUser)->from('/profile')->put('/password', [
        'current_password' => 'password',
        'password' => 'new-admin-password',
        'password_confirmation' => 'new-admin-password',
    ]);
    $respAdminPass->assertSessionHasNoErrors();
    $this->assertTrue(Hash::check('new-admin-password', $adminUser->refresh()->password));

    // 5. Guru aktif tetap bisa membuka /profile dan mengganti password
    $guruAktifUser = User::factory()->create(['role' => 'guru', 'password' => Hash::make('password')]);
    Guru::factory()->create(['user_id' => $guruAktifUser->id]);
    $this->actingAs($guruAktifUser)->get('/profile')->assertOk();
    $respGuruPass = $this->actingAs($guruAktifUser)->from('/profile')->put('/password', [
        'current_password' => 'password',
        'password' => 'new-guru-password',
        'password_confirmation' => 'new-guru-password',
    ]);
    $respGuruPass->assertSessionHasNoErrors();
    $this->assertTrue(Hash::check('new-guru-password', $guruAktifUser->refresh()->password));

    // 6. Siswa aktif tetap bisa membuka /profile dan mengganti password
    $siswaAktifUser = User::factory()->create(['role' => 'siswa', 'password' => Hash::make('password')]);
    Siswa::factory()->create(['user_id' => $siswaAktifUser->id]);
    $this->actingAs($siswaAktifUser)->get('/profile')->assertOk();
    $respSiswaPass = $this->actingAs($siswaAktifUser)->from('/profile')->put('/password', [
        'current_password' => 'password',
        'password' => 'new-siswa-password',
        'password_confirmation' => 'new-siswa-password',
    ]);
    $respSiswaPass->assertSessionHasNoErrors();
    $this->assertTrue(Hash::check('new-siswa-password', $siswaAktifUser->refresh()->password));

    // 7. Setelah logout, kembali menjadi tamu dan diarahkan ke login
    auth()->logout();
    $this->get('/profile')->assertRedirect(route('login'));
    $this->put('/password', [])->assertRedirect(route('login'));
});
