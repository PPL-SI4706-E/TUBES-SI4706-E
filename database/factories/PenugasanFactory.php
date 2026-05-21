<?php

namespace Database\Factories;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenugasanFactory extends Factory
{
    protected $model = \App\Models\Penugasan::class;
    public function definition(): array
    {
        return [
            'laporan_id'        => Laporan::factory(),
            'user_id'           => User::factory(['role' => 'petugas']),
            'tanggal_penugasan' => now()->toDateString(),
            'foto_bukti'        => null,
            'status_tugas'      => 'Ditugaskan',
            'catatan_admin'     => null,
        ];
    }
}
