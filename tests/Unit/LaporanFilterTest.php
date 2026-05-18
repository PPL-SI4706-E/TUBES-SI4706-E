<?php

namespace Tests\Unit;

use App\Http\Requests\FilterLaporanRequest;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LaporanFilterTest extends TestCase
{
    use RefreshDatabase;

    // ── AT-01 | TC-01: Keyword — by nama warga ────────────────────────────────

    public function test_filter_by_keyword_returns_matching_laporan_by_name(): void
    {
        $match = User::factory()->masyarakat()->create(['name' => 'Siti Aminah']);
        $matchingLaporan = Laporan::factory()->create(['user_id' => $match->id]);
        Laporan::factory()->create(['user_id' => User::factory()->masyarakat()->create(['name' => 'Budi Mamat'])->id]); // noise

        $resultIds = Laporan::query()->filterKeyword('Siti')->pluck('id');

        $this->assertTrue($resultIds->contains($matchingLaporan->id));
        $this->assertCount(1, $resultIds);
    }

    public function test_filter_by_keyword_returns_matching_laporan_by_alamat(): void
    {
        $matching = Laporan::factory()->create(['alamat' => 'Jalan Melati Nomor 7']);
        Laporan::factory()->create(['alamat' => 'Jalan Kenanga Nomor 8']); // noise

        $resultIds = Laporan::query()->filterKeyword('Melati')->pluck('id');

        $this->assertTrue($resultIds->contains($matching->id));
        $this->assertCount(1, $resultIds);
    }

    public function test_filter_by_keyword_returns_matching_laporan_by_nomor_laporan(): void
    {
        $matching = Laporan::factory()->create();
        Laporan::factory()->count(2)->create(); // noise

        $resultIds = Laporan::query()->filterKeyword((string) $matching->id)->pluck('id');

        $this->assertTrue($resultIds->contains($matching->id));
    }

    public function test_filter_by_keyword_empty_returns_all(): void
    {
        Laporan::factory()->count(3)->create();

        $resultIds = Laporan::query()->filterKeyword(null)->pluck('id');

        $this->assertCount(3, $resultIds);
    }

    // ── AT-02 | TC-03: Status Bayar — semua opsi ─────────────────────────────

    public function test_filter_by_status_bayar_lunas(): void
    {
        $lunas  = Laporan::factory()->create();
        $other  = Laporan::factory()->create();

        Pembayaran::factory()->create([
            'laporan_id'         => $lunas->id,
            'user_id'            => $lunas->user_id,
            'status_pembayaran'  => 'Lunas',
        ]);
        Pembayaran::factory()->create([
            'laporan_id'         => $other->id,
            'user_id'            => $other->user_id,
            'status_pembayaran'  => 'Menunggu',
        ]);

        $resultIds = Laporan::query()->filterStatusBayar('lunas')->pluck('id');

        $this->assertTrue($resultIds->contains($lunas->id));
        $this->assertFalse($resultIds->contains($other->id));
    }

    public function test_filter_by_status_bayar_menunggu_verifikasi(): void
    {
        $terverifikasi = Laporan::factory()->create();
        $lunas         = Laporan::factory()->create();

        Pembayaran::factory()->create([
            'laporan_id'         => $terverifikasi->id,
            'user_id'            => $terverifikasi->user_id,
            'status_pembayaran'  => 'Terverifikasi',
        ]);
        Pembayaran::factory()->create([
            'laporan_id'         => $lunas->id,
            'user_id'            => $lunas->user_id,
            'status_pembayaran'  => 'Lunas',
        ]);

        $resultIds = Laporan::query()->filterStatusBayar('menunggu_verifikasi')->pluck('id');

        $this->assertTrue($resultIds->contains($terverifikasi->id));
        $this->assertFalse($resultIds->contains($lunas->id));
    }

    public function test_filter_by_status_bayar_belum_lunas_includes_menunggu_ditolak_kadaluarsa(): void
    {
        $menunggu   = Laporan::factory()->create();
        $ditolak    = Laporan::factory()->create();
        $kadaluarsa = Laporan::factory()->create();
        $lunas      = Laporan::factory()->create();
        $tanpaBayar = Laporan::factory()->create(); // no pembayaran record

        Pembayaran::factory()->create(['laporan_id' => $menunggu->id,   'user_id' => $menunggu->user_id,   'status_pembayaran' => 'Menunggu']);
        Pembayaran::factory()->create(['laporan_id' => $ditolak->id,    'user_id' => $ditolak->user_id,    'status_pembayaran' => 'Ditolak']);
        Pembayaran::factory()->create(['laporan_id' => $kadaluarsa->id, 'user_id' => $kadaluarsa->user_id, 'status_pembayaran' => 'Kadaluarsa']);
        Pembayaran::factory()->create(['laporan_id' => $lunas->id,      'user_id' => $lunas->user_id,      'status_pembayaran' => 'Lunas']);

        $resultIds = Laporan::query()->filterStatusBayar('belum_lunas')->pluck('id');

        $this->assertTrue($resultIds->contains($menunggu->id));
        $this->assertTrue($resultIds->contains($ditolak->id));
        $this->assertTrue($resultIds->contains($kadaluarsa->id));
        $this->assertTrue($resultIds->contains($tanpaBayar->id));
        $this->assertFalse($resultIds->contains($lunas->id));
    }

    public function test_filter_by_status_bayar_null_returns_all(): void
    {
        Laporan::factory()->count(3)->create();

        $resultIds = Laporan::query()->filterStatusBayar(null)->pluck('id');

        $this->assertCount(3, $resultIds);
    }

    // ── AT-03 | TC-04: Rentang bulan valid ───────────────────────────────────

    public function test_filter_by_date_range_valid(): void
    {
        $before   = Laporan::factory()->create(['tanggal_lapor' => '2026-01-15 08:00:00']);
        $matching = Laporan::factory()->create(['tanggal_lapor' => '2026-03-20 08:00:00']);
        $after    = Laporan::factory()->create(['tanggal_lapor' => '2026-05-10 08:00:00']);

        $resultIds = Laporan::query()->filterRentangBulan('2026-02', '2026-04')->pluck('id');

        $this->assertTrue($resultIds->contains($matching->id));
        $this->assertFalse($resultIds->contains($before->id));
        $this->assertFalse($resultIds->contains($after->id));
        $this->assertCount(1, $resultIds);
    }

    public function test_filter_bulan_awal_only_returns_from_start_of_that_month(): void
    {
        $before  = Laporan::factory()->create(['tanggal_lapor' => '2026-01-31 23:59:59']);
        $inRange = Laporan::factory()->create(['tanggal_lapor' => '2026-02-01 00:00:00']);
        $after   = Laporan::factory()->create(['tanggal_lapor' => '2026-06-15 00:00:00']);

        $resultIds = Laporan::query()->filterRentangBulan('2026-02', null)->pluck('id');

        $this->assertFalse($resultIds->contains($before->id));
        $this->assertTrue($resultIds->contains($inRange->id));
        $this->assertTrue($resultIds->contains($after->id));
    }

    public function test_filter_bulan_akhir_only_returns_up_to_end_of_that_month(): void
    {
        $inRange = Laporan::factory()->create(['tanggal_lapor' => '2026-03-31 23:59:59']);
        $after   = Laporan::factory()->create(['tanggal_lapor' => '2026-04-01 00:00:00']);

        $resultIds = Laporan::query()->filterRentangBulan(null, '2026-03')->pluck('id');

        $this->assertTrue($resultIds->contains($inRange->id));
        $this->assertFalse($resultIds->contains($after->id));
    }

    public function test_filter_same_bulan_awal_and_akhir_returns_only_that_month(): void
    {
        $before   = Laporan::factory()->create(['tanggal_lapor' => '2026-02-28 23:59:59']);
        $inRange  = Laporan::factory()->create(['tanggal_lapor' => '2026-03-15 12:00:00']);
        $after    = Laporan::factory()->create(['tanggal_lapor' => '2026-04-01 00:00:00']);

        $resultIds = Laporan::query()->filterRentangBulan('2026-03', '2026-03')->pluck('id');

        $this->assertTrue($resultIds->contains($inRange->id));
        $this->assertFalse($resultIds->contains($before->id));
        $this->assertFalse($resultIds->contains($after->id));
    }

    // ── AT-04 | TC-05: Rentang bulan tidak valid ─────────────────────────────

    public function test_filter_by_date_range_invalid_returns_validation_error(): void
    {
        $request = FilterLaporanRequest::create('/admin/laporan', 'GET', [
            'bulan_awal'  => '2026-05',
            'bulan_akhir' => '2026-04',
        ]);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('bulan_akhir'));
        $this->assertSame('Rentang bulan tidak valid.', $validator->errors()->first('bulan_akhir'));
    }

    public function test_filter_by_date_range_equal_months_passes_validation(): void
    {
        $request = FilterLaporanRequest::create('/admin/laporan', 'GET', [
            'bulan_awal'  => '2026-03',
            'bulan_akhir' => '2026-03',
        ]);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_filter_bulan_akhir_without_bulan_awal_passes_validation(): void
    {
        $request = FilterLaporanRequest::create('/admin/laporan', 'GET', [
            'bulan_akhir' => '2026-03',
        ]);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    // ── AT-05 | TC-06: Filter wilayah ────────────────────────────────────────

    public function test_filter_by_wilayah(): void
    {
        $wilayah  = Wilayah::factory()->create();
        $matching = Laporan::factory()->create(['wilayah_id' => $wilayah->id]);
        Laporan::factory()->create(['wilayah_id' => Wilayah::factory()->create()->id]); // noise — different wilayah

        $resultIds = Laporan::query()->filterWilayah($wilayah->id)->pluck('id');

        $this->assertTrue($resultIds->contains($matching->id));
        $this->assertCount(1, $resultIds);
    }

    public function test_filter_by_wilayah_null_returns_all(): void
    {
        Laporan::factory()->count(3)->create();

        $resultIds = Laporan::query()->filterWilayah(null)->pluck('id');

        $this->assertCount(3, $resultIds);
    }

    public function test_filter_wilayah_does_not_return_other_wilayah(): void
    {
        $wilayahA = Wilayah::factory()->create();
        $wilayahB = Wilayah::factory()->create();
        Laporan::factory()->create(['wilayah_id' => $wilayahA->id]);
        $laporanB = Laporan::factory()->create(['wilayah_id' => $wilayahB->id]);

        $resultIds = Laporan::query()->filterWilayah($wilayahA->id)->pluck('id');

        $this->assertFalse($resultIds->contains($laporanB->id));
    }

    // ── AT-06 | TC-07: Filter kategori ───────────────────────────────────────

    public function test_filter_by_kategori(): void
    {
        $kategori = KategoriLaporan::factory()->create();
        $matching = Laporan::factory()->create(['kategori_laporan_id' => $kategori->id]);
        Laporan::factory()->create(['kategori_laporan_id' => KategoriLaporan::factory()->create()->id]); // noise — different kategori

        $resultIds = Laporan::query()->filterKategori($kategori->id)->pluck('id');

        $this->assertTrue($resultIds->contains($matching->id));
        $this->assertCount(1, $resultIds);
    }

    public function test_filter_by_kategori_null_returns_all(): void
    {
        Laporan::factory()->count(3)->create();

        $resultIds = Laporan::query()->filterKategori(null)->pluck('id');

        $this->assertCount(3, $resultIds);
    }

    public function test_filter_kategori_does_not_return_other_kategori(): void
    {
        $kategoriA = KategoriLaporan::factory()->create();
        $kategoriB = KategoriLaporan::factory()->create();
        Laporan::factory()->create(['kategori_laporan_id' => $kategoriA->id]);
        $laporanB  = Laporan::factory()->create(['kategori_laporan_id' => $kategoriB->id]);

        $resultIds = Laporan::query()->filterKategori($kategoriA->id)->pluck('id');

        $this->assertFalse($resultIds->contains($laporanB->id));
    }

    // ── Validation: wilayah_id & kategori_id must exist in DB ─────────────────

    public function test_filter_wilayah_id_not_in_db_fails_validation(): void
    {
        $request = FilterLaporanRequest::create('/admin/laporan', 'GET', [
            'wilayah_id' => 99999,
        ]);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('wilayah_id'));
    }

    public function test_filter_kategori_id_not_in_db_fails_validation(): void
    {
        $request = FilterLaporanRequest::create('/admin/laporan', 'GET', [
            'kategori_id' => 99999,
        ]);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('kategori_id'));
    }

    public function test_filter_status_bayar_invalid_value_fails_validation(): void
    {
        $request = FilterLaporanRequest::create('/admin/laporan', 'GET', [
            'status_bayar' => 'tidak_valid',
        ]);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('status_bayar'));
    }

    public function test_keyword_exceeding_max_length_fails_validation(): void
    {
        $request = FilterLaporanRequest::create('/admin/laporan', 'GET', [
            'keyword' => str_repeat('a', 101),
        ]);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('keyword'));
    }
}
