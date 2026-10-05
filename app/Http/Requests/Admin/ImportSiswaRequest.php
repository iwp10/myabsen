<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportSiswaRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk role admin.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi untuk impor siswa.
     */
    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'exists:kelas,id'],
            'file' => ['required', 'mimes:xlsx,csv,xls'],
        ];
    }

    /**
     * Pesan validasi kustom dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas tidak valid.',
            'file.required' => 'Berkas Excel wajib diunggah.',
            'file.mimes' => 'Berkas harus berupa file berekstensi xlsx, csv, atau xls.',
        ];
    }
}
