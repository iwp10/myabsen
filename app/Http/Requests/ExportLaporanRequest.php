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
        if ($user->role === 'guru' && $this->filled('kelas_id')) {
            $guru = $user->guru;
            if (! $guru) {
                return false;
            }

            return Jadwal::where('guru_id', $guru->id)
                ->where('kelas_id', $this->input('kelas_id'))
                ->exists();
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
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'mapel_id' => ['nullable', 'integer', 'exists:mapel,id'],
            'bulan' => ['nullable', 'date_format:Y-m'],
        ];
    }
}
