<?php

namespace App\Http\Requests;

use App\Models\Kelas;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'integer', 'min:1', 'max:13'],
            'tahun_ajaran' => ['required', 'string', 'max:9'],
            'semester' => ['required', 'in:Ganjil,Genap'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $kelas = $this->route('kelas');
            if (! $kelas instanceof Kelas) {
                return;
            }

            $tahunBaru = (string) $this->input('tahun_ajaran');
            $semesterBaru = (string) $this->input('semester');

            if ($tahunBaru !== $kelas->tahun_ajaran || $semesterBaru !== $kelas->semester) {
                $jumlahJadwal = $kelas->jadwal()
                    ->where('tahun_ajaran', $kelas->tahun_ajaran)
                    ->where('semester', $kelas->semester)
                    ->count();

                if ($jumlahJadwal > 0) {
                    $pesan = "Kelas ini sudah punya {$jumlahJadwal} jadwal pada periode {$kelas->semester} {$kelas->tahun_ajaran}. Pindahkan atau hapus jadwal tersebut sebelum mengubah periode kelas.";
                    if ($tahunBaru !== $kelas->tahun_ajaran) {
                        $validator->errors()->add('tahun_ajaran', $pesan);
                    }
                    if ($semesterBaru !== $kelas->semester) {
                        $validator->errors()->add('semester', $pesan);
                    }
                }
            }
        });
    }
}
