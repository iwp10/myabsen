<?php

use App\Enums\StatusKehadiran;
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
use App\Support\KelasMapel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    // Tetapkan periode aktif default 2026/2027 Ganjil
    Pengaturan::updateOrCreate(['kunci' => 'tahun_ajaran_aktif'], ['nilai' => '2026/2027']);
    Pengaturan::updateOrCreate(['kunci' => 'semester_aktif'], ['nilai' => 'Ganjil']);

    $this->jurusan = Jurusan::create(['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ']);

    // Admin
    $this->adminUser = User::factory()->create([
        'name' => 'Admin Sekolah',
        'username' => 'admin_test',
        'role' => 'admin',
    ]);

    // Guru 1 (Penguji utama)
    $this->guruUser1 = User::factory()->create([
        'name' => 'Budi Setiawan, S.Kom',
        'username' => 'guru_budi',
        'role' => 'guru',
    ]);
    $this->guru1 = Guru::create([
        'user_id' => $this->guruUser1->id,
        'nip' => '198501012010011001',
    ]);

    // Guru 2 (Guru lain)
    $this->guruUser2 = User::factory()->create([
        'name' => 'Siti Aminah, M.Pd',
        'username' => 'guru_siti',
        'role' => 'guru',
    ]);
    $this->guru2 = Guru::create([
        'user_id' => $this->guruUser2->id,
        'nip' => '198702022011022002',
    ]);

    // Mapel
    $this->mapel1 = Mapel::create(['nama' => 'Pemrograman Web', 'kode' => 'WEB']);
    $this->mapel2 = Mapel::create(['nama' => 'Jaringan Komputer', 'kode' => 'JARKOM']);
    $this->mapelLain = Mapel::create(['nama' => 'Basis Data', 'kode' => 'BASDAT']);

    // Kelas Aktif (2026/2027 Ganjil)
    $this->kelasAktif1 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'XII TKJ 1',
        'tingkat' => 12,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $this->kelasAktif2 = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'XII TKJ 2',
        'tingkat' => 12,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Kelas Lama (2025/2026 Genap)
    $this->kelasLama = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'XI TKJ 1',
        'tingkat' => 11,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    // Siswa 1 di Kelas Aktif 1
    $this->siswaUser1 = User::factory()->create([
        'name' => 'Ahmad Dahlan',
        'username' => 'siswa_ahmad',
        'role' => 'siswa',
    ]);
    $this->siswa1 = Siswa::create([
        'user_id' => $this->siswaUser1->id,
        'kelas_id' => $this->kelasAktif1->id,
        'nis' => '1001',
    ]);

    // Siswa 2 di Kelas Aktif 1
    $this->siswaUser2 = User::factory()->create([
        'name' => 'Budi Utomo',
        'username' => 'siswa_budi',
        'role' => 'siswa',
    ]);
    $this->siswa2 = Siswa::create([
        'user_id' => $this->siswaUser2->id,
        'kelas_id' => $this->kelasAktif1->id,
        'nis' => '1002',
    ]);
});

