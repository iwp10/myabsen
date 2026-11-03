<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterResponsifSemuaRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $userAdmin;

    protected User $userGuru;

    protected User $userSiswa;

    protected Guru $guru;

    protected Siswa $siswa;

    protected Kelas $kelas;

    protected Mapel $mapel;

    protected Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userAdmin = User::factory()->create(['role' => 'admin', 'username' => 'admin_test']);

        $this->userGuru = User::factory()->create(['role' => 'guru', 'username' => 'guru_test']);
        $this->guru = Guru::factory()->create(['user_id' => $this->userGuru->id, 'nip' => '198501012010011001']);

        $this->userSiswa = User::factory()->create(['role' => 'siswa', 'username' => 'siswa_test']);
        $this->kelas = Kelas::factory()->create([
            'nama' => 'X RPL 1',
            'tingkat' => 'X',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);
        $this->siswa = Siswa::factory()->create([
            'user_id' => $this->userSiswa->id,
            'kelas_id' => $this->kelas->id,
            'nis' => '12345678',
        ]);

        $this->mapel = Mapel::factory()->create(['nama' => 'Pemrograman Web', 'kode' => 'PW']);

        $this->jadwal = Jadwal::factory()->create([
            'guru_id' => $this->guru->id,
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'hari' => 'senin',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:00',
        ]);
    }

    /**
     * Memastikan layout app memasang kelas "js" via script inline di <head>
     * dan CSS tidak menyembunyikan .filter-panel-content jika tanpa kelas "js".
     */
    public function test_layout_memasang_kelas_js_dan_progressive_enhancement_css(): void
    {
        $response = $this->actingAs($this->userGuru)->get(route('guru.jadwal'));
        $response->assertOk();

        // 1. Script inline menambahkan class js pada root element
        $content = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/document\.documentElement\.classList\.add\([\'"]js[\'"]\)/',
            $content,
            'Layout harus memasang kelas "js" via script inline untuk progressive enhancement'
        );

        // 2. Periksa app.css bahwa penyembunyian panel filter saat x-cloak hanya berlaku di bawah html.js dan di bawah md (max-width: 767px)
        $cssPath = resource_path('css/app.css');
        $this->assertFileExists($cssPath);
        $css = file_get_contents($cssPath);

        $this->assertStringContainsString('html.js [x-cloak]:not(.filter-panel-content)', $css);
        $this->assertStringContainsString('html.js .filter-panel-content[x-cloak]', $css);
        $this->assertStringContainsString('max-width: 767px', $css);

        // Pastikan tidak ada media query orientation di CSS proyek
        $this->assertDoesNotMatchRegularExpression(
            '/@media[^{]*\(\s*orientation\s*:/i',
            $css,
            'Tidak boleh ada media query orientation di CSS proyek'
        );
    }

    /**
     * Memastikan Riwayat Siswa Tab 1 (Per Mata Pelajaran) memiliki tombol filter,
     * field periode dengan nama yang sama, dan chip filter aktif saat ada parameter non-default.
     */
    public function test_riwayat_siswa_tab_per_mapel_filter_dan_chip(): void
    {
        // 1. Tanpa parameter periode kustom
        $res = $this->actingAs($this->userSiswa)->get(route('siswa.riwayat', ['tab' => 'per_mapel']));
        $res->assertOk();
        $html = $res->getContent();

        $this->assertStringContainsString('Filter & Penyaringan', $html);
        $this->assertStringContainsString('name="periode"', $html);
        $this->assertStringNotContainsString('Filter aktif:', $html);

        // 2. Dengan parameter periode kustom
        $resFilter = $this->actingAs($this->userSiswa)->get(route('siswa.riwayat', [
            'tab' => 'per_mapel',
            'periode' => '2025/2026|Genap',
        ]));
        $resFilter->assertOk();
        $htmlFilter = $resFilter->getContent();

        $this->assertStringContainsString('Filter aktif:', $htmlFilter);
        $this->assertStringContainsString('Periode:', $htmlFilter);
        $this->assertStringContainsString('min-h-[44px]', $htmlFilter);
    }

    /**
     * Memastikan Riwayat Siswa Tab 2 (Semua Riwayat) memiliki kontrol filter lengkap,
     * field yang sama (periode, bulan, tanggal, mapel_id, status), dan chip filter aktif.
     */
    public function test_riwayat_siswa_tab_semua_filter_dan_chip(): void
    {
        // 1. Tanpa parameter filter
        $res = $this->actingAs($this->userSiswa)->get(route('siswa.riwayat', ['tab' => 'semua']));
        $res->assertOk();
        $html = $res->getContent();

        $this->assertStringContainsString('Filter & Penyaringan', $html);
        $this->assertStringContainsString('name="periode"', $html);
        $this->assertStringContainsString('name="bulan"', $html);
        $this->assertStringContainsString('name="tanggal"', $html);
        $this->assertStringContainsString('name="mapel_id"', $html);
        $this->assertStringContainsString('name="status"', $html);

        // 2. Dengan parameter filter
        $resFilter = $this->actingAs($this->userSiswa)->get(route('siswa.riwayat', [
            'tab' => 'semua',
            'status' => 'hadir',
            'mapel_id' => $this->mapel->id,
        ]));
        $resFilter->assertOk();
        $htmlFilter = $resFilter->getContent();

        $this->assertStringContainsString('Filter aktif:', $htmlFilter);
        $this->assertStringContainsString('Status: Hadir', $htmlFilter);
        $this->assertStringContainsString('Mapel: Pemrograman Web', $htmlFilter);
        $this->assertStringContainsString('Reset Semua', $htmlFilter);
    }

    /**
     * Memastikan Riwayat Guru memiliki tombol filter, slot pencarian teks di luar panel lipat,
     * dan chip filter aktif.
     */
    public function test_riwayat_guru_filter_pencarian_dan_chip(): void
    {
        // 1. Tanpa parameter
        $res = $this->actingAs($this->userGuru)->get(route('guru.riwayat'));
        $res->assertOk();
        $html = $res->getContent();

        $this->assertStringContainsString('Filter & Penyaringan', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="periode"', $html);
        $this->assertStringNotContainsString('Filter aktif:', $html);

        // 2. Dengan parameter q dan periode kustom
        $resFilter = $this->actingAs($this->userGuru)->get(route('guru.riwayat', [
            'q' => 'RPL',
        ]));
        $resFilter->assertOk();
        $htmlFilter = $resFilter->getContent();

        $this->assertStringContainsString('Filter aktif:', $htmlFilter);
        $this->assertStringContainsString('Pencarian: RPL', $htmlFilter);
        $this->assertStringContainsString('value="RPL"', $htmlFilter);
    }

    /**
     * Memastikan Riwayat Detail Guru memiliki tombol filter, slot pencarian siswa di luar panel,
     * dan chip filter aktif.
     */
    public function test_riwayat_detail_guru_filter_pencarian_dan_chip(): void
    {
        $res = $this->actingAs($this->userGuru)->get(route('guru.riwayat.detail', [
            'kelas' => $this->kelas->id,
            'mapel' => $this->mapel->id,
            'q' => 'budi',
        ]));
        $res->assertOk();
        $html = $res->getContent();

        $this->assertStringContainsString('Filter & Penyaringan', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('Filter aktif:', $html);
        $this->assertStringContainsString('Pencarian: budi', $html);
    }

    /**
     * Memastikan Jadwal Mengajar Guru memiliki tombol filter, field hari, kelas_id, mapel_id,
     * dan chip filter aktif.
     */
    public function test_jadwal_guru_filter_dan_chip(): void
    {
        // 1. Tanpa filter
        $res = $this->actingAs($this->userGuru)->get(route('guru.jadwal'));
        $res->assertOk();
        $html = $res->getContent();

        $this->assertStringContainsString('Filter & Penyaringan', $html);
        $this->assertStringContainsString('name="hari"', $html);
        $this->assertStringContainsString('name="kelas_id"', $html);
        $this->assertStringContainsString('name="mapel_id"', $html);
        $this->assertStringNotContainsString('Filter aktif:', $html);

        // 2. Dengan filter hari
        $resFilter = $this->actingAs($this->userGuru)->get(route('guru.jadwal', ['hari' => 'senin']));
        $resFilter->assertOk();
        $htmlFilter = $resFilter->getContent();

        $this->assertStringContainsString('Filter aktif:', $htmlFilter);
        $this->assertStringContainsString('Hari: Senin', $htmlFilter);
    }

    /**
     * Memastikan Jadwal Siswa memiliki tombol filter, field hari dan mapel_id,
     * serta chip filter aktif.
     */
    public function test_jadwal_siswa_filter_dan_chip(): void
    {
        // 1. Tanpa filter
        $res = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal'));
        $res->assertOk();
        $html = $res->getContent();

        $this->assertStringContainsString('Filter & Penyaringan', $html);
        $this->assertStringContainsString('name="hari"', $html);
        $this->assertStringContainsString('name="mapel_id"', $html);
        $this->assertStringNotContainsString('Filter aktif:', $html);

        // 2. Dengan filter hari
        $resFilter = $this->actingAs($this->userSiswa)->get(route('siswa.jadwal', ['hari' => 'senin']));
        $resFilter->assertOk();
        $htmlFilter = $resFilter->getContent();

        $this->assertStringContainsString('Filter aktif:', $htmlFilter);
        $this->assertStringContainsString('Hari: Senin', $htmlFilter);
    }

    /**
     * Memastikan tombol Filter & Penyaringan di seluruh halaman tidak memiliki token kelas tunggal 'hidden'
     * dan tidak ber-x-cloak, sehingga selalu terjangkau di mode portrait mobile.
     */
    public function test_tombol_filter_tidak_pernah_hidden_atau_x_cloak(): void
    {
        $urls = [
            route('guru.jadwal'),
            route('guru.riwayat'),
            route('guru.riwayat.detail', ['kelas' => $this->kelas->id, 'mapel' => $this->mapel->id]),
            route('siswa.jadwal'),
            route('siswa.riwayat', ['tab' => 'per_mapel']),
            route('siswa.riwayat', ['tab' => 'semua']),
        ];

        foreach ($urls as $url) {
            $user = str_contains($url, 'siswa') ? $this->userSiswa : $this->userGuru;
            $res = $this->actingAs($user)->get($url);
            $res->assertOk();
            $html = $res->getContent();

            // Ekstrak tag button pembuka Filter & Penyaringan
            preg_match_all('/<button\b[^>]*>(?:(?!<\/button>).)*?Filter & Penyaringan.*?<\/button>/is', $html, $matches);
            $this->assertNotEmpty($matches[0], "Halaman {$url} harus memuat tombol Filter & Penyaringan");

            foreach ($matches[0] as $buttonMarkup) {
                // Tombol tidak boleh memiliki atribut x-cloak
                $this->assertDoesNotMatchRegularExpression(
                    '/\bx-cloak\b/i',
                    $buttonMarkup,
                    "Tombol Filter pada {$url} tidak boleh memiliki atribut x-cloak"
                );

                // Ekstrak class attribute
                if (preg_match('/class=[\'"]([^\'"]*)[\'"]/i', $buttonMarkup, $classMatch)) {
                    $tokens = preg_split('/\s+/', trim($classMatch[1]));
                    $this->assertNotContains(
                        'hidden',
                        $tokens,
                        "Tombol Filter pada {$url} tidak boleh memuat token kelas tunggal 'hidden'"
                    );
                }
            }
        }
    }
}
