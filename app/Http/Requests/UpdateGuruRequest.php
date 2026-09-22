<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nip' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('users', 'username')->ignore($this->guru->user_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama guru wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
        ];
    }
}
