<?php

use App\Models\User;

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertStatus(200);
});

test('guru cannot access admin dashboard', function () {
    $guru = User::factory()->create(['role' => 'guru']);

    $response = $this->actingAs($guru)->get(route('admin.dashboard'));
    $response->assertStatus(403);
});

test('siswa cannot access admin dashboard', function () {
    $siswa = User::factory()->create(['role' => 'siswa']);

    $response = $this->actingAs($siswa)->get(route('admin.dashboard'));
    $response->assertStatus(403);
});

test('guru can access guru dashboard', function () {
    $guru = User::factory()->create(['role' => 'guru']);

    $response = $this->actingAs($guru)->get(route('guru.dashboard'));
    $response->assertStatus(200);
});

test('admin cannot access guru dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('guru.dashboard'));
    $response->assertStatus(403);
});

test('siswa can access siswa dashboard', function () {
    $siswa = User::factory()->create(['role' => 'siswa']);
    \App\Models\Siswa::factory()->create(['user_id' => $siswa->id]);

    $response = $this->actingAs($siswa)->get(route('siswa.dashboard'));
    $response->assertStatus(200);
});
