<?php

namespace Database\Factories;

use App\Models\SesiAbsensi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SesiAbsensi>
 */
class SesiAbsensiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jadwal_id' => \App\Models\Jadwal::factory(),
            'tanggal' => $this->faker->date(),
            'diabsen_oleh' => \App\Models\User::factory(),
        ];
    }
}
