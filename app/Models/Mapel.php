<?php

namespace App\Models;

use Database\Factories\MapelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    /** @use HasFactory<MapelFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'mapel';

    protected $fillable = ['nama', 'kode'];

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}
