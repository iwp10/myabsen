<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    /**
     * Memeriksa apakah akun pengguna sudah tidak aktif karena profil Guru atau Siswa di-soft-delete.
     * Akun yang profilnya tidak ada sama sekali (bukan trashed) tidak dianggap tidak aktif.
     */
    public function sudahTidakAktif(): bool
    {
        if ($this->role === 'guru') {
            return Guru::onlyTrashed()->where('user_id', $this->id)->exists();
        }

        if ($this->role === 'siswa') {
            return Siswa::onlyTrashed()->where('user_id', $this->id)->exists();
        }

        return false;
    }
}
