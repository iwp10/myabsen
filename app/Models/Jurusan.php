<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    /** @use HasFactory<\Database\Factories\JurusanFactory> */
    use HasFactory;

    protected $table = 'jurusan';

    protected $fillable = ['nama', 'kode'];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}

