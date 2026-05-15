<?php

namespace Database\Factories;

use App\Models\KategoriLaporan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laporan>
 */
class LaporanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->masyarakat(),
            'wilayah_id' => Wilayah::factory(),
            'kategori_laporan_id' => KategoriLaporan::factory(),
            'judul' => fake()->sentence(4),
            'deskripsi' => fake()->paragraph(),
            'alamat' => fake()->streetAddress(),
            'foto' => null,
            'status' => 'pending',
            'tanggal_lapor' => fake()->dateTimeBetween('-6 months', 'now'),
            'catatan_admin' => null,
        ];
    }
}
