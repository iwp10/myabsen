<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'integer', 'min:1', 'max:13'], // SMK biasanya sampai tingkat 12 atau 13
            'tahun_ajaran' => ['required', 'string', 'max:9'], // misalnya "2023/2024"
        ];
    }
}
