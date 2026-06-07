<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\File;

class Pbi4PelaporanBerbasisPetaTest extends DuskTestCase
{
    protected $warga;
    protected $kategori;
    protected $wilayah;
    protected $validPhotoPath;
    protected $largePhotoPath;
    protected $invalidDocPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warga = User::where('role', 'masyarakat')->first();
        if (!$this->warga) {
            $this->warga = User::factory()->create(['role' => 'masyarakat']);
        }

        $this->kategori = KategoriLaporan::first() ?? KategoriLaporan::factory()->create();
        $this->wilayah = Wilayah::first() ?? Wilayah::factory()->create();

        // Siapkan file dummy untuk upload
        $tempDir = storage_path('app/testing_files');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0777, true);
        }

        // 1. Valid Photo (< 5MB)
        $this->validPhotoPath = $tempDir . '/bukti.jpg';

        $jpg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8PDw8QDw8PDw8PDw8PDw8PFREWFhURFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGi0lHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAgMBIgACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAABf/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/Aaf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/Aaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Aqf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IV//2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z'
        );

        file_put_contents($this->validPhotoPath, $jpg);

        // 2. Large Photo (> 5MB)
        $this->largePhotoPath = $tempDir . '/foto_besar.jpg';

        file_put_contents(
            $this->largePhotoPath,
            str_repeat('A', 6 * 1024 * 1024)
        );

        // 3. Invalid format (.pdf)
        $this->invalidDocPath = $tempDir . '/dokumen.pdf';

        File::put(
            $this->invalidDocPath,
            'Ini adalah file PDF palsu'
        );
    }

    protected function loginAndGoToCreate(Browser $browser)
    {
        $browser->loginAs($this->warga->id)
                ->visit('/warga/laporan/create')
                ->waitFor('form', 5);
    }

    // -- TC-001 --
    public function test_TC001_berhasil_membuat_laporan_baru()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);

            $browser->click('.grid button')
                    ->select('wilayah_id', $this->wilayah->id)
                    ->type('deskripsi', 'Jembatan retak dan sangat membahayakan pengguna jalan.')
                    ->type('alamat', 'Jalan Raya Utama No 1');

            $browser->script("
                let comp = document.querySelector('[x-data=\"laporanForm()\"]')._x_dataStack[0];
                comp.form.map_marked = '1';
                comp.form.lat = -6.9175;
                comp.form.lng = 107.6191;
            ");
            $browser->pause(1000);
            
            $browser->attach('@input-foto', $this->validPhotoPath)
                    ->pause(1000);

            $browser->click('@btn-kirim-laporan')
                    ->pause(1500);

            if (str_contains($browser->driver->getCurrentURL(), '/create')) {
                throw new \Exception("GAGAL VALIDASI FORM: " . $browser->driver->getCurrentURL());
            }

            $browser->assertPathIs('/warga/laporan')
                    ->assertSee('Laporan berhasil dibuat');
        });
    }

    // -- TC-002 --
    public function test_TC002_gagal_deskripsi_kosong()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);

            $browser->click('.grid button')
                    ->select('wilayah_id', $this->wilayah->id)
                    ->type('alamat', 'Jalan Kosong')
                    ->attach('@input-foto', $this->validPhotoPath);

            $browser->script("
                let comp = document.querySelector('[x-data=\"laporanForm()\"]')._x_dataStack[0];
                comp.form.map_marked = '1';
                comp.form.lat = -6.9175;
                comp.form.lng = 107.6191;
            ");
            $browser->pause(1000);

            $browser->click('@btn-kirim-laporan')
                    ->pause(1500);

            $browser->assertPathIs('/warga/laporan/create');
        });
    }

    // -- TC-003 --
    public function test_TC003_gagal_lokasi_map_belum_ditandai()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);

            $browser->click('.grid button')
                    ->select('wilayah_id', $this->wilayah->id)
                    ->type('deskripsi', 'Pohon tumbang menutup jalan.')
                    ->type('alamat', 'Jalan Hutan')
                    ->attach('@input-foto', $this->validPhotoPath);

            $browser->click('@btn-kirim-laporan')
                    ->pause(1500);

            $browser->assertPathIs('/warga/laporan/create')
                    ->assertSee('Titik lokasi wajib ditentukan di peta.');
        });
    }

    // -- TC-004 --
    public function test_TC004_gagal_foto_melebihi_batas_ukuran()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);

            $browser->click('.grid button')
                    ->select('wilayah_id', $this->wilayah->id)
                    ->type('deskripsi', 'Saluran air mampet dan banjir.')
                    ->attach('@input-foto', $this->largePhotoPath)
                    ->pause(1000);

            $browser->assertSee('Ukuran foto maksimal 5MB');
        });
    }

    // -- TC-005 --
    public function test_TC005_gagal_format_foto_tidak_valid()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);

            $browser->click('.grid button')
                    ->select('wilayah_id', $this->wilayah->id)
                    ->type('deskripsi', 'Lampu jalan mati.')
                    ->attach('@input-foto', $this->invalidDocPath)
                    ->pause(1000);

            $browser->assertSee('Format foto tidak didukung');
        });
    }

    // -- TC-006 --
    public function test_TC006_gagal_deskripsi_kurang_dari_10_karakter()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);

            $browser->click('.grid button')
                    ->select('wilayah_id', $this->wilayah->id)
                    ->type('deskripsi', 'Rusak') // 5 karakter
                    ->type('alamat', 'Jalan Pendek');

            $browser->script("
                let comp = document.querySelector('[x-data=\"laporanForm()\"]')._x_dataStack[0];
                comp.form.map_marked = '1';
                comp.form.lat = -6.9175;
                comp.form.lng = 107.6191;
            ");
            $browser->pause(1000);

            $browser->click('@btn-kirim-laporan')
                    ->pause(1500);

            $browser->assertPathIs('/warga/laporan/create')
                    ->assertSee('Deskripsi masalah minimal 10 karakter');
        });
    }

    // -- TC-007 --
    public function test_TC007_gagal_kategori_belum_dipilih()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);

            $browser->select('wilayah_id', $this->wilayah->id)
                    ->type('deskripsi', 'Jalanan ini rusak parah dan berlubang.')
                    ->type('alamat', 'Jalan Panjang No 2');

            $browser->script("
                let comp = document.querySelector('[x-data=\"laporanForm()\"]')._x_dataStack[0];
                comp.form.map_marked = '1';
                comp.form.lat = -6.9175;
                comp.form.lng = 107.6191;
            ");
            $browser->pause(1000);

            $browser->click('@btn-kirim-laporan')
                    ->pause(1500);

            $browser->assertPathIs('/warga/laporan/create')
                    ->assertSee('Kategori masalah wajib dipilih');
        });
    }
}
