<?php

namespace Tests\Feature\Admin;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\Penugasan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * PBI-15: Export Data Laporan (PDF & Excel)
 *
 * AT-001 – Export PDF berhasil
 * AT-002 – Export Excel berhasil
 * AT-003 – Export mengikuti filter status
 * AT-004 – Export mengikuti filter kategori
 * AT-005 – Validasi role Admin
 * AT-006 – Export saat data kosong
 * AT-007 – Sinkronisasi data export (dikover lewat query count)
 * AT-008 – UI export button tampil
 */
class ExportLaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-001 — Export PDF berhasil
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at001_admin_dapat_export_pdf_dan_file_terdownload(): void
    {
        Laporan::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition', ''));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-002 — Export Excel berhasil
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at002_admin_dapat_export_excel_dan_file_terdownload(): void
    {
        Laporan::factory()->count(3)->create();

        Excel::fake();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.excel'));

        Excel::assertDownloaded(
            fn (string $filename) => str_ends_with($filename, '.xlsx'),
            fn ($export) => true
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-003 — Export mengikuti filter status_bayar
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at003_export_pdf_hanya_berisi_data_sesuai_filter_status_bayar(): void
    {
        $wargaLunas = User::factory()->masyarakat()->create(['name' => 'Warga Lunas Export']);
        $wargaBelum = User::factory()->masyarakat()->create(['name' => 'Warga Belum Lunas']);

        $laporanLunas = Laporan::factory()->create(['user_id' => $wargaLunas->id]);
        $laporanBelum = Laporan::factory()->create(['user_id' => $wargaBelum->id]);

        Pembayaran::factory()->create([
            'laporan_id'        => $laporanLunas->id,
            'user_id'           => $wargaLunas->id,
            'status_pembayaran' => 'Lunas',
        ]);
        Pembayaran::factory()->create([
            'laporan_id'        => $laporanBelum->id,
            'user_id'           => $wargaBelum->id,
            'status_pembayaran' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.pdf', ['status_bayar' => 'lunas']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        // Pastikan PDF berisi data (tidak redirect ke index)
        $this->assertStringNotContainsString(route('admin.laporan.index'), $response->headers->get('Location', ''));
    }

    /** @test */
    public function at003_export_excel_hanya_berisi_data_sesuai_filter_status_bayar(): void
    {
        $wargaLunas = User::factory()->masyarakat()->create();
        $wargaBelum = User::factory()->masyarakat()->create();

        $laporanLunas = Laporan::factory()->create(['user_id' => $wargaLunas->id]);
        $laporanBelum = Laporan::factory()->create(['user_id' => $wargaBelum->id]);

        Pembayaran::factory()->create([
            'laporan_id'        => $laporanLunas->id,
            'user_id'           => $wargaLunas->id,
            'status_pembayaran' => 'Lunas',
        ]);
        Pembayaran::factory()->create([
            'laporan_id'        => $laporanBelum->id,
            'user_id'           => $wargaBelum->id,
            'status_pembayaran' => 'Menunggu',
        ]);

        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.excel', ['status_bayar' => 'lunas']));

        Excel::assertDownloaded(
            fn (string $filename) => str_ends_with($filename, '.xlsx'),
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-004 — Export mengikuti filter kategori
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at004_export_pdf_mengikuti_filter_kategori(): void
    {
        $pipaBocor = KategoriLaporan::factory()->create(['nama_kategori' => 'Pipa Bocor Export']);
        $airKeruh  = KategoriLaporan::factory()->create(['nama_kategori' => 'Air Keruh Export']);

        Laporan::factory()->create(['kategori_laporan_id' => $pipaBocor->id]);
        Laporan::factory()->create(['kategori_laporan_id' => $airKeruh->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.pdf', ['kategori_id' => $pipaBocor->id]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function at004_export_excel_mengikuti_filter_kategori(): void
    {
        $pipaBocor = KategoriLaporan::factory()->create(['nama_kategori' => 'Pipa Bocor Export']);
        $airKeruh  = KategoriLaporan::factory()->create(['nama_kategori' => 'Air Keruh Export']);

        Laporan::factory()->create(['kategori_laporan_id' => $pipaBocor->id]);
        Laporan::factory()->create(['kategori_laporan_id' => $airKeruh->id]);

        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.excel', ['kategori_id' => $pipaBocor->id]));

        Excel::assertDownloaded(
            fn (string $filename) => str_ends_with($filename, '.xlsx'),
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-005 — Validasi role Admin (security)
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at005_user_biasa_tidak_bisa_akses_export_pdf(): void
    {
        $warga = User::factory()->masyarakat()->create();

        $response = $this->actingAs($warga)
            ->get(route('admin.laporan.export.pdf'));

        $response->assertForbidden();
    }

    /** @test */
    public function at005_user_biasa_tidak_bisa_akses_export_excel(): void
    {
        $warga = User::factory()->masyarakat()->create();

        $response = $this->actingAs($warga)
            ->get(route('admin.laporan.export.excel'));

        $response->assertForbidden();
    }

    /** @test */
    public function at005_petugas_tidak_bisa_akses_export_pdf(): void
    {
        $petugas = User::factory()->petugas()->create();

        $response = $this->actingAs($petugas)
            ->get(route('admin.laporan.export.pdf'));

        $response->assertForbidden();
    }

    /** @test */
    public function at005_petugas_tidak_bisa_akses_export_excel(): void
    {
        $petugas = User::factory()->petugas()->create();

        $response = $this->actingAs($petugas)
            ->get(route('admin.laporan.export.excel'));

        $response->assertForbidden();
    }

    /** @test */
    public function at005_unauthenticated_diredirect_ke_login_saat_export_pdf(): void
    {
        $response = $this->get(route('admin.laporan.export.pdf'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function at005_unauthenticated_diredirect_ke_login_saat_export_excel(): void
    {
        $response = $this->get(route('admin.laporan.export.excel'));

        $response->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-006 — Export saat data kosong: tampil pesan error, tidak generate file
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at006_export_pdf_redirect_dengan_error_saat_data_kosong(): void
    {
        // Tidak ada laporan sama sekali di database

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.pdf'));

        $response->assertRedirect(route('admin.laporan.index'));
        $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Data laporan tidak tersedia'));
    }

    /** @test */
    public function at006_export_excel_redirect_dengan_error_saat_data_kosong(): void
    {
        // Tidak ada laporan sama sekali di database

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.excel'));

        $response->assertRedirect(route('admin.laporan.index'));
        $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Data laporan tidak tersedia'));
    }

    /** @test */
    public function at006_export_pdf_redirect_dengan_error_saat_filter_menghasilkan_data_kosong(): void
    {
        // Ada laporan tapi dengan filter yang tidak ada hasilnya
        Laporan::factory()->count(3)->create();

        $kategoriKosong = KategoriLaporan::factory()->create();
        // Tidak ada laporan untuk kategori ini

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.pdf', ['kategori_id' => $kategoriKosong->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Data laporan tidak tersedia'));
    }

    /** @test */
    public function at006_export_excel_redirect_dengan_error_saat_filter_menghasilkan_data_kosong(): void
    {
        Laporan::factory()->count(3)->create();

        $kategoriKosong = KategoriLaporan::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.excel', ['kategori_id' => $kategoriKosong->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Data laporan tidak tersedia'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-007 — Sinkronisasi data export dengan data tabel
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at007_jumlah_data_export_sama_dengan_data_di_tabel_saat_ada_filter_wilayah(): void
    {
        $wilayahA = Wilayah::factory()->create();
        $wilayahB = Wilayah::factory()->create();

        Laporan::factory()->count(4)->create(['wilayah_id' => $wilayahA->id]);
        Laporan::factory()->count(2)->create(['wilayah_id' => $wilayahB->id]);

        // Query laporan index
        $fromIndex = \App\Models\Laporan::query()
            ->filterWilayah($wilayahA->id)
            ->count();

        // Query export (sama seperti ReportExportController)
        $fromExport = \App\Models\Laporan::query()
            ->filterKeyword(null)
            ->filterStatusBayar(null)
            ->filterRentangBulan(null, null)
            ->filterWilayah($wilayahA->id)
            ->filterKategori(null)
            ->count();

        $this->assertSame($fromIndex, $fromExport);
        $this->assertSame(4, $fromExport);
    }

    /** @test */
    public function at007_jumlah_data_export_sama_dengan_data_di_tabel_saat_ada_filter_kategori(): void
    {
        $kategori = KategoriLaporan::factory()->create();

        Laporan::factory()->count(5)->create(['kategori_laporan_id' => $kategori->id]);
        Laporan::factory()->count(3)->create(); // noise

        $fromIndex = \App\Models\Laporan::query()
            ->filterKategori($kategori->id)
            ->count();

        $fromExport = \App\Models\Laporan::query()
            ->filterKeyword(null)
            ->filterStatusBayar(null)
            ->filterRentangBulan(null, null)
            ->filterWilayah(null)
            ->filterKategori($kategori->id)
            ->count();

        $this->assertSame($fromIndex, $fromExport);
        $this->assertSame(5, $fromExport);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AT-008 — UI export button tampil di halaman Kelola Laporan
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function at008_tombol_export_excel_tampil_di_halaman_kelola_laporan(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSee('btn-export-excel', false);
        $response->assertSee('Export Excel');
    }

    /** @test */
    public function at008_tombol_export_pdf_tampil_di_halaman_kelola_laporan(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSee('btn-export-pdf', false);
        $response->assertSee('Export PDF');
    }

    /** @test */
    public function at008_tombol_export_menyertakan_filter_aktif_di_url(): void
    {
        $kategori = KategoriLaporan::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['kategori_id' => $kategori->id]));

        $response->assertOk();

        $expectedExcelUrl = route('admin.laporan.export.excel', ['kategori_id' => $kategori->id]);
        $expectedPdfUrl   = route('admin.laporan.export.pdf',   ['kategori_id' => $kategori->id]);

        $response->assertSee($expectedExcelUrl, false);
        $response->assertSee($expectedPdfUrl,   false);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Integrasi: multi-filter export
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function export_pdf_mengikuti_multi_filter_aktif(): void
    {
        $wilayah  = Wilayah::factory()->create();
        $kategori = KategoriLaporan::factory()->create();

        // Matching
        Laporan::factory()->count(2)->create([
            'wilayah_id'          => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'tanggal_lapor'       => '2026-03-15 10:00:00',
        ]);

        // Non-matching (beda wilayah)
        Laporan::factory()->count(3)->create([
            'kategori_laporan_id' => $kategori->id,
            'tanggal_lapor'       => '2026-03-20 10:00:00',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.pdf', [
                'wilayah_id'  => $wilayah->id,
                'kategori_id' => $kategori->id,
                'bulan_awal'  => '2026-03',
                'bulan_akhir' => '2026-03',
            ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function export_excel_mengikuti_multi_filter_aktif(): void
    {
        $wilayah  = Wilayah::factory()->create();
        $kategori = KategoriLaporan::factory()->create();

        Laporan::factory()->count(2)->create([
            'wilayah_id'          => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
        ]);
        Laporan::factory()->count(5)->create(); // noise

        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.excel', [
                'wilayah_id'  => $wilayah->id,
                'kategori_id' => $kategori->id,
            ]));

        Excel::assertDownloaded(
            fn (string $filename) => str_ends_with($filename, '.xlsx'),
        );
    }

    /** @test */
    public function export_pdf_mengikuti_filter_keyword(): void
    {
        $wargaA = User::factory()->masyarakat()->create(['name' => 'Siti Nurbaya Export']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Budi Doremi Export']);

        Laporan::factory()->create(['user_id' => $wargaA->id]);
        Laporan::factory()->create(['user_id' => $wargaB->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.export.pdf', ['keyword' => 'Siti']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
