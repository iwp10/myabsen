<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJadwalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'required|exists:guru,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran' => 'required|string|max:255',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->jam_mulai || !$this->jam_selesai || !$this->hari) return;

            $overlapGuru = \App\Models\Jadwal::where('guru_id', $this->guru_id)
                ->where('hari', $this->hari)
                ->where(function ($q) {
                    $q->where('jam_mulai', '<', $this->jam_selesai)
                      ->where('jam_selesai', '>', $this->jam_mulai);
                });
            
            if ($overlapGuru->exists()) {
                $validator->errors()->add('guru_id', 'Guru sudah memiliki jadwal di hari dan jam yang beririsan.');
            }
            
            $overlapKelas = \App\Models\Jadwal::where('kelas_id', $this->kelas_id)
                ->where('hari', $this->hari)
                ->where(function ($q) {
                    $q->where('jam_mulai', '<', $this->jam_selesai)
                      ->where('jam_selesai', '>', $this->jam_mulai);
                });
            
            if ($overlapKelas->exists()) {
                $validator->errors()->add('kelas_id', 'Kelas sudah memiliki jadwal di hari dan jam yang beririsan.');
            }
        });
    }
}
