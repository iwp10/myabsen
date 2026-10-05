<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodeRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk role admin.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi pembaruan periode aktif.
     */
    public function rules(): array
    {
        return [
            'tahun_ajaran' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/^(\d{4})\/(\d{4})$/', $value, $matches)) {
                        $y1 = (int) $matches[1];
                        $y2 = (int) $matches[2];
                        if ($y2 !== $y1 + 1) {
                            $fail('Format tahun ajaran tidak valid. Tahun kedua harus merupakan tahun pertama ditambah 1.');
                        }
                    }
                },
            ],
            'semester' => ['required', 'string', 'in:Ganjil,Genap'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => 'Format tahun ajaran harus berupa YYYY/YYYY (contoh: 2026/2027).',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester hanya boleh bernilai Ganjil atau Genap.',
        ];
    }
}
