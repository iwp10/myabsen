<?php

namespace Database\Factories;

use App\Models\DetailAbsensi;
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
            'sesi_absensi_id' => \App\Models\SesiAbsensi::factory(),
            'siswa_id' => \App\Models\Siswa::factory(),
            'status' => \App\Enums\StatusKehadiran::HADIR,
        ];
    }
}
