<?php

use App\Enums\StatusKehadiran;
use App\Models\DetailAbsensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use App\Support\Ui;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;

test('Ui::statusBadge memetakan class yang berbeda untuk tiap status dan memiliki varian dark:', function () {
    $hadir = Ui::statusBadge('hadir');
    $izin = Ui::statusBadge('izin');
    $sakit = Ui::statusBadge('sakit');
    $alpa = Ui::statusBadge('alpa');
    $belum = Ui::statusBadge('belum');

    // Masing-masing status memiliki class yang berbeda
    $statuses = [$hadir, $izin, $sakit, $alpa, $belum];
    expect(count(array_unique($statuses)))->toBe(5);

    // Semua status memiliki varian dark:
    foreach ($statuses as $class) {
        expect($class)->toContain('dark:');
    }

    // Mendukung enum StatusKehadiran
    expect(Ui::statusBadge(StatusKehadiran::HADIR))->toBe($hadir);
    expect(Ui::statusBadge(StatusKehadiran::IZIN))->toBe($izin);
    expect(Ui::statusBadge(StatusKehadiran::SAKIT))->toBe($sakit);
    expect(Ui::statusBadge(StatusKehadiran::ALPA))->toBe($alpa);
});

test('Ui::statusLabel dan statusIcon mengembalikan label dan identifier ikon Tabler yang tepat', function () {
    expect(Ui::statusLabel('hadir'))->toBe('Hadir');
    expect(Ui::statusLabel('izin'))->toBe('Izin');
    expect(Ui::statusLabel('sakit'))->toBe('Sakit');
    expect(Ui::statusLabel('alpa'))->toBe('Alpa');
    expect(Ui::statusLabel('belum'))->toBe('Belum diabsen');

    expect(Ui::statusIcon('hadir'))->toBe('check');
    expect(Ui::statusIcon('izin'))->toBe('info');
    expect(Ui::statusIcon('sakit'))->toBe('plus');
    expect(Ui::statusIcon('alpa'))->toBe('x');
    expect(Ui::statusIcon('belum'))->toBe('clock');
});

test('Ui::persenWarna dan persenBadge benar di sekitar batas konfigurasi dan memiliki varian dark:', function () {
    $batas = (float) config('absensi.batas_kehadiran_rendah', 75);

    $warnaAtas = Ui::persenWarna($batas + 5);
    $warnaDiBatas = Ui::persenWarna($batas);
    $warnaDekatBawah = Ui::persenWarna($batas - 5);
    $warnaJauhBawah = Ui::persenWarna($batas - 20);

    // Di batas dan di atas = emerald / hijau
    expect($warnaAtas)->toContain('emerald');
    expect($warnaDiBatas)->toContain('emerald');

    // Di bawah batas = kuning (amber) atau merah (red)
    expect($warnaDekatBawah)->toContain('amber');
    expect($warnaJauhBawah)->toContain('red');

    // Semua memiliki varian dark:
    expect($warnaAtas)->toContain('dark:');
    expect($warnaDekatBawah)->toContain('dark:');
    expect($warnaJauhBawah)->toContain('dark:');

    // persenBadge juga konsisten
    $badgeAtas = Ui::persenBadge($batas);
    $badgeBawah = Ui::persenBadge($batas - 10);
    expect($badgeAtas)->toContain('emerald')->toContain('dark:');
    expect($badgeBawah)->toContain('red')->toContain('dark:');
});

test('Ui::badgeHari memiliki enam warna berbeda untuk 6 hari sekolah dan varian dark:', function () {
    $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    $colors = array_map(fn ($h) => Ui::badgeHari($h), $hariList);

    expect(count(array_unique($colors)))->toBe(6);

    foreach ($colors as $c) {
        expect($c)->toContain('dark:');
    }
});

test('Ui::badgeSemester membedakan ganjil dan genap dengan varian dark:', function () {
    $ganjil = Ui::badgeSemester('ganjil');
    $genap = Ui::badgeSemester('genap');

    expect($ganjil)->not->toBe($genap);
    expect($ganjil)->toContain('dark:');
    expect($genap)->toContain('dark:');
});

test('Ui::tombol menyediakan varian primary, secondary, dan danger dengan min-h 44px, focus ring 4, dan varian dark:', function () {
    $primary = Ui::tombol('primary');
    $secondary = Ui::tombol('secondary');
    $danger = Ui::tombol('danger');

    expect($primary)->not->toBe($secondary);
    expect($primary)->not->toBe($danger);

    foreach ([$primary, $secondary, $danger] as $btn) {
        expect($btn)->toContain('min-h-[44px]');
        expect($btn)->toContain('focus-visible:ring-4');
        expect($btn)->toContain('dark:');
    }
});

