<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

test('AB-10: 1. DemoSeeder menolak jalan di produksi; DatabaseSeeder di non-produksi tetap mengisi data demo', function () {
    // DemoSeeder menolak jalan di environment produksi
    app()['env'] = 'production';
    try {
        (new DemoSeeder)->run();
        $this->fail('DemoSeeder seharusnya melempar exception di lingkungan produksi.');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain('DemoSeeder tidak boleh dijalankan di environment produksi.');
    } finally {
        app()['env'] = 'testing';
    }

    // DatabaseSeeder di non-produksi tetap mengisi data demo
    app()['env'] = 'testing';
    (new DatabaseSeeder)->run();

    $this->assertDatabaseHas('users', ['username' => 'admin', 'role' => 'admin']);
    $this->assertDatabaseHas('users', ['username' => 'guru1', 'role' => 'guru']);
    $this->assertDatabaseHas('users', ['username' => 'siswa1', 'role' => 'siswa']);
    $this->assertDatabaseHas('kelas', ['nama' => '10 RPL 1']);
    expect(Guru::count())->toBeGreaterThan(0);
    expect(Siswa::count())->toBeGreaterThan(0);
});

test('AB-10: 2. AdminSeeder membuat satu admin dan tidak membuat data lain; dijalankan dua kali tidak menimpa password; password lemah di produksi ditolak; password kosong menghasilkan akun dengan must_change_password true', function () {
    config([
        'absensi.admin.name' => 'Super Administrator',
        'absensi.admin.username' => 'superadmin_test',
        'absensi.admin.password' => 'KuatRahasia123!',
    ]);

    (new AdminSeeder)->run();

    $admin = User::where('username', 'superadmin_test')->first();
    expect($admin)->not->toBeNull();
    expect($admin->must_change_password)->toBeTrue();
    expect(Hash::check('KuatRahasia123!', $admin->password))->toBeTrue();

    // Tidak membuat data lain
    expect(Guru::count())->toBe(0);
    expect(Siswa::count())->toBe(0);
    expect(Kelas::count())->toBe(0);
    expect(Jadwal::count())->toBe(0);

    // Dijalankan dua kali tidak menimpa password
    $originalPasswordHash = $admin->password;
    config(['absensi.admin.password' => 'PasswordBaru999!']);
    (new AdminSeeder)->run();
    $admin->refresh();
    expect($admin->password)->toBe($originalPasswordHash);

    // Password lemah di produksi ditolak
    app()['env'] = 'production';
    config([
        'absensi.admin.username' => 'admin_lemah_prod',
        'absensi.admin.password' => 'password',
    ]);
    try {
        (new AdminSeeder)->run();
        $this->fail('AdminSeeder seharusnya menolak password lemah di lingkungan produksi.');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain('minimal 12 karakter');
    } finally {
        app()['env'] = 'testing';
    }
    expect(User::where('username', 'admin_lemah_prod')->exists())->toBeFalse();

    // Password kosong menghasilkan akun dengan must_change_password true
    config([
        'absensi.admin.username' => 'admin_acak_prod',
        'absensi.admin.password' => '',
    ]);
    (new AdminSeeder)->run();
    $adminAcak = User::where('username', 'admin_acak_prod')->first();
    expect($adminAcak)->not->toBeNull();
    expect($adminAcak->must_change_password)->toBeTrue();
});

