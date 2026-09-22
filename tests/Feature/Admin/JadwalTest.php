<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_jadwal_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_jadwal()
    {
        $kelas = Kelas::factory()->create();
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:00',
            'tahun_ajaran' => '2026/2027',
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('jadwal', [
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
        ]);
    }

    public function test_cannot_create_jadwal_with_overlap_for_guru()
    {
        $kelas1 = Kelas::factory()->create();
        $kelas2 = Kelas::factory()->create();
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();

        Jadwal::factory()->create([
            'kelas_id' => $kelas1->id,
            'guru_id' => $guru->id,
            'hari' => 'selasa',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
            'kelas_id' => $kelas2->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'selasa',
            'jam_mulai' => '09:00', // overlap
            'jam_selesai' => '11:00',
            'tahun_ajaran' => '2026/2027',
        ]);

        $response->assertSessionHasErrors(['guru_id']);
    }

    public function test_cannot_create_jadwal_with_overlap_for_kelas()
    {
        $kelas = Kelas::factory()->create();
        $mapel = Mapel::factory()->create();
        $guru1 = Guru::factory()->create();
        $guru2 = Guru::factory()->create();

        Jadwal::factory()->create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru1->id,
            'hari' => 'rabu',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.jadwal.store'), [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru2->id,
            'hari' => 'rabu',
            'jam_mulai' => '09:00', // overlap
            'jam_selesai' => '10:30',
            'tahun_ajaran' => '2026/2027',
        ]);

        $response->assertSessionHasErrors(['kelas_id']);
    }

    public function test_admin_can_update_jadwal()
    {
        $jadwal = Jadwal::factory()->create([
            'hari' => 'kamis',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00'
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.jadwal.update', $jadwal), [
            'kelas_id' => $jadwal->kelas_id,
            'mapel_id' => $jadwal->mapel_id,
            'guru_id' => $jadwal->guru_id,
            'hari' => 'jumat',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'tahun_ajaran' => '2026/2027',
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('jadwal', [
            'id' => $jadwal->id,
            'hari' => 'jumat',
            'jam_mulai' => '08:00',
        ]);
    }
}
