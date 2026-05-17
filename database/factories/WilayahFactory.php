<?php

namespace Database\Factories;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wilayah>
 */
class WilayahFactory extends Factory
{
    protected $model = Wilayah::class;

    public function definition(): array
    {
        return [
            'nama_wilayah' => fake()->randomElement([
                'Desa Sukamaju',
                'Desa Mekarsari',
                'Kelurahan Cibadak',
                'Kecamatan Cibadak',
            ]),
            'tipe' => fake()->randomElement(['kecamatan', 'desa', 'kelurahan']),
            'kode_wilayah' => strtoupper(fake()->bothify('WIL-###')),
        ];
    }
}
