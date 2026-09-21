<?php

namespace App\Models;

use Database\Factories\GuruFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guru extends Model
{
    /** @use HasFactory<GuruFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'guru';

    protected $fillable = ['user_id', 'nip'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}
