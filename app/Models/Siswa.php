<?php

namespace App\Models;

use Database\Factories\SiswaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Siswa extends Model
{
    /** @use HasFactory<SiswaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'siswa';

    protected $fillable = ['user_id', 'kelas_id', 'nis'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function detailAbsensi()
    {
        return $this->hasMany(DetailAbsensi::class);
    }

    public function getIsNonaktifAttribute(): bool
    {
        return $this->trashed();
    }

    public function getNamaSiswaAttribute(): string
    {
        return $this->user?->name ?? '-';
    }

    public function getNamaLaporanAttribute(): string
    {
        return self::formatNamaLaporan($this->nama_siswa, $this->is_nonaktif);
    }

    public static function formatNamaLaporan(string $nama, bool $isNonaktif): string
    {
        return $isNonaktif ? "{$nama} (nonaktif)" : $nama;
    }

    public function scopeUntukLaporan($query, int $kelasId, array|Collection $sesiIds = [])
    {
        $sesiIdsArray = $sesiIds instanceof Collection ? $sesiIds->all() : (array) $sesiIds;

        return $query->withTrashed()
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->where('siswa.kelas_id', $kelasId)
            ->where(function ($q) use ($sesiIdsArray) {
                $q->whereNull('siswa.deleted_at');
                if (! empty($sesiIdsArray)) {
                    $q->orWhere(function ($sub) use ($sesiIdsArray) {
                        $sub->whereNotNull('siswa.deleted_at')
                            ->whereExists(function ($dq) use ($sesiIdsArray) {
                                $dq->select(DB::raw(1))
                                    ->from('detail_absensi')
                                    ->whereColumn('detail_absensi.siswa_id', 'siswa.id')
                                    ->whereIn('detail_absensi.sesi_absensi_id', $sesiIdsArray);
                            });
                    });
                }
            })
            ->select('siswa.*')
            ->with('user')
            ->orderBy('users.name', 'asc');
    }
}