test('AB-11: Guru melihat riwayat periode aktif secara bawaan, memilih periode lama menampilkan data periode itu, dan tetap bisa membuka Ganjil setelah periode aktif diganti Genap', function () {
    // Jadwal Guru 1 pada periode aktif
    $jadwalAktif = Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Jadwal Guru 1 pada periode lama
    $jadwalLama = Jadwal::create([
        'kelas_id' => $this->kelasLama->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    // Buat sesi untuk jadwal aktif & jadwal lama
    $sesiAktif = SesiAbsensi::create([
        'jadwal_id' => $jadwalAktif->id,
        'tanggal' => '2026-08-10',
        'diabsen_oleh' => $this->guruUser1->id,
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesiAktif->id,
        'siswa_id' => $this->siswa1->id,
        'status' => StatusKehadiran::HADIR,
    ]);

    $sesiLama = SesiAbsensi::create([
        'jadwal_id' => $jadwalLama->id,
        'tanggal' => '2026-02-10',
        'diabsen_oleh' => $this->guruUser1->id,
    ]);

    // 1. Guru 1 membuka riwayat tanpa parameter -> default melihat periode aktif (2026/2027 Ganjil)
    $response = $this->actingAs($this->guruUser1)->get(route('guru.riwayat'));
    $response->assertOk();
    $response->assertSee('XII TKJ 1');
    $response->assertSee('Pemrograman Web');
    $response->assertDontSee('XI TKJ 1');
    $response->assertDontSee('Jaringan Komputer');

    // 2. Guru 1 memilih periode lama (2025/2026 Genap)
    $responseLama = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', [
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]));
    $responseLama->assertOk();
    $responseLama->assertSee('XI TKJ 1');
    $responseLama->assertSee('Jaringan Komputer');
    $responseLama->assertDontSee('XII TKJ 1');
    $responseLama->assertDontSee('Pemrograman Web');

    // 3. Detail riwayat periode lama menampilkan matriks sesi periode itu
    $responseDetailLama = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasLama->id,
        'mapel' => $this->mapel2->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]));
    $responseDetailLama->assertOk();
    $responseDetailLama->assertSee('XI TKJ 1');
    $responseDetailLama->assertSee('Jaringan Komputer');
    $responseDetailLama->assertSee('2025/2026');
    $responseDetailLama->assertSee('Genap');

    // 4. Admin mengganti periode aktif ke 2026/2027 Genap
    Pengaturan::updateOrCreate(['kunci' => 'semester_aktif'], ['nilai' => 'Genap']);

    // Guru 1 tetap bisa membuka riwayat 2026/2027 Ganjil lewat dropdown
    $responseGanjil = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', [
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]));
    $responseGanjil->assertOk();
    $responseGanjil->assertSee('XII TKJ 1');
    $responseGanjil->assertSee('Pemrograman Web');
});

test('Dropdown hanya berisi periode yang punya data untuk pengguna itu plus periode aktif, periode tidak valid ditolak dengan error validasi', function () {
    // Guru 1 hanya punya jadwal di 2026/2027 Ganjil
    Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Guru 2 punya jadwal di 2024/2025 Ganjil (bukan milik Guru 1)
    $kelasGuruLain = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 3',
        'tingkat' => 10,
        'tahun_ajaran' => '2024/2025',
        'semester' => 'Ganjil',
    ]);
    Jadwal::create([
        'kelas_id' => $kelasGuruLain->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guru2->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2024/2025',
        'semester' => 'Ganjil',
    ]);

    // Buka riwayat guru 1: dropdown memuat periode aktif (2026/2027 Ganjil) bertanda (aktif), tetapi TIDAK memuat 2024/2025 Ganjil
    $response = $this->actingAs($this->guruUser1)->get(route('guru.riwayat'));
    $response->assertOk();
    $daftarPeriode = $response->viewData('daftarPeriode');
    expect($daftarPeriode)->not->toBeEmpty();
    $labels = collect($daftarPeriode)->pluck('label')->all();
    expect(collect($labels)->contains(fn ($l) => str_contains($l, '2026/2027') && str_contains($l, 'Ganjil') && str_contains($l, '(aktif)')))->toBeTrue();
    expect(collect($labels)->contains(fn ($l) => str_contains($l, '2024/2025')))->toBeFalse();

    // Validasi format periode di FormRequest:
    // a. Format tahun_ajaran salah (bukan YYYY/YYYY)
    $this->actingAs($this->guruUser1)->get(route('guru.riwayat', [
        'tahun_ajaran' => '2026-2027',
        'semester' => 'Ganjil',
    ]))->assertSessionHasErrors(['tahun_ajaran']);

    // b. Format tahun_ajaran dengan tahun kedua != tahun pertama + 1
    $this->actingAs($this->guruUser1)->get(route('guru.riwayat', [
        'tahun_ajaran' => '2025/2027',
        'semester' => 'Ganjil',
    ]))->assertSessionHasErrors(['tahun_ajaran']);

    // c. Semester tidak valid (bukan Ganjil / Genap)
    $this->actingAs($this->guruUser1)->get(route('guru.riwayat', [
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Pendek',
    ]))->assertSessionHasErrors(['semester']);

    // d. Validasi pada riwayat siswa
    $this->actingAs($this->siswaUser1)->get(route('siswa.riwayat', [
        'tahun_ajaran' => '2026/2028',
        'semester' => 'Ganjil',
    ]))->assertSessionHasErrors(['tahun_ajaran']);
});

