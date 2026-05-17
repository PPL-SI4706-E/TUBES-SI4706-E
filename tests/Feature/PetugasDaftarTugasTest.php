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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetugasDaftarTugasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private User $warga;
    private KategoriLaporan $kategori;
    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->petugas = User::factory()->petugas()->create([
            'name' => 'Budi Hartono',
            'email' => 'budi@example.test',
            'phone' => '081234567890',
        ]);
        $this->warga = User::factory()->masyarakat()->create([
            'name' => 'Andi Pratama',
            'phone' => '089998887776',
        ]);
        $this->wilayah = Wilayah::factory()->create([
            'nama_wilayah' => 'Desa Sukamaju',
        ]);
        $this->kategori = KategoriLaporan::factory()->create([
            'nama_kategori' => 'Air Keruh / Berbau',
        ]);
    }

    public function test_admin_can_open_detail_laporan_page(): void
    {
        $laporan = $this->buatLaporanPending();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.show', $laporan));

        $response->assertOk();
        $response->assertSee('Detail Laporan');
        $response->assertSee('#' . $laporan->id);
        $response->assertSee('Validasi Laporan');
    }

    public function test_admin_can_accept_laporan_and_create_petugas_tugas(): void
    {
        $laporan = $this->buatLaporanPending();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.laporan.validasi', $laporan), [
                'aksi' => 'terima_lapangan',
                'catatan_admin' => 'Segera cek sumber kontaminasi di lokasi pelanggan.',
            ]);

        $response->assertRedirect(route('admin.laporan.show', $laporan));

        $laporan->refresh();

        $this->assertSame('diterima', $laporan->status);
        $this->assertSame('Segera cek sumber kontaminasi di lokasi pelanggan.', $laporan->catatan_admin);

        $penugasan = Penugasan::query()->where('laporan_id', $laporan->id)->first();
        $this->assertNotNull($penugasan);
        $this->assertSame($this->petugas->id, $penugasan->user_id);
        $this->assertSame('Ditugaskan', $penugasan->status_tugas);

        $taskPage = $this->actingAs($this->petugas)
            ->get(route('petugas.tugas.index'));

        $taskPage->assertOk();
        $taskPage->assertSee('#' . $laporan->id);
        $taskPage->assertSee('Air Keruh / Berbau');
        $taskPage->assertSee('Segera cek sumber kontaminasi di lokasi pelanggan.');
        $taskPage->assertSee('Tugas Baru');
    }

    public function test_solusi_virtual_marks_laporan_selesai_without_creating_penugasan(): void
    {
        $laporan = $this->buatLaporanPending();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.laporan.validasi', $laporan), [
                'aksi' => 'solusi_virtual',
                'catatan_admin' => 'Masalah selesai dengan panduan flushing dari rumah.',
            ]);

        $response->assertRedirect(route('admin.laporan.show', $laporan));

        $laporan->refresh();

        $this->assertSame('selesai', $laporan->status);
        $this->assertSame('Masalah selesai dengan panduan flushing dari rumah.', $laporan->catatan_admin);
        $this->assertDatabaseMissing('penugasan', ['laporan_id' => $laporan->id]);

        $taskPage = $this->actingAs($this->petugas)
            ->get(route('petugas.tugas.index'));

        $taskPage->assertOk();
        $taskPage->assertDontSee('#' . $laporan->id);
    }

    public function test_tolak_laporan_marks_ditolak_without_creating_penugasan(): void
    {
        $laporan = $this->buatLaporanPending();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.laporan.validasi', $laporan), [
                'aksi' => 'tolak',
                'catatan_admin' => 'Data alamat tidak lengkap dan tidak dapat diverifikasi.',
            ]);

        $response->assertRedirect(route('admin.laporan.show', $laporan));

        $laporan->refresh();

        $this->assertSame('ditolak', $laporan->status);
        $this->assertSame('Data alamat tidak lengkap dan tidak dapat diverifikasi.', $laporan->catatan_admin);
        $this->assertDatabaseMissing('penugasan', ['laporan_id' => $laporan->id]);

        $adminIndex = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'));

        $adminIndex->assertOk();
        $adminIndex->assertSee('Ditolak');

        $taskPage = $this->actingAs($this->petugas)
            ->get(route('petugas.tugas.index'));

        $taskPage->assertOk();
        $taskPage->assertDontSee('#' . $laporan->id);
    }

    public function test_upload_bukti_moves_task_to_menunggu_konfirmasi_without_finishing_laporan(): void
    {
        Storage::fake('public');

        $laporan = $this->buatLaporanPending();
        $penugasan = $this->terimaLaporanDanAmbilPenugasan($laporan);

        $response = $this->actingAs($this->petugas)
            ->post(route('petugas.tugas.bukti', $penugasan), [
                'foto_bukti' => UploadedFile::fake()->create('bukti.jpg', 120, 'image/jpeg'),
                'keterangan' => 'Pipa sudah diganti dan aliran kembali normal.',
            ]);

        $response->assertRedirect();

        $penugasan->refresh();
        $laporan->refresh();

        $this->assertSame('Menunggu Konfirmasi', $penugasan->status_tugas);
        $this->assertSame('diterima', $laporan->status);
        $this->assertNotNull($penugasan->foto_bukti);

        $penyelesaian = PenyelesaianTugas::query()->where('penugasan_id', $penugasan->id)->first();
        $this->assertNotNull($penyelesaian);
        $this->assertSame('Pipa sudah diganti dan aliran kembali normal.', $penyelesaian->keterangan);

        $taskPage = $this->actingAs($this->petugas)
            ->get(route('petugas.tugas.index'));

        $taskPage->assertOk();
        $taskPage->assertSee('Menunggu Konfirmasi Warga');
        $taskPage->assertDontSee('Riwayat tugas selesai');
    }

    public function test_menandai_tugas_selesai_ikut_menyelesaikan_status_laporan_admin(): void
    {
        Storage::fake('public');

        $laporan = $this->buatLaporanPending();
        $penugasan = $this->terimaLaporanDanAmbilPenugasan($laporan);

        $this->actingAs($this->petugas)
            ->post(route('petugas.tugas.bukti', $penugasan), [
                'foto_bukti' => UploadedFile::fake()->create('bukti.jpg', 120, 'image/jpeg'),
                'keterangan' => 'Perbaikan selesai.',
            ]);

        $response = $this->actingAs($this->petugas)
            ->post(route('petugas.tugas.status', $penugasan), [
                'status_tugas' => 'Selesai',
            ]);

        $response->assertRedirect();

        $penugasan->refresh();
        $laporan->refresh();

        $this->assertSame('Selesai', $penugasan->status_tugas);
        $this->assertSame('selesai', $laporan->status);

        $adminIndex = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'));

        $adminIndex->assertOk();
        $adminIndex->assertSee('Selesai');

        $taskPage = $this->actingAs($this->petugas)
            ->get(route('petugas.tugas.index'));

        $taskPage->assertOk();
        $taskPage->assertSee('Riwayat Selesai');
        $taskPage->assertSee('#' . $laporan->id . ' - ' . $this->kategori->nama_kategori);
    }

    private function buatLaporanPending(): Laporan
    {
        $laporan = Laporan::factory()->create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $this->wilayah->id,
            'kategori_laporan_id' => $this->kategori->id,
            'judul' => 'Air keruh sejak pagi',
            'alamat' => 'Jl. Kenanga No. 7A, RT 01/RW 02, Desa Sukamaju',
            'deskripsi' => 'Air berwarna cokelat dan berbau tanah sejak dua hari lalu.',
            'status' => 'pending',
            'tanggal_lapor' => now()->subMinutes(5),
        ]);

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => -6.875000,
            'longitude' => 106.771000,
        ]);

        return $laporan;
    }

    private function terimaLaporanDanAmbilPenugasan(Laporan $laporan): Penugasan
    {
        $this->actingAs($this->admin)
            ->post(route('admin.laporan.validasi', $laporan), [
                'aksi' => 'terima_lapangan',
                'catatan_admin' => 'Segera cek sumber kontaminasi di lokasi pelanggan.',
            ]);

        return Penugasan::query()->where('laporan_id', $laporan->id)->firstOrFail();
    }
}
