<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wilayah>
 */
class WilayahFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_wilayah' => fake()->city(),
            'tipe' => fake()->randomElement(['kecamatan', 'desa', 'kelurahan']),
            'kode_wilayah' => fake()->unique()->numerify('W###'),
        ];
    }
}
