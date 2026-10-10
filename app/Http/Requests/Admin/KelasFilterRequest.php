<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasFilterRequest extends FormRequest
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
            'periode' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'in:Ganjil,Genap'],
            'tingkat' => [
                'nullable',
                'string',
                Rule::in(['10', '11', '12', '13', 'X', 'XI', 'XII', 'XIII']),
            ],
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
            'tingkat.in' => 'Pilihan tingkat tidak valid.',
            'semester.in' => 'Pilihan semester tidak valid.',
        ];
    }
}
