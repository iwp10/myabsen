<?php

namespace App\Policies;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;

class JadwalPolicy
{
    /**
     * Menentukan apakah user dapat melakukan absensi pada jadwal ini.
     */
    public function absen(User $user, Jadwal $jadwal): Response
    {
        if ($user->role === 'admin') {
            return Response::allow();
        }

        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();

            if (! $guru) {
                return Response::deny('Anda tidak terdaftar sebagai guru.');
            }

            if ($jadwal->guru_id !== $guru->id) {
                return Response::deny('Anda tidak mengajar jadwal ini.');
            }

            // Cek apakah hari jadwal cocok dengan hari ini di server
            $hariIni = Carbon::now('Asia/Jakarta');

            $hariMap = [
                1 => 'senin',
                2 => 'selasa',
                3 => 'rabu',
                4 => 'kamis',
                5 => 'jumat',
                6 => 'sabtu',
            ];

            $hariServer = $hariMap[$hariIni->dayOfWeek] ?? null;

            if ($jadwal->hari !== $hariServer) {
                return Response::deny('Anda hanya dapat mengabsen jadwal pada hari yang sama.');
            }

            return Response::allow();
        }

        return Response::deny('Akses ditolak.');
    }
}
