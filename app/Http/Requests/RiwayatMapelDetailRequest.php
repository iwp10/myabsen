<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiwayatMapelDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'siswa';
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('periode') && str_contains($this->input('periode'), '|')) {
            [$ta, $sm] = explode('|', $this->input('periode'), 2);
            if (! $this->filled('tahun_ajaran')) {
                $this->merge(['tahun_ajaran' => $ta]);
            }
            if (! $this->filled('semester')) {
                $this->merge(['semester' => $sm]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'periode' => ['nullable', 'string', 'max:30'],
            'tahun_ajaran' => [
                'nullable',
                'string',
                'regex:/^\d{4}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/^(\d{4})\/(\d{4})$/', $value, $matches)) {
                        if ((int) $matches[2] !== (int) $matches[1] + 1) {
                            $fail('Format tahun ajaran tidak valid. Tahun kedua harus merupakan tahun pertama ditambah 1.');
                        }
                    }
                },
            ],
            'semester' => ['nullable', 'string', 'in:Ganjil,Genap'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY.',
            'semester.in' => 'Semester harus Ganjil atau Genap.',
        ];
    }
}
