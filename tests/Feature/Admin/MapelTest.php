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

    public function test_admin_can_search_mapel_by_name_or_kode()
    {
        Mapel::factory()->create(['nama' => 'Matematika Terapan', 'kode' => 'MTK']);
        Mapel::factory()->create(['nama' => 'Bahasa Indonesia', 'kode' => 'BIN']);

        $response = $this->actingAs($this->admin)->get(route('admin.mapel.index', ['search' => 'Matematika']));
        $response->assertStatus(200);
        $response->assertSee('Matematika Terapan');
        $response->assertDontSee('Bahasa Indonesia');

        $responseKode = $this->actingAs($this->admin)->get(route('admin.mapel.index', ['search' => 'BIN']));
        $responseKode->assertStatus(200);
        $responseKode->assertSee('Bahasa Indonesia');
        $responseKode->assertDontSee('Matematika Terapan');
    }

    public function test_mapel_index_paginates_10_items_per_page()
    {
        Mapel::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.mapel.index'));
        $response->assertStatus(200);
        $mapels = $response->viewData('mapels');
        $this->assertCount(10, $mapels);
        $this->assertEquals(15, $mapels->total());
    }
}
