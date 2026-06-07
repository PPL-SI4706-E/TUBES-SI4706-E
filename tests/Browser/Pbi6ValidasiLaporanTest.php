<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi6ValidasiLaporanTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $admin;
    private User $warga;
    private Wilayah $wilayah;
    private KategoriLaporan $kategori;

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
            ['nama_wilayah' => 'Area PBI 6'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI6',
            ]
        );

        $this->kategori = KategoriLaporan::firstOrCreate(
            ['nama_kategori' => 'Pipa Bocor'],
            [
                'deskripsi' => 'Kebocoran pipa air',
                'tarif' => 50000,
                'icon' => '💧',
                'is_active' => true,
            ]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.pbi6@test.com'],
            [
                'name' => 'Admin PBI 6',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi6@test.com'],
            [
                'name' => 'Warga PBI 6',
                'phone' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
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
            ->type('email', 'admin.pbi6@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'admin');

        $browser->press('Masuk')
            ->waitForLocation('/admin/dashboard', 10)
            ->assertPathIs('/admin/dashboard');
    }

    private function buatLaporan(string $statusPembayaran = 'Lunas', array $override = []): Laporan
    {
        $laporan = Laporan::create(array_merge([
            'user_id' => $this->warga->id,
            'wilayah_id' => $this->wilayah->id,
            'kategori_laporan_id' => $this->kategori->id,
            'judul' => 'Laporan Validasi PBI 6',
            'deskripsi' => 'Pipa bocor cukup deras dan perlu segera ditangani.',
            'alamat' => 'Jalan Validasi PBI 6 Nomor 1',
            'status' => 'pending',
            'tanggal_lapor' => now(),
            'catatan_admin' => null,
        ], $override));

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => -6.973000,
            'longitude' => 107.630000,
        ]);

        Pembayaran::create([
            'laporan_id' => $laporan->id,
            'user_id' => $this->warga->id,
            'harga' => 50000,
            'status_pembayaran' => $statusPembayaran,
        ]);

        return $laporan;
    }

    private function submitValidasiViaJs(Browser $browser, Laporan $laporan, string $status, string $catatan): void
    {
        $browser->script("
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/laporan/{$laporan->id}/validasi';

            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                || document.querySelector('input[name=\"_token\"]')?.value;

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = '{$status}';
            form.appendChild(statusInput);

            const catatanInput = document.createElement('input');
            catatanInput.type = 'hidden';
            catatanInput.name = 'catatan_admin';
            catatanInput.value = '{$catatan}';
            form.appendChild(catatanInput);

            document.body.appendChild(form);
            form.submit();
        ");
    }

    public function testTC001BerhasilValidasiLaporanPenangananLapangan()
    {
        $laporan = $this->buatLaporan('Lunas', [
            'alamat' => 'Jalan Lapangan PBI 6',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan/' . $laporan->id)
                ->waitForText('Detail Laporan', 10)
                ->assertSee('Validasi Laporan')
                ->assertSee('Terima — Penanganan Lapangan');

            $this->submitValidasiViaJs(
                $browser,
                $laporan,
                'diterima',
                'Laporan valid dan perlu penanganan lapangan.'
            );

            $browser->pause(1500)
                ->assertPathIs('/admin/laporan/' . $laporan->id)
                ->assertSee('Laporan diterima dan siap untuk penugasan petugas lapangan.')
                ->assertSee('Laporan Diterima');

            $this->assertDatabaseHas('laporan', [
                'id' => $laporan->id,
                'status' => 'diterima',
                'catatan_admin' => 'Laporan valid dan perlu penanganan lapangan.',
            ]);
        });
    }

    public function testTC002BerhasilValidasiDenganSolusiVirtual()
    {
        $laporan = $this->buatLaporan('Lunas', [
            'alamat' => 'Jalan Virtual PBI 6',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan/' . $laporan->id)
                ->waitForText('Detail Laporan', 10)
                ->assertSee('Solusi Virtual');

            $this->submitValidasiViaJs(
                $browser,
                $laporan,
                'selesai',
                'Cek pipa rumah'
            );

            $browser->pause(1500)
                ->assertPathIs('/admin/laporan/' . $laporan->id)
                ->assertSee('Laporan diselesaikan dengan solusi virtual.')
                ->assertSee('Selesai (Solusi Virtual)')
                ->assertSee('Solusi: Cek pipa rumah');

            $this->assertDatabaseHas('laporan', [
                'id' => $laporan->id,
                'status' => 'selesai',
                'catatan_admin' => 'Cek pipa rumah',
            ]);
        });
    }

    public function testTC003BerhasilMenolakLaporan()
    {
        $laporan = $this->buatLaporan('Lunas', [
            'alamat' => 'Jalan Tolak PBI 6',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan/' . $laporan->id)
                ->waitForText('Detail Laporan', 10)
                ->assertSee('Tolak Laporan');

            $this->submitValidasiViaJs(
                $browser,
                $laporan,
                'ditolak',
                'Foto tidak jelas'
            );

            $browser->pause(1500)
                ->assertPathIs('/admin/laporan/' . $laporan->id)
                ->assertSee('Laporan berhasil ditolak.')
                ->assertSee('Laporan Ditolak')
                ->assertSee('Alasan: Foto tidak jelas');

            $this->assertDatabaseHas('laporan', [
                'id' => $laporan->id,
                'status' => 'ditolak',
                'catatan_admin' => 'Foto tidak jelas',
            ]);
        });
    }

    public function testTC004GagalValidasiKarenaPembayaranBelumSelesai()
    {
        $laporan = $this->buatLaporan('Menunggu', [
            'alamat' => 'Jalan Pending Payment PBI 6',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/laporan/' . $laporan->id)
                ->waitForText('Detail Laporan', 10)
                ->assertSee('Validasi Terkunci')
                ->assertSee('Laporan ini belum dapat divalidasi karena pembayaran belum diverifikasi.')
                ->assertSee('Menunggu');

            $this->submitValidasiViaJs(
                $browser,
                $laporan,
                'diterima',
                'Laporan valid tetapi pembayaran belum lunas.'
            );

            $browser->pause(1500)
                ->assertPathIs('/admin/laporan/' . $laporan->id)
                ->assertSee('Laporan tidak dapat divalidasi')
                ->assertSee('Pembayaran belum diverifikasi');

            $this->assertDatabaseHas('laporan', [
                'id' => $laporan->id,
                'status' => 'pending',
            ]);
        });
    }
}