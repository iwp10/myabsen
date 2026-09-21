<?php

namespace App\Http\Requests\Guru;

use App\Enums\StatusKehadiran;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsensiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Dihandle oleh Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $jadwal = $this->route('jadwal');
        // Ambil ID siswa aktif di kelas tersebut
        $validSiswaIds = $jadwal->kelas->siswa()->pluck('id')->toArray();

        return [
            'catatan' => ['nullable', 'string', 'max:255'],
            'siswa' => ['nullable', 'array'],
            // Validasi kunci array (id siswa) harus merupakan bagian dari siswa kelas ini
            'siswa.*' => [
                function ($attribute, $value, $fail) use ($validSiswaIds) {
                    $siswaId = explode('.', $attribute)[1]; // format 'siswa.{id}'
                    if (! in_array($siswaId, $validSiswaIds)) {
                        $fail('Siswa yang diubah bukan anggota kelas pada jadwal ini.');
                    }
                },
            ],
            'siswa.*.status' => ['required', Rule::enum(StatusKehadiran::class)],
            'siswa.*.keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'siswa.*.status.required' => 'Status kehadiran wajib diisi.',
            'siswa.*.status.Illuminate\Validation\Rules\Enum' => 'Status kehadiran tidak valid.',
        ];
    }
}
