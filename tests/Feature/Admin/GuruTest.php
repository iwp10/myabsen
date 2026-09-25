<?php

use App\Models\Guru;
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
