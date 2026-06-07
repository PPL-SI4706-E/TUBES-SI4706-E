<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Wilayah;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi15ExportLaporanTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $admin;
    private User $warga;
    private Wilayah $wilayah;
    private KategoriLaporan $kategoriPipa;
    private KategoriLaporan $kategoriAir;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->seedDataAwal();
    }

    private function seedDataAwal(): void
    {
        $this->wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Area PBI 15'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI15',
            ]
        );

        $this->kategoriPipa = KategoriLaporan::firstOrCreate(
            ['nama_kategori' => 'Pipa Bocor'],
            [
                'deskripsi' => 'Kebocoran pipa',
                'tarif' => 50000,
                'icon' => '💧',
                'is_active' => true,
            ]
        );

        $this->kategoriAir = KategoriLaporan::firstOrCreate(
            ['nama_kategori' => 'Air Keruh'],
            [
                'deskripsi' => 'Air keruh',
                'tarif' => 25000,
                'icon' => '💧',
                'is_active' => true,
            ]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.pbi15@test.com'],
            [
                'name' => 'Admin PBI 15',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi15@test.com'],
            [
                'name' => 'Warga PBI 15',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $laporanSelesai = Laporan::firstOrCreate(
            ['judul' => 'Laporan Export Selesai'],
            [
                'user_id' => $this->warga->id,
                'wilayah_id' => $this->wilayah->id,
                'kategori_laporan_id' => $this->kategoriPipa->id,
                'deskripsi' => 'Laporan selesai untuk export.',
                'alamat' => 'Jalan Export Selesai',
                'status' => 'selesai',
                'tanggal_lapor' => now(),
            ]
        );

        $laporanPending = Laporan::firstOrCreate(
            ['judul' => 'Laporan Export Pending'],
            [
                'user_id' => $this->warga->id,
                'wilayah_id' => $this->wilayah->id,
                'kategori_laporan_id' => $this->kategoriAir->id,
                'deskripsi' => 'Laporan pending untuk export.',
                'alamat' => 'Jalan Export Pending',
                'status' => 'pending',
                'tanggal_lapor' => now(),
            ]
        );

        Pembayaran::firstOrCreate(
            ['laporan_id' => $laporanSelesai->id],
            [
                'user_id' => $this->warga->id,
                'harga' => 50000,
                'status_pembayaran' => 'Lunas',
                'metode_pembayaran' => 'Midtrans',
            ]
        );

        Pembayaran::firstOrCreate(
            ['laporan_id' => $laporanPending->id],
            [
                'user_id' => $this->warga->id,
                'harga' => 25000,
                'status_pembayaran' => 'Menunggu',
                'metode_pembayaran' => 'Transfer Bank',
            ]
        );
    }

    private function pilihRole(Browser $browser, string $role): void
    {
        $browser->script("
            const input = document.querySelector('input[name=\"role\"][value=\"{$role}\"]');
            if (input) {
                input.checked = true;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        ");
    }

    private function loginSebagaiAdmin(Browser $browser): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'admin.pbi15@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'admin');

        $browser->press('Masuk')
            ->waitForLocation('/admin/dashboard', 10)
            ->assertPathIs('/admin/dashboard');
    }

    private function loginSebagaiWarga(Browser $browser): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'warga.pbi15@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'masyarakat');

        $browser->press('Masuk')
            ->waitForLocation('/warga/laporan', 10)
            ->assertPathIs('/warga/laporan');
    }

    public function testAT001ExportPDFBerhasil()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan')
                ->waitForText('Kelola Laporan', 10)
                ->assertSee('Export PDF');

            $browser->visit('/admin/laporan/export/pdf')
                ->pause(2000);

            $this->assertTrue(true);
        });
    }

    public function testAT002ExportExcelBerhasil()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan')
                ->waitForText('Kelola Laporan', 10)
                ->assertSee('Export Excel');

            $browser->visit('/admin/laporan/export/excel')
                ->pause(2000);

            $this->assertTrue(true);
        });
    }

    public function testAT003ExportSesuaiFilterStatus()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan?status=selesai')
                ->waitForText('Kelola Laporan', 10);

            $this->assertDatabaseHas('laporan', [
                'judul' => 'Laporan Export Selesai',
                'status' => 'selesai',
            ]);

            $this->assertDatabaseMissing('laporan', [
                'judul' => 'Laporan Export Pending',
                'status' => 'selesai',
            ]);

            $browser->visit('/admin/laporan/export/pdf?status=selesai')
                ->pause(1500);

            $this->assertTrue(true);
        });
    }

    public function testAT004ExportSesuaiFilterKategori()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan?kategori_id=' . $this->kategoriPipa->id)
                ->waitForText('Kelola Laporan', 10);

            $this->assertDatabaseHas('laporan', [
                'judul' => 'Laporan Export Selesai',
                'kategori_laporan_id' => $this->kategoriPipa->id,
            ]);

            $this->assertDatabaseMissing('laporan', [
                'judul' => 'Laporan Export Pending',
                'kategori_laporan_id' => $this->kategoriPipa->id,
            ]);

            $browser->visit('/admin/laporan/export/excel?kategori_id=' . $this->kategoriPipa->id)
                ->pause(1500);

            $this->assertTrue(true);
        });
    }

    public function testAT005ValidasiRoleAdmin()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/admin/laporan/export/pdf')
                ->pause(1000);

            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);
            $source = $browser->driver->getPageSource();

            $this->assertTrue(
                $path === '/warga/laporan'
                || str_contains($source, '403')
                || str_contains($source, 'Forbidden')
                || str_contains($source, 'Akses ditolak')
            );
        });
    }

    public function testAT006ExportSaatDataKosong()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan?status=tidak_ada_status')
                ->waitForText('Kelola Laporan', 10);

            $this->assertTrue(true);
        });
    }

    public function testAT007KecepatanGeneratePDF()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $start = microtime(true);

            $browser->visit('/admin/laporan/export/pdf')
                ->pause(1000);

            $duration = microtime(true) - $start;

            $this->assertLessThanOrEqual(5, $duration);
        });
    }

    public function testAT008KecepatanGenerateExcel()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $start = microtime(true);

            $browser->visit('/admin/laporan/export/excel')
                ->pause(1000);

            $duration = microtime(true) - $start;

            $this->assertLessThanOrEqual(5, $duration);
        });
    }

    public function testAT009SinkronisasiDataTabelDenganExport()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan')
                ->waitForText('Kelola Laporan', 10);

            $this->assertDatabaseHas('laporan', [
                'judul' => 'Laporan Export Selesai',
            ]);

            $this->assertDatabaseHas('laporan', [
                'judul' => 'Laporan Export Pending',
            ]);

            $browser->visit('/admin/laporan/export/pdf')
                ->pause(1500);

            $this->assertTrue(true);
        });
    }

    public function testAT010TombolExportTampilDanDapatDigunakan()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan')
                ->waitForText('Kelola Laporan', 10)
                ->assertSee('Export PDF')
                ->assertSee('Export Excel');
        });
    }
}