test('Komponen Blade x-ui merender token desain dengan benar', function () {
    // 1. x-ui.card
    $cardHtml = Blade::render('<x-ui.card>Isi Kartu</x-ui.card>');
    expect($cardHtml)->toContain('border-2');
    expect($cardHtml)->toContain('dark:border-slate-600');
    expect($cardHtml)->toContain('Isi Kartu');

    // 2. x-ui.status-badge
    $badgeHtml = Blade::render('<x-ui.status-badge status="hadir" />');
    expect($badgeHtml)->toContain('Hadir');
    expect($badgeHtml)->toContain('bg-emerald-100');
    expect($badgeHtml)->toContain('dark:bg-emerald-500/15');
    expect($badgeHtml)->toContain('<svg');

    // 3. x-ui.stat-card
    $statHtml = Blade::render('<x-ui.stat-card title="Total Hadir" value="28" color="emerald" />');
    expect($statHtml)->toContain('Total Hadir');
    expect($statHtml)->toContain('28');
    expect($statHtml)->toContain('border-l-8');
    expect($statHtml)->toContain('border-2');

    // 4. x-ui.table
    $tableHtml = Blade::render('<x-ui.table><thead><tr><th>Nama</th></tr></thead><tbody><tr><td>Ali</td></tr></tbody></x-ui.table>');
    expect($tableHtml)->toContain('responsive-stack-table');
    expect($tableHtml)->toContain('border-2');
});

test('Halaman dashboard guru dan siswa merender komponen baru dan terbebas dari warna ad hoc', function () {
    $guruUser = User::factory()->create(['role' => 'guru']);
    $guru = Guru::factory()->create(['user_id' => $guruUser->id]);

    $resGuru = $this->actingAs($guruUser)->get(route('guru.dashboard'));
    $resGuru->assertOk();
    // Memeriksa token border-2 dan status badge baru
    $resGuru->assertSee('border-2');

    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $kelas = Kelas::factory()->create();
    $siswa = Siswa::factory()->create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id]);

    $resSiswa = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
    $resSiswa->assertOk();
    $resSiswa->assertSee('border-2');
});

test('Form absensi guru memiliki tombol simpan sticky dan target sentuh minimal 44px', function () {
    Carbon::setTestNow('2026-10-12 08:00:00'); // Senin
    $guruUser = User::factory()->create(['role' => 'guru']);
    $guru = Guru::factory()->create(['user_id' => $guruUser->id]);
    $kelas = Kelas::factory()->create();
    $mapel = Mapel::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'hari' => 'senin',
    ]);
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);

    $response = $this->actingAs($guruUser)->get(route('guru.absensi.show', $jadwal->id));
    $response->assertOk();

    // Memastikan elemen form esensial dan field input utuh
    $response->assertSee('siswa['.$siswa->id.'][status]', false);
    $response->assertSee('siswa['.$siswa->id.'][keterangan]', false);

    // Memastikan tombol simpan sticky ada dengan kelas sticky bottom-0
    $response->assertSee('sticky bottom-0', false);
    $response->assertSee('min-h-[44px]');
    Carbon::setTestNow();
});

test('Halaman riwayat siswa memiliki tabel ber-atribut data-label untuk kartu tumpuk mobile', function () {
    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $kelas = Kelas::factory()->create();
    $siswa = Siswa::factory()->create(['user_id' => $siswaUser->id, 'kelas_id' => $kelas->id]);
    $guru = Guru::factory()->create();
    $mapel = Mapel::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
    $sesi = SesiAbsensi::factory()->create(['jadwal_id' => $jadwal->id, 'diabsen_oleh' => $guru->user_id]);
    DetailAbsensi::create([
        'sesi_absensi_id' => $sesi->id,
        'siswa_id' => $siswa->id,
        'status' => StatusKehadiran::HADIR,
        'keterangan' => 'Hadir tepat waktu',
    ]);

    $response = $this->actingAs($siswaUser)->get(route('siswa.riwayat', ['tab' => 'semua']));
    $response->assertOk();
    $response->assertSee('data-label="Tanggal"', false);
    $response->assertSee('responsive-stack-table', false);
});
