<?php

namespace Tests\Feature\Admin;

use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurusanTest extends TestCase
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

    public function test_admin_can_view_jurusan_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.jurusan.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.jurusan.index');
    }

    public function test_non_admin_cannot_view_jurusan_index()
    {
        $response = $this->actingAs($this->guru)->get(route('admin.jurusan.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->siswa)->get(route('admin.jurusan.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_jurusan()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.jurusan.store'), [
            'nama' => 'Rekayasa Perangkat Lunak',
            'kode' => 'RPL',
        ]);

        $response->assertRedirect(route('admin.jurusan.index'));
        $this->assertDatabaseHas('jurusan', [
            'nama' => 'Rekayasa Perangkat Lunak',
            'kode' => 'RPL',
        ]);
    }

    public function test_admin_can_update_jurusan()
    {
        $jurusan = Jurusan::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.jurusan.update', $jurusan), [
            'nama' => 'Nama Baru',
            'kode' => 'NB',
        ]);

        $response->assertRedirect(route('admin.jurusan.index'));
        $this->assertDatabaseHas('jurusan', [
            'id' => $jurusan->id,
            'nama' => 'Nama Baru',
            'kode' => 'NB',
        ]);
    }

    public function test_admin_can_delete_jurusan()
    {
        $jurusan = Jurusan::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.jurusan.destroy', $jurusan));

        $response->assertRedirect(route('admin.jurusan.index'));
        $this->assertDatabaseMissing('jurusan', [
            'id' => $jurusan->id,
        ]);
    }

    public function test_admin_can_search_jurusan_by_name_or_kode()
    {
        Jurusan::factory()->create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);
        Jurusan::factory()->create(['nama' => 'Teknik Komputer Jaringan', 'kode' => 'TKJ']);

        $response = $this->actingAs($this->admin)->get(route('admin.jurusan.index', ['search' => 'Perangkat']));
        $response->assertStatus(200);
        $response->assertSee('Rekayasa Perangkat Lunak');
        $response->assertDontSee('Teknik Komputer Jaringan');

        $responseKode = $this->actingAs($this->admin)->get(route('admin.jurusan.index', ['search' => 'TKJ']));
        $responseKode->assertStatus(200);
        $responseKode->assertSee('Teknik Komputer Jaringan');
        $responseKode->assertDontSee('Rekayasa Perangkat Lunak');
    }

    public function test_jurusan_index_paginates_10_items_per_page()
    {
        Jurusan::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.jurusan.index'));
        $response->assertStatus(200);
        $jurusans = $response->viewData('jurusans');
        $this->assertCount(10, $jurusans);
        $this->assertEquals(15, $jurusans->total());
    }
}
