<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class JadwalFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Prepare inputs for validation.
     */
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

        if ($this->filled('jam_dari') && ! $this->filled('jam_mulai_dari')) {
            $this->merge(['jam_mulai_dari' => $this->input('jam_dari')]);
        }

        if ($this->filled('jam_sampai') && ! $this->filled('jam_mulai_sampai')) {
            $this->merge(['jam_mulai_sampai' => $this->input('jam_sampai')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'periode' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'in:Ganjil,Genap'],
            'hari' => ['nullable', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'guru_id' => ['nullable', 'integer', 'exists:guru,id'],
            'mapel_id' => ['nullable', 'integer', 'exists:mapel,id'],
            'jam_mulai_dari' => ['nullable', 'date_format:H:i'],
            'jam_mulai_sampai' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $jamDari = $this->input('jam_mulai_dari');
                    if ($jamDari && $value && $jamDari > $value) {
                        $fail('Jam awal tidak boleh melebihi jam akhir.');
                    }
                },
            ],
            'jam_dari' => ['nullable', 'date_format:H:i'],
            'jam_sampai' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hari.in' => 'Pilihan hari tidak valid. Pilih antara Senin sampai Sabtu.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'guru_id.exists' => 'Guru yang dipilih tidak ditemukan.',
            'mapel_id.exists' => 'Mata pelajaran yang dipilih tidak ditemukan.',
            'jam_mulai_dari.date_format' => 'Format jam mulai awal harus HH:MM (contoh: 07:00).',
            'jam_mulai_sampai.date_format' => 'Format jam mulai akhir harus HH:MM (contoh: 12:00).',
            'jam_dari.date_format' => 'Format jam awal harus HH:MM (contoh: 07:00).',
            'jam_sampai.date_format' => 'Format jam akhir harus HH:MM (contoh: 12:00).',
        ];
    }
}
