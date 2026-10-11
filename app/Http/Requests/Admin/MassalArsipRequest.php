<?php

namespace App\Http\Requests\Admin;

use App\Services\ArsipService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MassalArsipRequest extends FormRequest
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
        $jenis = (string) $this->route('jenis');
        $validJenis = ArsipService::WHITELIST;

        if (! in_array($jenis, $validJenis, true)) {
            abort(404);
        }

        return [
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists($jenis, 'id')->whereNotNull('deleted_at'),
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
            'ids.required' => 'Pilih minimal satu data yang akan dipulihkan.',
            'ids.array' => 'Data pilihan tidak valid.',
            'ids.min' => 'Pilih minimal satu data yang akan dipulihkan.',
            'ids.max' => 'Maksimal 100 data yang dapat diproses sekaligus.',
            'ids.*.required' => 'ID data wajib diisi.',
            'ids.*.integer' => 'ID data harus berupa angka.',
            'ids.*.distinct' => 'Terdapat duplikasi data yang dipilih.',
            'ids.*.exists' => 'Data yang dipilih tidak ditemukan dalam data terhapus.',
        ];
    }
}
