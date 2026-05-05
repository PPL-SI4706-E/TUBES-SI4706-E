<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPengumumanTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin-pengumuman@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_pengumuman_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.pengumuman.index'));

        $response->assertOk();
        $response->assertSeeText('Pengumuman');
        $response->assertSeeText('Buat Pengumuman');
    }

    public function test_admin_can_create_update_and_delete_pengumuman(): void
    {
        $admin = $this->createAdmin();

        $storeResponse = $this->actingAs($admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Darurat Distribusi Air',
            'isi' => 'Aliran air akan dihentikan sementara untuk perbaikan pipa utama.',
            'kategori' => 'darurat',
            'tanggal_post' => '2026-05-05',
            'is_penting' => '1',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('pengumuman', [
            'judul' => 'Darurat Distribusi Air',
            'kategori' => 'darurat',
            'is_penting' => true,
        ]);

        $pengumumanId = \DB::table('pengumuman')->value('id');

        $updateResponse = $this->actingAs($admin)->put(route('admin.pengumuman.update', $pengumumanId), [
            'judul' => 'Jadwal Distribusi Air',
            'isi' => 'Pengiriman tangki air dilakukan hari Senin dan Kamis.',
            'kategori' => 'jadwal',
            'tanggal_post' => '2026-05-06',
            'is_penting' => '0',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('pengumuman', [
            'id' => $pengumumanId,
            'judul' => 'Jadwal Distribusi Air',
            'kategori' => 'jadwal',
            'is_penting' => false,
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.pengumuman.destroy', $pengumumanId));

        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('pengumuman', [
            'id' => $pengumumanId,
        ]);
    }

    public function test_home_prefers_database_pengumuman_when_available(): void
    {
        $admin = $this->createAdmin();

        \DB::table('pengumuman')->insert([
            'user_id' => $admin->id,
            'judul' => 'Info Tarif Baru Sambungan Air 2026',
            'isi' => 'Tarif sambungan air baru berlaku mulai April 2026.',
            'kategori' => 'info',
            'tanggal_post' => '2026-05-05',
            'is_penting' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText('Info Tarif Baru Sambungan Air 2026');
    }
}
