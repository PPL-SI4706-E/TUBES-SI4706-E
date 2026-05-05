<?php

namespace Tests\Feature;

use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPengumumanTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_admin_pengumuman_menampilkan_data_yang_ada(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        Pengumuman::query()->create([
            'user_id' => $admin->id,
            'judul' => 'Pemadaman Air Wilayah Cianjur',
            'isi' => 'Perbaikan pipa utama sedang berlangsung.',
            'kategori' => 'darurat',
            'is_penting' => true,
            'tanggal_post' => '2026-03-14',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pengumuman.index'));

        $response->assertOk();
        $response->assertSee('Pemadaman Air Wilayah Cianjur');
        $response->assertSee('Buat Pengumuman');
    }

    public function test_admin_bisa_membuat_pengumuman(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Jadwal Tangki Air Darurat',
            'isi' => 'Distribusi dilakukan hari Senin dan Kamis.',
            'kategori' => 'jadwal',
            'tanggal_post' => '2026-03-12',
            'is_penting' => '1',
        ]);

        $response->assertRedirect(route('admin.pengumuman.index'));

        $this->assertDatabaseHas('pengumuman', [
            'judul' => 'Jadwal Tangki Air Darurat',
            'kategori' => 'jadwal',
            'is_penting' => 1,
        ]);
    }
}
