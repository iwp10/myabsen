<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->userGuru = User::factory()->create(['role' => 'guru', 'name' => 'Pak Budi']);
    $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id]);

    $this->userGuruLain = User::factory()->create(['role' => 'guru', 'name' => 'Bu Guru Lain']);
    $this->guruLain = Guru::factory()->create(['user_id' => $this->userGuruLain->id]);

    $this->kelas = Kelas::factory()->create(['nama' => 'X RPL 1', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
    $this->mapel = Mapel::factory()->create(['nama' => 'Pemrograman Web']);
});

test('AB-03: jadwal 7 hari terakhir yang belum diabsen dihitung pada pengingat dashboard guru', function () {
    // Tetapkan waktu: Selasa 2026-09-22 jam 12:00:00
    // H-1 adalah Senin 2026-09-21
    Carbon::setTestNow('2026-09-22 12:00:00');

    $jadwalSenin = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->userGuru)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('1 jadwal belum diabsen dalam 7 hari terakhir');
    $response->assertSee('Pemrograman Web');
    $response->assertSee('X RPL 1');
    $response->assertSee(route('guru.absensi.show', ['jadwal' => $jadwalSenin->id, 'tanggal' => '2026-09-21']));
    $response->assertSee(route('guru.koreksi-absensi'));

    Carbon::setTestNow();
});

test('AB-03: jadwal yang sudah diabsen tidak dihitung pada pengingat dashboard guru', function () {
    Carbon::setTestNow('2026-09-22 12:00:00');

    $jadwalSenin = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Sudah diabsen
    SesiAbsensi::factory()->create([
        'jadwal_id' => $jadwalSenin->id,
        'tanggal' => '2026-09-21',
        'diabsen_oleh' => $this->userGuru->id,
    ]);

    $response = $this->actingAs($this->userGuru)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Semua jadwal sudah diabsen.');
    $response->assertDontSee('belum diabsen dalam 7 hari terakhir');

    Carbon::setTestNow();
});

test('AB-03: jadwal hari ini sebelum jam mulai tidak dihitung dan setelah jam mulai dihitung', function () {
    // Jadwal hari ini (Selasa) jam 10:00
    $jadwalSelasa = Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'hari' => 'selasa',
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '11:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Kasus 1: Pukul 08:00 (sebelum jam 10:00) -> belum dianggap menunggak / tidak dihitung
    Carbon::setTestNow('2026-09-22 08:00:00');

    $responsePagi = $this->actingAs($this->userGuru)->get(route('guru.dashboard'));
    $responsePagi->assertStatus(200);
    $responsePagi->assertSee('Semua jadwal sudah diabsen.');

    // Kasus 2: Pukul 10:30 (setelah jam 10:00) -> dihitung belum diabsen
    Carbon::setTestNow('2026-09-22 10:30:00');

    $responseSiang = $this->actingAs($this->userGuru)->get(route('guru.dashboard'));
    $responseSiang->assertStatus(200);
    $responseSiang->assertSee('1 jadwal belum diabsen dalam 7 hari terakhir');
    $responseSiang->assertSee(route('guru.absensi.show', ['jadwal' => $jadwalSelasa->id, 'tanggal' => '2026-09-22']));

    Carbon::setTestNow();
});

test('AB-03: jadwal guru lain yang belum diabsen tidak dihitung', function () {
    Carbon::setTestNow('2026-09-22 12:00:00');

    // Jadwal milik guru lain
    Jadwal::factory()->create([
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'guru_id' => $this->guruLain->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->userGuru)->get(route('guru.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Semua jadwal sudah diabsen.');

    Carbon::setTestNow();
});
