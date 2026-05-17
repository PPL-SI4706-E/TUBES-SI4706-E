<?php

namespace Database\Factories;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laporan>
 */
class LaporanFactory extends Factory
{
    protected $model = Laporan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->masyarakat(),
            'wilayah_id' => Wilayah::factory(),
            'kategori_laporan_id' => KategoriLaporan::factory(),
            'judul' => fake()->randomElement([
                'Air keruh sejak pagi',
                'Pipa bocor di depan rumah',
                'Aliran air tersumbat',
            ]),
            'deskripsi' => 'Air yang keluar dari keran berwarna cokelat dan berbau tanah.',
            'alamat' => fake()->randomElement([
                'Jl. Kenanga No. 7A',
                'Jl. Melati No. 12',
                'Jl. Raya Cibadak No. 5',
            ]),
            'foto' => null,
            'status' => 'pending',
            'tanggal_lapor' => now(),
            'catatan_admin' => null,
        ];
    }
}
