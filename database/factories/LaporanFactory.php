<?php

namespace Database\Factories;

use App\Models\KategoriLaporan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanFactory extends Factory
{
    protected $model = \App\Models\Laporan::class;
    public function definition(): array
    {
        // Buat atau pakai ulang record FK agar tidak melanggar foreign key constraint
        $kategori = KategoriLaporan::first() ?? KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'deskripsi'     => 'Kategori test',
            'tarif'         => 0,
            'icon'          => '🔧',
            'is_active'     => true,
        ]);

        $wilayah = Wilayah::first() ?? Wilayah::create([
            'nama_wilayah'  => 'Wilayah Test',
            'tipe'          => 'kelurahan',
            'kode_wilayah'  => 'WT01',
        ]);

        $userId = User::inRandomOrder()->value('id') ?? User::factory()->create()->id;

        return [
            'user_id'             => $userId,
            'wilayah_id'          => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'judul'               => fake()->sentence(4),
            'deskripsi'           => fake()->paragraph(),
            'alamat'              => fake()->streetAddress() . ', RT 01/RW 02, Kel. ' . fake()->city(),
            'status'              => 'dikerjakan',
            'tanggal_lapor'       => now()->subDays(rand(1, 30)),
            'catatan_admin'       => null,
        ];
    }
}
