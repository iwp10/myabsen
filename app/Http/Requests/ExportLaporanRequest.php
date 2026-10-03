<?php

namespace App\Http\Requests;

use App\Models\Jadwal;
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
        if ($user->role === 'guru' && $this->filled('kelas_mapel')) {
            $guru = $user->guru;
            if (! $guru) {
                return false;
            }

            $parts = explode('-', $this->input('kelas_mapel'));
            if (count($parts) === 2) {
                return \App\Models\Jadwal::where('guru_id', $guru->id)
                    ->where('kelas_id', $parts[0])
                    ->where('mapel_id', $parts[1])
                    ->exists();
            }
            return false;
        }

        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'], // Untuk Admin
            'mapel_id' => ['nullable', 'integer', 'exists:mapel,id'], // Untuk Admin
            'kelas_mapel' => ['nullable', 'string'],
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'bulan' => ['nullable', 'date_format:Y-m'],
            'hari' => ['nullable', 'array'],
            'hari.*' => ['in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'mode' => ['nullable', 'in:data,template'],
            'jumlah_pertemuan' => ['nullable', 'integer', 'min:1', 'max:60'],
        ];
    }
}
