<?php

namespace App\Models;

use App\Support\Ui;
use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    /** @use HasFactory<KelasFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'kelas';

    protected $fillable = ['jurusan_id', 'nama', 'tingkat', 'tahun_ajaran', 'semester'];

    public function getLabelLengkapAttribute(): string
    {
        return Ui::labelKelas($this);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}
