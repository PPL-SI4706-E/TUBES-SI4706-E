<?php

namespace Tests\Feature;

use App\Models\TestimoniPublik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTestimoniPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_menyetujui_testimoni_pending(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $testimoni = TestimoniPublik::query()->create([
            'nama' => 'Nina',
            'email' => 'nina@example.com',
            'pesan' => 'Mohon tampil setelah approve.',
            'status' => 'pending',
            'session_token' => 'admin-review-token',
            'editable_until' => now()->addMinutes(5),
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.testimoni.approve', $testimoni));

        $response->assertRedirect(route('admin.testimoni.index'));

        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoni->id,
            'status' => 'approved',
        ]);
    }

    public function test_halaman_admin_menampilkan_daftar_testimoni(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        TestimoniPublik::query()->create([
            'nama' => 'Joko',
            'email' => null,
            'pesan' => 'Butuh ditinjau admin',
            'status' => 'pending',
            'session_token' => 'list-token',
            'editable_until' => now()->addMinutes(5),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.testimoni.index'));

        $response->assertOk();
        $response->assertSee('Butuh ditinjau admin');
    }
}
