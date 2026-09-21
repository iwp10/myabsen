<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiAbsensi extends Model
{
    /** @use HasFactory<\Database\Factories\SesiAbsensiFactory> */
    use HasFactory;

    protected $table = 'sesi_absensi';

    protected $fillable = ['jadwal_id', 'tanggal', 'diabsen_oleh', 'diubah_oleh', 'catatan'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function detailAbsensi()
    {
        return $this->hasMany(DetailAbsensi::class);
    }

    public function diabsenOleh()
    {
        return $this->belongsTo(User::class, 'diabsen_oleh');
    }

    public function diubahOleh()
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}

