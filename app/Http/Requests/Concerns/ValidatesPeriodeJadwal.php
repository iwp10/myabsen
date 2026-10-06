<?php

namespace App\Http\Requests\Concerns;

use App\Models\Kelas;
use Illuminate\Validation\Validator;

trait ValidatesPeriodeJadwal
{
    /**
     * Validasi bahwa periode jadwal harus sama dengan periode kelas (AB-06).
     */
    protected function validatePeriodeSamaDenganKelas(Validator $validator): void
    {
        if (! $this->filled('kelas_id')) {
            return;
        }

        $kelas = Kelas::find($this->input('kelas_id'));
        if (! $kelas) {
            return;
        }

        $pesan = "Periode jadwal harus sama dengan periode kelas ({$kelas->semester} {$kelas->tahun_ajaran}).";

        if ($this->filled('tahun_ajaran') && $this->input('tahun_ajaran') !== $kelas->tahun_ajaran) {
            $validator->errors()->add('tahun_ajaran', $pesan);
        }

        if ($this->filled('semester') && $this->input('semester') !== $kelas->semester) {
            $validator->errors()->add('semester', $pesan);
        }
    }
}
