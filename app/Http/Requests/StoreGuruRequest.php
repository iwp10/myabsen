<?php

namespace App\Http\Requests;

use App\Services\ArsipService;
use Illuminate\Foundation\Http\FormRequest;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:users,username'],
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('nip')) {
                if ($pesan = app(ArsipService::class)->cekDuplikatTerhapus('guru', (string) $this->nip)) {
                    $validator->errors()->forget('nip');
                    $validator->errors()->add('nip', $pesan);
                }
            }
        });
    }
}
