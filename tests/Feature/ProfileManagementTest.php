<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    // ── VIEW PROFILE ─────────────────────────────────────────────────────────

    public function test_user_dapat_melihat_halaman_profil(): void
    {
        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee('Manajemen Profil');
    }

    public function test_guest_tidak_bisa_akses_profil(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_dapat_melihat_halaman_profil(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee($admin->name);
    }

    public function test_petugas_dapat_melihat_halaman_profil(): void
    {
        $petugas = User::factory()->create([
            'role'      => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($petugas)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee($petugas->name);
    }

    // ── UPDATE PROFILE ───────────────────────────────────────────────────────

    public function test_user_dapat_update_profil(): void
    {
        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name'  => 'Nama Baru',
            'email' => 'emailbaru@example.com',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Nama Baru',
            'email' => 'emailbaru@example.com',
            'phone' => '081234567890',
        ]);
    }

    public function test_gagal_update_email_invalid(): void
    {
        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name'  => 'Nama Valid',
            'email' => 'bukan-email-valid',
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_gagal_upload_foto_terlalu_besar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => UploadedFile::fake()->create('big.jpg', 3000, 'image/jpeg'), // 3MB > 2MB limit
        ]);

        $response->assertSessionHasErrors('avatar');
    }

    public function test_berhasil_upload_foto_profil(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => UploadedFile::fake()->create('avatar.jpg', 1024, 'image/jpeg'), // 1MB
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    // ── UPDATE PASSWORD ──────────────────────────────────────────────────────

    public function test_berhasil_ganti_password(): void
    {
        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
            'password'  => 'oldpassword123',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.password'), [
            'old_password'          => 'oldpassword123',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_gagal_jika_password_lama_salah(): void
    {
        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
            'password'  => 'correctpassword',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.password'), [
            'old_password'          => 'wrongpassword',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('old_password');
    }

    public function test_gagal_jika_password_baru_terlalu_pendek(): void
    {
        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
            'password'  => 'oldpassword123',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.password'), [
            'old_password'          => 'oldpassword123',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_gagal_jika_password_baru_sama_dengan_password_lama(): void
    {
        $user = User::factory()->create([
            'role'      => 'masyarakat',
            'is_active' => true,
            'password'  => 'samepassword123',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.password'), [
            'old_password'          => 'samepassword123',
            'password'              => 'samepassword123',
            'password_confirmation' => 'samepassword123',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
