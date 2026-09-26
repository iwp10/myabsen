<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    protected $kelas_id;

    public function __construct($kelas_id)
    {
        $this->kelas_id = $kelas_id;
    }

    public function model(array $row): Model|array|null
    {
        $user = User::create([
            'name' => $row['nama'],
            'username' => $row['nis'],
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);

        return new Siswa([
            'user_id' => $user->id,
            'nis' => $row['nis'],
            'kelas_id' => $this->kelas_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|unique:siswa,nis|unique:users,username',
            'nama' => 'required|string|max:255',
        ];
    }
}
