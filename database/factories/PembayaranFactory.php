<?php

namespace Database\Factories;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pembayaran>
 */
class PembayaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'laporan_id' => Laporan::factory(),
            'user_id' => User::factory()->masyarakat(),
            'harga' => fake()->randomElement([25000, 50000, 75000]),
            'metode_pembayaran' => null,
            'qr_code_generate' => null,
            'bukti_transaksi' => null,
            'status_pembayaran' => 'Menunggu',
        ];
    }

    public function lunas(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_pembayaran' => 'Lunas',
        ]);
    }

    public function menungguVerifikasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_pembayaran' => 'Terverifikasi',
        ]);
    }
}
