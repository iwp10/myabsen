<?php

namespace Database\Factories;

use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jurusan_id' => Jurusan::factory(),
            'nama' => $this->faker->numerify('X RPL #'),
            'tingkat' => 'X',
            'tahun_ajaran' => '2026/2027',
        ];
    }
}
