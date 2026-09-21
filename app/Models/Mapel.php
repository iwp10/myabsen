<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    /** @use HasFactory<\Database\Factories\MapelFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'mapel';

    protected $fillable = ['nama', 'kode'];

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}

