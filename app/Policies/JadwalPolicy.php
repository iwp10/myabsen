<?php

namespace App\Policies;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\User;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;

class JadwalPolicy
{
    public function __construct(protected AbsensiService $absensiService) {}

    /**
     * Menentukan apakah user dapat melakukan absensi pada jadwal ini.
     */
    public function absen(User $user, Jadwal $jadwal, Carbon|string|null $tanggal = null): Response
    {
        $tanggalObj = $tanggal
            ? ($tanggal instanceof Carbon ? $tanggal->copy()->setTimezone('Asia/Jakarta') : Carbon::parse($tanggal, 'Asia/Jakarta'))
            : Carbon::now('Asia/Jakarta');

        // Tanggal masa depan ditolak untuk semua role
        if ($this->absensiService->isTanggalMasaDepan($tanggalObj)) {
            return Response::deny('Tidak dapat mengabsen pada tanggal di masa depan.');
        }

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

            if (! $this->absensiService->isHariCocokDenganJadwal($jadwal, $tanggalObj)) {
                return Response::deny('Hari pada tanggal yang dipilih tidak cocok dengan hari jadwal.');
            }

            if (! $this->absensiService->isTanggalDalamBatasKoreksiRole($user, $tanggalObj)) {
                return Response::deny('Tanggal absensi berada di luar batas waktu koreksi.');
            }

            return Response::allow();
        }

        return Response::deny('Akses ditolak.');
    }

    /**
     * Menentukan apakah user (guru) dapat melihat riwayat absensi pada jadwal ini.
     */
    public function viewRiwayat(User $user, Jadwal $jadwal): Response
    {
        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();

            if (! $guru) {
                return Response::deny('Anda tidak terdaftar sebagai guru.');
            }

            if ($jadwal->guru_id !== $guru->id) {
                return Response::deny('Anda tidak mengajar jadwal ini.');
            }

            return Response::allow();
        }

        return Response::deny('Akses ditolak.');
    }
}
