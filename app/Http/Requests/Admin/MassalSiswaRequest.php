<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MassalSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('siswa', 'id')->whereNull('deleted_at'),
            ],
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
            'ids.required' => 'Pilih minimal satu siswa.',
            'ids.array' => 'Data pilihan siswa tidak valid.',
            'ids.min' => 'Pilih minimal satu siswa.',
            'ids.max' => 'Maksimal 100 siswa yang dapat diproses sekaligus.',
            'ids.*.required' => 'ID siswa wajib diisi.',
            'ids.*.integer' => 'ID siswa harus berupa angka.',
            'ids.*.distinct' => 'Terdapat duplikasi data siswa yang dipilih.',
            'ids.*.exists' => 'Siswa yang dipilih tidak valid atau sudah terhapus.',
        ];
    }
}
