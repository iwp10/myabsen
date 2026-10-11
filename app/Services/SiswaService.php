<?php

namespace App\Services;

use App\Models\DetailAbsensi;
use App\Models\Siswa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SiswaService
{
    /**
     * Menghapus satu data siswa.
     *
     * Jika siswa memiliki riwayat absensi, dilakukan soft delete dan akun user dipertahankan.
     * Jika belum memiliki riwayat absensi, dilakukan hard delete pada siswa dan akun user-nya.
     * Jika akun user terkait memiliki constraint FK (misal diabsen_oleh pada sesi),
     * penghapusan ditolak dan transaksi di-rollback.
     *
     * @return array{status: 'berhasil'|'ditolak', jenis: 'nonaktif'|'permanen'|null, pesan: string}
     */
    public function hapus(Siswa $siswa): array
    {
        $hasHistory = DetailAbsensi::where('siswa_id', $siswa->id)->exists();

        if ($hasHistory) {
            $siswa->delete();

            return [
                'status' => 'berhasil',
                'jenis' => 'nonaktif',
                'pesan' => 'Siswa di-soft-delete karena memiliki riwayat absensi.',
            ];
        }

        try {
            DB::transaction(function () use ($siswa) {
                $user = $siswa->user;
                $siswa->forceDelete();
                $user?->delete();
            });

            return [
                'status' => 'berhasil',
                'jenis' => 'permanen',
                'pesan' => 'Siswa beserta akun berhasil dihapus.',
            ];
        } catch (QueryException $e) {
            return [
                'status' => 'ditolak',
                'jenis' => null,
                'pesan' => 'Akun tidak dapat dihapus karena memiliki riwayat absensi.',
            ];
        }
    }

    /**
     * Mereset password satu siswa ke password awal dan mengaktifkan flag must_change_password.
     *
     * @return array{status: 'berhasil'|'ditolak', pesan: string}
     *
     * @throws RuntimeException Bila password awal di environment produksi belum aman
     */
    public function resetPassword(Siswa $siswa, ?string $passwordAwal = null): array
    {
        $password = $passwordAwal ?? PasswordAwalService::get();

        $siswa->user->update([
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        return [
            'status' => 'berhasil',
            'pesan' => 'Password siswa berhasil direset ke password awal.',
        ];
    }
}
