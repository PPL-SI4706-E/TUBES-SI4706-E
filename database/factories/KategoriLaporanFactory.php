<?php

namespace Database\Factories;

use App\Models\KategoriLaporan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KategoriLaporan>
 */
class KategoriLaporanFactory extends Factory
{
    protected $model = KategoriLaporan::class;

    public function definition(): array
    {
        return [
            'nama_kategori' => fake()->randomElement([
                'Air Keruh / Berbau',
                'Pipa Bocor',
                'Pipa Tersumbat',
                'Sambungan Baru',
            ]),
            'deskripsi' => fake()->randomElement([
                'Laporan terkait kualitas air keruh atau berbau.',
                'Laporan mengenai kebocoran pipa distribusi air.',
                'Laporan mengenai saluran air yang tersumbat.',
            ]),
            'tarif' => fake()->randomElement([0, 25000, 50000]),
            'icon' => fake()->randomElement(['droplet', 'wrench', 'ban', 'house']),
            'is_active' => true,
        ];
    }
}
