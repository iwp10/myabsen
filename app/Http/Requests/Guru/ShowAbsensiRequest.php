<?php

namespace App\Http\Requests\Guru;

use App\Models\Jadwal;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowAbsensiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $jadwal = $this->route('jadwal');
        $tanggal = $this->input('tanggal') ?: Carbon::now('Asia/Jakarta')->toDateString();

        return $jadwal instanceof Jadwal && $this->user()?->can('absen', [$jadwal, $tanggal]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $jadwal = $this->route('jadwal');

        return [
            'tanggal' => [
                'nullable',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($jadwal) {
                    if (! $value || ! ($jadwal instanceof Jadwal)) {
                        return;
                    }

                    $absensiService = app(AbsensiService::class);
                    $date = Carbon::parse($value, 'Asia/Jakarta');

                    if ($absensiService->isTanggalMasaDepan($date)) {
                        $fail('Tanggal absensi tidak boleh di masa depan.');

                        return;
                    }

                    if (! $absensiService->isTanggalDalamBatasKoreksiRole($this->user(), $date)) {
                        $fail('Tanggal absensi berada di luar batas waktu koreksi.');

                        return;
                    }

                    if ($this->user()?->role === 'guru') {
                        if (! $absensiService->isHariCocokDenganJadwal($jadwal, $date)) {
                            $fail('Hari pada tanggal yang dipilih tidak cocok dengan hari jadwal.');

                            return;
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ];
    }
}
