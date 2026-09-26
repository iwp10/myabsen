<?php

namespace Database\Factories;

use App\Enums\StatusKehadiran;
use App\Models\DetailAbsensi;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailAbsensi>
 */
class DetailAbsensiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sesi_absensi_id' => SesiAbsensi::factory(),
            'siswa_id' => Siswa::factory(),
            'status' => StatusKehadiran::HADIR,
        ];
    }
}
