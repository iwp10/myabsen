<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Support\Ui;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMasterMobileSmokeTest extends TestCase
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

    public function test_daftar_siswa_merender_tabel_kartu_filter_dan_paginasi_query_string(): void
    {
        $jurusan = Jurusan::factory()->create(['kode' => 'RPL']);
        $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'tingkat' => 10, 'nama' => 'RPL 1']);

        // Buat 25 siswa agar memicu paginasi (default 20 item per halaman)
        $siswas = Siswa::factory()->count(25)->create(['kelas_id' => $kelas->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index', [
            'kelas_id' => $kelas->id,
            'page' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('responsive-stack-table');
        $response->assertSee('data-label="Nama Siswa"', false);
        $response->assertSee('data-label="NIS / Username"', false);
        $response->assertSee('data-label="Kelas"', false);
        $response->assertSee('data-label="Aksi"', false);
        $response->assertSee('name="search"', false);
        $response->assertSee('name="kelas_id"', false);
        $response->assertSee('name="urut"', false);
        // Memastikan parameter filter terbawa di URL link paginasi (page=2)
        $response->assertSee('kelas_id='.$kelas->id);
    }

    public function test_kelola_siswa_jumlah_checkbox_sama_dengan_baris_data_tidak_dobel(): void
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
        $siswas = Siswa::factory()->count(3)->create(['kelas_id' => $kelas->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
        $response->assertOk();

        $content = $response->getContent();

        // Checkbox baris tabel memakai type="checkbox" dan name="ids[]"
        preg_match_all('/<input[^>]*type="checkbox"[^>]*name="ids\[\]"/i', $content, $matches);
        $this->assertCount(3, $matches[0], 'Jumlah checkbox baris harus sama persis dengan jumlah data siswa di halaman (tidak ada markup ganda untuk HP/desktop).');

        // Pastikan masing-masing id siswa ada di nilai value checkbox
        foreach ($siswas as $s) {
            $this->assertStringContainsString('value="'.$s->id.'"', $content);
        }
    }

    public function test_daftar_guru_merender_tabel_kartu_dan_filter(): void
    {
        $guruModel = Guru::factory()->create(['nip' => '1987654321']);

        $response = $this->actingAs($this->admin)->get(route('admin.guru.index', ['search' => '1987654321']));
        $response->assertOk();
        $response->assertSee('responsive-stack-table');
        $response->assertSee('data-label="Nama Guru"', false);
        $response->assertSee('data-label="NIP / Username"', false);
        $response->assertSee('data-label="Aksi"', false);
        $response->assertSee('name="search"', false);
    }

    public function test_daftar_kelas_merender_tabel_kartu_dan_filter(): void
    {
        $jurusan = Jurusan::factory()->create(['nama' => 'Teknik Komputer']);
        $kelas = Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'TKJ 1',
            'tingkat' => 11,
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['tingkat' => 11]));
        $response->assertOk();
        $response->assertSee('responsive-stack-table');
        $response->assertSee('data-label="Nama Kelas"', false);
        $response->assertSee('data-label="Tingkat"', false);
        $response->assertSee('data-label="Jurusan"', false);
        $response->assertSee('data-label="Semester"', false);
        $response->assertSee('data-label="Aksi"', false);
        $response->assertSee('name="periode"', false);
        $response->assertSee('name="tingkat"', false);
        $response->assertSee('name="search"', false);
    }

    public function test_daftar_mapel_merender_tabel_kartu_dan_filter(): void
    {
        $mapel = Mapel::factory()->create(['nama' => 'Pemrograman Web', 'kode' => 'PW-01']);

        $response = $this->actingAs($this->admin)->get(route('admin.mapel.index', ['search' => 'PW-01']));
        $response->assertOk();
        $response->assertSee('responsive-stack-table');
        $response->assertSee('data-label="Nama Mata Pelajaran"', false);
        $response->assertSee('data-label="Kode"', false);
        $response->assertSee('data-label="Aksi"', false);
        $response->assertSee('name="search"', false);
    }

    public function test_daftar_jurusan_merender_tabel_kartu_dan_filter(): void
    {
        $jurusan = Jurusan::factory()->create(['nama' => 'Desain Komunikasi Visual', 'kode' => 'DKV']);

        $response = $this->actingAs($this->admin)->get(route('admin.jurusan.index', ['search' => 'DKV']));
        $response->assertOk();
        $response->assertSee('responsive-stack-table');
        $response->assertSee('data-label="Nama Jurusan"', false);
        $response->assertSee('data-label="Kode"', false);
        $response->assertSee('data-label="Aksi"', false);
        $response->assertSee('name="search"', false);
    }

    public function test_daftar_jadwal_merender_tabel_kartu_dan_memakai_ui_badge_helper(): void
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();

        $jadwal = Jadwal::factory()->create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'kamis',
            'semester' => 'Ganjil',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.jadwal.index'));
        $response->assertOk();
        $response->assertSee('responsive-stack-table');
        $response->assertSee('data-label="Hari"', false);
        $response->assertSee('data-label="Jam Pelajaran"', false);
        $response->assertSee('data-label="Kelas"', false);
        $response->assertSee('data-label="Mata Pelajaran"', false);
        $response->assertSee('data-label="Guru"', false);
        $response->assertSee('data-label="Semester"', false);
        $response->assertSee('data-label="Aksi"', false);

        // Memastikan helper Ui::badgeHari('kamis') ter-render (amber-100 dark:bg-amber-900/40)
        $badgeHariKamis = Ui::badgeHari('kamis');
        $response->assertSee($badgeHariKamis, false);

        // Memastikan helper Ui::badgeSemester('Ganjil') ter-render (blue-100 dark:bg-blue-900/40)
        $badgeSemesterGanjil = Ui::badgeSemester('Ganjil');
        $response->assertSee($badgeSemesterGanjil, false);
    }

    public function test_semua_form_create_dan_edit_admin_200_admin_dan_403_guru_siswa(): void
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);
        $mapel = Mapel::factory()->create();
        $guruModel = Guru::factory()->create();
        $siswaModel = Siswa::factory()->create(['kelas_id' => $kelas->id]);
        $jadwal = Jadwal::factory()->create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guruModel->id,
        ]);

        $urls = [
            route('admin.siswa.create'),
            route('admin.siswa.edit', $siswaModel),
            route('admin.guru.create'),
            route('admin.guru.edit', $guruModel),
            route('admin.kelas.create'),
            route('admin.kelas.edit', $kelas),
            route('admin.mapel.create'),
            route('admin.mapel.edit', $mapel),
            route('admin.jurusan.create'),
            route('admin.jurusan.edit', $jurusan),
            route('admin.jadwal.create'),
            route('admin.jadwal.edit', $jadwal),
        ];

        foreach ($urls as $url) {
            // Admin: 200 OK
            $this->actingAs($this->admin)->get($url)->assertOk();

            // Guru: 403 Forbidden
            $this->actingAs($this->guru)->get($url)->assertForbidden();

            // Siswa: 403 Forbidden
            $this->actingAs($this->siswa)->get($url)->assertForbidden();
        }
    }
}
