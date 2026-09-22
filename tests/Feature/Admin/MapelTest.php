<?php

namespace Tests\Feature\Admin;

use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $guru;
    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->guru = User::factory()->create(['role' => 'guru']);
        $this->siswa = User::factory()->create(['role' => 'siswa']);
    }

    public function test_admin_can_view_mapel_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.mapel.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.mapel.index');
    }

    public function test_non_admin_cannot_view_mapel_index()
    {
        $response = $this->actingAs($this->guru)->get(route('admin.mapel.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->siswa)->get(route('admin.mapel.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_mapel()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.mapel.store'), [
            'nama' => 'Matematika',
            'kode' => 'MTK',
        ]);

        $response->assertRedirect(route('admin.mapel.index'));
        $this->assertDatabaseHas('mapel', [
            'nama' => 'Matematika',
            'kode' => 'MTK',
        ]);
    }

    public function test_admin_can_update_mapel()
    {
        $mapel = Mapel::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.mapel.update', $mapel), [
            'nama' => 'Nama Baru',
            'kode' => 'NB',
        ]);

        $response->assertRedirect(route('admin.mapel.index'));
        $this->assertDatabaseHas('mapel', [
            'id' => $mapel->id,
            'nama' => 'Nama Baru',
            'kode' => 'NB',
        ]);
    }

    public function test_admin_can_delete_mapel()
    {
        $mapel = Mapel::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.mapel.destroy', $mapel));

        $response->assertRedirect(route('admin.mapel.index'));
        $this->assertSoftDeleted('mapel', [
            'id' => $mapel->id,
        ]);
    }
}
