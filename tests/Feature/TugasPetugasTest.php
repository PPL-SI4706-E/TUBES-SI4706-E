<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\Penugasan;
use App\Models\PenyelesaianTugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TugasPetugasTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePetugas(): User
    {
        return User::factory()->create(['role' => 'petugas', 'is_active' => true]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function makeWarga(): User
    {
        return User::factory()->create(['role' => 'masyarakat', 'is_active' => true]);
    }

    private function makePenugasan(User $petugas, array $attrs = []): Penugasan
    {
        $warga   = $this->makeWarga();
        $laporan = Laporan::factory()->create(['user_id' => $warga->id, 'status' => 'dikerjakan']);

        return Penugasan::factory()->create(array_merge([
            'laporan_id'        => $laporan->id,
            'user_id'           => $petugas->id,
            'status_tugas'      => 'Ditugaskan',
            'tanggal_penugasan' => now()->toDateString(),
        ], $attrs));
    }

    // ── ATC-001 Daftar Tugas ──────────────────────────────────────────────────

    public function test_ATC001_petugas_dapat_membuka_daftar_tugas(): void
    {
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas);

        $response = $this->actingAs($petugas)->get(route('petugas.tugas.index'));

        $response->assertOk();
        $response->assertSee('Daftar Tugas');
        $response->assertSee('Tugas Aktif');
    }

    public function test_petugas_hanya_melihat_tugasnya_sendiri(): void
    {
        $petugas1 = $this->makePetugas();
        $petugas2 = $this->makePetugas();
        $p1       = $this->makePenugasan($petugas1);
        $p2       = $this->makePenugasan($petugas2);

        $response = $this->actingAs($petugas1)->get(route('petugas.tugas.index'));

        $response->assertOk();
        // petugas1 sees their laporan ID
        $response->assertSee('#' . $p1->laporan->id);
        // does not see petugas2's laporan
        $response->assertDontSee('#' . $p2->laporan->id . ' ');
    }

    // ── ATC-002 Detail Tugas ──────────────────────────────────────────────────

    public function test_ATC002_petugas_dapat_melihat_detail_tugas(): void
    {
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas);

        $response = $this->actingAs($petugas)->get(route('petugas.tugas.show', $penugasan->id));

        $response->assertOk();
        $response->assertSee('Detail Tugas');
        $response->assertSee($penugasan->laporan->user->name);
        $response->assertSee($penugasan->laporan->deskripsi);
    }

    public function test_peta_dan_google_maps_tampil_jika_ada_koordinat(): void
    {
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas);

        MapLokasi::create([
            'laporan_id' => $penugasan->laporan_id,
            'latitude'   => -6.876,
            'longitude'  => 106.8,
        ]);

        $response = $this->actingAs($petugas)->get(route('petugas.tugas.show', $penugasan->id));

        $response->assertOk();
        $response->assertSee('tugas-map');
        $response->assertSee('maps.google.com');
        $response->assertSee('Buka di Google Maps');
    }

    // ── ATC-003 Update Status ─────────────────────────────────────────────────

    public function test_ATC003_petugas_berhasil_update_status(): void
    {
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas, ['status_tugas' => 'Ditugaskan']);

        $response = $this->actingAs($petugas)->post(route('petugas.tugas.status', $penugasan->id), [
            'status_tugas' => 'Menuju Lokasi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penugasan', [
            'id'           => $penugasan->id,
            'status_tugas' => 'Menuju Lokasi',
        ]);
    }

    public function test_gagal_update_status_tidak_valid(): void
    {
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas);

        $response = $this->actingAs($petugas)->post(route('petugas.tugas.status', $penugasan->id), [
            'status_tugas' => 'StatusTidakAda',
        ]);

        $response->assertSessionHasErrors('status_tugas');
    }

    public function test_gagal_update_status_mundur(): void
    {
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas, ['status_tugas' => 'Sedang Dikerjakan']);

        $response = $this->actingAs($petugas)->post(route('petugas.tugas.status', $penugasan->id), [
            'status_tugas' => 'Ditugaskan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('penugasan', [
            'id'           => $penugasan->id,
            'status_tugas' => 'Sedang Dikerjakan',
        ]);
    }

    // ── ATC-004 Upload Bukti ──────────────────────────────────────────────────

    public function test_ATC004_petugas_berhasil_upload_bukti(): void
    {
        Storage::fake('public');
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas, ['status_tugas' => 'Sedang Dikerjakan']);

        $response = $this->actingAs($petugas)->post(route('petugas.tugas.bukti', $penugasan->id), [
            'foto_bukti' => UploadedFile::fake()->create('bukti.jpg', 1024, 'image/jpeg'),
            'keterangan' => 'Pipa telah diperbaiki.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $penugasan->refresh();
        $this->assertEquals('Menunggu Konfirmasi', $penugasan->status_tugas);
        $this->assertDatabaseHas('penyelesaian_tugas', [
            'penugasan_id' => $penugasan->id,
            'keterangan'   => 'Pipa telah diperbaiki.',
        ]);
    }

    // ── ATC-005 Validasi Upload ───────────────────────────────────────────────

    public function test_ATC005_gagal_upload_bukti_tanpa_foto(): void
    {
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas, ['status_tugas' => 'Sedang Dikerjakan']);

        $response = $this->actingAs($petugas)->post(route('petugas.tugas.bukti', $penugasan->id), [
            'keterangan' => 'Perbaikan selesai.',
        ]);

        $response->assertSessionHasErrors('foto_bukti');
    }

    public function test_gagal_upload_foto_terlalu_besar(): void
    {
        Storage::fake('public');
        $petugas   = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas, ['status_tugas' => 'Sedang Dikerjakan']);

        $response = $this->actingAs($petugas)->post(route('petugas.tugas.bukti', $penugasan->id), [
            'foto_bukti' => UploadedFile::fake()->create('big.jpg', 6000, 'image/jpeg'), // 6MB > 5MB
        ]);

        $response->assertSessionHasErrors('foto_bukti');
    }

    // ── ATC-006 Otorisasi ─────────────────────────────────────────────────────

    public function test_ATC006_masyarakat_tidak_bisa_akses_halaman_petugas(): void
    {
        $warga    = $this->makeWarga();
        $response = $this->actingAs($warga)->get(route('petugas.tugas.index'));
        $response->assertForbidden();
    }

    public function test_admin_tidak_bisa_akses_halaman_petugas(): void
    {
        $admin    = $this->makeAdmin();
        $response = $this->actingAs($admin)->get(route('petugas.tugas.index'));
        $response->assertForbidden();
    }

    public function test_guest_tidak_bisa_akses_halaman_petugas(): void
    {
        $response = $this->get(route('petugas.tugas.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_petugas_lain_tidak_bisa_edit_tugas_milik_petugas_lain(): void
    {
        $petugas1  = $this->makePetugas();
        $petugas2  = $this->makePetugas();
        $penugasan = $this->makePenugasan($petugas1, ['status_tugas' => 'Ditugaskan']);

        // petugas2 coba update status tugas milik petugas1
        $response = $this->actingAs($petugas2)->post(route('petugas.tugas.status', $penugasan->id), [
            'status_tugas' => 'Menuju Lokasi',
        ]);

        $response->assertNotFound(); // findOrFail gagal karena where user_id
    }
}
