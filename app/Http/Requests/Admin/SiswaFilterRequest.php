<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
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
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'urut' => ['nullable', 'string', Rule::in(['nama_asc', 'nama_desc', 'nis', 'nama-az', 'nama-za'])],
        ];
    }

    /**
     * Custom message for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kelas_id.integer' => 'Pilihan kelas tidak valid.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'urut.in' => 'Pilihan urutan tidak valid.',
        ];
    }
}
