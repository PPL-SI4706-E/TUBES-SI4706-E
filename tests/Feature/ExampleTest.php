<?php

namespace Tests\Feature;

use App\Models\Pengumuman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Pengumuman::query()->create([
            'user_id' => \App\Models\User::factory()->create([
                'role' => 'admin',
                'is_active' => true,
            ])->id,
            'judul' => 'Informasi Sambungan Baru',
            'isi' => 'Pendaftaran dapat dilakukan melalui aplikasi.',
            'kategori' => 'info',
            'is_penting' => false,
            'tanggal_post' => '2026-03-10',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Informasi Sambungan Baru');
    }
}
