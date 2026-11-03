<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\User;
use App\Support\Ui;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelKelasDanFilterJadwalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);

        Pengaturan::updateOrCreate(['kunci' => 'tahun_ajaran_aktif'], ['nilai' => '2026/2027']);
        Pengaturan::updateOrCreate(['kunci' => 'semester_aktif'], ['nilai' => 'Ganjil']);
    }

    public function test_ui_label_kelas_memformat_periode_ganjil_dan_genap_dengan_benar(): void
    {
        $jurusan = Jurusan::factory()->create();

        $kelasGanjil = Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'DKV 1',
            'tingkat' => 'X',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $kelasGenap = Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'DKV 1',
            'tingkat' => 'X',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Genap',
        ]);

        $this->assertSame('DKV 1 · Ganjil 2026/2027', Ui::labelKelas($kelasGanjil));
        $this->assertSame('DKV 1 · Genap 2026/2027', Ui::labelKelas($kelasGenap));

        // Melalui accessor model
        $this->assertSame('DKV 1 · Ganjil 2026/2027', $kelasGanjil->label_lengkap);
        $this->assertSame('DKV 1 · Genap 2026/2027', $kelasGenap->label_lengkap);

        // Array fallback
        $this->assertSame('XII TKJ 2 · Genap 2025/2026', Ui::labelKelas([
            'nama' => 'XII TKJ 2',
            'semester' => 'Genap',
            'tahun_ajaran' => '2025/2026',
        ]));

        // Null atau tanpa periode
        $this->assertSame('-', Ui::labelKelas(null));
        $this->assertSame('Kelas Polos', Ui::labelKelas(['nama' => 'Kelas Polos']));
    }

    public function test_ui_sort_kelas_mengurutkan_periode_terbaru_tingkat_dan_nama(): void
    {
        $jurusan = Jurusan::factory()->create();

        $k1 = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'RPL 1', 'tingkat' => 'X', 'tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil']);
        $k2 = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'RPL 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap']);
        $k3 = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'TKJ 1', 'tingkat' => 'XI', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        $k4 = Kelas::factory()->create(['jurusan_id' => $jurusan->id, 'nama' => 'AKL 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

        $sorted = Ui::sortKelas([$k1, $k2, $k3, $k4]);

        // Urutan:
        // 1. 2026/2027 Genap (k2)
        // 2. 2026/2027 Ganjil tingkat X (k4: AKL 1)
        // 3. 2026/2027 Ganjil tingkat XI (k3: TKJ 1)
        // 4. 2025/2026 Ganjil (k1)
        $this->assertEquals([$k2->id, $k4->id, $k3->id, $k1->id], $sorted->pluck('id')->all());
    }

    public function test_halaman_tambah_jadwal_memuat_label_berperiode_dan_atribut_data_filter(): void
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'DKV 1',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.jadwal.create'));

        $response->assertOk();
        $response->assertSee('DKV 1 · Ganjil 2026/2027');
        $response->assertSee('data-tahun-ajaran="2026/2027"', false);
        $response->assertSee('data-semester="Ganjil"', false);
        $response->assertSee('kelolaFilterKelas', false);
        $response->assertSee('Pilih ulang kelas sesuai periode');
        $response->assertSee('Belum ada kelas di periode ini');
    }

    public function test_halaman_edit_jadwal_memuat_label_berperiode_dan_atribut_data_filter(): void
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'DKV 1',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();

        $jadwal = Jadwal::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.jadwal.edit', $jadwal));

        $response->assertOk();
        $response->assertSee('DKV 1 · Ganjil 2026/2027');
        $response->assertSee('data-tahun-ajaran="2026/2027"', false);
        $response->assertSee('data-semester="Ganjil"', false);
        $response->assertSee('kelolaFilterKelas', false);
    }

    public function test_halaman_siswa_create_dan_edit_memuat_label_kelas_berperiode(): void
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'TKJ 2',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $responseCreate = $this->actingAs($this->admin)->get(route('admin.siswa.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('TKJ 2 · Ganjil 2026/2027');

        $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);

        $responseEdit = $this->actingAs($this->admin)->get(route('admin.siswa.edit', $siswa));
        $responseEdit->assertOk();
        $responseEdit->assertSee('TKJ 2 · Ganjil 2026/2027');
    }

    public function test_halaman_siswa_index_memuat_label_kelas_berperiode_di_filter_modal_dan_tabel(): void
    {
        $jurusan = Jurusan::factory()->create();
        $kelas = Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'TKJ 2',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);
        Siswa::factory()->create(['kelas_id' => $kelas->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));

        $response->assertOk();
        $response->assertSee('TKJ 2 · Ganjil 2026/2027');
    }

    public function test_halaman_filter_jadwal_laporan_dan_koreksi_memuat_label_kelas_berperiode(): void
    {
        $jurusan = Jurusan::factory()->create();
        Kelas::factory()->create([
            'jurusan_id' => $jurusan->id,
            'nama' => 'AKL 1',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        // Filter jadwal
        $responseJadwal = $this->actingAs($this->admin)->get(route('admin.jadwal.index'));
        $responseJadwal->assertOk();
        $responseJadwal->assertSee('AKL 1 · Ganjil 2026/2027');

        // Filter laporan
        $responseLaporan = $this->actingAs($this->admin)->get(route('admin.laporan.index'));
        $responseLaporan->assertOk();
        $responseLaporan->assertSee('AKL 1 · Ganjil 2026/2027');

        // Filter koreksi absensi
        $responseKoreksi = $this->actingAs($this->admin)->get(route('admin.koreksi-absensi.index'));
        $responseKoreksi->assertOk();
        $responseKoreksi->assertSee('AKL 1 · Ganjil 2026/2027');
    }
}
