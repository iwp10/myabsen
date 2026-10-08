<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PindahkanSiswaRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk role admin.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi pemindahan siswa.
     */
    public function rules(): array
    {
        return [
            'kelas_asal_id' => [
                'required',
                'integer',
                Rule::exists('kelas', 'id')->whereNull('deleted_at'),
            ],
            'kelas_tujuan_id' => [
                'required',
                'integer',
                'different:kelas_asal_id',
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
            'kelas_asal_id.required' => 'Kelas asal wajib dipilih.',
            'kelas_asal_id.integer' => 'ID kelas asal harus berupa angka.',
            'kelas_asal_id.exists' => 'Kelas asal tidak ditemukan atau sudah tidak aktif.',
            'kelas_tujuan_id.required' => 'Kelas tujuan wajib dipilih.',
            'kelas_tujuan_id.integer' => 'ID kelas tujuan harus berupa angka.',
            'kelas_tujuan_id.different' => 'Kelas tujuan harus berbeda dari kelas asal.',
            'kelas_tujuan_id.exists' => 'Kelas tujuan tidak ditemukan atau sudah tidak aktif.',
            'siswa_ids.required' => 'Pilih minimal satu siswa untuk dipindahkan.',
            'siswa_ids.array' => 'Data siswa yang dipilih tidak valid.',
            'siswa_ids.min' => 'Pilih minimal satu siswa untuk dipindahkan.',
            'siswa_ids.max' => 'Maksimal 500 siswa yang dapat dipindahkan sekaligus.',
            'siswa_ids.*.required' => 'Siswa yang dipilih tidak valid.',
            'siswa_ids.*.integer' => 'ID siswa harus berupa angka.',
            'siswa_ids.*.exists' => 'Salah satu siswa yang dipilih tidak ditemukan atau sudah terhapus.',
        ];
    }
}
