<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PergantianSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester_asal' => ['required', 'string', 'in:Ganjil'],
            'salin_kelas' => ['nullable', 'boolean'],
            'pindah_siswa' => ['nullable', 'boolean'],
            'salin_jadwal' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'semester_asal.in' => 'Pergantian otomatis hanya untuk Ganjil ke Genap pada tahun ajaran yang sama. Untuk tahun ajaran baru, gunakan tab Salin kelas, Pindahkan siswa, dan Luluskan siswa.',
            'semester_asal.required' => 'Semester asal wajib dipilih.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib dipilih.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid (contoh: 2026/2027).',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $salinKelas = $this->boolean('salin_kelas', false);
            $pindahSiswa = $this->boolean('pindah_siswa', false);
            $salinJadwal = $this->boolean('salin_jadwal', false);

            if (! $salinKelas && ! $pindahSiswa && ! $salinJadwal) {
                $validator->errors()->add('opsi', 'Minimal satu opsi tindakan (Salin Kelas, Pindahkan Siswa, atau Salin Jadwal) harus dipilih.');
            }

            $ta = $this->input('tahun_ajaran');
            if ($ta && str_contains($ta, '/')) {
                [$t1, $t2] = explode('/', $ta, 2);
                if ((int) $t2 !== (int) $t1 + 1) {
                    $validator->errors()->add('tahun_ajaran', 'Tahun ajaran tidak konsisten (tahun kedua harus tepat satu tahun setelah tahun pertama).');
                }
            }
        });
    }
}
