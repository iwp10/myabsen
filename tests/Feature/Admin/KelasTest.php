<?php

namespace Tests\Feature\Admin;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasTest extends TestCase
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

    public function test_admin_can_view_kelas_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kelas.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.kelas.index');
    }

    public function test_non_admin_cannot_view_kelas_index()
    {
        $response = $this->actingAs($this->guru)->get(route('admin.kelas.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->siswa)->get(route('admin.kelas.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_kelas()
    {
        $jurusan = Jurusan::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
            'jurusan_id' => $jurusan->id,
            'nama' => '10 RPL 1',
            'tingkat' => 10,
            'tahun_ajaran' => '2023/2024',
        ]);

        $response->assertRedirect(route('admin.kelas.index'));
        $this->assertDatabaseHas('kelas', [
            'jurusan_id' => $jurusan->id,
            'nama' => '10 RPL 1',
            'tingkat' => 10,
            'tahun_ajaran' => '2023/2024',
        ]);
    }

    public function test_admin_can_update_kelas()
    {
        $kelas = Kelas::factory()->create();
        $jurusanBaru = Jurusan::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.kelas.update', $kelas), [
            'jurusan_id' => $jurusanBaru->id,
            'nama' => 'Nama Baru',
            'tingkat' => 11,
            'tahun_ajaran' => '2024/2025',
        ]);

        $response->assertRedirect(route('admin.kelas.index'));
        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'jurusan_id' => $jurusanBaru->id,
            'nama' => 'Nama Baru',
            'tingkat' => 11,
            'tahun_ajaran' => '2024/2025',
        ]);
    }

    public function test_admin_can_delete_kelas()
    {
        $kelas = Kelas::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $kelas));

        $response->assertRedirect(route('admin.kelas.index'));
        $this->assertSoftDeleted('kelas', [
            'id' => $kelas->id,
        ]);
    }

    public function test_admin_can_search_kelas_by_name_tingkat_or_jurusan()
    {
        $jurusanRpl = Jurusan::factory()->create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);
        $jurusanTkj = Jurusan::factory()->create(['nama' => 'Teknik Komputer Jaringan', 'kode' => 'TKJ']);

        Kelas::factory()->create(['jurusan_id' => $jurusanRpl->id, 'nama' => 'X RPL 1', 'tingkat' => 10]);
        Kelas::factory()->create(['jurusan_id' => $jurusanTkj->id, 'nama' => 'XI TKJ 2', 'tingkat' => 11]);

        $response = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['search' => 'X RPL']));
        $response->assertStatus(200);
        $response->assertSee('X RPL 1');
        $response->assertDontSee('XI TKJ 2');

        $responseJurusan = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['search' => 'TKJ']));
        $responseJurusan->assertStatus(200);
        $responseJurusan->assertSee('XI TKJ 2');
        $responseJurusan->assertDontSee('X RPL 1');
    }

    public function test_kelas_index_paginates_10_items_per_page()
    {
        $jurusan = Jurusan::factory()->create();
        Kelas::factory()->count(15)->create(['jurusan_id' => $jurusan->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.kelas.index'));
        $response->assertStatus(200);
        $kelas = $response->viewData('kelas');
        $this->assertCount(10, $kelas);
        $this->assertEquals(15, $kelas->total());
    }
}
