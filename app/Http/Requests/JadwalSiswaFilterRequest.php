<?php

namespace App\Http\Requests;

use App\Models\Jadwal;
use App\Services\PeriodeService;
use Illuminate\Foundation\Http\FormRequest;

class JadwalSiswaFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'siswa';
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('hari')) {
            $this->merge(['hari' => strtolower((string) $this->input('hari'))]);
        }
    }

    public function rules(): array
    {
        return [
            'hari' => ['nullable', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'mapel_id' => [
                'nullable',
                'integer',
                'exists:mapel,id',
                function ($attribute, $value, $fail) {
                    $siswa = $this->user()?->siswa;
                    if (! $siswa || ! $siswa->kelas_id) {
                        return $fail('Data kelas siswa tidak ditemukan.');
                    }

                    $activePeriode = app(PeriodeService::class)->getActivePeriode();
                    $isClassSubject = Jadwal::where('kelas_id', $siswa->kelas_id)
                        ->where('mapel_id', $value)
                        ->when($activePeriode['tahun_ajaran'], function ($q) use ($activePeriode) {
                            $q->where('tahun_ajaran', $activePeriode['tahun_ajaran'])
                                ->where('semester', $activePeriode['semester']);
                        })
                        ->exists();

                    if (! $isClassSubject) {
                        $fail('Mata pelajaran yang dipilih tidak terdaftar pada jadwal kelas Anda di periode aktif.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'hari.in' => 'Hari yang dipilih tidak valid. Pilih antara Senin sampai Sabtu.',
            'mapel_id.integer' => 'ID mata pelajaran harus berupa angka.',
            'mapel_id.exists' => 'Mata pelajaran yang dipilih tidak terdaftar dalam sistem.',
        ];
    }
}
