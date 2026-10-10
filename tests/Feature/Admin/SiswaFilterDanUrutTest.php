<?php

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->siswaUser = User::factory()->create(['role' => 'siswa']);

    $this->jurusan = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);

    $this->kelasA = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'X RPL 1',
        'tingkat' => 10,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);

    $this->kelasB = Kelas::create([
        'jurusan_id' => $this->jurusan->id,
        'nama' => 'XI RPL 1',
        'tingkat' => 11,
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
    ]);
});

test('AB-06: filter kelas hanya memuat siswa kelas yang dipilih', function () {
    $u1 = User::factory()->create(['name' => 'Ahmad Kelas A']);
    $s1 = Siswa::create(['user_id' => $u1->id, 'nis' => '1001', 'kelas_id' => $this->kelasA->id]);

    $u2 = User::factory()->create(['name' => 'Budi Kelas B']);
    $s2 = Siswa::create(['user_id' => $u2->id, 'nis' => '1002', 'kelas_id' => $this->kelasB->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['kelas_id' => $this->kelasA->id]));

    $response->assertStatus(200);
    $response->assertSee('Ahmad Kelas A');
    $response->assertDontSee('Budi Kelas B');
    $response->assertSee('Menampilkan 1 siswa');
});

test('AB-06: urutan nama A-Z dan Z-A benar dengan huruf campuran dan awalan sama', function () {
    // Siapkan nama dengan awalan sama dan variasi huruf besar/kecil
    $u1 = User::factory()->create(['name' => 'andi pratama']);
    $s1 = Siswa::create(['user_id' => $u1->id, 'nis' => '2001', 'kelas_id' => $this->kelasA->id]);

    $u2 = User::factory()->create(['name' => 'Andi Setiawan']);
    $s2 = Siswa::create(['user_id' => $u2->id, 'nis' => '2002', 'kelas_id' => $this->kelasA->id]);

    $u3 = User::factory()->create(['name' => 'BAMBANG KURNIA']);
    $s3 = Siswa::create(['user_id' => $u3->id, 'nis' => '2003', 'kelas_id' => $this->kelasA->id]);

    // Uji A-Z (bawaan)
    $resAZ = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['urut' => 'nama_asc']));
    $resAZ->assertStatus(200);
    $siswasAZ = $resAZ->viewData('siswas');
    $namesAZ = $siswasAZ->pluck('user.name')->all();

    expect(strtolower($namesAZ[0]))->toBe('andi pratama');
    expect(strtolower($namesAZ[1]))->toBe('andi setiawan');
    expect(strtolower($namesAZ[2]))->toBe('bambang kurnia');

    // Uji Z-A
    $resZA = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['urut' => 'nama_desc']));
    $resZA->assertStatus(200);
    $siswasZA = $resZA->viewData('siswas');
    $namesZA = $siswasZA->pluck('user.name')->all();

    expect(strtolower($namesZA[0]))->toBe('bambang kurnia');
    expect(strtolower($namesZA[1]))->toBe('andi setiawan');
    expect(strtolower($namesZA[2]))->toBe('andi pratama');
});

test('AB-06: urut NIS mengurutkan siswa berdasarkan nomor induk', function () {
    $u1 = User::factory()->create(['name' => 'Zahra']);
    Siswa::create(['user_id' => $u1->id, 'nis' => '9000', 'kelas_id' => $this->kelasA->id]);

    $u2 = User::factory()->create(['name' => 'Adam']);
    Siswa::create(['user_id' => $u2->id, 'nis' => '1000', 'kelas_id' => $this->kelasA->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['urut' => 'nis']));
    $response->assertStatus(200);

    $siswas = $response->viewData('siswas');
    expect($siswas->first()->nis)->toBe('1000');
    expect($siswas->last()->nis)->toBe('9000');
});

test('AB-06: filter kelas dan urut bekerja bersama pencarian dan paginasi membawa parameter', function () {
    for ($i = 1; $i <= 12; $i++) {
        $pad = str_pad($i, 2, '0', STR_PAD_LEFT);
        $u = User::factory()->create(['name' => "Siswa Khusus {$pad}"]);
        Siswa::create([
            'user_id' => $u->id,
            'nis' => "NIS{$pad}",
            'kelas_id' => $this->kelasA->id,
        ]);
    }

    // Siswa di kelas lain dengan nama mirip
    $uOther = User::factory()->create(['name' => 'Siswa Khusus 99']);
    Siswa::create(['user_id' => $uOther->id, 'nis' => 'NIS99', 'kelas_id' => $this->kelasB->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.siswa.index', [
        'search' => 'Khusus',
        'kelas_id' => $this->kelasA->id,
        'urut' => 'nama_asc',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Menampilkan 12 siswa');
    $response->assertDontSee('NIS99');

    // Cek bahwa link paginasi membawa parameter pencarian, filter kelas, dan urutan
    $response->assertSee('search=Khusus');
    $response->assertSee('kelas_id='.$this->kelasA->id);
    $response->assertSee('urut=nama_asc');
});

test('AB-06: kelas_id tidak ada atau urut tidak valid memberi error validasi', function () {
    // kelas_id tidak ada di tabel kelas
    $resKelas = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['kelas_id' => 99999]));
    $resKelas->assertSessionHasErrors('kelas_id');

    // urut tidak valid
    $resUrut = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['urut' => 'acak_invalid']));
    $resUrut->assertSessionHasErrors('urut');
});

test('AB-06: jumlah query tidak bertambah proporsional dengan jumlah siswa', function () {
    for ($i = 1; $i <= 10; $i++) {
        $u = User::factory()->create(['name' => "Siswa {$i}"]);
        Siswa::create([
            'user_id' => $u->id,
            'nis' => "NIS{$i}",
            'kelas_id' => $this->kelasA->id,
        ]);
    }

    DB::enableQueryLog();
    $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
    $response->assertStatus(200);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Query harus tetap terikat konstan (maksimal 15 query untuk layout, session, auth, siswa, relasi eager-load)
    expect($queryCount)->toBeLessThan(15);
});

test('AB-06: guru dan siswa tetap ditolak 403 saat mengakses kelola siswa', function () {
    $this->actingAs($this->guru)
        ->get(route('admin.siswa.index'))
        ->assertStatus(403);

    $this->actingAs($this->siswaUser)
        ->get(route('admin.siswa.index'))
        ->assertStatus(403);
});
