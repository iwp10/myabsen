<?php

namespace Tests\Feature;

use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruSiswaMobileTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $guruUser;

    protected Guru $guru;

    protected User $siswaUser;

    protected Siswa $siswa;

    protected Kelas $kelas;

    protected Mapel $mapel;

    protected Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-26 08:00:00'); // Hari Senin

        $jurusan = Jurusan::create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);
        $this->kelas = Kelas::create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'X TKJ 1',
            'tingkat' => 10,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $this->adminUser = User::factory()->create(['role' => 'admin', 'name' => 'Admin PKM']);

        $this->guruUser = User::factory()->create(['role' => 'guru', 'name' => 'Pak Guru Budi']);
        $this->guru = Guru::create(['user_id' => $this->guruUser->id, 'nip' => '198501012010011001']);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'name' => 'Ahmad Siswa']);
        $this->siswa = Siswa::create(['user_id' => $this->siswaUser->id, 'kelas_id' => $this->kelas->id, 'nis' => '1001']);

        $this->mapel = Mapel::create(['nama' => 'Pemrograman Dasar', 'kode' => 'PD01']);

        $this->jadwal = Jadwal::create([
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
            'mapel_id' => $this->mapel->id,
            'hari' => 'senin',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_bottom_nav_renders_for_guru_and_siswa_but_not_admin(): void
    {
        // 1. Guru melihat bottom nav dengan 4 tab
        $resGuru = $this->actingAs($this->guruUser)->get(route('guru.dashboard'));
        $resGuru->assertOk();
        $resGuru->assertSee('aria-label="Navigasi bawah mobile"', false);
        $resGuru->assertSee(route('guru.dashboard'));
        $resGuru->assertSee(route('guru.jadwal'));
        $resGuru->assertSee(route('guru.koreksi-absensi'));
        $resGuru->assertSee(route('guru.riwayat'));

        // 2. Siswa melihat bottom nav dengan 4 tab
        $resSiswa = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));
        $resSiswa->assertOk();
        $resSiswa->assertSee('aria-label="Navigasi bawah mobile"', false);
        $resSiswa->assertSee(route('siswa.dashboard'));
        $resSiswa->assertSee(route('siswa.jadwal'));
        $resSiswa->assertSee(route('siswa.mapel'));
        $resSiswa->assertSee(route('siswa.riwayat'));

        // 3. Admin tidak melihat bottom nav
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $resAdmin->assertOk();
        $resAdmin->assertDontSee('aria-label="Navigasi bawah mobile"', false);
    }

    public function test_bottom_nav_hidden_on_form_absensi(): void
    {
        $response = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', $this->jadwal->id));
        $response->assertOk();

        // Bottom nav disembunyikan agar tombol Simpan sticky leluasa
        $response->assertDontSee('aria-label="Navigasi bawah mobile"', false);
    }

    public function test_form_absensi_renders_segmented_status_kata_penuh_and_sticky_summary(): void
    {
        $response = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', $this->jadwal->id));
        $response->assertOk();

        // 1. Memuat segmented status 4 pilihan radio per siswa dengan kata penuh
        $response->assertSee('siswa['.$this->siswa->id.'][status]', false);
        $response->assertSee('value="hadir"', false);
        $response->assertSee('value="izin"', false);
        $response->assertSee('value="sakit"', false);
        $response->assertSee('value="alpa"', false);

        // Memuat label teks kata penuh
        $response->assertSee('Hadir');
        $response->assertSee('Izin');
        $response->assertSee('Sakit');
        $response->assertSee('Alpa');

        // Memuat grid responsif 2x2 di bawah 360px dan 1x4 di 360px ke atas
        $response->assertSee('grid grid-cols-2 min-[360px]:grid-cols-4 gap-2', false);

        // 2. Memuat input keterangan
        $response->assertSee('siswa['.$this->siswa->id.'][keterangan]', false);

        // 3. Memuat tombol "Semua Hadir"
        $response->assertSee('Semua Hadir');

        // 4. Memuat tombol simpan sticky dan tombol Batal
        $response->assertSee('sticky bottom-0', false);
        $response->assertSee('Simpan Absensi');
        $response->assertSee('Batal');
    }

    public function test_filter_panel_renders_on_guru_and_siswa_jadwal(): void
    {
        // 1. Guru Jadwal
        $resGuru = $this->actingAs($this->guruUser)->get(route('guru.jadwal'));
        $resGuru->assertOk();
        $resGuru->assertSee('Filter & Penyaringan', false);
        $resGuru->assertSee('name="kelas_id"', false);
        $resGuru->assertSee('name="mapel_id"', false);
        $resGuru->assertSee('name="hari"', false);

        // 2. Siswa Jadwal
        $resSiswa = $this->actingAs($this->siswaUser)->get(route('siswa.jadwal'));
        $resSiswa->assertOk();
        $resSiswa->assertSee('Filter & Penyaringan', false);
        $resSiswa->assertSee('name="hari"', false);
        $resSiswa->assertSee('name="mapel_id"', false);
    }

    public function test_filter_panel_displays_active_chips_with_clear_urls(): void
    {
        $response = $this->actingAs($this->guruUser)->get(route('guru.jadwal', [
            'hari' => 'senin',
            'kelas_id' => $this->kelas->id,
        ]));
        $response->assertOk();

        // Chip filter aktif tampil
        $response->assertSee('Filter aktif:');
        $response->assertSee('Hari: Senin');
        $response->assertSee('Kelas: '.$this->kelas->nama);
    }

    public function test_tombol_kembali_dan_menu_memuat_svg_bergambar_di_halaman_turunan(): void
    {
        // 1. Halaman form absensi guru memuat back-button dan tombol menu dengan SVG berisi path
        $resAbsensi = $this->actingAs($this->guruUser)->get(route('guru.absensi.show', $this->jadwal->id));
        $resAbsensi->assertOk();
        $resAbsensi->assertSee('aria-label="Kembali"', false);
        $resAbsensi->assertSee(route('guru.dashboard'));

        // Tombol menu topbar memuat ikon menu dengan path
        $resAbsensi->assertSee('aria-label="Buka Menu"', false);
        $resAbsensi->assertSee('<path d="M4 6l16 0"', false);

        // Tombol kembali memuat ikon arrow-left dengan path
        $resAbsensi->assertSee('<path d="M5 12l14 0"', false);

        // 2. Halaman riwayat detail guru memuat back-button menuju guru.riwayat
        $resRiwayat = $this->actingAs($this->guruUser)->get(route('guru.riwayat.detail', [
            'kelas' => $this->kelas->id,
            'mapel' => $this->mapel->id,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]));
        $resRiwayat->assertOk();
        $resRiwayat->assertSee('aria-label="Kembali"', false);
        $resRiwayat->assertSee('<path d="M5 12l14 0"', false); // path arrow-left

        // 3. Halaman riwayat mapel siswa memuat back-button menuju siswa.riwayat
        // Buat dummy sesi agar riwayat mapel ada
        $sesi = SesiAbsensi::create([
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-10-26',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'diabsen_oleh' => $this->guruUser->id,
        ]);
        DetailAbsensi::create([
            'sesi_absensi_id' => $sesi->id,
            'siswa_id' => $this->siswa->id,
            'status' => 'hadir',
        ]);

        $resSiswaMapel = $this->actingAs($this->siswaUser)->get(route('siswa.riwayat.mapel', [
            'mapel' => $this->mapel->id,
            'periode' => '2026/2027-Ganjil',
        ]));
        $resSiswaMapel->assertOk();
        $resSiswaMapel->assertSee('aria-label="Kembali"', false);
        $resSiswaMapel->assertSee('<path d="M5 12l14 0"', false); // path arrow-left
    }

    public function test_koreksi_absensi_memuat_teks_tombol_tanpa_entitas_html(): void
    {
        $response = $this->actingAs($this->guruUser)->get(route('guru.koreksi-absensi'));
        $response->assertOk();

        // Tidak boleh ada entitas HTML &rarr; atau &amp;rarr;
        $response->assertDontSee('&rarr;', false);
        $response->assertDontSee('&amp;rarr;', false);
        $response->assertDontSee('&RARR;', false);

        // Memuat teks bersih tombol aksi
        $response->assertSee('Isi Susulan');
    }

    public function test_render_halaman_utama_bebas_escape_ganda_entitas(): void
    {
        // 1. Pages Guru
        $guruUrls = [
            route('guru.dashboard'),
            route('guru.jadwal'),
            route('guru.koreksi-absensi'),
            route('guru.riwayat'),
            route('guru.riwayat.detail', [
                'kelas' => $this->kelas->id,
                'mapel' => $this->mapel->id,
                'tahun_ajaran' => '2026/2027',
                'semester' => 'Ganjil',
            ]),
            route('guru.absensi.show', $this->jadwal->id),
        ];

        foreach ($guruUrls as $url) {
            $res = $this->actingAs($this->guruUser)->get($url);
            $res->assertOk();
            $content = $res->getContent();
            $this->assertDoesNotMatchRegularExpression(
                '/&amp;(?:[a-zA-Z]+|#\d+);/',
                $content,
                "Ditemukan tanda escape ganda entitas HTML pada halaman guru: {$url}"
            );
        }

        // 2. Pages Siswa
        $siswaUrls = [
            route('siswa.dashboard'),
            route('siswa.jadwal'),
            route('siswa.mapel'),
            route('siswa.riwayat'),
        ];

        foreach ($siswaUrls as $url) {
            $res = $this->actingAs($this->siswaUser)->get($url);
            $res->assertOk();
            $content = $res->getContent();
            $this->assertDoesNotMatchRegularExpression(
                '/&amp;(?:[a-zA-Z]+|#\d+);/',
                $content,
                "Ditemukan tanda escape ganda entitas HTML pada halaman siswa: {$url}"
            );
        }

        // 3. Pages Admin
        $adminUrls = [
            route('admin.dashboard'),
            route('admin.siswa.index'),
            route('admin.guru.index'),
            route('admin.kelas.index'),
            route('admin.mapel.index'),
        ];

        foreach ($adminUrls as $url) {
            $res = $this->actingAs($this->adminUser)->get($url);
            $res->assertOk();
            $content = $res->getContent();
            $this->assertDoesNotMatchRegularExpression(
                '/&amp;(?:[a-zA-Z]+|#\d+);/',
                $content,
                "Ditemukan tanda escape ganda entitas HTML pada halaman admin: {$url}"
            );
        }
    }
}
