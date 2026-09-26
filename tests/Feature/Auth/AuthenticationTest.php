<?php

use App\Models\User;

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
