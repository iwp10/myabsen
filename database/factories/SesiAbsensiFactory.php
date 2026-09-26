<?php

namespace Database\Factories;

use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use App\Models\User;
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
            'jadwal_id' => Jadwal::factory(),
            'tanggal' => $this->faker->date(),
            'diabsen_oleh' => User::factory(),
        ];
    }
}
