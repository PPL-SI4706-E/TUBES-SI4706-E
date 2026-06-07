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

class Pbi13DashboardTest extends DuskTestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->seedData();
    }

    private function seedData(): void
    {
        $wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Area Dashboard'],
            ['tipe' => 'kecamatan', 'kode_wilayah' => 'DSH']
        );

        $kategori = KategoriLaporan::firstOrCreate(
            ['nama_kategori' => 'Pipa Bocor'],
            [
                'deskripsi' => 'Kebocoran pipa',
                'tarif' => 50000,
                'icon' => '💧',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@tirtabantu.id'],
            [
                'name' => 'Admin Dashboard',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $warga = User::firstOrCreate(
            ['email' => 'warga.dashboard@test.com'],
            [
                'name' => 'Warga Dashboard',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $wilayah->id,
            ]
        );

        $laporan = Laporan::firstOrCreate(
            ['judul' => 'Laporan Dashboard Test'],
            [
                'user_id' => $warga->id,
                'wilayah_id' => $wilayah->id,
                'kategori_laporan_id' => $kategori->id,
                'deskripsi' => 'Laporan untuk dashboard admin.',
                'alamat' => 'Jalan Dashboard Nomor 1',
                'status' => 'pending',
                'tanggal_lapor' => now(),
            ]
        );

        Pembayaran::firstOrCreate(
            ['laporan_id' => $laporan->id],
            [
                'user_id' => $warga->id,
                'harga' => 50000,
                'status_pembayaran' => 'Lunas',
                'metode_pembayaran' => 'Midtrans',
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
        $browser->visit('/')->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('#email', 'admin@tirtabantu.id')
            ->type('#password', 'password');

        $this->pilihRole($browser, 'admin');

        $browser->press('Masuk')
            ->waitForLocation('/admin/dashboard', 10)
            ->assertPathIs('/admin/dashboard');
    }

    public function testTC1301AdminBerhasilMembukaDashboard()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->assertSee('Dashboard Admin')
                ->assertSee('Ringkasan sistem pelaporan dan distribusi air bersih');
        });
    }

    public function testTC1302DashboardMenampilkanStatistikUtama()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->assertSee('Total Pendapatan')
                ->assertSee('Rasio Penyelesaian Laporan')
                ->assertSee('Laporan Perlu Tindakan');
        });
    }

    public function testTC1303DashboardMenampilkanLaporanTerbaru()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->assertSee('Laporan Terbaru')
                ->assertSee('Jalan Dashboard Nomor 1')
                ->assertSee('Pipa Bocor');
        });
    }

    public function testTC1304DashboardMenampilkanPersebaranWilayah()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->assertSee('Persebaran Wilayah')
                ->assertSee('Area Dashboard');
        });
    }

    public function testTC1305DashboardMenampilkanDataPendapatan()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->assertSee('Total Pendapatan')
                ->assertSee('Rp');
        });
    }

    public function testTC1306UserNonAdminTidakBisaAksesDashboardAdmin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                ->waitForText('Masuk ke Sistem', 10)
                ->type('#email', 'warga.dashboard@test.com')
                ->type('#password', 'password');

            $this->pilihRole($browser, 'masyarakat');

            $browser->press('Masuk')
                ->waitForLocation('/warga/laporan', 10)
                ->assertPathIs('/warga/laporan');

            $browser->visit('/admin/dashboard')
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
}