<?php

namespace Database\Seeders;

use App\Enums\StatusKehadiran;
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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $userGuru = User::create([
            'name' => 'Guru Satu',
            'username' => 'guru1',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);

        $userSiswa = User::create([
            'name' => 'Siswa Satu',
            'username' => 'siswa1',
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);

        // 2. Master Data
        $jurusan = Jurusan::create(['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL']);
        $kelas = Kelas::create([
            'jurusan_id' => $jurusan->id,
            'nama' => '10 RPL 1',
            'tingkat' => '10',
            'tahun_ajaran' => '2026/2027',
        ]);

        $guru = Guru::create(['user_id' => $userGuru->id, 'nip' => '198001012000011001']);

        $siswa = Siswa::create(['user_id' => $userSiswa->id, 'kelas_id' => $kelas->id, 'nis' => '1001']);

        // Buat beberapa siswa tambahan untuk demo
        for ($i = 2; $i <= 5; $i++) {
            $u = User::create([
                'name' => 'Siswa '.$i,
                'username' => 'siswa'.$i,
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ]);
            Siswa::create(['user_id' => $u->id, 'kelas_id' => $kelas->id, 'nis' => '100'.$i]);
        }

        $mapel = Mapel::create(['nama' => 'Pemrograman Dasar', 'kode' => 'PD']);

        // 3. Jadwal (Senin - Sabtu)
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $jadwals = [];
        foreach ($hari as $h) {
            $jadwals[] = Jadwal::create([
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'guru_id' => $guru->id,
                'hari' => $h,
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '09:00:00',
                'tahun_ajaran' => '2026/2027',
            ]);
        }

        // 4. Riwayat Absensi (Beberapa hari ke belakang)
        $allSiswa = Siswa::where('kelas_id', $kelas->id)->get();
        $statuses = [StatusKehadiran::HADIR, StatusKehadiran::IZIN, StatusKehadiran::SAKIT, StatusKehadiran::ALPA];

        for ($i = 1; $i <= 5; $i++) { // 5 hari ke belakang
            $tanggal = Carbon::now()->subDays($i);

            // Skip jika hari minggu
            if ($tanggal->isSunday()) {
                continue;
            }

            $hariIndo = [
                1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 0 => 'minggu',
            ];
            $hariIni = $hariIndo[$tanggal->dayOfWeek];

            $jadwalHariIni = collect($jadwals)->firstWhere('hari', $hariIni);

            if ($jadwalHariIni) {
                $sesi = SesiAbsensi::create([
                    'jadwal_id' => $jadwalHariIni->id,
                    'tanggal' => $tanggal->toDateString(),
                    'diabsen_oleh' => $userGuru->id,
                ]);

                foreach ($allSiswa as $s) {
                    // Random status, tapi dominan Hadir (contoh: 70% hadir)
                    $rand = rand(1, 100);
                    if ($rand <= 70) {
                        $status = StatusKehadiran::HADIR;
                    } else {
                        $status = $statuses[array_rand($statuses)];
                    }

                    DetailAbsensi::create([
                        'sesi_absensi_id' => $sesi->id,
                        'siswa_id' => $s->id,
                        'status' => $status,
                        'keterangan' => $status === StatusKehadiran::HADIR ? null : 'Keterangan demo',
                    ]);
                }
            }
        }
    }
}
