<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicTestimoniFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_public_testimoni(): void
    {
        $response = $this->post(route('testimoni.store'), [
            'nama' => 'Ayu Lestari',
            'email' => 'ayu@example.com',
            'pesan' => 'Pelayanannya cepat dan informatif.',
        ]);

        $response->assertRedirect(route('home') . '#testimoni');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('testimoni_publik', [
            'nama' => 'Ayu Lestari',
            'email' => 'ayu@example.com',
            'pesan' => 'Pelayanannya cepat dan informatif.',
            'status_validasi' => 'pending',
        ]);
    }

    public function test_home_only_shows_approved_testimoni(): void
    {
        if (! Schema::hasTable('testimoni_publik')) {
            $this->markTestIncomplete('Table testimoni_publik belum tersedia.');
        }

        \DB::table('testimoni_publik')->insert([
            [
                'nama' => 'Dina',
                'email' => null,
                'pesan' => 'Sudah bagus.',
                'status_validasi' => 'disetujui',
                'editable_until' => now()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Rudi',
                'email' => null,
                'pesan' => 'Masih menunggu validasi.',
                'status_validasi' => 'pending',
                'editable_until' => now()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Sudah bagus.');
        $response->assertDontSee('Masih menunggu validasi.');
    }

    public function test_guest_can_update_own_testimoni_within_five_minutes(): void
    {
        if (! Schema::hasTable('testimoni_publik')) {
            $this->markTestIncomplete('Table testimoni_publik belum tersedia.');
        }

        $id = \DB::table('testimoni_publik')->insertGetId([
            'nama' => 'Bima',
            'email' => null,
            'pesan' => 'Pesan awal.',
            'status_validasi' => 'pending',
            'edit_token' => Hash::make('token-edit'),
            'editable_until' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->withSession(["testimoni_guest.$id" => 'token-edit'])
            ->put(route('testimoni.update', $id), [
                'nama' => 'Bima',
                'email' => 'bima@example.com',
                'pesan' => 'Pesan revisi.',
            ]);

        $response->assertRedirect(route('home') . '#kelola-testimoni');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $id,
            'email' => 'bima@example.com',
            'pesan' => 'Pesan revisi.',
        ]);
    }

    public function test_guest_cannot_update_after_edit_window_expires(): void
    {
        if (! Schema::hasTable('testimoni_publik')) {
            $this->markTestIncomplete('Table testimoni_publik belum tersedia.');
        }

        Carbon::setTestNow(now());

        $id = \DB::table('testimoni_publik')->insertGetId([
            'nama' => 'Sari',
            'email' => null,
            'pesan' => 'Pesan awal.',
            'status_validasi' => 'pending',
            'edit_token' => Hash::make('token-expired'),
            'editable_until' => now()->subMinute(),
            'created_at' => now()->subMinutes(6),
            'updated_at' => now()->subMinutes(6),
        ]);

        $response = $this
            ->withSession(["testimoni_guest.$id" => 'token-expired'])
            ->from(route('home'))
            ->put(route('testimoni.update', $id), [
                'nama' => 'Sari',
                'email' => null,
                'pesan' => 'Pesan baru.',
            ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('testimoni');

        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $id,
            'pesan' => 'Pesan awal.',
        ]);

        Carbon::setTestNow();
    }

    public function test_guest_can_delete_own_testimoni_within_five_minutes(): void
    {
        if (! Schema::hasTable('testimoni_publik')) {
            $this->markTestIncomplete('Table testimoni_publik belum tersedia.');
        }

        $id = \DB::table('testimoni_publik')->insertGetId([
            'nama' => 'Nina',
            'email' => null,
            'pesan' => 'Akan dihapus.',
            'status_validasi' => 'pending',
            'edit_token' => Hash::make('token-delete'),
            'editable_until' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->withSession(["testimoni_guest.$id" => 'token-delete'])
            ->delete(route('testimoni.destroy', $id));

        $response->assertRedirect(route('home') . '#kelola-testimoni');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('testimoni_publik', ['id' => $id]);
    }

    public function test_admin_can_validate_testimoni(): void
    {
        if (! Schema::hasTable('testimoni_publik')) {
            $this->markTestIncomplete('Table testimoni_publik belum tersedia.');
        }

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $id = \DB::table('testimoni_publik')->insertGetId([
            'nama' => 'Yani',
            'email' => null,
            'pesan' => 'Mohon ditampilkan.',
            'status_validasi' => 'pending',
            'editable_until' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.testimoni.update-status', $id), [
                'status_validasi' => 'disetujui',
            ]);

        $response->assertRedirect(route('admin.testimoni.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $id,
            'status_validasi' => 'disetujui',
        ]);
    }
}
