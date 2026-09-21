<?php

namespace App\Models;

use Database\Factories\JurusanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    /** @use HasFactory<JurusanFactory> */
    use HasFactory;

    protected $table = 'jurusan';

    protected $fillable = ['nama', 'kode'];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}
