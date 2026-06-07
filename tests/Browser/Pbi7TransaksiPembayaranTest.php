<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi7TransaksiPembayaranTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $warga;
    private User $wargaLain;
    private Wilayah $wilayah;
    private KategoriLaporan $kategori;

    private string $validPhotoPath;
    private string $invalidDocPath;
    private string $largePhotoPath;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->seedDataAwal();
        $this->buatFileTesting();
    }

    private function seedDataAwal(): void
    {
        $this->wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Area PBI 7'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI7',
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

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi7@test.com'],
            [
                'name' => 'Warga PBI 7',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $this->wargaLain = User::firstOrCreate(
            ['email' => 'warga.lain.pbi7@test.com'],
            [
                'name' => 'Warga Lain PBI 7',
                'phone' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );
    }

    private function buatFileTesting(): void
    {
        $tempDir = storage_path('app/testing');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $this->validPhotoPath = $tempDir . '/bukti_pembayaran.jpg';

        $jpg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8PDw8QDw8PDw8PDw8PDw8PFREWFhURFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGi0lHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAgMBIgACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAABf/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/Aaf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/Aaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Aqf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IV//2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z'
        );

        file_put_contents($this->validPhotoPath, $jpg);

        $this->invalidDocPath = $tempDir . '/dokumen.pdf';
        File::put($this->invalidDocPath, 'Ini file PDF palsu untuk test.');

        $this->largePhotoPath = $tempDir . '/foto_besar.jpg';
        file_put_contents($this->largePhotoPath, str_repeat('A', 6 * 1024 * 1024));
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

    private function loginSebagaiWarga(Browser $browser, string $email = 'warga.pbi7@test.com'): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', $email)
            ->type('password', 'password');

        $this->pilihRole($browser, 'masyarakat');

        $browser->press('Masuk')
            ->waitForLocation('/warga/laporan', 10)
            ->assertPathIs('/warga/laporan');
    }

    private function buatLaporan(User $user, array $override = []): Laporan
    {
        $laporan = Laporan::create(array_merge([
            'user_id' => $user->id,
            'wilayah_id' => $this->wilayah->id,
            'kategori_laporan_id' => $this->kategori->id,
            'judul' => 'Laporan Pembayaran PBI 7',
            'deskripsi' => 'Pipa bocor cukup deras dan perlu segera ditangani.',
            'alamat' => 'Jalan Pembayaran PBI 7 Nomor 1',
            'status' => 'pending',
            'tanggal_lapor' => now(),
        ], $override));

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => -6.973000,
            'longitude' => 107.630000,
        ]);

        return $laporan;
    }

    private function buatPembayaran(User $user, string $status = 'Menunggu', ?Laporan $laporan = null, array $override = []): Pembayaran
    {
        $laporan = $laporan ?: $this->buatLaporan($user);

        return Pembayaran::create(array_merge([
            'laporan_id' => $laporan->id,
            'user_id' => $user->id,
            'harga' => 50000,
            'status_pembayaran' => $status,
            'metode_pembayaran' => null,
            'bukti_transaksi' => null,
            'snap_token' => null,
        ], $override));
    }

    private function uploadPembayaranViaJs(Browser $browser, Pembayaran $pembayaran, string $metode, ?string $filePath = null): void
    {
        $browser->script("
            const form = document.createElement('form');
            form.method = 'POST';
            form.enctype = 'multipart/form-data';
            form.action = '/warga/pembayaran/{$pembayaran->id}/upload';

            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                || document.querySelector('input[name=\"_token\"]')?.value;

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            const metodeInput = document.createElement('input');
            metodeInput.type = 'hidden';
            metodeInput.name = 'metode_pembayaran';
            metodeInput.value = '{$metode}';
            form.appendChild(metodeInput);

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'bukti_transaksi';
            fileInput.id = 'dusk_bukti_transaksi';
            form.appendChild(fileInput);

            document.body.appendChild(form);
        ");

        if ($filePath) {
            $browser->attach('#dusk_bukti_transaksi', $filePath);
        }

        $browser->script("
            document.querySelector('form[action=\"/warga/pembayaran/{$pembayaran->id}/upload\"]').submit();
        ");
    }

    public function testTC01MenampilkanDetailTagihanAktif()
    {
        $laporan = $this->buatLaporan($this->warga, [
            'judul' => 'Invoice Pipa Bocor PBI 7',
            'alamat' => 'Jalan Invoice PBI 7',
        ]);

        $this->buatPembayaran($this->warga, 'Menunggu', $laporan, [
            'harga' => 100000,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10)
                ->assertSee('Invoice Pipa Bocor PBI 7')
                ->assertSee('Rp')
                ->assertSee('100.000')
                ->assertSee('Menunggu');
        });
    }

    public function testTC02PembayaranManualBerhasilUploadBukti()
    {
        $pembayaran = $this->buatPembayaran($this->warga, 'Menunggu');

        $this->browse(function (Browser $browser) use ($pembayaran) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10);

            $this->uploadPembayaranViaJs(
                $browser,
                $pembayaran,
                'Transfer Bank',
                $this->validPhotoPath
            );

            $browser->pause(1500)
                ->assertPathIs('/warga/pembayaran')
                ->assertSee('Pembayaran Berhasil');

            $pembayaran->refresh();

            $this->assertEquals('Terverifikasi', $pembayaran->status_pembayaran);
            $this->assertEquals('Transfer Bank', $pembayaran->metode_pembayaran);
            $this->assertNotNull($pembayaran->bukti_transaksi);
        });
    }

    public function testTC03PembayaranManualGagalFormatTidakValid()
    {
        $pembayaran = $this->buatPembayaran($this->warga, 'Menunggu');

        $this->browse(function (Browser $browser) use ($pembayaran) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10);

            $this->uploadPembayaranViaJs(
                $browser,
                $pembayaran,
                'Transfer Bank',
                $this->invalidDocPath
            );

            $browser->pause(1500)
                ->assertPathIs('/warga/pembayaran')
                ->assertSee('Format file tidak didukung');

            $pembayaran->refresh();

            $this->assertEquals('Menunggu', $pembayaran->status_pembayaran);
            $this->assertNull($pembayaran->bukti_transaksi);
        });
    }

    public function testTC04PembayaranManualGagalUkuranMelebihiBatas()
    {
        $pembayaran = $this->buatPembayaran($this->warga, 'Menunggu');

        $this->browse(function (Browser $browser) use ($pembayaran) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10);

            $this->uploadPembayaranViaJs(
                $browser,
                $pembayaran,
                'Transfer Bank',
                $this->largePhotoPath
            );

            $browser->pause(1500)
                ->assertPathIs('/warga/pembayaran')
                ->assertSee('Ukuran file terlalu besar');

            $pembayaran->refresh();

            $this->assertEquals('Menunggu', $pembayaran->status_pembayaran);
            $this->assertNull($pembayaran->bukti_transaksi);
        });
    }

    public function testTC05GenerateSnapTokenMidtransBerhasil()
    {
        $pembayaran = $this->buatPembayaran($this->warga, 'Menunggu');

        $this->browse(function (Browser $browser) use ($pembayaran) {

            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10);

            $pembayaran->update([
                'snap_token' => 'snap-token-test-pbi7'
            ]);

            $pembayaran->refresh();

            $this->assertNotNull($pembayaran->snap_token);
            $this->assertEquals(
                'snap-token-test-pbi7',
                $pembayaran->snap_token
            );
        });
    }

    public function testTC06PembayaranKadaluarsa()
    {
        $pembayaran = $this->buatPembayaran($this->warga, 'Menunggu');

        $pembayaran->created_at = now()->subHours(25);
        $pembayaran->updated_at = now()->subHours(25);
        $pembayaran->save();

        $this->browse(function (Browser $browser) use ($pembayaran) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran/' . $pembayaran->id . '/snap-token')
                ->pause(1500);

            $pembayaran->refresh();

            $this->assertEquals('Kadaluarsa', $pembayaran->status_pembayaran);
        });
    }

    public function testTC07RiwayatPembayaranTampil()
    {
        $pembayaran1 = $this->buatPembayaran($this->warga, 'Terverifikasi', null, [
            'harga' => 75000,
            'metode_pembayaran' => 'Transfer Bank',
        ]);

        $pembayaran2 = $this->buatPembayaran($this->warga, 'Lunas', null, [
            'harga' => 100000,
            'metode_pembayaran' => 'Midtrans',
        ]);

        $this->browse(function (Browser $browser) use ($pembayaran1, $pembayaran2) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10);

            $this->assertDatabaseHas('pembayaran', [
                'id' => $pembayaran1->id,
                'status_pembayaran' => 'Terverifikasi',
                'harga' => 75000,
            ]);

            $this->assertDatabaseHas('pembayaran', [
                'id' => $pembayaran2->id,
                'status_pembayaran' => 'Lunas',
                'harga' => 100000,
            ]);
        });
    }

    public function testTC08DataPembayaranUserLainTidakBocor()
    {
        $laporanWarga = $this->buatLaporan($this->warga, [
            'judul' => 'Tagihan Milik Warga PBI 7',
        ]);

        $laporanWargaLain = $this->buatLaporan($this->wargaLain, [
            'judul' => 'Tagihan Rahasia Warga Lain',
        ]);

        $this->buatPembayaran($this->warga, 'Menunggu', $laporanWarga);
        $this->buatPembayaran($this->wargaLain, 'Menunggu', $laporanWargaLain);

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiWarga($browser, 'warga.pbi7@test.com');

            $browser->visit('/warga/pembayaran')
                ->waitForText('Tagihan Milik Warga PBI 7', 10)
                ->assertSee('Tagihan Milik Warga PBI 7')
                ->assertDontSee('Tagihan Rahasia Warga Lain');
        });
    }

    public function testTC09AksesPembayaranTanpaTagihan()
    {
        $wargaKosong = User::create([
            'name' => 'Warga Kosong PBI 7',
            'email' => 'warga.kosong.pbi7.' . time() . '@test.com',
            'phone' => '083333333333',
            'password' => Hash::make('password'),
            'role' => 'masyarakat',
            'is_active' => true,
            'wilayah_id' => $this->wilayah->id,
        ]);

        $this->browse(function (Browser $browser) use ($wargaKosong) {
            $this->loginSebagaiWarga($browser, $wargaKosong->email);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10)
                ->assertDontSee('Menunggu')
                ->assertDontSee('Terverifikasi')
                ->assertDontSee('Lunas');
        });
    }

    public function testTC10TransferBankTanpaBuktiDitolak()
    {
        $pembayaran = $this->buatPembayaran($this->warga, 'Menunggu');

        $this->browse(function (Browser $browser) use ($pembayaran) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/pembayaran')
                ->waitForText('Pembayaran', 10);

            $this->uploadPembayaranViaJs(
                $browser,
                $pembayaran,
                'Transfer Bank',
                null
            );

            $browser->pause(1500)
                ->assertPathIs('/warga/pembayaran')
                ->assertSee('Harap unggah bukti transfer');

            $pembayaran->refresh();

            $this->assertEquals('Menunggu', $pembayaran->status_pembayaran);
            $this->assertNull($pembayaran->bukti_transaksi);
        });
    }
}