test('AB-10: 3. Admin menambah guru dan siswa, impor siswa, dan reset password menghasilkan akun dengan must_change_password true dan password awal dari config; di produksi dengan password_awal kosong atau password pembuatan ditolak dengan pesan ramah dan tidak ada data tersimpan', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jurusan = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);
    $kelas = Kelas::create(['jurusan_id' => $jurusan->id, 'nama' => '10 RPL 1', 'tingkat' => '10', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

    config(['absensi.password_awal' => 'KunciAwal2026!']);

    // Admin tambah guru
    $respGuru = $this->actingAs($admin)->post(route('admin.guru.store'), [
        'name' => 'Guru Pengujian',
        'nip' => '198501012010011099',
    ]);
    $respGuru->assertRedirect(route('admin.guru.index'));
    $guruUser = User::where('username', '198501012010011099')->first();
    expect($guruUser)->not->toBeNull();
    expect($guruUser->must_change_password)->toBeTrue();
    expect(Hash::check('KunciAwal2026!', $guruUser->password))->toBeTrue();

    // Admin tambah siswa
    $respSiswa = $this->actingAs($admin)->post(route('admin.siswa.store'), [
        'name' => 'Siswa Pengujian',
        'nis' => '8888',
        'kelas_id' => $kelas->id,
    ]);
    $respSiswa->assertRedirect(route('admin.siswa.index'));
    $siswaUser = User::where('username', '8888')->first();
    expect($siswaUser)->not->toBeNull();
    expect($siswaUser->must_change_password)->toBeTrue();
    expect(Hash::check('KunciAwal2026!', $siswaUser->password))->toBeTrue();

    // Reset password guru
    $guru = Guru::where('nip', '198501012010011099')->first();
    $guruUser->update(['password' => Hash::make('sudahDigantiDulu'), 'must_change_password' => false]);
    $this->actingAs($admin)->post(route('admin.guru.reset-password', $guru));
    $guruUser->refresh();
    expect($guruUser->must_change_password)->toBeTrue();
    expect(Hash::check('KunciAwal2026!', $guruUser->password))->toBeTrue();

    // Reset password siswa
    $siswa = Siswa::where('nis', '8888')->first();
    $siswaUser->update(['password' => Hash::make('sudahDigantiDulu'), 'must_change_password' => false]);
    $this->actingAs($admin)->post(route('admin.siswa.reset-password', $siswa));
    $siswaUser->refresh();
    expect($siswaUser->must_change_password)->toBeTrue();
    expect(Hash::check('KunciAwal2026!', $siswaUser->password))->toBeTrue();

    // Impor siswa
    $file = UploadedFile::fake()->createWithContent('siswa.csv', "nis,nama\n9991,Siswa Impor\n");
    $this->actingAs($admin)->post(route('admin.siswa.import'), [
        'kelas_id' => $kelas->id,
        'file' => $file,
    ]);
    $imporUser = User::where('username', '9991')->first();
    expect($imporUser)->not->toBeNull();
    expect($imporUser->must_change_password)->toBeTrue();
    expect(Hash::check('KunciAwal2026!', $imporUser->password))->toBeTrue();

    // Di produksi dengan password_awal kosong atau 'password' ditolak dan tidak ada data tersimpan
    app()['env'] = 'production';
    config(['absensi.password_awal' => 'password']);

    try {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $respTolakGuru = $this->actingAs($admin)->post(route('admin.guru.store'), [
            'name' => 'Guru Ditolak',
            'nip' => '1999999999',
        ]);
        $respTolakGuru->assertSessionHas('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        expect(User::where('username', '1999999999')->exists())->toBeFalse();

        $respTolakSiswa = $this->actingAs($admin)->post(route('admin.siswa.store'), [
            'name' => 'Siswa Ditolak',
            'nis' => '7777',
            'kelas_id' => $kelas->id,
        ]);
        $respTolakSiswa->assertSessionHas('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        expect(User::where('username', '7777')->exists())->toBeFalse();

        $respTolakReset = $this->actingAs($admin)->post(route('admin.guru.reset-password', $guru));
        $respTolakReset->assertSessionHas('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');

        $fileTolak = UploadedFile::fake()->createWithContent('siswa_tolak.csv', "nis,nama\n7778,Siswa Gagal\n");
        $respTolakImpor = $this->actingAs($admin)->post(route('admin.siswa.import'), [
            'kelas_id' => $kelas->id,
            'file' => $fileTolak,
        ]);
        $respTolakImpor->assertSessionHas('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        expect(User::where('username', '7778')->exists())->toBeFalse();
    } finally {
        app()['env'] = 'testing';
    }
});

test('AB-10: 4. Pengguna dengan must_change_password true hanya bisa membuka profil dan logout; dashboard, jadwal, laporan, dan route lain diarahkan ke profil dengan banner', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => true,
    ]);

    // Buka profil diizinkan dan memuat banner
    $respProfile = $this->actingAs($user)->get(route('profile.edit'));
    $respProfile->assertOk();
    $respProfile->assertSee('Anda harus mengganti password awal sebelum melanjutkan.');

    // Buka dashboard admin diarahkan ke profil dengan pesan banner
    $respDashboard = $this->actingAs($user)->get(route('admin.dashboard'));
    $respDashboard->assertRedirect(route('profile.edit'));
    $respDashboard->assertSessionHas('warning', 'Anda harus mengganti password awal sebelum melanjutkan.');

    // Buka laporan admin diarahkan ke profil
    $respLaporan = $this->actingAs($user)->get(route('admin.laporan.index'));
    $respLaporan->assertRedirect(route('profile.edit'));

    // Request AJAX/JSON dibalas 403
    $respJson = $this->actingAs($user)->getJson(route('admin.dashboard'));
    $respJson->assertStatus(403);
    expect($respJson->json('message'))->toContain('Anda harus mengganti password awal sebelum melanjutkan.');

    // Guru dengan must_change_password = true
    $guruUser = User::factory()->create([
        'role' => 'guru',
        'must_change_password' => true,
    ]);
    Guru::factory()->create(['user_id' => $guruUser->id]);

    $respGuruJadwal = $this->actingAs($guruUser)->get(route('guru.jadwal'));
    $respGuruJadwal->assertRedirect(route('profile.edit'));
    $respGuruJadwal->assertSessionHas('warning', 'Anda harus mengganti password awal sebelum melanjutkan.');

    // Siswa dengan must_change_password = true
    $siswaUser = User::factory()->create([
        'role' => 'siswa',
        'must_change_password' => true,
    ]);
    Siswa::factory()->create(['user_id' => $siswaUser->id]);

    $respSiswaDash = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
    $respSiswaDash->assertRedirect(route('profile.edit'));

    // Logout tetap diizinkan
    $respLogout = $this->actingAs($user)->post(route('logout'));
    $respLogout->assertRedirect('/');
    $this->assertGuest();
});

