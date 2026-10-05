<?php

namespace App\Http\Requests;

use App\Models\Jadwal;
use App\Support\KelasMapel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportLaporanRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        // Pembatasan guru (AB-08): hanya boleh mengekspor kelas yang diampunya
        if ($user->role === 'guru') {
            $guru = $user->guru;
            if (! $guru) {
                return false;
            }

            if ($this->filled('kelas_mapel')) {
                $parsed = KelasMapel::parse($this->input('kelas_mapel'));
                if ($parsed) {
                    return Jadwal::where('guru_id', $guru->id)
                        ->where('kelas_id', $parsed['kelas_id'])
                        ->where('mapel_id', $parsed['mapel_id'])
                        ->exists();
                }

                // Format tidak valid diloloskan ke aturan validasi (rules) agar menghasilkan error validasi
                return true;
            }

            if ($this->filled('kelas_id') || $this->filled('mapel_id')) {
                $query = Jadwal::where('guru_id', $guru->id);
                if ($this->filled('kelas_id')) {
                    $query->where('kelas_id', $this->input('kelas_id'));
                }
                if ($this->filled('mapel_id')) {
                    $query->where('mapel_id', $this->input('mapel_id'));
                }

                return $query->exists();
            }
        }

        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jurusan_id' => ['nullable', 'integer', 'exists:jurusan,id'], // Untuk Admin
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'], // Untuk Admin
            'mapel_id' => ['nullable', 'integer', 'exists:mapel,id'], // Untuk Admin
            'kelas_mapel' => ['nullable', 'string', 'regex:/^[1-9]\d*-[1-9]\d*$/'],
            'bulan' => ['nullable', 'date_format:Y-m'],
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'tahun_ajaran' => [
                'nullable',
                'string',
                'regex:/^\d{4}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/^(\d{4})\/(\d{4})$/', $value, $matches)) {
                        if ((int) $matches[2] !== (int) $matches[1] + 1) {
                            $fail('Format tahun ajaran tidak valid. Tahun kedua harus merupakan tahun pertama ditambah 1.');
                        }
                    }
                },
            ],
            'semester' => ['nullable', 'string', 'in:Ganjil,Genap'],
            'hari' => ['nullable', 'array'],
            'hari.*' => ['in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'mode' => ['nullable', 'in:data,template'],
            'jumlah_pertemuan' => ['nullable', 'integer', 'min:1', 'max:60'],
        ];
    }
}
