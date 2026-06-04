<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-17 — Automated E2E Testing: Fitur Filter & Pencarian Laporan
 *
 * Test Cases:
 *   TC-01  Pencarian dengan kata kunci valid (nama warga)
 *   TC-02  Pencarian kata kunci tidak ditemukan
 *   TC-03  Filter berdasarkan status bayar (Lunas)
 *   TC-04  Filter berdasarkan rentang bulan valid
 *   TC-05  Filter rentang bulan tidak valid (bulan akhir < bulan awal)
 *   TC-06  Filter berdasarkan wilayah
 *   TC-07  Filter berdasarkan kategori
 *   TC-08  Kombinasi multi-filter
 *   TC-09  Reset filter mengembalikan seluruh data
 *   TC-10  Hasil filter kosong menampilkan empty state
 *   TC-11  Filter tetap aktif saat berpindah halaman pagination
 */
class Pbi17FilterLaporanTest extends DuskTestCase
{
    protected User $admin;
    protected Wilayah $wilayahCidadap;
    protected Wilayah $wilayahCoblong;
    protected KategoriLaporan $kategoriPipaBocor;
    protected KategoriLaporan $kategoriAirKeruh;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh');

        $this->wilayahCidadap = Wilayah::create([
            'nama_wilayah' => 'Kecamatan Cidadap',
            'tipe'         => 'kecamatan',
            'kode_wilayah' => 'CDP-01',
        ]);

        $this->wilayahCoblong = Wilayah::create([
            'nama_wilayah' => 'Kecamatan Coblong',
            'tipe'         => 'kecamatan',
            'kode_wilayah' => 'CBL-01',
        ]);