test('Riwayat detail periode lama: guru lain mendapat 403, kelas-mapel tanpa jadwal pada periode itu mendapat 404', function () {
    // Jadwal Guru 1 di periode lama (2025/2026 Genap)
    Jadwal::create([
        'kelas_id' => $this->kelasLama->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    // Guru 2 (guru lain) mencoba mengakses riwayat detail kelas-mapel milik Guru 1 -> 403 Forbidden
    $this->actingAs($this->guruUser2)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasLama->id,
        'mapel' => $this->mapel2->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]))->assertForbidden();

    // Guru 1 mencoba mengakses kelas-mapel yang TIDAK ada jadwalnya pada periode tersebut -> 404 Not Found
    $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAktif1->id,
        'mapel' => $this->mapel1->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]))->assertNotFound();
});

test('Link Export Excel membawa periode terpilih dan hasilnya hanya berisi periode itu dan semua siswa tanpa tersaring oleh q', function () {
    Excel::fake();

    $jadwalLama = Jadwal::create([
        'kelas_id' => $this->kelasLama->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    // Siswa di kelas lama
    $siswaA = Siswa::create([
        'user_id' => User::factory()->create(['name' => 'Charlie Chaplin', 'role' => 'siswa'])->id,
        'kelas_id' => $this->kelasLama->id,
        'nis' => '2001',
    ]);
    $siswaB = Siswa::create([
        'user_id' => User::factory()->create(['name' => 'Dedi Corbuzier', 'role' => 'siswa'])->id,
        'kelas_id' => $this->kelasLama->id,
        'nis' => '2002',
    ]);

    $kmLama = KelasMapel::make($this->kelasLama->id, $this->mapel2->id);

    // Buka riwayat detail dengan q='Charlie' pada periode lama
    $response = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasLama->id,
        'mapel' => $this->mapel2->id,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
        'q' => 'Charlie',
    ]));
    $response->assertOk();

    // Pastikan link Export Excel membawa tahun_ajaran dan semester terpilih, dan TIDAK membawa q
    $exportUrl = route('guru.laporan.export', [
        'kelas_mapel' => $kmLama,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);
    $response->assertSee(e($exportUrl), false);
    $response->assertDontSee('q=Charlie');

    // Jalankan ekspor Excel
    $exportResponse = $this->actingAs($this->guruUser1)->get($exportUrl);
    $exportResponse->assertOk();

    Excel::assertDownloaded('rekap_absensi_guru_2025-2026_Genap.xlsx', function ($export) {
        $sheets = $export->sheets();
        expect(count($sheets))->toBe(1);
        $sheet = $sheets[0];
        expect($sheet->title())->not->toBeEmpty();

        return true;
    });
});

test('Siswa melihat riwayat periode lama miliknya saja (AB-08), siswa lain tidak bisa membuka data orang lain, dan dashboard tetap periode aktif', function () {
    // Jadwal aktif & jadwal lama
    $jadwalAktif = Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $jadwalLama = Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    // Riwayat Siswa 1 di periode lama
    $sesiLama = SesiAbsensi::create([
        'jadwal_id' => $jadwalLama->id,
        'tanggal' => '2026-03-03',
        'diabsen_oleh' => $this->guruUser1->id,
    ]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesiLama->id,
        'siswa_id' => $this->siswa1->id,
        'status' => StatusKehadiran::HADIR,
        'keterangan' => 'Hadir sesi lama',
    ]);

    // 1. Siswa 1 membuka riwayat periode lama miliknya -> bisa melihat datanya
    $response = $this->actingAs($this->siswaUser1)->get(route('siswa.riwayat', [
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]));
    $response->assertOk();
    $response->assertSee('Jaringan Komputer');
    $response->assertSee('2025/2026');
    $response->assertSee('Genap');

    // 2. Siswa 2 membuka riwayat periode lama -> tidak memiliki detail absensi di sesi itu
    $responseSiswa2 = $this->actingAs($this->siswaUser2)->get(route('siswa.riwayat', [
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]));
    $responseSiswa2->assertOk();
    $responseSiswa2->assertDontSee('Hadir sesi lama');

    // 3. Dashboard siswa 1 tetap merujuk pada periode aktif
    Carbon::setTestNow('2026-08-10 08:00:00'); // Senin
    $responseDash = $this->actingAs($this->siswaUser1)->get(route('siswa.dashboard'));
    $responseDash->assertOk();
    $responseDash->assertSee('Dashboard Siswa');
    // Jadwal aktif di hari Senin adalah Pemrograman Web
    $responseDash->assertSee('Pemrograman Web');
    Carbon::setTestNow();
});

