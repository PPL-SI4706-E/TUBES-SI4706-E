<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicTestimoniTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengunjung_bisa_mengirim_testimoni_dan_status_awalnya_pending(): void
    {
        $response = $this->post(route('testimoni.store'), [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'pesan' => 'Layanan informasinya sangat membantu.',
        ]);

        $response->assertRedirect(route('home') . '#testimoni');

        $this->assertDatabaseHas('testimoni_publik', [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'pesan' => 'Layanan informasinya sangat membantu.',
            'status' => 'pending',
        ]);

        $this->assertNotEmpty(session('public_testimoni_token'));
    }

    public function test_landing_page_hanya_menampilkan_testimoni_yang_sudah_disetujui(): void
    {
        \App\Models\TestimoniPublik::query()->create([
            'nama' => 'Siti',
            'email' => 'siti@example.com',
            'pesan' => 'Approved testimonial',
            'status' => 'approved',
            'session_token' => 'approved-token',
            'editable_until' => now()->addMinutes(5),
            'approved_at' => now(),
        ]);

        \App\Models\TestimoniPublik::query()->create([
            'nama' => 'Andi',
            'email' => 'andi@example.com',
            'pesan' => 'Pending testimonial',
            'status' => 'pending',
            'session_token' => 'pending-token',
            'editable_until' => now()->addMinutes(5),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Approved testimonial');
        $response->assertDontSee('Pending testimonial');
    }

    public function test_pemilik_session_bisa_mengubah_testimoni_dalam_batas_lima_menit(): void
    {
        Carbon::setTestNow('2026-05-05 10:00:00');

        $testimoni = \App\Models\TestimoniPublik::query()->create([
            'nama' => 'Rina',
            'email' => 'rina@example.com',
            'pesan' => 'Pesan awal',
            'status' => 'pending',
            'session_token' => 'session-abc',
            'editable_until' => now()->addMinutes(5),
        ]);

        $response = $this->withSession([
            'public_testimoni_token' => 'session-abc',
        ])->put(route('testimoni.update', $testimoni), [
            'nama' => 'Rina Update',
            'email' => 'rina@example.com',
            'pesan' => 'Pesan revisi',
        ]);

        $response->assertRedirect(route('home') . '#testimoni-saya');

        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoni->id,
            'nama' => 'Rina Update',
            'pesan' => 'Pesan revisi',
            'status' => 'pending',
        ]);

        Carbon::setTestNow();
    }

    public function test_pemilik_session_tidak_bisa_mengubah_testimoni_setelah_lima_menit(): void
    {
        Carbon::setTestNow('2026-05-05 10:00:00');

        $testimoni = \App\Models\TestimoniPublik::query()->create([
            'nama' => 'Dewi',
            'email' => 'dewi@example.com',
            'pesan' => 'Pesan awal',
            'status' => 'pending',
            'session_token' => 'session-expired',
            'editable_until' => now()->addMinutes(5),
        ]);

        Carbon::setTestNow('2026-05-05 10:06:00');

        $response = $this->withSession([
            'public_testimoni_token' => 'session-expired',
        ])->put(route('testimoni.update', $testimoni), [
            'nama' => 'Dewi',
            'email' => 'dewi@example.com',
            'pesan' => 'Tidak boleh tersimpan',
        ]);

        $response->assertSessionHasErrors('pesan');

        $this->assertDatabaseHas('testimoni_publik', [
            'id' => $testimoni->id,
            'pesan' => 'Pesan awal',
        ]);

        Carbon::setTestNow();
    }

    public function test_pemilik_session_bisa_menghapus_testimoni_dalam_batas_waktu(): void
    {
        $testimoni = \App\Models\TestimoniPublik::query()->create([
            'nama' => 'Tono',
            'email' => null,
            'pesan' => 'Akan dihapus',
            'status' => 'pending',
            'session_token' => 'delete-token',
            'editable_until' => now()->addMinutes(5),
        ]);

        $response = $this->withSession([
            'public_testimoni_token' => 'delete-token',
        ])->delete(route('testimoni.destroy', $testimoni));

        $response->assertRedirect(route('home') . '#testimoni');
        $this->assertDatabaseMissing('testimoni_publik', ['id' => $testimoni->id]);
    }
}
