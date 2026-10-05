<?php

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('siswa can authenticate and redirect to siswa dashboard', function () {
    $user = User::factory()->create(['role' => 'siswa']);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('siswa.dashboard', absolute: false));
});

test('guru can authenticate and redirect to guru dashboard', function () {
    $user = User::factory()->create(['role' => 'guru']);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('guru.dashboard', absolute: false));
});

test('admin can authenticate and redirect to admin dashboard', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password and shows remaining attempts', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('username');
    $errors = session('errors')->get('username');
    $this->assertStringContainsString('Kredensial tidak valid. Sisa percobaan Anda: 4 kali lagi sebelum dikunci.', $errors[0]);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users are rate limited after five failed login attempts', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['username', 'seconds_left']);
    $errors = session('errors')->get('username');
    $this->assertNotEmpty($errors);
    $this->assertStringContainsString('Terlalu banyak percobaan masuk', $errors[0]);
    $secondsLeft = session('errors')->first('seconds_left');
    $this->assertGreaterThan(0, (int) $secondsLeft);
});

test('login view renders alpine rate limiting countdown elements', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('x-data="{ seconds: 0 }"', false);
    $response->assertSee('x-show="seconds > 0"', false);
    $response->assertSee("Terlalu banyak percobaan. Silakan coba lagi dalam <span x-text='seconds'></span> detik.", false);
});

test('AB-10: guru bisa login memakai NIP + password yang benar, lalu diarahkan ke dashboard guru', function () {
    $user = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_username_custom',
        'password' => Hash::make('password123'),
    ]);
    Guru::factory()->create([
        'user_id' => $user->id,
        'nip' => '198501012010011001',
    ]);

    $response = $this->post('/login', [
        'username' => '198501012010011001',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('guru.dashboard', absolute: false));
});

test('AB-10: siswa bisa login memakai NIS + password yang benar, lalu diarahkan ke dashboard siswa', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'username' => 'siswa_username_custom',
        'password' => Hash::make('password123'),
    ]);
    Siswa::factory()->create([
        'user_id' => $user->id,
        'nis' => '12345',
    ]);

    $response = $this->post('/login', [
        'username' => '12345',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('siswa.dashboard', absolute: false));
});