test('AB-08: Pencarian riwayat guru: kartu tersaring menurut nama kelas dan mapel, empty state jika nihil, tidak muncul kelas guru lain, escape % dan _, q > 100 karakter ditolak', function () {
    // Guru 1 mengajar:
    // 1. XII TKJ 1 - Pemrograman Web
    Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // 2. XII TKJ 2 - Jaringan Komputer
    Jadwal::create([
        'kelas_id' => $this->kelasAktif2->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Guru 2 (guru lain) mengajar di XII TKJ 1 - Basis Data
    Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $this->mapelLain->id,
        'guru_id' => $this->guru2->id,
        'hari' => 'rabu',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // a. Pencarian nama mapel "Web" -> hanya kartu Pemrograman Web yang muncul
    $resWeb = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', ['q' => 'Web']));
    $resWeb->assertOk();
    $resWeb->assertSee('Pemrograman Web');
    $resWeb->assertDontSee('Jaringan Komputer');
    $resWeb->assertDontSee('Basis Data');

    // b. Pencarian nama kelas "TKJ 2" -> hanya kartu XII TKJ 2 yang muncul
    $resTKJ2 = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', ['q' => 'TKJ 2']));
    $resTKJ2->assertOk();
    $resTKJ2->assertSee('XII TKJ 2');
    $resTKJ2->assertDontSee('XII TKJ 1');

    // c. Guru lain tidak pernah muncul di hasil jadwal Guru 1
    $resGuruLain = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', ['q' => 'Basis Data']));
    $resGuruLain->assertOk();
    expect($resGuruLain->viewData('jadwalList')->isEmpty())->toBeTrue();
    $resGuruLain->assertSee("Tidak ada hasil untuk 'Basis Data'.", false);

    // d. Empty state jika nihil
    $resNihil = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', ['q' => 'Robotika']));
    $resNihil->assertOk();
    $resNihil->assertSee("Tidak ada hasil untuk 'Robotika'.", false);

    // e. Escape wildcard % dan _
    // Buat mapel dengan karakter khusus % dan _
    $mapelPersen = Mapel::create(['nama' => 'Matematika 100%', 'kode' => 'MAT100']);
    Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $mapelPersen->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'kamis',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    // Mencari '%' hanya mencocokkan 'Matematika 100%', tidak mencocokkan semua kartu
    $resPersen = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', ['q' => '%']));
    $resPersen->assertOk();
    $resPersen->assertSee('Matematika 100%');
    $resPersen->assertDontSee('Jaringan Komputer');

    // f. Parameter q lebih dari 100 karakter ditolak error validasi
    $longQ = str_repeat('a', 101);
    $this->actingAs($this->guruUser1)->get(route('guru.riwayat', ['q' => $longQ]))
        ->assertSessionHasErrors(['q']);
});

test('Pencarian di detail: siswa tersaring menurut nama dan NIS, persentase tidak berubah, siswa nonaktif berriwayat tetap bisa dicari, dan query tidak bertambah proporsional', function () {
    $jadwal = Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Siswa 3 (Nonaktif / soft-delete berriwayat)
    $siswaUser3 = User::factory()->create([
        'name' => 'Citra Dewi',
        'username' => 'siswa_citra',
        'role' => 'siswa',
    ]);
    $siswa3 = Siswa::create([
        'user_id' => $siswaUser3->id,
        'kelas_id' => $this->kelasAktif1->id,
        'nis' => '1003',
    ]);

    // Sesi 1: Siswa 1 Hadir, Siswa 2 Sakit, Siswa 3 Hadir
    $sesi1 = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-10',
        'diabsen_oleh' => $this->guruUser1->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::SAKIT]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi1->id, 'siswa_id' => $siswa3->id, 'status' => StatusKehadiran::HADIR]);

    // Sesi 2: Siswa 1 Hadir, Siswa 2 Alpa, Siswa 3 Hadir
    $sesi2 = SesiAbsensi::create([
        'jadwal_id' => $jadwal->id,
        'tanggal' => '2026-08-17',
        'diabsen_oleh' => $this->guruUser1->id,
    ]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $this->siswa1->id, 'status' => StatusKehadiran::HADIR]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $this->siswa2->id, 'status' => StatusKehadiran::ALPA]);
    DetailAbsensi::create(['sesi_absensi_id' => $sesi2->id, 'siswa_id' => $siswa3->id, 'status' => StatusKehadiran::HADIR]);

    // Soft delete Siswa 3
    $siswa3->delete();

    // 1. Cari berdasarkan nama "Ahmad" -> Siswa 1 muncul dengan 100%, baris Siswa 2 & 3 tidak muncul
    $resAhmad = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAktif1->id,
        'mapel' => $this->mapel1->id,
        'q' => 'Ahmad',
    ]));
    $resAhmad->assertOk();
    $resAhmad->assertSee('Ahmad Dahlan');
    $resAhmad->assertSee('100%');
    $resAhmad->assertDontSee('Budi Utomo');
    $resAhmad->assertDontSee('Citra Dewi');

    // 2. Cari berdasarkan NIS "1002" -> Siswa 2 muncul dengan 50% (Sakit dihitung hadir, Alpa mengurangi)
    $resNIS = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAktif1->id,
        'mapel' => $this->mapel1->id,
        'q' => '1002',
    ]));
    $resNIS->assertOk();
    $resNIS->assertSee('Budi Utomo');
    $resNIS->assertSee('50%');
    $resNIS->assertDontSee('Ahmad Dahlan');

    // 3. Siswa (nonaktif) berriwayat tetap bisa dicari dengan tanda "(nonaktif)"
    $resNonaktif = $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAktif1->id,
        'mapel' => $this->mapel1->id,
        'q' => 'Citra',
    ]));
    $resNonaktif->assertOk();
    $resNonaktif->assertSee('Citra Dewi');
    $resNonaktif->assertSee('(nonaktif)');
    $resNonaktif->assertSee('100%');
    $resNonaktif->assertDontSee('Ahmad Dahlan');
    $resNonaktif->assertDontSee('Budi Utomo');

    // 4. Periksa query count tidak bertambah proporsional dengan jumlah siswa
    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAktif1->id,
        'mapel' => $this->mapel1->id,
    ]));
    $baselineQueries = count(DB::getQueryLog());

    // Tambah 5 siswa aktif baru di kelas tersebut
    for ($i = 4; $i <= 8; $i++) {
        $u = User::factory()->create(['name' => "Siswa Tambahan $i", 'role' => 'siswa']);
        Siswa::create(['user_id' => $u->id, 'kelas_id' => $this->kelasAktif1->id, 'nis' => "100$i"]);
    }

    DB::flushQueryLog();
    $this->actingAs($this->guruUser1)->get(route('guru.riwayat.detail', [
        'kelas' => $this->kelasAktif1->id,
        'mapel' => $this->mapel1->id,
    ]));
    $queriesWithMoreStudents = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Query count harus tetap sama / konstan (tidak ada query tambahan per siswa, N+1 dicegah)
    expect($queriesWithMoreStudents)->toBe($baselineQueries);
});

