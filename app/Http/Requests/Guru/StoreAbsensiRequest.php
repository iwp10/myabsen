<?php

namespace App\Http\Requests\Guru;

use App\Enums\StatusKehadiran;
use App\Models\Jadwal;
use App\Services\AbsensiService;
use Carbon\Carbon;
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
        $jadwal = $this->route('jadwal');
        $tanggal = $this->input('tanggal') ?: Carbon::now('Asia/Jakarta')->toDateString();

        return $jadwal instanceof Jadwal && $this->user()?->can('absen', [$jadwal, $tanggal]);
    }

    /**
     * Prepare inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('tanggal') || empty($this->input('tanggal'))) {
            $this->merge([
                'tanggal' => Carbon::now('Asia/Jakarta')->toDateString(),
            ]);
        }
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
            'catatan' => ['nullable', 'string', 'max:255'],
            'dari' => ['nullable', 'in:dashboard,jadwal,koreksi'],
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
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'siswa.*.status.required' => 'Status kehadiran wajib diisi.',
            'siswa.*.status.Illuminate\Validation\Rules\Enum' => 'Status kehadiran tidak valid.',
        ];
    }
}
