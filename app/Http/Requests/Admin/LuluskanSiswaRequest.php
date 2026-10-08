<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LuluskanSiswaRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk role admin.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi pelulusan siswa.
     */
    public function rules(): array
    {
        return [
            'kelas_id' => [
                'required',
                'integer',
                Rule::exists('kelas', 'id')->whereNull('deleted_at'),
            ],
            'siswa_ids' => ['required', 'array', 'min:1', 'max:500'],
            'siswa_ids.*' => [
                'required',
                'integer',
                Rule::exists('siswa', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.integer' => 'ID kelas harus berupa angka.',
            'kelas_id.exists' => 'Kelas tidak ditemukan atau sudah tidak aktif.',
            'siswa_ids.required' => 'Pilih minimal satu siswa untuk diluluskan.',
            'siswa_ids.array' => 'Data siswa yang dipilih tidak valid.',
            'siswa_ids.min' => 'Pilih minimal satu siswa untuk diluluskan.',
            'siswa_ids.max' => 'Maksimal 500 siswa yang dapat diluluskan sekaligus.',
            'siswa_ids.*.required' => 'Siswa yang dipilih tidak valid.',
            'siswa_ids.*.integer' => 'ID siswa harus berupa angka.',
            'siswa_ids.*.exists' => 'Salah satu siswa yang dipilih tidak ditemukan atau sudah tidak aktif.',
        ];
    }
}
