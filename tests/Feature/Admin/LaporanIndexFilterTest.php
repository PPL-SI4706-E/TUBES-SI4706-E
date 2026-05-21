<?php

namespace Tests\Feature\Admin;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    // ── TC-01: Pencarian kata kunci valid ─────────────────────────────────────

    public function test_admin_can_search_by_warga_name(): void
    {
        $warga = User::factory()->masyarakat()->create(['name' => 'Siti Aminah']);
        Laporan::factory()->create(['user_id' => $warga->id]);
        Laporan::factory()->create(); // noise

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['keyword' => 'Siti']));

        $response->assertOk();
        $response->assertSee('Siti Aminah');
    }

    public function test_admin_can_search_by_alamat(): void
    {
        Laporan::factory()->create(['alamat' => 'Jalan Merdeka Nomor 5']);
        Laporan::factory()->create(['alamat' => 'Jalan Kenanga Nomor 8']); // noise

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['keyword' => 'Merdeka']));

        $response->assertOk();
        $response->assertSee('Jalan Merdeka Nomor 5');
        $response->assertDontSee('Jalan Kenanga Nomor 8');
    }

    public function test_admin_can_search_by_nomor_laporan(): void
    {
        $laporan = Laporan::factory()->create();
        Laporan::factory()->count(2)->create(); // noise

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['keyword' => (string) $laporan->id]));

        $response->assertOk();
        $response->assertSee('#' . $laporan->id);
    }

    // ── TC-02: Kata kunci tidak ditemukan ─────────────────────────────────────

    public function test_keyword_not_found_shows_laporan_tidak_ditemukan_message(): void
    {
        Laporan::factory()->create(['alamat' => 'Jalan Ada Datanya']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['keyword' => 'XYZ_TIDAK_ADA_99999']));

        $response->assertOk();
        $response->assertSee('Laporan tidak ditemukan.');
    }

    // ── TC-03: Filter status bayar ────────────────────────────────────────────

    public function test_filter_status_lunas_shows_only_lunas(): void
    {
        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Lunas']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Belum']);

        $lunas  = Laporan::factory()->create(['user_id' => $wargaA->id]);
        $belum  = Laporan::factory()->create(['user_id' => $wargaB->id]);

        Pembayaran::factory()->create(['laporan_id' => $lunas->id, 'user_id' => $wargaA->id, 'status_pembayaran' => 'Lunas']);
        Pembayaran::factory()->create(['laporan_id' => $belum->id, 'user_id' => $wargaB->id, 'status_pembayaran' => 'Menunggu']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['status_bayar' => 'lunas']));

        $response->assertOk();
        $response->assertSee('Warga Lunas');
        $response->assertDontSee('Warga Belum');
    }

    public function test_filter_status_belum_lunas_shows_correct_laporan(): void
    {
        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Belum Bayar']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Sudah Lunas']);

        $belum = Laporan::factory()->create(['user_id' => $wargaA->id]);
        $lunas = Laporan::factory()->create(['user_id' => $wargaB->id]);

        Pembayaran::factory()->create(['laporan_id' => $belum->id, 'user_id' => $wargaA->id, 'status_pembayaran' => 'Menunggu']);
        Pembayaran::factory()->create(['laporan_id' => $lunas->id, 'user_id' => $wargaB->id, 'status_pembayaran' => 'Lunas']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['status_bayar' => 'belum_lunas']));

        $response->assertOk();
        $response->assertSee('Warga Belum Bayar');
        $response->assertDontSee('Warga Sudah Lunas');
    }

    public function test_filter_status_menunggu_verifikasi_shows_correct_laporan(): void
    {
        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Terverifikasi']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Lunas Juga']);

        $terverifikasi = Laporan::factory()->create(['user_id' => $wargaA->id]);
        $lunas         = Laporan::factory()->create(['user_id' => $wargaB->id]);

        Pembayaran::factory()->create(['laporan_id' => $terverifikasi->id, 'user_id' => $wargaA->id, 'status_pembayaran' => 'Terverifikasi']);
        Pembayaran::factory()->create(['laporan_id' => $lunas->id,         'user_id' => $wargaB->id, 'status_pembayaran' => 'Lunas']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['status_bayar' => 'menunggu_verifikasi']));

        $response->assertOk();
        $response->assertSee('Warga Terverifikasi');
        $response->assertDontSee('Warga Lunas Juga');
    }

    // ── TC-04: Rentang bulan valid ────────────────────────────────────────────

    public function test_filter_rentang_bulan_valid_shows_only_matching_period(): void
    {
        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Januari']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Maret']);
        $wargaC = User::factory()->masyarakat()->create(['name' => 'Warga Mei']);

        Laporan::factory()->create(['user_id' => $wargaA->id, 'tanggal_lapor' => '2026-01-15 08:00:00']);
        Laporan::factory()->create(['user_id' => $wargaB->id, 'tanggal_lapor' => '2026-03-20 08:00:00']);
        Laporan::factory()->create(['user_id' => $wargaC->id, 'tanggal_lapor' => '2026-05-10 08:00:00']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['bulan_awal' => '2026-02', 'bulan_akhir' => '2026-04']));

        $response->assertOk();
        $response->assertSee('Warga Maret');
        $response->assertDontSee('Warga Januari');
        $response->assertDontSee('Warga Mei');
    }

    // ── TC-05: Rentang bulan tidak valid ──────────────────────────────────────

    public function test_filter_rentang_bulan_invalid_shows_validation_error(): void
    {
        // Invalid range: bulan_akhir (Jan) < bulan_awal (May) — triggers validation redirect
        $response = $this->actingAs($this->admin)
            ->from(route('admin.laporan.index'))
            ->get(route('admin.laporan.index', ['bulan_awal' => '2026-05', 'bulan_akhir' => '2026-01']));

        $response->assertRedirect(route('admin.laporan.index'));
        $response->assertSessionHasErrors(['bulan_akhir']);
        $this->assertSame(
            'Rentang bulan tidak valid.',
            session('errors')->first('bulan_akhir')
        );
    }

    // ── TC-06: Filter wilayah ──────────────────────────────────────────────────

    public function test_filter_wilayah_shows_only_laporan_from_that_wilayah(): void
    {
        $wilayah = Wilayah::factory()->create(['nama_wilayah' => 'Kecamatan Cidadap']);
        $other   = Wilayah::factory()->create(['nama_wilayah' => 'Kecamatan Coblong']);

        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Cidadap']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Coblong']);

        Laporan::factory()->create(['user_id' => $wargaA->id, 'wilayah_id' => $wilayah->id]);
        Laporan::factory()->create(['user_id' => $wargaB->id, 'wilayah_id' => $other->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['wilayah_id' => $wilayah->id]));

        $response->assertOk();
        $response->assertSee('Warga Cidadap');
        $response->assertDontSee('Warga Coblong');
    }

    // ── TC-07: Filter kategori ────────────────────────────────────────────────

    public function test_filter_kategori_shows_only_laporan_with_that_kategori(): void
    {
        $pipaBocor = KategoriLaporan::factory()->create(['nama_kategori' => 'Pipa Bocor']);
        $airKeruh  = KategoriLaporan::factory()->create(['nama_kategori' => 'Air Keruh']);

        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Pipa Bocor']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Air Keruh']);

        Laporan::factory()->create(['user_id' => $wargaA->id, 'kategori_laporan_id' => $pipaBocor->id]);
        Laporan::factory()->create(['user_id' => $wargaB->id, 'kategori_laporan_id' => $airKeruh->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['kategori_id' => $pipaBocor->id]));

        $response->assertOk();
        // warga pipa bocor appears in the table; warga air keruh does not
        $response->assertSee('Warga Pipa Bocor');
        $response->assertDontSee('Warga Air Keruh');
    }

    // ── TC-08: Kombinasi multi-filter ─────────────────────────────────────────

    public function test_admin_can_apply_multiple_filters(): void
    {
        $wilayah  = Wilayah::factory()->create(['nama_wilayah' => 'Cidadap']);
        $kategori = KategoriLaporan::factory()->create(['nama_kategori' => 'Pipa Bocor']);

        $warga = User::factory()->masyarakat()->create(['name' => 'Siti Filter']);
        $matching = Laporan::factory()->create([
            'user_id'            => $warga->id,
            'wilayah_id'         => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'alamat'             => 'Jalan Filter Cocok',
            'tanggal_lapor'      => '2026-03-12 10:00:00',
        ]);
        Pembayaran::factory()->create([
            'laporan_id'        => $matching->id,
            'user_id'           => $warga->id,
            'status_pembayaran' => 'Lunas',
        ]);

        $otherWarga = User::factory()->masyarakat()->create(['name' => 'Budi Tidak Cocok']);
        $other = Laporan::factory()->create([
            'user_id'       => $otherWarga->id,
            'tanggal_lapor' => '2026-04-12 10:00:00',
        ]);
        Pembayaran::factory()->create([
            'laporan_id'        => $other->id,
            'user_id'           => $otherWarga->id,
            'status_pembayaran' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'keyword'      => 'Siti',
            'status_bayar' => 'lunas',
            'bulan_awal'   => '2026-03',
            'bulan_akhir'  => '2026-03',
            'wilayah_id'   => $wilayah->id,
            'kategori_id'  => $kategori->id,
        ]));

        $response->assertOk();
        $response->assertSee('Siti Filter');
        $response->assertSee('Jalan Filter Cocok');
        $response->assertDontSee('Budi Tidak Cocok');
    }

    public function test_multi_filter_status_and_kategori(): void
    {
        $kategori = KategoriLaporan::factory()->create(['nama_kategori' => 'Kebocoran']);

        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Cocok']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Tidak Cocok']);

        $cocok = Laporan::factory()->create(['user_id' => $wargaA->id, 'kategori_laporan_id' => $kategori->id]);
        Pembayaran::factory()->create(['laporan_id' => $cocok->id, 'user_id' => $wargaA->id, 'status_pembayaran' => 'Lunas']);

        $tidakCocok = Laporan::factory()->create(['user_id' => $wargaB->id, 'kategori_laporan_id' => $kategori->id]);
        Pembayaran::factory()->create(['laporan_id' => $tidakCocok->id, 'user_id' => $wargaB->id, 'status_pembayaran' => 'Menunggu']);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'status_bayar' => 'lunas',
            'kategori_id'  => $kategori->id,
        ]));

        $response->assertOk();
        $response->assertSee('Warga Cocok');
        $response->assertDontSee('Warga Tidak Cocok');
    }

    // ── TC-09: Reset filter ───────────────────────────────────────────────────

    public function test_reset_filter_returns_all_laporan(): void
    {
        $wargaA = User::factory()->masyarakat()->create(['name' => 'Warga Pertama']);
        $wargaB = User::factory()->masyarakat()->create(['name' => 'Warga Kedua']);

        Laporan::factory()->create(['user_id' => $wargaA->id]);
        Laporan::factory()->create(['user_id' => $wargaB->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSee('Warga Pertama');
        $response->assertSee('Warga Kedua');
        // Reset Filter button must link back to index with no params
        $response->assertSee(route('admin.laporan.index'), false);
    }

    // ── TC-10: Hasil filter kosong ────────────────────────────────────────────

    public function test_empty_result_from_keyword_shows_laporan_tidak_ditemukan(): void
    {
        Laporan::factory()->create(['alamat' => 'Jalan Ada Datanya']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['keyword' => 'TidakAdaLaporanIni']));

        $response->assertOk();
        $response->assertSee('Laporan tidak ditemukan.');
    }

    public function test_empty_result_from_non_keyword_filter_shows_correct_message(): void
    {
        $kategoriA = KategoriLaporan::factory()->create();
        $kategoriB = KategoriLaporan::factory()->create();

        Laporan::factory()->create(['kategori_laporan_id' => $kategoriA->id]);
        // kategoriB has no laporan

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['kategori_id' => $kategoriB->id]));

        $response->assertOk();
        $response->assertSee('Tidak ada laporan yang sesuai dengan filter yang dipilih.');
    }

    public function test_empty_result_from_wilayah_filter_shows_correct_message(): void
    {
        $wilayahA = Wilayah::factory()->create();
        $wilayahB = Wilayah::factory()->create();

        Laporan::factory()->create(['wilayah_id' => $wilayahA->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['wilayah_id' => $wilayahB->id]));

        $response->assertOk();
        $response->assertSee('Tidak ada laporan yang sesuai dengan filter yang dipilih.');
    }

    // ── TC-11: Filter tetap aktif saat pagination ─────────────────────────────

    public function test_filter_persists_on_pagination(): void
    {
        Laporan::factory()->count(16)->create(['alamat' => 'Jalan Persistensi']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['keyword' => 'Persistensi']));

        $response->assertOk();
        // Pagination link must carry the keyword param
        $response->assertSee('keyword=Persistensi', false);
        $response->assertSee('page=2', false);
    }

    public function test_filter_persists_with_status_bayar_on_pagination(): void
    {
        for ($i = 0; $i < 16; $i++) {
            $laporan = Laporan::factory()->create();
            Pembayaran::factory()->create([
                'laporan_id'        => $laporan->id,
                'user_id'           => $laporan->user_id,
                'status_pembayaran' => 'Lunas',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index', ['status_bayar' => 'lunas']));

        $response->assertOk();
        $response->assertSee('status_bayar=lunas', false);
        $response->assertSee('page=2', false);
    }

    // ── AT-11: Non-admin tidak bisa akses ─────────────────────────────────────

    public function test_non_admin_cannot_access_filter_page(): void
    {
        $nonAdmin = User::factory()->masyarakat()->create();

        $response = $this->actingAs($nonAdmin)
            ->get(route('admin.laporan.index'));

        $response->assertForbidden();
    }

    public function test_petugas_cannot_access_filter_page(): void
    {
        $petugas = User::factory()->petugas()->create();

        $response = $this->actingAs($petugas)
            ->get(route('admin.laporan.index'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.laporan.index'));

        $response->assertRedirect(route('login'));
    }

    // ── Dropdown data tersedia di view ────────────────────────────────────────

    public function test_filter_dropdowns_contain_wilayah_and_kategori_data(): void
    {
        Wilayah::factory()->create(['nama_wilayah' => 'Kecamatan Sukasari']);
        KategoriLaporan::factory()->create(['nama_kategori' => 'Pipa Bocor']);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSee('Kecamatan Sukasari');
        $response->assertSee('Pipa Bocor');
    }
}
