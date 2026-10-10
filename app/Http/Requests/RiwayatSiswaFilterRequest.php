<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiwayatSiswaFilterRequest extends FormRequest
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

        if ($this->filled('status')) {
            $this->merge(['status' => strtolower((string) $this->input('status'))]);
        }
    }

    public function rules(): array
    {
        return [
            'tab' => ['nullable', 'string', 'in:per_mapel,semua'],
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
            'tanggal' => ['nullable', 'date'],
            'bulan' => ['nullable', 'string', 'date_format:Y-m'],
            'mapel_id' => ['nullable', 'integer', 'exists:mapel,id'],
            'status' => ['nullable', 'string', 'in:hadir,izin,sakit,alpa'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY.',
            'semester.in' => 'Semester harus Ganjil atau Genap.',
            'bulan.date_format' => 'Format filter bulan tidak valid (harus YYYY-MM).',
            'status.in' => 'Status kehadiran tidak valid. Pilih antara Hadir, Izin, Sakit, atau Alpa.',
        ];
    }
}
