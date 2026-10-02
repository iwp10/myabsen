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
