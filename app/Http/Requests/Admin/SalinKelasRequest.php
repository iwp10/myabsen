<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SalinKelasRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk role admin.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi salin kelas.
     */
    public function rules(): array
    {
        return [
            'periode_asal' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}-(Ganjil|Genap)$/',
            ],
            'tahun_ajaran_tujuan' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/^(\d{4})\/(\d{4})$/', $value, $matches)) {
                        $y1 = (int) $matches[1];
                        $y2 = (int) $matches[2];
                        if ($y2 !== $y1 + 1) {
                            $fail('Format tahun ajaran tujuan tidak valid. Tahun kedua harus merupakan tahun pertama ditambah 1.');
                        }
                    }
                },
            ],
            'semester_tujuan' => ['required', 'string', 'in:Ganjil,Genap'],
            'kelas_ids' => ['required', 'array', 'min:1', 'max:100'],
            'kelas_ids.*' => ['required', 'integer', 'exists:kelas,id'],
        ];
    }

    /**
     * Validasi tambahan setelah aturan dasar lulus.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $periodeAsal = $this->input('periode_asal');
            $tahunTujuan = $this->input('tahun_ajaran_tujuan');
            $semesterTujuan = $this->input('semester_tujuan');

            if ($periodeAsal && $tahunTujuan && $semesterTujuan) {
                $periodeTujuan = "{$tahunTujuan}-{$semesterTujuan}";
                if ($periodeAsal === $periodeTujuan) {
                    $validator->errors()->add('tahun_ajaran_tujuan', 'Periode tujuan harus berbeda dari periode asal.');
                }
            }
        });
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'periode_asal.required' => 'Periode asal wajib dipilih.',
            'periode_asal.regex' => 'Format periode asal tidak valid.',
            'tahun_ajaran_tujuan.required' => 'Tahun ajaran tujuan wajib diisi.',
            'tahun_ajaran_tujuan.regex' => 'Format tahun ajaran tujuan harus berupa YYYY/YYYY (contoh: 2026/2027).',
            'semester_tujuan.required' => 'Semester tujuan wajib dipilih.',
            'semester_tujuan.in' => 'Semester tujuan hanya boleh bernilai Ganjil atau Genap.',
            'kelas_ids.required' => 'Pilih minimal satu kelas untuk disalin.',
            'kelas_ids.array' => 'Data kelas yang dipilih tidak valid.',
            'kelas_ids.min' => 'Pilih minimal satu kelas untuk disalin.',
            'kelas_ids.max' => 'Maksimal 100 kelas yang dapat disalin sekaligus.',
            'kelas_ids.*.required' => 'Kelas yang dipilih tidak valid.',
            'kelas_ids.*.integer' => 'ID kelas harus berupa angka.',
            'kelas_ids.*.exists' => 'Salah satu kelas yang dipilih tidak ditemukan.',
        ];
    }
}
