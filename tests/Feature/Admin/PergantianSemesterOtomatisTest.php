<?php

namespace Tests\Feature\Admin;

use App\Http\Requests\StoreJadwalRequest;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Pengaturan;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use App\Services\PergantianSemesterService;
use App\Services\PeriodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Tests\TestCase;

class PergantianSemesterOtomatisTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $guru;

    private User $siswa;

    private Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        Pengaturan::updateOrCreate(['kunci' => 'tahun_ajaran_aktif'], ['nilai' => '2026/2027']);
        Pengaturan::updateOrCreate(['kunci' => 'semester_aktif'], ['nilai' => 'Ganjil']);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->guru = User::factory()->create(['role' => 'guru']);
        $this->siswa = User::factory()->create(['role' => 'siswa']);

        $this->jurusan = Jurusan::create([
            'nama' => 'Teknik Komputer dan Jaringan',
            'kode' => 'TKJ',
        ]);
    }

    public function test_ab_15_1_kontrol_akses_halaman_dan_aksi(): void
    {
        // 1. Guest diarahkan ke login
        $this->get(route('admin.pergantian-semester.index'))->assertRedirect(route('login'));
        $this->post(route('admin.pergantian-semester.jalankan'))->assertRedirect(route('login'));
        $this->post(route('admin.pergantian-semester.aktifkan'))->assertRedirect(route('login'));

        // 2. Guru ditolak dengan 403
        $this->actingAs($this->guru)->get(route('admin.pergantian-semester.index'))->assertForbidden();
        $this->actingAs($this->guru)->post(route('admin.pergantian-semester.jalankan'))->assertForbidden();
        $this->actingAs($this->guru)->post(route('admin.pergantian-semester.aktifkan'))->assertForbidden();

        // 3. Siswa ditolak dengan 403
        $this->actingAs($this->siswa)->get(route('admin.pergantian-semester.index'))->assertForbidden();
        $this->actingAs($this->siswa)->post(route('admin.pergantian-semester.jalankan'))->assertForbidden();
        $this->actingAs($this->siswa)->post(route('admin.pergantian-semester.aktifkan'))->assertForbidden();

        // 4. Admin diizinkan (200)
        $this->actingAs($this->admin)->get(route('admin.pergantian-semester.index'))->assertOk();
    }

    public function test_ab_15_2_validasi_input_eksekusi(): void
    {
        // a. Semester asal bukan Ganjil
        $respGenap = $this->actingAs($this->admin)->post(route('admin.pergantian-semester.jalankan'), [
            'tahun_ajaran' => '2026/2027',
            'semester_asal' => 'Genap',
            'salin_kelas' => 1,
            'pindah_siswa' => 1,
            'salin_jadwal' => 1,
        ]);
        $respGenap->assertSessionHasErrors('semester_asal');

        // b. Format tahun ajaran tidak valid
        $respFormat = $this->actingAs($this->admin)->post(route('admin.pergantian-semester.jalankan'), [
            'tahun_ajaran' => '2026-2027',
            'semester_asal' => 'Ganjil',
            'salin_kelas' => 1,
        ]);
        $respFormat->assertSessionHasErrors('tahun_ajaran');

        // c. Tahun ajaran tidak konsisten (misal 2026/2028)
        $respInkonsisten = $this->actingAs($this->admin)->post(route('admin.pergantian-semester.jalankan'), [
            'tahun_ajaran' => '2026/2028',
            'semester_asal' => 'Ganjil',
            'salin_kelas' => 1,
        ]);
        $respInkonsisten->assertSessionHasErrors('tahun_ajaran');

        // d. Tidak ada opsi yang dipilih (semua 0)
        $respTanpaOpsi = $this->actingAs($this->admin)->post(route('admin.pergantian-semester.jalankan'), [
            'tahun_ajaran' => '2026/2027',
            'semester_asal' => 'Ganjil',
            'salin_kelas' => 0,
            'pindah_siswa' => 0,
            'salin_jadwal' => 0,
        ]);
        $respTanpaOpsi->assertSessionHasErrors('opsi');
    }

    public function test_ab_15_3_pratinjau_murni_read_only_dan_tidak_mengubah_database(): void
    {
        $k1 = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        Siswa::factory()->create(['kelas_id' => $k1->id]);
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();
        Jadwal::create([
            'kelas_id' => $k1->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $kelasSebelum = Kelas::count();
        $siswaSebelum = Siswa::count();
        $jadwalSebelum = Jadwal::count();

        $response = $this->actingAs($this->admin)->get(route('admin.pergantian-semester.index', [
            'tahun_ajaran' => '2026/2027',
            'salin_kelas' => 1,
            'pindah_siswa' => 1,
            'salin_jadwal' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Pratinjau Aman (Read-Only)');
        $response->assertSee('X TKJ 1');
        $response->assertSee('Akan Disalin');

        // Pastikan jumlah data sama persis sebelum dan sesudah pratinjau (read-only)
        $this->assertSame($kelasSebelum, Kelas::count());
        $this->assertSame($siswaSebelum, Siswa::count());
        $this->assertSame($jadwalSebelum, Jadwal::count());
    }

    public function test_ab_15_4_eksekusi_penuh_dan_keutuhan_riwayat_laporan_ganjil(): void
    {
        $kelasGanjil = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        $siswa = Siswa::factory()->create(['kelas_id' => $kelasGanjil->id]);
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();

        $jadwalGanjil = Jadwal::create([
            'kelas_id' => $kelasGanjil->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        // Buat riwayat sesi absensi dan detail absensi semester Ganjil
        $sesiGanjil = SesiAbsensi::create([
            'jadwal_id' => $jadwalGanjil->id,
            'tanggal' => '2026-08-10',
            'diabsen_oleh' => $this->admin->id,
        ]);
        DetailAbsensi::create([
            'sesi_absensi_id' => $sesiGanjil->id,
            'siswa_id' => $siswa->id,
            'status' => 'hadir',
        ]);

        // Jalankan pergantian semester otomatis
        $response = $this->actingAs($this->admin)->post(route('admin.pergantian-semester.jalankan'), [
            'tahun_ajaran' => '2026/2027',
            'semester_asal' => 'Ganjil',
            'salin_kelas' => 1,
            'pindah_siswa' => 1,
            'salin_jadwal' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 1. Kelas tujuan Genap berhasil dibuat dengan atribut sama
        $kelasGenap = Kelas::where('nama', 'X TKJ 1')
            ->where('tahun_ajaran', '2026/2027')
            ->where('semester', 'Genap')
            ->first();

        $this->assertNotNull($kelasGenap);
        $this->assertSame($kelasGanjil->tingkat, $kelasGenap->tingkat);
        $this->assertSame($kelasGanjil->jurusan_id, $kelasGenap->jurusan_id);

        // 2. Siswa aktif dipindahkan ke kelas Genap
        $siswa->refresh();
        $this->assertSame($kelasGenap->id, $siswa->kelas_id);

        // 3. Jadwal berhasil disalin ke semester Genap dengan data sama
        $jadwalGenap = Jadwal::where('kelas_id', $kelasGenap->id)
            ->where('tahun_ajaran', '2026/2027')
            ->where('semester', 'Genap')
            ->first();

        $this->assertNotNull($jadwalGenap);
        $this->assertSame($jadwalGanjil->mapel_id, $jadwalGenap->mapel_id);
        $this->assertSame($jadwalGanjil->guru_id, $jadwalGenap->guru_id);
        $this->assertSame($jadwalGanjil->hari, $jadwalGenap->hari);
        $this->assertSame('07:00:00', $jadwalGenap->jam_mulai);
        $this->assertSame('08:30:00', $jadwalGenap->jam_selesai);

        // 4. Jadwal lama dan sesi absensi lama TIDAK berubah
        $jadwalGanjil->refresh();
        $this->assertSame($kelasGanjil->id, $jadwalGanjil->kelas_id);
        $this->assertSame(1, $jadwalGanjil->sesiAbsensi()->count());

        // 5. AB-13: Laporan semester Ganjil tetap memuat siswa yang sudah dipindahkan via scopeUntukLaporan
        $siswaUntukLaporanGanjil = Siswa::untukLaporan($kelasGanjil->id, [$sesiGanjil->id])->get();
        $this->assertTrue($siswaUntukLaporanGanjil->contains('id', $siswa->id));
    }

    public function test_ab_15_5_idempoten_eksekusi_ulang_tidak_membuat_duplikat(): void
    {
        $k1 = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        Siswa::factory()->create(['kelas_id' => $k1->id]);
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();
        Jadwal::create([
            'kelas_id' => $k1->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $service = app(PergantianSemesterService::class);
        $opsi = ['salin_kelas' => true, 'pindah_siswa' => true, 'salin_jadwal' => true];

        // Eksekusi pertama
        $hasil1 = $service->jalankan('2026/2027', $opsi, $this->admin->id);
        $this->assertSame(1, $hasil1['kelas_berhasil']);
        $this->assertSame(1, $hasil1['siswa_dipindah']);
        $this->assertSame(1, $hasil1['jadwal_disalin']);

        $totalKelasSetelah1 = Kelas::count();
        $totalJadwalSetelah1 = Jadwal::count();

        // Eksekusi kedua (harus idempoten)
        $hasil2 = $service->jalankan('2026/2027', $opsi, $this->admin->id);
        $this->assertSame(0, $hasil2['siswa_dipindah']);
        $this->assertSame(0, $hasil2['jadwal_disalin']);
        $this->assertSame(1, $hasil2['jadwal_dilewati']); // Jadwal sudah identik

        $this->assertSame($totalKelasSetelah1, Kelas::count());
        $this->assertSame($totalJadwalSetelah1, Jadwal::count());
    }

    public function test_ab_15_6_opsi_parsial(): void
    {
        $k1 = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        $siswa = Siswa::factory()->create(['kelas_id' => $k1->id]);
        $mapel = Mapel::factory()->create();
        $guru = Guru::factory()->create();
        Jadwal::create([
            'kelas_id' => $k1->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $service = app(PergantianSemesterService::class);

        // 1. Hanya salin kelas (siswa tidak dipindah, jadwal tidak disalin)
        $hasil = $service->jalankan('2026/2027', [
            'salin_kelas' => true,
            'pindah_siswa' => false,
            'salin_jadwal' => false,
        ], $this->admin->id);

        $this->assertSame(1, $hasil['kelas_berhasil']);
        $this->assertSame(0, $hasil['siswa_dipindah']);
        $this->assertSame(0, $hasil['jadwal_disalin']);
        $siswa->refresh();
        $this->assertSame($k1->id, $siswa->kelas_id);
    }

    public function test_ab_15_7_alasan_jadwal_dilewati(): void
    {
        $kAsal = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        $kTujuan = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap']);

        $guruAktif = Guru::factory()->create();
        $guruTerhapus = Guru::factory()->create();
        $guruTerhapus->delete();

        $mapelAktif = Mapel::factory()->create();
        $mapelTerhapus = Mapel::factory()->create();
        $mapelTerhapus->delete();

        // 1. Jadwal dengan guru terhapus
        Jadwal::create([
            'kelas_id' => $kAsal->id,
            'mapel_id' => $mapelAktif->id,
            'guru_id' => $guruTerhapus->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        // 2. Jadwal dengan mapel terhapus
        Jadwal::create([
            'kelas_id' => $kAsal->id,
            'mapel_id' => $mapelTerhapus->id,
            'guru_id' => $guruAktif->id,
            'hari' => 'senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        // 3. Jadwal bentrok jam guru di tujuan
        $guruBentrok = Guru::factory()->create();
        $kLainTujuan = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 2', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap']);
        Jadwal::create([
            'kelas_id' => $kLainTujuan->id,
            'mapel_id' => $mapelAktif->id,
            'guru_id' => $guruBentrok->id,
            'hari' => 'selasa',
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Genap',
        ]);
        Jadwal::create([
            'kelas_id' => $kAsal->id,
            'mapel_id' => $mapelAktif->id,
            'guru_id' => $guruBentrok->id,
            'hari' => 'selasa',
            'jam_mulai' => '08:00', // Beririsan jam dengan 07:00-09:00
            'jam_selesai' => '10:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $service = app(PergantianSemesterService::class);
        $pratinjau = $service->pratinjau('2026/2027', ['salin_kelas' => true, 'pindah_siswa' => true, 'salin_jadwal' => true]);

        $rincianJadwal = collect($pratinjau['rencana_kelas'][0]['daftar_jadwal']);

        $this->assertTrue($rincianJadwal->contains(fn ($j) => str_contains($j['alasan'] ?? '', 'Guru pengajar sudah dinonaktifkan')));
        $this->assertTrue($rincianJadwal->contains(fn ($j) => str_contains($j['alasan'] ?? '', 'Mata pelajaran sudah dinonaktifkan')));
        $this->assertTrue($rincianJadwal->contains(fn ($j) => str_contains($j['alasan'] ?? '', 'Bentrok jam mengajar guru')));
    }

    public function test_ab_15_8_siswa_nonaktif_tidak_dipindahkan(): void
    {
        $kAsal = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        $siswaAktif = Siswa::factory()->create(['kelas_id' => $kAsal->id]);
        $siswaTerhapus = Siswa::factory()->create(['kelas_id' => $kAsal->id]);
        $siswaTerhapus->delete();

        $service = app(PergantianSemesterService::class);
        $service->jalankan('2026/2027', ['salin_kelas' => true, 'pindah_siswa' => true, 'salin_jadwal' => false], $this->admin->id);

        $siswaAktif->refresh();
        $siswaTerhapus->refresh();

        $kTujuan = Kelas::where('nama', 'X TKJ 1')->where('semester', 'Genap')->first();
        $this->assertSame($kTujuan->id, $siswaAktif->kelas_id);
        $this->assertSame($kAsal->id, $siswaTerhapus->kelas_id); // Siswa nonaktif tidak disentuh
    }

    public function test_ab_15_9_isolasi_kegagalan_satu_kelas_tidak_membatalkan_kelas_lain(): void
    {
        $k1 = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        $k2 = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 2', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

        Siswa::factory()->create(['kelas_id' => $k1->id]);
        Siswa::factory()->create(['kelas_id' => $k2->id]);

        // Buat kelas tujuan k1 terhapus untuk memicu status dilewati/tertahan
        $kTujuanTerhapus = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap']);
        $kTujuanTerhapus->delete();

        $service = app(PergantianSemesterService::class);
        $hasil = $service->jalankan('2026/2027', ['salin_kelas' => true, 'pindah_siswa' => true, 'salin_jadwal' => true], $this->admin->id);

        // Kelas 2 harus tetap berhasil diproses meskipun kelas 1 dilewati/tertahan
        $this->assertSame(1, $hasil['kelas_berhasil']);
        $this->assertSame(1, $hasil['kelas_gagal']);
        $this->assertDatabaseHas('kelas', ['nama' => 'X TKJ 2', 'semester' => 'Genap']);
    }

    public function test_ab_15_10_batas_keselamatan_menghentikan_eksekusi(): void
    {
        Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);
        Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 2', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil']);

        $service = app(PergantianSemesterService::class);
        // Set batas maksimal kelas ke 1 untuk menguji pencegahan keselamatan
        $service->setBatasMaksimal(kelas: 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('melebihi batas maksimal (1 kelas');

        $service->jalankan('2026/2027', ['salin_kelas' => true, 'pindah_siswa' => true, 'salin_jadwal' => true], $this->admin->id);
    }

    public function test_ab_15_11_aktivasi_periode_tujuan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.pergantian-semester.aktifkan'), [
            'tahun_ajaran' => '2026/2027',
        ]);

        $response->assertRedirect();
        // Karena belum ada jadwal di Genap, muncul session warning
        $response->assertSessionHas('warning');

        $periodeService = app(PeriodeService::class);
        $aktif = $periodeService->getActivePeriode();
        $this->assertSame('2026/2027', $aktif['tahun_ajaran']);
        $this->assertSame('Genap', $aktif['semester']);
    }

    public function test_ab_15_12_kesetaraan_aturan_bentrok_jadwal_dengan_form_request(): void
    {
        $k1 = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap']);
        $k2 = Kelas::create(['jurusan_id' => $this->jurusan->id, 'nama' => 'X TKJ 2', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap']);
        $mapel1 = Mapel::factory()->create();
        $guru1 = Guru::factory()->create();

        // Jadwal acuan: Senin 07:00 - 08:30 guru1 di kelas1
        Jadwal::create([
            'kelas_id' => $k1->id,
            'mapel_id' => $mapel1->id,
            'guru_id' => $guru1->id,
            'hari' => 'senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Genap',
        ]);

        $service = app(PergantianSemesterService::class);

        // Kasus A: Guru sama, jam beririsan (08:00 - 09:30) di kelas berbeda
        $bentrokServiceA = $service->cekBentrokJadwal($k2->id, $guru1->id, 'senin', '08:00', '09:30', '2026/2027', 'Genap');
        $this->assertTrue($bentrokServiceA['bentrok_guru']);

        // Uji dengan StoreJadwalRequest (FormRequest)
        $reqA = new StoreJadwalRequest;
        $reqA->merge([
            'kelas_id' => $k2->id,
            'mapel_id' => $mapel1->id,
            'guru_id' => $guru1->id,
            'hari' => 'senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Genap',
        ]);
        $valA = Validator::make($reqA->all(), $reqA->rules());
        $reqA->withValidator($valA);
        $this->assertTrue($valA->errors()->has('guru_id'), 'FormRequest harus mendeteksi bentrok guru yang sama dengan Service.');

        // Kasus B: Jam tidak beririsan (08:30 - 10:00)
        $bentrokServiceB = $service->cekBentrokJadwal($k2->id, $guru1->id, 'senin', '08:30', '10:00', '2026/2027', 'Genap');
        $this->assertFalse($bentrokServiceB['bentrok_guru']);
        $this->assertFalse($bentrokServiceB['bentrok_kelas']);

        $reqB = new StoreJadwalRequest;
        $reqB->merge([
            'kelas_id' => $k2->id,
            'mapel_id' => $mapel1->id,
            'guru_id' => $guru1->id,
            'hari' => 'senin',
            'jam_mulai' => '08:30',
            'jam_selesai' => '10:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Genap',
        ]);
        $valB = Validator::make($reqB->all(), $reqB->rules());
        $reqB->withValidator($valB);
        $this->assertFalse($valB->errors()->has('guru_id'), 'FormRequest dan Service sama-sama harus mengizinkan jam yang tidak beririsan.');
    }

    public function test_ab_15_13_kepatuhan_antarmuka_dan_kartu_tautan(): void
    {
        // 1. Kartu tautan muncul di halaman Pergantian Periode
        $respPeriode = $this->actingAs($this->admin)->get(route('admin.pergantian-periode.index'));
        $respPeriode->assertOk();
        $respPeriode->assertSee('Pergantian Semester Otomatis');
        $respPeriode->assertSee('Disarankan untuk Ganjil ke Genap');
        $respPeriode->assertSee(route('admin.pergantian-semester.index'));

        // 2. Tab lama Pergantian Periode tetap utuh
        $respPeriode->assertSee('Salin Kelas');
        $respPeriode->assertSee('Pindahkan Siswa');
        $respPeriode->assertSee('Luluskan Siswa');

        // 3. Halaman pergantian semester memuat x-ui.select dan tombol submit terproteksi
        $respSemester = $this->actingAs($this->admin)->get(route('admin.pergantian-semester.index'));
        $respSemester->assertOk();
        $respSemester->assertDontSee('<select class="mt-1 block w-full border-gray-300', false);
        $respSemester->assertSee('Pergantian Semester Otomatis');
    }

    public function test_ab_15_14_pencatatan_log_tanpa_data_sensitif(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Pastikan pesan benar
                if ($message !== 'Pergantian semester otomatis dijalankan') {
                    return false;
                }

                // Pastikan context memuat ringkasan dan tidak memuat kata sandi / data rahasia
                return isset($context['admin_id'], $context['tahun_ajaran'])
                    && ! isset($context['password'], $context['secret']);
            });

        $service = app(PergantianSemesterService::class);
        $service->jalankan('2026/2027', [
            'salin_kelas' => true,
            'pindah_siswa' => true,
            'salin_jadwal' => true,
        ], $this->admin->id);
    }
}
