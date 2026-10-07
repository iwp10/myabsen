<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use App\Services\ArsipService;
use App\Services\PasswordAwalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    protected $kelas_id;

    protected $passwordAwal;

    public function __construct($kelas_id, ?string $passwordAwal = null)
    {
        $this->kelas_id = $kelas_id;
        $this->passwordAwal = $passwordAwal ?? PasswordAwalService::get();
    }

    public function model(array $row): Model|array|null
    {
        $user = User::create([
            'name' => $row['nama'],
            'username' => $row['nis'],
            'password' => Hash::make($this->passwordAwal),
            'role' => 'siswa',
            'must_change_password' => true,
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            foreach ($data as $index => $row) {
                if (isset($row['nis']) && $validator->errors()->has("{$index}.nis")) {
                    if ($pesan = app(ArsipService::class)->cekDuplikatTerhapus('siswa', (string) $row['nis'])) {
                        $validator->errors()->forget("{$index}.nis");
                        $validator->errors()->add("{$index}.nis", $pesan);
                    }
                }
            }
        });
    }
}
