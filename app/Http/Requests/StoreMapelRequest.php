<?php

namespace App\Http\Requests;

use App\Services\ArsipService;
use Illuminate\Foundation\Http\FormRequest;

class StoreMapelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:255', 'unique:mapel,kode'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('kode')) {
                if ($pesan = app(ArsipService::class)->cekDuplikatTerhapus('mapel', (string) $this->kode)) {
                    $validator->errors()->forget('kode');
                    $validator->errors()->add('kode', $pesan);
                }
            }
        });
    }
}
