<?php

namespace Tests\Feature;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\Penugasan;
use App\Models\PenyelesaianTugas;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KonfirmasiLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function makeWarga(): User
    {
        return User::factory()->create(['role' => 'masyarakat', 'is_active' => true]);
    }

    private function makePetugas(): User
    {
        return User::factory()->create(['role' => 'petugas', 'is_active' => true]);
    }

    private function setupLaporanMenungguKonfirmasi(User $warga, User $petugas)
    {
        $kategori = KategoriLaporan::factory()->create();
        $wilayah = Wilayah::factory()->create();

        $laporan = Laporan::factory()->create([
            'user_id' => $warga->id,
            'kategori_laporan_id' => $kategori->id,
            'wilayah_id' => $wilayah->id,
            'status' => 'menunggu_konfirmasi',
        ]);

        $penugasan = Penugasan::factory()->create([
            'laporan_id' => $laporan->id,
            'user_id' => $petugas->id,
            'status_tugas' => 'Menunggu Konfirmasi',
        ]);

        PenyelesaianTugas::create([
            'penugasan_id' => $penugasan->id,
            'foto_bukti' => 'dummy/bukti.jpg',
            'tanggal_selesai' => now()->toDateString(),
            'keterangan' => 'Sudah beres',
        ]);

        return $laporan;
    }

    // ── DEV-72 (TC-11) Konfirmasi Laporan ─────────────────────────────────────

    public function test_TC1101_konfirmasi_selesai_berhasil(): void
    {
        $warga = $this->makeWarga();
        $petugas = $this->makePetugas();
        $laporan = $this->setupLaporanMenungguKonfirmasi($warga, $petugas);

        $response = $this->actingAs($warga)->post(route('warga.laporan.konfirmasi', $laporan->id), [
            'action'   => 'selesai',
            'rating'   => 5,
            'komentar' => 'Kerjanya cepat dan mantap!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('laporan', [
            'id'     => $laporan->id,
            'status' => 'selesai',
        ]);

        $this->assertDatabaseHas('penugasan', [
            'id'           => $laporan->penugasan->id,
            'status_tugas' => 'Selesai',
        ]);

        $this->assertDatabaseHas('ulasan', [
            'laporan_id' => $laporan->id,
            'rating'     => 5,
        ]);
    }

    public function test_TC1102_tolak_hasil_revisi_berhasil(): void
    {
        $warga = $this->makeWarga();
        $petugas = $this->makePetugas();
        $laporan = $this->setupLaporanMenungguKonfirmasi($warga, $petugas);

        $response = $this->actingAs($warga)->post(route('warga.laporan.konfirmasi', $laporan->id), [
            'action'   => 'revisi',
            'komentar' => 'Masih ada yang bocor di pipanya.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status kembali menjadi dikerjakan
        $this->assertDatabaseHas('laporan', [
            'id'     => $laporan->id,
            'status' => 'dikerjakan',
        ]);

        $this->assertDatabaseHas('penugasan', [
            'id'           => $laporan->penugasan->id,
            'status_tugas' => 'Sedang Dikerjakan',
        ]);

        // Catatan revisi ditambahkan ke catatan_admin
        $penugasan = Penugasan::find($laporan->penugasan->id);
        $this->assertStringContainsString('Masih ada yang bocor di pipanya.', $penugasan->catatan_admin);

        // Foto bukti dihapus
        $this->assertDatabaseMissing('penyelesaian_tugas', [
            'penugasan_id' => $penugasan->id,
        ]);
    }

    public function test_TC1103_gagal_konfirmasi_selesai_tanpa_rating(): void
    {
        $warga = $this->makeWarga();
        $petugas = $this->makePetugas();
        $laporan = $this->setupLaporanMenungguKonfirmasi($warga, $petugas);

        $response = $this->actingAs($warga)->post(route('warga.laporan.konfirmasi', $laporan->id), [
            'action'   => 'selesai',
            // rating dikosongkan
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_TC1104_gagal_konfirmasi_revisi_tanpa_memberi_catatan(): void
    {
        $warga = $this->makeWarga();
        $petugas = $this->makePetugas();
        $laporan = $this->setupLaporanMenungguKonfirmasi($warga, $petugas);

        $response = $this->actingAs($warga)->post(route('warga.laporan.konfirmasi', $laporan->id), [
            'action'   => 'revisi',
            // komentar dikosongkan
        ]);

        $response->assertSessionHasErrors('komentar');
    }
}
