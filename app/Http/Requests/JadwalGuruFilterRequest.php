<?php

namespace App\Http\Requests;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Services\PeriodeService;
use Illuminate\Foundation\Http\FormRequest;

class JadwalGuruFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'guru';
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
            'kelas_id' => [
                'nullable',
                'integer',
                'exists:kelas,id',
                function ($attribute, $value, $fail) {
                    $guru = Guru::where('user_id', $this->user()->id)->first();
                    if (! $guru) {
                        return $fail('Data guru tidak ditemukan.');
                    }

                    $activePeriode = app(PeriodeService::class)->getActivePeriode();
                    $isTaughtByGuru = Jadwal::where('guru_id', $guru->id)
                        ->where('kelas_id', $value)
                        ->when($activePeriode['tahun_ajaran'], function ($q) use ($activePeriode) {
                            $q->where('tahun_ajaran', $activePeriode['tahun_ajaran'])
                                ->where('semester', $activePeriode['semester']);
                        })
                        ->exists();

                    if (! $isTaughtByGuru) {
                        $fail('Kelas yang dipilih tidak ditemukan pada jadwal mengajar Anda di periode aktif.');
                    }
                },
            ],
            'mapel_id' => [
                'nullable',
                'integer',
                'exists:mapel,id',
                function ($attribute, $value, $fail) {
                    $guru = Guru::where('user_id', $this->user()->id)->first();
                    if (! $guru) {
                        return $fail('Data guru tidak ditemukan.');
                    }

                    $activePeriode = app(PeriodeService::class)->getActivePeriode();
                    $isTaughtByGuru = Jadwal::where('guru_id', $guru->id)
                        ->where('mapel_id', $value)
                        ->when($activePeriode['tahun_ajaran'], function ($q) use ($activePeriode) {
                            $q->where('tahun_ajaran', $activePeriode['tahun_ajaran'])
                                ->where('semester', $activePeriode['semester']);
                        })
                        ->exists();

                    if (! $isTaughtByGuru) {
                        $fail('Mata pelajaran yang dipilih tidak ditemukan pada jadwal mengajar Anda di periode aktif.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'hari.in' => 'Hari yang dipilih tidak valid. Pilih antara Senin sampai Sabtu.',
            'kelas_id.integer' => 'ID kelas harus berupa angka.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak terdaftar dalam sistem.',
            'mapel_id.integer' => 'ID mata pelajaran harus berupa angka.',
            'mapel_id.exists' => 'Mata pelajaran yang dipilih tidak terdaftar dalam sistem.',
        ];
    }
}