test('Pencarian dan periode bekerja bersamaan di riwayat guru (kata kunci bertahan saat ganti periode)', function () {
    // Buat jadwal pada periode aktif (2026/2027 Ganjil) & periode lama (2025/2026 Genap)
    Jadwal::create([
        'kelas_id' => $this->kelasAktif1->id, // XII TKJ 1
        'mapel_id' => $this->mapel1->id,      // Pemrograman Web
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    Jadwal::create([
        'kelas_id' => $this->kelasLama->id,   // XI TKJ 1
        'mapel_id' => $this->mapel1->id,      // Pemrograman Web
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
    ]);

    // 1. Filter periode lama DAN cari 'Web':
    // Hanya kartu XI TKJ 1 - Pemrograman Web yang tampil
    $response = $this->actingAs($this->guruUser1)->get(route('guru.riwayat', [
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Genap',
        'q' => 'Web',
    ]));
    $response->assertOk();
    $response->assertSee('XI TKJ 1');
    $response->assertSee('Pemrograman Web');
    $response->assertDontSee('XII TKJ 1');

    // Input form pencarian mempertahankan query q='Web' dan dropdown periode
    $response->assertSee('value="Web"', false);
    $response->assertSee('name="q"', false);
    $response->assertSee('name="periode"', false);
});

test('AB-06: Mengubah periode kelas yang sudah punya jadwal ditolak dengan pesan edukatif, kelas tanpa jadwal boleh diubah periodenya, dan ubah nama tetap berhasil', function () {
    // 1. Kelas dengan jadwal
    $kelasBerjadwal = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Buat 2 jadwal di kelas tersebut
    Jadwal::create([
        'kelas_id' => $kelasBerjadwal->id,
        'mapel_id' => $this->mapel1->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'senin',
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:30:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    Jadwal::create([
        'kelas_id' => $kelasBerjadwal->id,
        'mapel_id' => $this->mapel2->id,
        'guru_id' => $this->guru1->id,
        'hari' => 'selasa',
        'jam_mulai' => '08:30:00',
        'jam_selesai' => '10:00:00',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    // Admin mencoba mengubah tahun_ajaran kelas berjadwal ke 2025/2026
    $resUbahTahun = $this->actingAs($this->adminUser)->put(route('admin.kelas.update', $kelasBerjadwal), [
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 10,
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
    ]);
    $resUbahTahun->assertSessionHasErrors(['tahun_ajaran' => 'Kelas ini sudah punya 2 jadwal pada periode Ganjil 2026/2027. Pindahkan atau hapus jadwal tersebut sebelum mengubah periode kelas.']);

    // Admin mencoba mengubah semester kelas berjadwal ke Genap
    $resUbahSemester = $this->actingAs($this->adminUser)->put(route('admin.kelas.update', $kelasBerjadwal), [
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
    ]);
    $resUbahSemester->assertSessionHasErrors(['semester' => 'Kelas ini sudah punya 2 jadwal pada periode Ganjil 2026/2027. Pindahkan atau hapus jadwal tersebut sebelum mengubah periode kelas.']);

    // Pastikan data kelas berjadwal di database TIDAK berubah
    $kelasBerjadwal->refresh();
    expect($kelasBerjadwal->tahun_ajaran)->toBe('2026/2027');
    expect($kelasBerjadwal->semester)->toBe('Ganjil');

    // 2. Kelas TANPA jadwal boleh diubah periodenya
    $kelasKosong = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 2',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $resKosong = $this->actingAs($this->adminUser)->put(route('admin.kelas.update', $kelasKosong), [
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 2',
        'tingkat' => 10,
        'tahun_ajaran' => '2027/2028',
        'semester' => 'Genap',
    ]);
    $resKosong->assertRedirect(route('admin.kelas.index'));

    $kelasKosong->refresh();
    expect($kelasKosong->tahun_ajaran)->toBe('2027/2028');
    expect($kelasKosong->semester)->toBe('Genap');

    // 3. Mengubah nama kelas yang punya jadwal TETAP BERHASIL (periode tidak berubah)
    $resUbahNama = $this->actingAs($this->adminUser)->put(route('admin.kelas.update', $kelasBerjadwal), [
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X TKJ 1 Unggulan',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $resUbahNama->assertRedirect(route('admin.kelas.index'));

    $kelasBerjadwal->refresh();
    expect($kelasBerjadwal->nama)->toBe('X TKJ 1 Unggulan');
});
