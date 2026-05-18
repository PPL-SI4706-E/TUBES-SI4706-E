<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KategoriLaporan>
 */
class KategoriLaporanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_kategori' => fake()->unique()->words(2, true),
            'deskripsi' => fake()->sentence(),
            'tarif' => fake()->randomElement([0, 25000, 50000, 75000]),
            'icon' => 'droplet',
            'is_active' => true,
        ];
    }
}
