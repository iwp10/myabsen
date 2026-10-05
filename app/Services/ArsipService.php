<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ArsipService
{
    public const WHITELIST = ['guru', 'siswa', 'kelas', 'mapel'];

    public function isValidJenis(string $jenis): bool
    {
        return in_array($jenis, self::WHITELIST, true);
    }

    public function getCounts(): array
    {
        return [
            'guru' => Guru::onlyTrashed()->count(),
            'siswa' => Siswa::onlyTrashed()->count(),
            'kelas' => Kelas::onlyTrashed()->count(),
            'mapel' => Mapel::onlyTrashed()->count(),
        ];
    }

    public function getDaftar(string $jenis, ?string $search = null): LengthAwarePaginator
    {
        if (! $this->isValidJenis($jenis)) {
            abort(404);
        }

        return match ($jenis) {
            'guru' => Guru::onlyTrashed()
                ->with('user')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nip', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'siswa' => Siswa::onlyTrashed()
                ->with(['user', 'kelas' => fn ($q) => $q->withTrashed()->with('jurusan')])
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nis', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'kelas' => Kelas::onlyTrashed()
                ->with('jurusan')
                ->when($search, function ($query, $search) {
                    $query->where('nama', 'like', "%{$search}%");
                })
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'mapel' => Mapel::onlyTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),
        };
    }

    public function pulihkan(string $jenis, int $id): array
    {
        if (! $this->isValidJenis($jenis)) {
            abort(404);
        }

        $model = match ($jenis) {
            'guru' => Guru::onlyTrashed()->with('user')->find($id),
            'siswa' => Siswa::onlyTrashed()->with(['user', 'kelas' => fn ($q) => $q->withTrashed()])->find($id),
            'kelas' => Kelas::onlyTrashed()->with('jurusan')->find($id),
            'mapel' => Mapel::onlyTrashed()->find($id),
        };

        if (! $model) {
            abort(404);
        }

        // Pengecekan sebelum restore
        if ($jenis === 'siswa') {
            $kelas = Kelas::withTrashed()->find($model->kelas_id);
            if (! $kelas || $kelas->trashed()) {
                $namaKelas = $kelas ? $kelas->nama : 'terkait';

                return [
                    'success' => false,
                    'message' => "Kelas {$namaKelas} masih terhapus. Pulihkan kelas tersebut lebih dulu.",
                ];
            }

            if (Siswa::whereNull('deleted_at')->where('nis', $model->nis)->exists()
                || User::where('username', $model->nis)->where('id', '!=', $model->user_id)->exists()) {
                return [
                    'success' => false,
                    'message' => "NIS {$model->nis} sudah digunakan oleh siswa aktif lain.",
                ];
            }
        } elseif ($jenis === 'guru') {
            if ($model->nip && (Guru::whereNull('deleted_at')->where('nip', $model->nip)->exists()
                || User::where('username', $model->nip)->where('id', '!=', $model->user_id)->exists())) {
                return [
                    'success' => false,
                    'message' => "NIP {$model->nip} sudah digunakan oleh guru aktif lain.",
                ];
            }
        } elseif ($jenis === 'mapel') {
            if (Mapel::whereNull('deleted_at')->where('kode', $model->kode)->exists()) {
                return [
                    'success' => false,
                    'message' => "Kode {$model->kode} sudah digunakan oleh mata pelajaran aktif lain.",
                ];
            }
        } elseif ($jenis === 'kelas') {
            if (! $model->jurusan_id || ! Jurusan::find($model->jurusan_id)) {
                return [
                    'success' => false,
                    'message' => 'Jurusan untuk kelas ini sudah tidak ada.',
                ];
            }

            if (Kelas::whereNull('deleted_at')
                ->where('nama', $model->nama)
                ->where('tahun_ajaran', $model->tahun_ajaran)
                ->where('semester', $model->semester)
                ->exists()) {
                return [
                    'success' => false,
                    'message' => "Kelas {$model->nama} untuk periode {$model->tahun_ajaran} semester {$model->semester} sudah ada dan aktif.",
                ];
            }
        }

        DB::transaction(function () use ($model) {
            $model->restore();
        });

        $nama = match ($jenis) {
            'guru' => $model->user?->name ?? $model->nip,
            'siswa' => $model->user?->name ?? $model->nis,
            'kelas' => $model->nama,
            'mapel' => $model->nama,
        };

        $label = ucfirst($jenis);

        return [
            'success' => true,
            'message' => "{$label} {$nama} berhasil dipulihkan.",
        ];
    }

    public function cekDuplikatTerhapus(string $jenis, string $value): ?string
    {
        if ($jenis === 'guru') {
            $trashed = Guru::onlyTrashed()
                ->where(function ($q) use ($value) {
                    $q->where('nip', $value)
                        ->orWhereHas('user', fn ($u) => $u->where('username', $value));
                })
                ->with('user')
                ->first();

            if ($trashed) {
                $nama = $trashed->user?->name ?? $trashed->nip;

                return "NIP ini milik guru yang sudah dihapus ({$nama}). Pulihkan lewat menu Data Terhapus.";
            }
        }

        if ($jenis === 'siswa') {
            $trashed = Siswa::onlyTrashed()
                ->where(function ($q) use ($value) {
                    $q->where('nis', $value)
                        ->orWhereHas('user', fn ($u) => $u->where('username', $value));
                })
                ->with('user')
                ->first();

            if ($trashed) {
                $nama = $trashed->user?->name ?? $trashed->nis;

                return "NIS ini milik siswa yang sudah dihapus ({$nama}). Pulihkan lewat menu Data Terhapus.";
            }
        }

        if ($jenis === 'mapel') {
            $trashed = Mapel::onlyTrashed()
                ->where('kode', $value)
                ->first();

            if ($trashed) {
                return "Kode ini milik mata pelajaran yang sudah dihapus ({$trashed->nama}). Pulihkan lewat menu Data Terhapus.";
            }
        }

        return null;
    }
}
