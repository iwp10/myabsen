<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AktifkanSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_ajaran.required' => 'Tahun ajaran wajib ditentukan.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid.',
        ];
    }
}
