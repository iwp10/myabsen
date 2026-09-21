<?php

namespace Database\Factories;

use App\Models\Jadwal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jadwal>
 */
class JadwalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kelas_id' => \App\Models\Kelas::factory(),
            'mapel_id' => \App\Models\Mapel::factory(),
            'guru_id' => \App\Models\Guru::factory(),
            'hari' => $this->faker->randomElement(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']),
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
            'tahun_ajaran' => '2026/2027',
        ];
    }
}
