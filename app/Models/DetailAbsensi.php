<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusKehadiran;

class DetailAbsensi extends Model
{
    /** @use HasFactory<\Database\Factories\DetailAbsensiFactory> */
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

