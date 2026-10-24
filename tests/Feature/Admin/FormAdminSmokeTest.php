<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormAdminSmokeTest extends TestCase
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

    /**
     * @return array<string, string>
     */
    private function getRoutes(): array
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
        $mapel = Mapel::factory()->create();
        $guruModel = Guru::factory()->create();
        $siswaModel = Siswa::factory()->create(['kelas_id' => $kelas->id]);

        return [
            'admin.jurusan.create' => route('admin.jurusan.create'),
            'admin.jurusan.edit' => route('admin.jurusan.edit', $jurusan),
            'admin.kelas.create' => route('admin.kelas.create'),
            'admin.kelas.edit' => route('admin.kelas.edit', $kelas),
            'admin.mapel.create' => route('admin.mapel.create'),
            'admin.mapel.edit' => route('admin.mapel.edit', $mapel),
            'admin.guru.create' => route('admin.guru.create'),
            'admin.guru.edit' => route('admin.guru.edit', $guruModel),
            'admin.siswa.create' => route('admin.siswa.create'),
            'admin.siswa.edit' => route('admin.siswa.edit', $siswaModel),
        ];
    }

    public function test_admin_can_access_all_ten_admin_forms(): void
    {
        $routes = $this->getRoutes();
        $this->assertCount(10, $routes);

        foreach ($routes as $routeName => $url) {
            $response = $this->actingAs($this->admin)->get($url);
            $response->assertOk();
        }
    }

    public function test_guru_forbidden_from_all_ten_admin_forms(): void
    {
        $routes = $this->getRoutes();
        $this->assertCount(10, $routes);

        foreach ($routes as $routeName => $url) {
            $response = $this->actingAs($this->guru)->get($url);
            $response->assertForbidden();
        }
    }

    public function test_siswa_forbidden_from_all_ten_admin_forms(): void
    {
        $routes = $this->getRoutes();
        $this->assertCount(10, $routes);

        foreach ($routes as $routeName => $url) {
            $response = $this->actingAs($this->siswa)->get($url);
            $response->assertForbidden();
        }
    }

    public function test_guest_redirected_to_login_from_all_ten_admin_forms(): void
    {
        $routes = $this->getRoutes();
        $this->assertCount(10, $routes);

        foreach ($routes as $routeName => $url) {
            $response = $this->get($url);
            $response->assertRedirect(route('login'));
        }
    }
}
