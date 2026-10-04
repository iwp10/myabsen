<?php

namespace App\Models;

use Database\Factories\JadwalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    /** @use HasFactory<JadwalFactory> */
    use HasFactory;

    protected $table = 'jadwal';

    protected $fillable = ['kelas_id', 'mapel_id', 'guru_id', 'hari', 'jam_mulai', 'jam_selesai', 'tahun_ajaran', 'semester'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class)->withTrashed();
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class)->withTrashed();
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class)->withTrashed();
    }

    public function sesiAbsensi()
    {
        return $this->hasMany(SesiAbsensi::class);
    }
}
