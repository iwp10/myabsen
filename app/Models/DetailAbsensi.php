<?php

namespace App\Models;

use App\Enums\StatusKehadiran;
use Database\Factories\DetailAbsensiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailAbsensi extends Model
{
    /** @use HasFactory<DetailAbsensiFactory> */
    use HasFactory;

    protected $table = 'detail_absensi';

    protected $fillable = ['sesi_absensi_id', 'siswa_id', 'status', 'keterangan'];

    protected function casts(): array
    {
        return [
            'status' => StatusKehadiran::class,
        ];
    }

    public function sesiAbsensi()
    {
        return $this->belongsTo(SesiAbsensi::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