test('AB-10: NIP dan NIS dengan password salah ditolak dan user tetap guest', function () {
    $guruUser = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_invalid_pw',
        'password' => Hash::make('password123'),
    ]);
    Guru::factory()->create([
        'user_id' => $guruUser->id,
        'nip' => '198501012010011002',
    ]);

    $siswaUser = User::factory()->create([
        'role' => 'siswa',
        'username' => 'siswa_invalid_pw',
        'password' => Hash::make('password123'),
    ]);
    Siswa::factory()->create([
        'user_id' => $siswaUser->id,
        'nis' => '54321',
    ]);

    $responseGuru = $this->post('/login', [
        'username' => '198501012010011002',
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
    $responseGuru->assertSessionHasErrors('username');

    $responseSiswa = $this->post('/login', [
        'username' => '54321',
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
    $responseSiswa->assertSessionHasErrors('username');
});

test('AB-10: NIP/NIS yang tidak terdaftar ditolak dengan pesan error yang sama seperti login gagal biasa', function () {
    $guruUser = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_valid_user',
        'password' => Hash::make('password123'),
    ]);
    Guru::factory()->create([
        'user_id' => $guruUser->id,
        'nip' => '198501012010011003',
    ]);

    // Percobaan login dengan NIP terdaftar tapi password salah
    $responseRegistered = $this->post('/login', [
        'username' => '198501012010011003',
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
    $responseRegistered->assertSessionHasErrors('username');
    $registeredErrorMessage = session('errors')->first('username');

    // Percobaan login dengan NIP tidak terdaftar
    $responseUnregisteredNip = $this->post('/login', [
        'username' => '999999999999999999',
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
    $responseUnregisteredNip->assertSessionHasErrors('username');
    $unregisteredNipErrorMessage = session('errors')->first('username');

    // Percobaan login dengan NIS tidak terdaftar
    $responseUnregisteredNis = $this->post('/login', [
        'username' => '88888',
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
    $responseUnregisteredNis->assertSessionHasErrors('username');
    $unregisteredNisErrorMessage = session('errors')->first('username');

    // Pesan error sama persis dan tidak membocorkan keberadaan NIP/NIS
    expect($unregisteredNipErrorMessage)->toBe($registeredErrorMessage);
    expect($unregisteredNisErrorMessage)->toBe($registeredErrorMessage);
    expect($registeredErrorMessage)->toContain('Kredensial tidak valid. Sisa percobaan Anda: 4 kali lagi sebelum dikunci.');
});

test('AB-10: rate limiting server: setelah 5 kali gagal memakai NIP yang sama, percobaan berikutnya mendapat respons terkunci walau password benar', function () {
    $user = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_throttle_nip',
        'password' => Hash::make('password123'),
    ]);
    Guru::factory()->create([
        'user_id' => $user->id,
        'nip' => '198501012010011004',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'username' => '198501012010011004',
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post('/login', [
        'username' => '198501012010011004',
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['username', 'seconds_left']);
    $errors = session('errors')->get('username');
    $this->assertNotEmpty($errors);
    $this->assertStringContainsString('Terlalu banyak percobaan masuk', $errors[0]);
    $secondsLeft = session('errors')->first('seconds_left');
    $this->assertGreaterThan(0, (int) $secondsLeft);
});

test('AB-05: guru yang di-soft-delete ditolak login lewat username dan NIP; siswa lewat username dan NIS, pesan tidak aktif muncul dan user tetap guest', function () {
    // 1. Guru soft delete
    $guruUser = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_trashed',
        'password' => Hash::make('password123'),
    ]);
    $guru = Guru::factory()->create([
        'user_id' => $guruUser->id,
        'nip' => '198001012005011001',
    ]);
    $guru->delete();

    // Login via username
    $responseGuruUsername = $this->post('/login', [
        'username' => 'guru_trashed',
        'password' => 'password123',
    ]);
    $this->assertGuest();
    $responseGuruUsername->assertSessionHasErrors('username');
    $this->assertStringContainsString('Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.', session('errors')->first('username'));

    // Login via NIP
    $responseGuruNip = $this->post('/login', [
        'username' => '198001012005011001',
        'password' => 'password123',
    ]);
    $this->assertGuest();
    $responseGuruNip->assertSessionHasErrors('username');
    $this->assertStringContainsString('Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.', session('errors')->first('username'));

    // 2. Siswa soft delete
    $siswaUser = User::factory()->create([
        'role' => 'siswa',
        'username' => 'siswa_trashed',
        'password' => Hash::make('password123'),
    ]);
    $siswa = Siswa::factory()->create([
        'user_id' => $siswaUser->id,
        'nis' => '99887',
    ]);
    $siswa->delete();

    // Login via username
    $responseSiswaUsername = $this->post('/login', [
        'username' => 'siswa_trashed',
        'password' => 'password123',
    ]);
    $this->assertGuest();
    $responseSiswaUsername->assertSessionHasErrors('username');
    $this->assertStringContainsString('Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.', session('errors')->first('username'));

    // Login via NIS
    $responseSiswaNis = $this->post('/login', [
        'username' => '99887',
        'password' => 'password123',
    ]);
    $this->assertGuest();
    $responseSiswaNis->assertSessionHasErrors('username');
    $this->assertStringContainsString('Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.', session('errors')->first('username'));
});

test('AB-05: guru dan siswa yang aktif tetap bisa login seperti biasa lewat username, NIP/NIS, dan admin tetap bisa login', function () {
    // 1. Guru aktif: login via username dan NIP
    $guruUser = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_aktif',
        'password' => Hash::make('password123'),
    ]);
    Guru::factory()->create([
        'user_id' => $guruUser->id,
        'nip' => '198101012006011002',
    ]);

    $respGuruUser = $this->post('/login', [
        'username' => 'guru_aktif',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($guruUser);
    $respGuruUser->assertRedirect(route('guru.dashboard', absolute: false));

    $this->post('/logout');
    $this->assertGuest();

    $respGuruNip = $this->post('/login', [
        'username' => '198101012006011002',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($guruUser);
    $respGuruNip->assertRedirect(route('guru.dashboard', absolute: false));

    $this->post('/logout');
    $this->assertGuest();

    // 2. Siswa aktif: login via username dan NIS
    $siswaUser = User::factory()->create([
        'role' => 'siswa',
        'username' => 'siswa_aktif',
        'password' => Hash::make('password123'),
    ]);
    Siswa::factory()->create([
        'user_id' => $siswaUser->id,
        'nis' => '11223',
    ]);

    $respSiswaUser = $this->post('/login', [
        'username' => 'siswa_aktif',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($siswaUser);
    $respSiswaUser->assertRedirect(route('siswa.dashboard', absolute: false));

    $this->post('/logout');
    $this->assertGuest();

    $respSiswaNis = $this->post('/login', [
        'username' => '11223',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($siswaUser);
    $respSiswaNis->assertRedirect(route('siswa.dashboard', absolute: false));

    $this->post('/logout');
    $this->assertGuest();

    // 3. Admin: login via username
    $adminUser = User::factory()->create([
        'role' => 'admin',
        'username' => 'admin_aktif',
        'password' => Hash::make('password123'),
    ]);

    $respAdmin = $this->post('/login', [
        'username' => 'admin_aktif',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($adminUser);
    $respAdmin->assertRedirect(route('admin.dashboard', absolute: false));
});

test('AB-05: pengguna guru/siswa yang sedang login lalu profilnya di-soft-delete dikeluarkan pada request berikutnya dan diarahkan ke login', function () {
    // 1. Sesi guru berjalan
    $guruUser = User::factory()->create([
        'role' => 'guru',
    ]);
    $guru = Guru::factory()->create([
        'user_id' => $guruUser->id,
    ]);

    $responseAktif = $this->actingAs($guruUser)->get(route('guru.dashboard'));
    $responseAktif->assertStatus(200);

    // Profil guru di-soft-delete oleh admin
    $guru->delete();

    // Request berikutnya dari guru tersebut
    $responseNext = $this->actingAs($guruUser)->get(route('guru.dashboard'));
    $responseNext->assertRedirect(route('login'));
    $responseNext->assertSessionHas('error', 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.');
    $this->assertGuest();

    // 2. Sesi siswa berjalan
    $siswaUser = User::factory()->create([
        'role' => 'siswa',
    ]);
    $siswa = Siswa::factory()->create([
        'user_id' => $siswaUser->id,
    ]);

    $responseSiswaAktif = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
    $responseSiswaAktif->assertStatus(200);

    // Profil siswa di-soft-delete oleh admin
    $siswa->delete();

    // Request berikutnya dari siswa tersebut
    $responseSiswaNext = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
    $responseSiswaNext->assertRedirect(route('login'));
    $responseSiswaNext->assertSessionHas('error', 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.');
    $this->assertGuest();
});

test('AB-05: setelah profil dipulihkan (restore), login berhasil lagi', function () {
    // 1. Guru dipulihkan
    $guruUser = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_dipulihkan',
        'password' => Hash::make('password123'),
    ]);
    $guru = Guru::factory()->create([
        'user_id' => $guruUser->id,
        'nip' => '198201012007011003',
    ]);
    $guru->delete();

    // Pastikan sebelumnya ditolak
    $this->post('/login', [
        'username' => 'guru_dipulihkan',
        'password' => 'password123',
    ])->assertSessionHasErrors('username');
    $this->assertGuest();

    // Admin memulihkan profil guru (restore)
    $guru->restore();

    // Login via username berhasil
    $resGuruUser = $this->post('/login', [
        'username' => 'guru_dipulihkan',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($guruUser);
    $resGuruUser->assertRedirect(route('guru.dashboard', absolute: false));

    $this->post('/logout');

    // Login via NIP berhasil
    $resGuruNip = $this->post('/login', [
        'username' => '198201012007011003',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($guruUser);
    $resGuruNip->assertRedirect(route('guru.dashboard', absolute: false));

    $this->post('/logout');

    // 2. Siswa dipulihkan
    $siswaUser = User::factory()->create([
        'role' => 'siswa',
        'username' => 'siswa_dipulihkan',
        'password' => Hash::make('password123'),
    ]);
    $siswa = Siswa::factory()->create([
        'user_id' => $siswaUser->id,
        'nis' => '33445',
    ]);
    $siswa->delete();

    // Pastikan sebelumnya ditolak
    $this->post('/login', [
        'username' => 'siswa_dipulihkan',
        'password' => 'password123',
    ])->assertSessionHasErrors('username');
    $this->assertGuest();

    // Admin memulihkan profil siswa (restore)
    $siswa->restore();

    // Login via username berhasil
    $resSiswaUser = $this->post('/login', [
        'username' => 'siswa_dipulihkan',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($siswaUser);
    $resSiswaUser->assertRedirect(route('siswa.dashboard', absolute: false));

    $this->post('/logout');

    // Login via NIS berhasil
    $resSiswaNis = $this->post('/login', [
        'username' => '33445',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($siswaUser);
    $resSiswaNis->assertRedirect(route('siswa.dashboard', absolute: false));
});

test('AB-05: kegagalan login biasa (password salah) tetap memberi pesan sisa percobaan dan rate limiting 5 kali tetap berlaku', function () {
    $user = User::factory()->create([
        'username' => 'user_test_ratelimit',
        'password' => Hash::make('correct-password'),
    ]);

    // Percobaan 1: password salah
    $response = $this->post('/login', [
        'username' => 'user_test_ratelimit',
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
    $response->assertSessionHasErrors('username');
    $this->assertStringContainsString('Kredensial tidak valid. Sisa percobaan Anda: 4 kali lagi sebelum dikunci.', session('errors')->first('username'));

    // Ulangi sampai total 5 kali gagal
    for ($i = 0; $i < 4; $i++) {
        $this->post('/login', [
            'username' => 'user_test_ratelimit',
            'password' => 'wrong-password',
        ]);
    }

    // Percobaan ke-6: harus dikunci rate limiter
    $responseLocked = $this->post('/login', [
        'username' => 'user_test_ratelimit',
        'password' => 'correct-password',
    ]);
    $this->assertGuest();
    $responseLocked->assertSessionHasErrors(['username', 'seconds_left']);
    $this->assertStringContainsString('Terlalu banyak percobaan masuk', session('errors')->first('username'));

    // Cek juga untuk guru yang di-soft-delete bila password salah:
    // tidak membocorkan status akun tidak aktif, melainkan pesan sisa percobaan kredensial biasa
    $guruUser = User::factory()->create([
        'role' => 'guru',
        'username' => 'guru_pw_salah_trashed',
        'password' => Hash::make('correct-password'),
    ]);
    $guru = Guru::factory()->create([
        'user_id' => $guruUser->id,
        'nip' => '198901012015011009',
    ]);
    $guru->delete();

    $responseGuruWrong = $this->post('/login', [
        'username' => '198901012015011009',
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
    $responseGuruWrong->assertSessionHasErrors('username');
    $this->assertStringContainsString('Kredensial tidak valid. Sisa percobaan Anda: 4 kali lagi sebelum dikunci.', session('errors')->first('username'));
});
