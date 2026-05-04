<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDashboardTestimoniFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin TirtaBantu',
            'email' => 'admin.dashboard@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_dashboard_shows_pending_testimoni_summary(): void
    {
        if (! Schema::hasTable('testimoni_publik')) {
            $this->markTestIncomplete('Table testimoni_publik belum tersedia.');
        }

        \DB::table('testimoni_publik')->insert([
            [
                'nama' => 'Rina',
                'pesan' => 'Mohon segera dicek.',
                'status_validasi' => 'pending',
                'editable_until' => now()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Dodi',
                'pesan' => 'Layanan cukup baik.',
                'status_validasi' => 'pending',
                'editable_until' => now()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Alya',
                'pesan' => 'Sudah tayang.',
                'status_validasi' => 'disetujui',
                'editable_until' => now()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->createAdmin())->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Testimoni Menunggu Validasi', false);
        $response->assertSee('2', false);
        $response->assertSee(route('admin.testimoni.index'), false);
    }

    public function test_admin_sidebar_shows_pending_testimoni_badge(): void
    {
        if (! Schema::hasTable('testimoni_publik')) {
            $this->markTestIncomplete('Table testimoni_publik belum tersedia.');
        }

        \DB::table('testimoni_publik')->insert([
            'nama' => 'Banu',
            'pesan' => 'Perlu validasi.',
            'status_validasi' => 'pending',
            'editable_until' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createAdmin())->get(route('admin.testimoni.index'));

        $response->assertOk();
        $response->assertSee('Testimoni Publik', false);
        $response->assertSee('1', false);
    }
}
