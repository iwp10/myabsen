<?php

use App\Models\User;

test('AB-03: menu Koreksi Absensi hanya bisa diakses admin (guru dan siswa 403)', function () {
    $adminUser = User::factory()->create(['role' => 'admin']);
    $guruUser = User::factory()->create(['role' => 'guru']);
    $siswaUser = User::factory()->create(['role' => 'siswa']);

    // 1. Guest diarahkan ke login
    $responseGuest = $this->get(route('admin.koreksi-absensi.index'));
    $responseGuest->assertRedirect(route('login'));

    // 2. Guru ditolak (403)
    $responseGuru = $this->actingAs($guruUser)->get(route('admin.koreksi-absensi.index'));
    $responseGuru->assertStatus(403);

    // 3. Siswa ditolak (403)
    $responseSiswa = $this->actingAs($siswaUser)->get(route('admin.koreksi-absensi.index'));
    $responseSiswa->assertStatus(403);

    // 4. Admin berhasil (200)
    $responseAdmin = $this->actingAs($adminUser)->get(route('admin.koreksi-absensi.index'));
    $responseAdmin->assertStatus(200);
    $responseAdmin->assertSee('Koreksi Absensi');
});