test('AB-10: 5. Setelah ganti password berhasil, flag menjadi false dan dashboard terbuka. Password baru yang sama dengan password awal atau kurang dari 8 karakter ditolak', function () {
    config(['absensi.password_awal' => 'InitialPass123!']);

    $user = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('InitialPass123!'),
        'must_change_password' => true,
    ]);

    // Password baru < 8 karakter ditolak
    $respPendek = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'InitialPass123!',
        'password' => 'pendek',
        'password_confirmation' => 'pendek',
    ]);
    $respPendek->assertRedirect(route('profile.edit'));
    $respPendek->assertSessionHasErrorsIn('updatePassword', 'password');
    $user->refresh();
    expect($user->must_change_password)->toBeTrue();

    // Password baru sama dengan password awal dari config ditolak
    $respSamaConfig = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'InitialPass123!',
        'password' => 'InitialPass123!',
        'password_confirmation' => 'InitialPass123!',
    ]);
    $respSamaConfig->assertRedirect(route('profile.edit'));
    $respSamaConfig->assertSessionHasErrorsIn('updatePassword', 'password');

    // Password baru bernilai 'password' juga ditolak
    $respSamaPassword = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'InitialPass123!',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);
    $respSamaPassword->assertRedirect(route('profile.edit'));
    $respSamaPassword->assertSessionHasErrorsIn('updatePassword', 'password');

    // Ganti password valid berhasil
    $respSukses = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
        'current_password' => 'InitialPass123!',
        'password' => 'KunciBaruAdmin99!',
        'password_confirmation' => 'KunciBaruAdmin99!',
    ]);
    $respSukses->assertRedirect(route('profile.edit'));
    $respSukses->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('KunciBaruAdmin99!', $user->password))->toBeTrue();

    // Dashboard sekarang bisa dibuka
    $respDash = $this->actingAs($user)->get(route('admin.dashboard'));
    $respDash->assertOk();
});

test('AB-10: 6. Login pertama langsung mengarahkan ke profil. Akun lama (flag false) tidak terpengaruh. Rate limiting login tetap berlaku', function () {
    // Akun dengan must_change_password = true login -> diarahkan ke profil
    $userBaru = User::factory()->create([
        'username' => 'user_baru_123',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'must_change_password' => true,
    ]);

    $respLoginBaru = $this->post(route('login'), [
        'username' => 'user_baru_123',
        'password' => 'password123',
    ]);
    $respLoginBaru->assertRedirect(route('profile.edit'));

    auth()->logout();

    // Akun lama dengan must_change_password = false login -> diarahkan ke dashboard
    $userLama = User::factory()->create([
        'username' => 'user_lama_123',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'must_change_password' => false,
    ]);

    $respLoginLama = $this->post(route('login'), [
        'username' => 'user_lama_123',
        'password' => 'password123',
    ]);
    $respLoginLama->assertRedirect(route('admin.dashboard'));

    auth()->logout();

    // Rate limiting tetap berlaku
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), [
            'username' => 'user_throttle_test',
            'password' => 'wrong',
        ]);
    }

    $respThrottle = $this->post(route('login'), [
        'username' => 'user_throttle_test',
        'password' => 'wrong',
    ]);
    $respThrottle->assertSessionHasErrors('username');
    $errors = session('errors')->get('username');
    expect($errors[0])->toContain('Terlalu banyak percobaan masuk');
});
