<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicTestimoniTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_public_testimoni(): void
    {
        $response = $this->post(route('testimoni.store'), [
            'nama' => 'Budi',
            'email' => 'budi@example.com',
            'pesan' => 'Pelayanannya cepat dan informatif.',
        ]);

        $response->assertRedirect(route('home') . '#testimoni');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('testimoni_publik', [
            'nama' => 'Budi',
            'email' => 'budi@example.com',
            'pesan' => 'Pelayanannya cepat dan informatif.',
            'status' => 'pending',
        ]);
    }

    public function test_home_only_shows_approved_testimoni(): void
    {
        \DB::table('testimoni_publik')->insert([
            [
                'nama' => 'Rina',
                'email' => 'rina@example.com',
                'pesan' => 'Approved testimonial',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Dani',
                'email' => 'dani@example.com',
                'pesan' => 'Pending testimonial',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Sari',
                'email' => 'sari@example.com',
                'pesan' => 'Rejected testimonial',
                'status' => 'rejected',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText('Approved testimonial');
        $response->assertDontSeeText('Pending testimonial');
        $response->assertDontSeeText('Rejected testimonial');
    }

    public function test_guest_can_update_owned_testimoni_within_five_minutes(): void
    {
        Carbon::setTestNow('2026-05-05 08:00:00');

        $this->post(route('testimoni.store'), [
            'nama' => 'Budi',
            'email' => 'budi@example.com',
            'pesan' => 'Pesan awal',
        ]);

        $testimoniId = \DB::table('testimoni_publik')->value('id');

        Carbon::setTestNow('2026-05-05 08:04:00');

        $response = $this->from(route('home') . '#testimoni')->put(route('testimoni.update', $testimoniId), [
            'nama' => 'Budi Update',
            'email' => 'budi-update@example.com',
            'pesan' => 'Pesan sudah diperbarui',
        ]);

        $response->assertRedirect(route('home') . '#testimoni');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoniId,
            'nama' => 'Budi Update',
            'email' => 'budi-update@example.com',
            'pesan' => 'Pesan sudah diperbarui',
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_update_owned_testimoni_after_five_minutes(): void
    {
        Carbon::setTestNow('2026-05-05 08:00:00');

        $this->post(route('testimoni.store'), [
            'nama' => 'Budi',
            'email' => 'budi@example.com',
            'pesan' => 'Pesan awal',
        ]);

        $testimoniId = \DB::table('testimoni_publik')->value('id');

        Carbon::setTestNow('2026-05-05 08:06:00');

        $response = $this->from(route('home') . '#testimoni')->put(route('testimoni.update', $testimoniId), [
            'nama' => 'Budi Update',
            'email' => 'budi-update@example.com',
            'pesan' => 'Pesan sudah diperbarui',
        ]);

        $response->assertRedirect(route('home') . '#testimoni');
        $response->assertSessionHasErrors('testimoni');

        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoniId,
            'nama' => 'Budi',
            'pesan' => 'Pesan awal',
        ]);
    }

    public function test_guest_can_delete_owned_testimoni_within_five_minutes(): void
    {
        Carbon::setTestNow('2026-05-05 08:00:00');

        $this->post(route('testimoni.store'), [
            'nama' => 'Budi',
            'email' => 'budi@example.com',
            'pesan' => 'Pesan awal',
        ]);

        $testimoniId = \DB::table('testimoni_publik')->value('id');

        Carbon::setTestNow('2026-05-05 08:03:00');

        $response = $this->delete(route('testimoni.destroy', $testimoniId));

        $response->assertRedirect(route('home') . '#testimoni');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('testimoni_publik', [
            'id' => $testimoniId,
        ]);
    }

    public function test_admin_can_manage_testimoni_status_and_delete_it(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        \DB::table('testimoni_publik')->insert([
            'nama' => 'Nia',
            'email' => 'nia@example.com',
            'pesan' => 'Mohon ditinjau',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $testimoniId = \DB::table('testimoni_publik')->value('id');

        $approveResponse = $this->actingAs($admin)->patch(route('admin.testimoni.approve', $testimoniId));
        $approveResponse->assertRedirect();
        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoniId,
            'status' => 'approved',
        ]);

        $rejectResponse = $this->actingAs($admin)->patch(route('admin.testimoni.reject', $testimoniId));
        $rejectResponse->assertRedirect();
        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoniId,
            'status' => 'rejected',
        ]);

        $pendingResponse = $this->actingAs($admin)->patch(route('admin.testimoni.pending', $testimoniId));
        $pendingResponse->assertRedirect();
        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoniId,
            'status' => 'pending',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.testimoni.destroy', $testimoniId));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('testimoni_publik', [
            'id' => $testimoniId,
        ]);
    }
}
