<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HalamanGalatDanProfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_403_page_displays_indonesian_content_and_dashboard_button_for_guru(): void
    {
        $guruUser = User::factory()->create(['role' => 'guru']);
        Guru::factory()->create(['user_id' => $guruUser->id]);

        $response = $this->actingAs($guruUser)->get(route('admin.dashboard'));

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
        $response->assertSee('Anda tidak memiliki izin untuk membuka halaman ini. Jika Anda merasa ini keliru, hubungi admin sekolah.');
        $response->assertSee('Kembali ke Dashboard');
        $response->assertSee(route('guru.dashboard'));

        // Tidak memuat teks bawaan bahasa Inggris
        $response->assertDontSee('THIS ACTION IS UNAUTHORIZED', false);
        $response->assertDontSee('Forbidden', false);
    }

    public function test_403_page_displays_login_button_for_guest(): void
    {
        // Daftarkan route pengujian sementara yang melempar 403 untuk tamu
        Route::get('/_smoke_test_403', fn () => abort(403));

        $response = $this->get('/_smoke_test_403');

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
        $response->assertSee('Ke Halaman Login');
        $response->assertSee(route('login'));
        $response->assertDontSee('Kembali ke Dashboard');
        $response->assertDontSee('THIS ACTION IS UNAUTHORIZED', false);
        $response->assertDontSee('Forbidden', false);
    }

    public function test_503_view_renders_standalone_indonesian_content_without_auth_or_csrf(): void
    {
        $view = view('errors.503')->render();

        $this->assertStringContainsString('503', $view);
        $this->assertStringContainsString('Sistem Sedang Dalam Pemeliharaan', $view);
        $this->assertStringContainsString('MyAbsen sedang diperbarui. Silakan coba lagi beberapa saat lagi.', $view);
        $this->assertStringContainsString('Muat Ulang', $view);
        $this->assertStringContainsString('Ke Halaman Beranda', $view);

        // Tidak bergantung pada Auth, session, atau token CSRF
        $this->assertStringNotContainsString('_token', $view);
        $this->assertStringNotContainsString('@csrf', $view);
        $this->assertStringNotContainsString('auth()->user()', $view);
        $this->assertStringNotContainsString('Auth::', $view);
    }

    public function test_profile_page_accessible_for_admin_guru_siswa_and_uses_design_tokens(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
        $guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
        Guru::factory()->create(['user_id' => $guru->id]);
        $siswa = User::factory()->create(['role' => 'siswa', 'must_change_password' => false]);
        Siswa::factory()->create(['user_id' => $siswa->id]);

        $editBlade = file_get_contents(resource_path('views/profile/edit.blade.php'));
        $formBlade = file_get_contents(resource_path('views/profile/partials/update-password-form.blade.php'));

        // Form ganti password dan view profil bebas dari gray-800 lama
        $this->assertStringNotContainsString('gray-800', $editBlade);
        $this->assertStringNotContainsString('gray-800', $formBlade);
        $this->assertStringNotContainsString('text-gray-', $editBlade);
        $this->assertStringNotContainsString('text-gray-', $formBlade);

        foreach ([$admin, $guru, $siswa] as $user) {
            $response = $this->actingAs($user)->get(route('profile.edit'));
            $response->assertOk();
            $response->assertSee('Profil');
            $response->assertSee('Perbarui Password');

            // Token border-2, min-h-[44px], dan dark:
            $response->assertSee('border-2');
            $response->assertSee('min-h-[44px]');
            $response->assertSee('dark:bg-slate-800');
            $response->assertSee('border-slate-300');
            $response->assertSee('dark:border-slate-600');
        }
    }
}