        $this->kategoriPipaBocor = KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'deskripsi'     => 'Kebocoran pipa distribusi air',
            'tarif'         => 50000,
            'icon'          => 'droplet',
            'is_active'     => true,
        ]);

        $this->kategoriAirKeruh = KategoriLaporan::create([
            'nama_kategori' => 'Air Keruh',
            'deskripsi'     => 'Kualitas air keruh atau berbau',
            'tarif'         => 0,
            'icon'          => 'droplet',
            'is_active'     => true,
        ]);

        $this->admin = User::create([
            'name'      => 'Admin PBI 17',
            'email'     => 'admin.pbi17@tirtabantu.com',
            'password'  => bcrypt('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);
    }

    private function buatLaporan(array $wargaAttr, array $laporanAttr, ?string $statusPembayaran = null): Laporan
    {
        $warga = User::create(array_merge([
            'password'  => bcrypt('password'),
            'role'      => 'masyarakat',
            'is_active' => true,
        ], $wargaAttr));

        $laporan = Laporan::create(array_merge([
            'user_id'            => $warga->id,
            'wilayah_id'         => $this->wilayahCidadap->id,
            'kategori_laporan_id'=> $this->kategoriPipaBocor->id,
            'judul'              => 'Laporan Test',
            'deskripsi'          => 'Deskripsi laporan untuk keperluan pengujian.',
            'alamat'             => 'Jalan Test Nomor 1',
            'status'             => 'pending',
            'tanggal_lapor'      => now(),
        ], $laporanAttr));

        if ($statusPembayaran) {
            Pembayaran::create([
                'laporan_id'        => $laporan->id,
                'user_id'           => $warga->id,
                'harga'             => 50000,
                'status_pembayaran' => $statusPembayaran,
            ]);
        }

        return $laporan;
    }

    // ── TC-01: Pencarian kata kunci valid ─────────────────────────────────────

    public function test_TC01_pencarian_kata_kunci_valid_nama_warga(): void
    {
        $this->buatLaporan(['name' => 'Siti Aminah', 'email' => 'siti@test.com'], []);
        $this->buatLaporan(['name' => 'Budi Mamat',  'email' => 'budi@test.com'], []);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index')
                ->assertSee('Kelola Laporan');

            $browser->type('#keyword', 'Siti')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Siti Aminah')
                ->assertDontSee('Budi Mamat');
        });
    }

    public function test_TC01b_pencarian_kata_kunci_valid_alamat(): void
    {
        $this->buatLaporan(['name' => 'Warga Merdeka', 'email' => 'merdeka@test.com'], ['alamat' => 'Jalan Merdeka Nomor 5']);
        $this->buatLaporan(['name' => 'Warga Kenanga',  'email' => 'kenanga@test.com'], ['alamat' => 'Jalan Kenanga Nomor 8']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->type('#keyword', 'Merdeka')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Jalan Merdeka Nomor 5')
                ->assertDontSee('Jalan Kenanga Nomor 8');
        });
    }

    // ── TC-02: Pencarian kata kunci tidak ditemukan ───────────────────────────

    public function test_TC02_pencarian_kata_kunci_tidak_ditemukan(): void
    {
        $this->buatLaporan(['name' => 'Warga Ada', 'email' => 'ada@test.com'], ['alamat' => 'Jalan Ada Datanya']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->type('#keyword', 'XYZ_TIDAK_ADA_99999')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Laporan tidak ditemukan.');
        });
    }

    // ── TC-03: Filter berdasarkan status bayar ────────────────────────────────

    public function test_TC03_filter_status_bayar_lunas(): void
    {
        $this->buatLaporan(['name' => 'Warga Lunas', 'email' => 'lunas@test.com'],  [], 'Lunas');
        $this->buatLaporan(['name' => 'Warga Belum', 'email' => 'belum@test.com'],  [], 'Menunggu');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->select('#status_bayar', 'lunas')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Warga Lunas')
                ->assertDontSee('Warga Belum');
        });
    }

    public function test_TC03b_filter_status_bayar_belum_lunas(): void
    {
        $this->buatLaporan(['name' => 'Warga Belum Bayar', 'email' => 'belum2@test.com'], [], 'Menunggu');
        $this->buatLaporan(['name' => 'Warga Sudah Lunas',  'email' => 'lunas2@test.com'], [], 'Lunas');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->select('#status_bayar', 'belum_lunas')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Warga Belum Bayar')
                ->assertDontSee('Warga Sudah Lunas');
        });
    }

    public function test_TC03c_filter_status_bayar_menunggu_verifikasi(): void
    {
        $this->buatLaporan(['name' => 'Warga Verifikasi', 'email' => 'verif@test.com'], [], 'Terverifikasi');
        $this->buatLaporan(['name' => 'Warga Lunas Juga',  'email' => 'lunas3@test.com'], [], 'Lunas');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->select('#status_bayar', 'menunggu_verifikasi')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Warga Verifikasi')
                ->assertDontSee('Warga Lunas Juga');
        });
    }

    // ── TC-04: Filter rentang bulan valid ─────────────────────────────────────

    public function test_TC04_filter_rentang_bulan_valid(): void
    {
        $this->buatLaporan(['name' => 'Warga Januari', 'email' => 'jan@test.com'], ['tanggal_lapor' => '2026-01-15 08:00:00']);
        $this->buatLaporan(['name' => 'Warga Maret',   'email' => 'mar@test.com'], ['tanggal_lapor' => '2026-03-20 08:00:00']);
        $this->buatLaporan(['name' => 'Warga Mei',     'email' => 'mei@test.com'], ['tanggal_lapor' => '2026-05-10 08:00:00']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            // Set bulan_awal & bulan_akhir via JS (type="month" tidak support type() di semua ChromeDriver versi)
            $browser->script([
                "document.getElementById('bulan_awal').value = '2026-02';",
                "document.getElementById('bulan_akhir').value = '2026-04';",
            ]);

            $browser->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Warga Maret')
                ->assertDontSee('Warga Januari')
                ->assertDontSee('Warga Mei');
        });
    }

    // ── TC-05: Filter rentang bulan tidak valid ───────────────────────────────

    public function test_TC05_filter_rentang_bulan_tidak_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->script([
                "document.getElementById('bulan_awal').value = '2026-05';",
                "document.getElementById('bulan_akhir').value = '2026-01';",
            ]);

            $browser->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Rentang bulan tidak valid.');
        });
    }

    // ── TC-06: Filter berdasarkan wilayah ────────────────────────────────────

    public function test_TC06_filter_berdasarkan_wilayah(): void
    {
        $this->buatLaporan(
            ['name' => 'Warga Cidadap', 'email' => 'cidadap@test.com'],
            ['wilayah_id' => $this->wilayahCidadap->id]
        );
        $this->buatLaporan(
            ['name' => 'Warga Coblong', 'email' => 'coblong@test.com'],
            ['wilayah_id' => $this->wilayahCoblong->id]
        );

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->select('#wilayah_id', (string) $this->wilayahCidadap->id)
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Warga Cidadap')
                ->assertDontSee('Warga Coblong');
        });
    }

    // ── TC-07: Filter berdasarkan kategori ───────────────────────────────────

    public function test_TC07_filter_berdasarkan_kategori(): void
    {
        $this->buatLaporan(
            ['name' => 'Warga Pipa Bocor', 'email' => 'pipa@test.com'],
            ['kategori_laporan_id' => $this->kategoriPipaBocor->id]
        );
        $this->buatLaporan(
            ['name' => 'Warga Air Keruh', 'email' => 'keruh@test.com'],
            ['kategori_laporan_id' => $this->kategoriAirKeruh->id]
        );

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->select('#kategori_id', (string) $this->kategoriPipaBocor->id)
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Warga Pipa Bocor')
                ->assertDontSee('Warga Air Keruh');
        });
    }

    // ── TC-08: Kombinasi multi-filter ─────────────────────────────────────────

    public function test_TC08_kombinasi_multi_filter(): void
    {
        // Laporan yang harus muncul
        $this->buatLaporan(
            ['name' => 'Siti Filter', 'email' => 'siti.filter@test.com'],
            [
                'wilayah_id'          => $this->wilayahCidadap->id,
                'kategori_laporan_id' => $this->kategoriPipaBocor->id,
                'alamat'              => 'Jalan Filter Cocok',
                'tanggal_lapor'       => '2026-03-12 10:00:00',
            ],
            'Lunas'
        );

        // Laporan noise — beda status & beda bulan
        $this->buatLaporan(
            ['name' => 'Budi Tidak Cocok', 'email' => 'budi.noise@test.com'],
            [
                'wilayah_id'          => $this->wilayahCoblong->id,
                'kategori_laporan_id' => $this->kategoriAirKeruh->id,
                'tanggal_lapor'       => '2026-04-12 10:00:00',
            ],
            'Menunggu'
        );

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->type('#keyword', 'Siti')
                ->select('#status_bayar', 'lunas')
                ->select('#wilayah_id', (string) $this->wilayahCidadap->id)
                ->select('#kategori_id', (string) $this->kategoriPipaBocor->id);

            $browser->script([
                "document.getElementById('bulan_awal').value = '2026-03';",
                "document.getElementById('bulan_akhir').value = '2026-03';",
            ]);

            $browser->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Siti Filter')
                ->assertSee('Jalan Filter Cocok')
                ->assertDontSee('Budi Tidak Cocok');
        });
    }

    // ── TC-09: Reset filter ───────────────────────────────────────────────────

    public function test_TC09_reset_filter_mengembalikan_seluruh_data(): void
    {
        $this->buatLaporan(['name' => 'Warga Pertama', 'email' => 'pertama@test.com'], []);
        $this->buatLaporan(['name' => 'Warga Kedua',   'email' => 'kedua@test.com'],   []);

        $this->browse(function (Browser $browser) {
            // Terapkan filter dulu
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->type('#keyword', 'Pertama')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Warga Pertama')
                ->assertDontSee('Warga Kedua');

            // Klik Reset Filter
            $browser->clickLink('Reset Filter')
                ->pause(1000);

            // Semua data kembali tampil & semua field kosong
            $browser->assertSee('Warga Pertama')
                ->assertSee('Warga Kedua');

            $keywordValue = $browser->value('#keyword');
            $this->assertSame('', $keywordValue, 'Field keyword seharusnya kosong setelah reset.');

            $statusValue = $browser->value('#status_bayar');
            $this->assertSame('', $statusValue, 'Dropdown status_bayar seharusnya kosong setelah reset.');
        });
    }

    // ── TC-10: Hasil filter kosong menampilkan empty state ────────────────────

    public function test_TC10_hasil_filter_kosong_tampil_pesan_empty_state(): void
    {
        $this->buatLaporan(
            ['name' => 'Warga Kategori A', 'email' => 'katA@test.com'],
            ['kategori_laporan_id' => $this->kategoriPipaBocor->id]
        );
        // kategoriAirKeruh tidak memiliki laporan

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->select('#kategori_id', (string) $this->kategoriAirKeruh->id)
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Tidak ada laporan yang sesuai dengan filter yang dipilih.');
        });
    }

    public function test_TC10b_hasil_filter_kosong_dengan_keyword(): void
    {
        $this->buatLaporan(['name' => 'Warga Ada Data', 'email' => 'ada2@test.com'], []);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->type('#keyword', 'TidakAdaLaporanIni_99999')
                ->press('Terapkan Filter')
                ->pause(1000);

            $browser->assertSee('Laporan tidak ditemukan.');
        });
    }

    // ── TC-11: Filter tetap aktif saat berpindah halaman pagination ───────────

    public function test_TC11_filter_tetap_aktif_saat_paginasi(): void
    {
        // Buat 16 laporan (melebihi 15 per halaman) dengan alamat yang sama
        for ($i = 1; $i <= 16; $i++) {
            $this->buatLaporan(
                ['name' => "Warga Paginasi {$i}", 'email' => "paginasi{$i}@test.com"],
                ['alamat' => 'Jalan Persistensi']
            );
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.index');

            $browser->type('#keyword', 'Persistensi')
                ->press('Terapkan Filter')
                ->pause(1000);

            // Halaman 1: 15 hasil, ada link ke halaman 2
            $browser->assertSee('Jalan Persistensi');

            // Klik pagination halaman 2
            $browser->clickLink('2')
                ->pause(1000);

            // Hasil masih konsisten — keyword masih aktif di URL & data tetap tampil
            $browser->assertQueryStringHas('keyword', 'Persistensi')
                ->assertSee('Jalan Persistensi');
        });
    }
}
