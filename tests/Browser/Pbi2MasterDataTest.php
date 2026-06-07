<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Pbi2MasterDataTest extends DuskTestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!self::$migrated) {
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh');
            self::$migrated = true;
        }
    }

    protected function loginAsAdmin(Browser $browser)
    {
        User::firstOrCreate([
            'email' => 'admin@tirtabantu.id',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $browser->visit('/login')
                ->type('#email', 'admin@tirtabantu.id')
                ->type('#password', 'password')
                ->script("let r=document.querySelector('input[name=\"role\"][value=\"admin\"]'); r.checked=true; r.dispatchEvent(new Event('change',{bubbles:true}));");
        
        $browser->pause(500)
                ->press('Masuk')
                ->waitForLocation('/admin/dashboard', 10);
    }

    public function testTC01LihatDaftarKategoriLaporan()
    {
        \App\Models\KategoriLaporan::firstOrCreate(['nama_kategori' => 'Kategori Dummy'], ['tarif' => 5000, 'deskripsi' => 'Dummy', 'is_active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            $browser->visit('/admin/master-kategori')
                    ->assertSee('Master Kategori Laporan')
                    ->assertSee('Kategori Dummy')
                    ->assertSee('Dummy')
                    ->assertSee('Rp 5.000');
        });
    }

    public function testTC02TambahKategoriBaru()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            $browser->visit('/admin/master-kategori')
                    ->waitFor('@btn-tambah-kategori', 5)
                    ->click('@btn-tambah-kategori')
                    ->pause(500)
                    ->type('nama_kategori', 'Pipa Bocor Test')
                    ->type('deskripsi', 'Laporan kebocoran pipa utama')
                    ->type('tarif', '15000')
                    ->press('Simpan')
                    ->waitForText('Kategori berhasil ditambahkan.', 10)
                    ->assertSee('Pipa Bocor Test')
                    ->assertSee('Laporan kebocoran pipa utama')
                    ->assertSee('Rp 15.000');
        });
    }

    public function testTC03TambahKategoriDenganNamaDuplikat()
    {
        \App\Models\KategoriLaporan::firstOrCreate([
            'nama_kategori' => 'Pipa Bocor Test',
        ], [
            'tarif' => 15000,
            'is_active' => true
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            $browser->visit('/admin/master-kategori')
                    ->waitFor('@btn-tambah-kategori', 5)
                    ->click('@btn-tambah-kategori')
                    ->pause(500)
                    ->type('nama_kategori', 'Pipa Bocor Test')
                    ->type('tarif', '0')
                    ->press('Simpan')
                    ->waitForText('Nama kategori sudah ada.', 10);
        });
    }

    public function testTC04EditKategoriLaporan()
    {
        $kategori = \App\Models\KategoriLaporan::firstOrCreate([
            'nama_kategori' => 'Kategori Lama',
        ], [
            'tarif' => 10000,
            'is_active' => true
        ]);

        $this->browse(function (Browser $browser) use ($kategori) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            $browser->visit('/admin/master-kategori')
                    ->waitFor('@btn-edit-kategori-' . $kategori->id, 5)
                    ->click('@btn-edit-kategori-' . $kategori->id)
                    ->pause(500)
                    ->type('tarif', '20000')
                    ->press('Simpan')
                    ->waitForText('Kategori berhasil diperbarui.', 10)
                    ->assertSee('Rp 20.000');
        });
    }

    public function testTC05HapusKategoriLaporan()
    {
        $kategori = \App\Models\KategoriLaporan::firstOrCreate([
            'nama_kategori' => 'Temp Kategori',
        ], [
            'tarif' => 0,
            'is_active' => true
        ]);

        $this->browse(function (Browser $browser) use ($kategori) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            $browser->visit('/admin/master-kategori')
                    ->waitFor('@btn-delete-kategori-' . $kategori->id, 5)
                    ->click('@btn-delete-kategori-' . $kategori->id)
                    ->acceptDialog()
                    ->waitForText('Kategori berhasil dihapus.', 10)
                    ->assertDontSee('Temp Kategori');
        });
    }

    public function testTC06HapusKategoriYangMasihDipakaiLaporan()
    {
        $wilayah = \App\Models\Wilayah::firstOrCreate([
            'nama_wilayah' => 'Area Test',
        ], [
            'tipe' => 'desa'
        ]);
        $kategori = \App\Models\KategoriLaporan::firstOrCreate([
            'nama_kategori' => 'Pipa Bocor Dipakai',
        ], [
            'tarif' => 0,
            'is_active' => true
        ]);
        
        \App\Models\Laporan::create([
            'user_id' => User::firstOrCreate([
                'email' => 'admin@tirtabantu.id'
            ], [
                'name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true
            ])->id,
            'kategori_laporan_id' => $kategori->id,
            'wilayah_id' => $wilayah->id,
            'judul' => 'Test Laporan',
            'deskripsi' => 'Testing',
            'alamat_detail' => 'Jalan Test',
            'status' => 'pending',
            'kode_laporan' => 'TEST-002'
        ]);

        $this->browse(function (Browser $browser) use ($kategori) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            $browser->visit('/admin/master-kategori')
                    ->waitFor('@btn-delete-kategori-' . $kategori->id, 5)
                    ->click('@btn-delete-kategori-' . $kategori->id)
                    ->acceptDialog()
                    ->waitForText('Kategori tidak dapat dihapus karena masih memiliki laporan terkait.', 10)
                    ->assertSee('Pipa Bocor Dipakai');
        });
    }

    public function testTC07CRUDMasterWilayah()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            // Tambah
            $namaWilayah = 'Kecamatan Sukamaju ' . time();
            $browser->visit('/admin/master-wilayah')
                    ->waitFor('@btn-tambah-wilayah', 5)
                    ->click('@btn-tambah-wilayah')
                    ->pause(500)
                    ->type('nama_wilayah', $namaWilayah)
                    ->select('tipe', 'kecamatan')
                    ->type('kode_wilayah', '1234')
                    ->press('Simpan')
                    ->waitForText('Wilayah berhasil ditambahkan.', 10)
                    ->assertSee($namaWilayah)
                    ->assertSee('1234');

            // Edit
            $wilayah = \App\Models\Wilayah::where('nama_wilayah', $namaWilayah)->first();
            $browser->waitFor('@btn-edit-wilayah-' . $wilayah->id, 5)
                    ->click('@btn-edit-wilayah-' . $wilayah->id)
                    ->pause(500)
                    ->type('kode_wilayah', '9999')
                    ->press('Simpan')
                    ->waitForText('Wilayah berhasil diperbarui.', 10)
                    ->assertSee('9999');

            // Hapus
            $browser->waitFor('@btn-delete-wilayah-' . $wilayah->id, 5)
                    ->click('@btn-delete-wilayah-' . $wilayah->id)
                    ->acceptDialog()
                    ->waitForText('Wilayah berhasil dihapus.', 10)
                    ->assertDontSee($namaWilayah);
        });
    }

    public function testTC08ValidasiInputFieldWajibKosong()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $this->loginAsAdmin($browser);

            // Validasi Kategori Kosong
            $browser->visit('/admin/master-kategori')
                    ->waitFor('@btn-tambah-kategori', 5)
                    ->click('@btn-tambah-kategori')
                    ->pause(500);
            
            $browser->script("document.querySelector('input[name=\"nama_kategori\"]').removeAttribute('required'); document.querySelector('input[name=\"tarif\"]').removeAttribute('required');");
            $browser->keys('input[name="tarif"]', ['{control}', 'a'], '{backspace}')
                    ->pause(200)
                    ->press('Simpan')
                    ->waitForText('Nama kategori wajib diisi.', 10)
                    ->assertSee('Tarif wajib diisi.');

            // Validasi Wilayah Kosong
            $browser->visit('/admin/master-wilayah')
                    ->waitFor('@btn-tambah-wilayah', 5)
                    ->click('@btn-tambah-wilayah')
                    ->pause(500);
            
            $browser->script("document.querySelector('input[name=\"nama_wilayah\"]').removeAttribute('required'); document.querySelector('select[name=\"tipe\"]').removeAttribute('required');");
            $browser->type('nama_wilayah', '')
                    ->press('Simpan')
                    ->waitForText('Nama wilayah wajib diisi.', 10);
        });
    }
}
