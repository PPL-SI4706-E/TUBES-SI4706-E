<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPengumumanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Pengumuman',
            'email' => 'admin.pengumuman@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_pengumuman_index(): void
    {
        $response = $this->actingAs($this->createAdmin())->get(route('admin.pengumuman.index'));

        $response->assertOk();
        $response->assertSee('Pengumuman');
    }

    public function test_admin_can_create_pengumuman(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Info Gangguan Distribusi Air',
            'isi' => 'Akan ada gangguan distribusi air besok pagi untuk perawatan pipa utama.',
            'kategori' => 'darurat',
            'is_penting' => '1',
            'tanggal_post' => '2026-05-05',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pengumuman', [
            'judul' => 'Info Gangguan Distribusi Air',
            'user_id' => $admin->id,
            'kategori' => 'darurat',
            'is_penting' => 1,
        ]);
    }

    public function test_admin_can_update_pengumuman(): void
    {
        $admin = $this->createAdmin();

        $id = \DB::table('pengumuman')->insertGetId([
            'user_id' => $admin->id,
            'judul' => 'Judul Lama',
            'isi' => 'Isi lama pengumuman untuk warga.',
            'tanggal_post' => '2026-05-05',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.pengumuman.update', $id), [
            'judul' => 'Judul Baru',
            'isi' => 'Isi pengumuman telah diperbarui untuk warga.',
            'kategori' => 'jadwal',
            'tanggal_post' => '2026-05-06',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pengumuman', [
            'id' => $id,
            'judul' => 'Judul Baru',
            'kategori' => 'jadwal',
        ]);
        $this->assertStringStartsWith('2026-05-06', (string) \DB::table('pengumuman')->where('id', $id)->value('tanggal_post'));
    }

    public function test_admin_can_delete_pengumuman(): void
    {
        $admin = $this->createAdmin();

        $id = \DB::table('pengumuman')->insertGetId([
            'user_id' => $admin->id,
            'judul' => 'Akan Dihapus',
            'isi' => 'Pengumuman ini akan dihapus.',
            'tanggal_post' => '2026-05-05',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.pengumuman.destroy', $id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('pengumuman', ['id' => $id]);
    }

    public function test_home_reads_pengumuman_from_database(): void
    {
        $admin = $this->createAdmin();

        \DB::table('pengumuman')->insert([
            [
                'user_id' => $admin->id,
                'judul' => 'Pengumuman Database 1',
                'isi' => 'Isi pengumuman pertama dari database.',
                'kategori' => 'darurat',
                'is_penting' => true,
                'tanggal_post' => '2026-05-05',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'user_id' => $admin->id,
                'judul' => 'Pengumuman Database 2',
                'isi' => 'Isi pengumuman kedua dari database.',
                'kategori' => 'informasi',
                'is_penting' => false,
                'tanggal_post' => '2026-05-04',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Pengumuman Database 1');
        $response->assertSee('Pengumuman Database 2');
        $response->assertSee('PENTING');
    }

    public function test_public_can_open_pengumuman_detail_from_database(): void
    {
        $admin = $this->createAdmin();

        $id = \DB::table('pengumuman')->insertGetId([
            'user_id' => $admin->id,
            'judul' => 'Detail Pengumuman',
            'isi' => 'Isi detail pengumuman yang harus tampil penuh di halaman publik.',
            'kategori' => 'informasi',
            'is_penting' => false,
            'tanggal_post' => '2026-05-05',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('pengumuman.detail', $id));

        $response->assertOk();
        $response->assertSee('Detail Pengumuman');
        $response->assertSee('Isi detail pengumuman yang harus tampil penuh di halaman publik.');
    }
}
