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
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);

        $this->warga = User::where('email', 'andi@gmail.com')->first();
        $this->kategori = KategoriLaporan::first();
        $this->wilayah = Wilayah::first();

        $this->validPhotoPath = base_path('Tesupload/gambar.jpg');
        $this->largePhotoPath = base_path('Tesupload/6mb.jpg');
        $this->invalidDocPath = base_path('Tesupload/pdfuji.pdf');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Tutup browser setelah setiap TC agar tidak ada state residual antar TC
        static::closeAll();
    }

    protected function loginAndGoToCreate(Browser $browser): void
    {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/')
                ->pause(1000)
                ->clickLink('Masuk')
                ->pause(1000)
                ->click('@role-masyarakat')
                ->pause(500)
                ->type('email', $this->warga->email)
                ->pause(1000)
                ->type('password', 'password')
                ->pause(1000)
                ->click('button[type="submit"]')
                ->waitForText($this->warga->name, 15) // Tunggu konfirmasi login berhasil
                ->pause(500);

        $browser->visit('/warga/laporan/create')
                ->waitFor('form', 8)
                ->pause(2000)
                ->assertPathIs('/warga/laporan/create');
    }

    /**
     * Tunggu Alpine.js selesai merender konten x-if="!submitted" template.
     */
    protected function waitForAlpineForm(Browser $browser, int $seconds = 10): void
    {
        $browser->waitUsing($seconds, 200, function () use ($browser) {
            return $browser->script(
                'return document.querySelector("form[action*=\"laporan\"]") !== null;'
            )[0];
        }, 'Form laporan tidak muncul setelah Alpine init.');
    }

    /**
     * Set koordinat peta langsung ke DOM hidden inputs (untuk TC yang butuh GAGAL validasi map).
     * mapMarked='' akan menyebabkan validasi server gagal (map_marked required|in:1).
     */
    protected function setMapCoordinates(Browser $browser, string $mapMarked = '1'): void
    {
        $browser->script("
            var latEl  = document.querySelector('input[name=\"latitude\"]');
            var lngEl  = document.querySelector('input[name=\"longitude\"]');
            var mapEl  = document.querySelector('input[name=\"map_marked\"]');
            if (latEl)  latEl.value  = '-6.9175';
            if (lngEl)  lngEl.value  = '107.6191';
            if (mapEl)  mapEl.value  = '" . $mapMarked . "';
        ");
        $browser->pause(500);
    }

    /**
     * Set semua nilai form DAN submit dalam satu script synchronous.
     *
     * Masalah fundamental: antara dua Dusk command (script() dan click()),
     * browser event loop memproses microtasks — termasuk Alpine reactive effects.
     * Jika Alpine x-model mereset textarea/input ke nilai kosong di microtask,
     * form yang di-submit akan memiliki field kosong.
     *
     * Solusi: gabungkan setValue + form.submit() dalam SATU script block.
     * Dalam eksekusi synchronous, microtasks TIDAK jalan.
     * form.submit() mengkoleksi nilai SEBELUM microtasks bisa reset apapun.
     *
     * Note: form.submit() melewati HTML5 required validation — server
     * validation tetap jalan dan menolak jika data tidak valid.
     */
    protected function submitFormDirectly(
        Browser $browser,
        string  $deskripsi,
        string  $alamat,
        string  $mapMarked = '1',
        string  $kategoriId = '',
        string  $wilayahId = ''
    ): void {
        $escapedDesc   = addslashes($deskripsi);
        $escapedAlamat = addslashes($alamat);

        $browser->script("
            (function() {
                var ta = document.querySelector('textarea[name=\"deskripsi\"]');
                var ai = document.querySelector('input[name=\"alamat\"]');
                var la = document.querySelector('input[name=\"latitude\"]');
                var lo = document.querySelector('input[name=\"longitude\"]');
                var mm = document.querySelector('input[name=\"map_marked\"]');
                var ki = document.querySelector('input[name=\"kategori_laporan_id\"]');
                var wi = document.querySelector('select[name=\"wilayah_id\"]');

                // Set nilai — synchronous, belum ada microtask yang jalan
                if (ta) ta.value = '{$escapedDesc}';
                if (ai) ai.value = '{$escapedAlamat}';
                if (la) la.value = '-6.9175';
                if (lo) lo.value = '107.6191';
                if (mm) mm.value = '{$mapMarked}';
                if (ki && '{$kategoriId}' !== '') ki.value = '{$kategoriId}';
                if (wi && '{$wilayahId}' !== '') wi.value = '{$wilayahId}';

                // Klik tombol submit (native submission path — file input diproses benar)
                // btn.click() melewati HTML5 validation dgn nilai yang baru kita set
                var btn = document.querySelector('[dusk=\"btn-kirim-laporan\"]');
                if (btn) {
                    btn.click();
                } else {
                    // Fallback: form.submit() jika tombol tidak ditemukan
                    var form = document.querySelector('form[action*=\"laporan\"]');
                    if (form) form.submit();
                }
            })();
        ");

        // Tunggu server memproses dan redirect selesai
        $browser->pause(5000);
    }

    // -- TC-001 --
    // Memastikan warga dapat membuat laporan baru dengan semua data valid.
    public function test_TC001_berhasil_membuat_laporan_baru(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC001: Pilih kategori Pipa Bocor + wilayah via UI
            $browser->click('.grid button')->pause(1000)
                    ->select('wilayah_id', $this->wilayah->id)->pause(1000);

            // Attach foto terlebih dahulu (file input perlu diisi sebelum submit)
            $browser->attach('@input-foto', $this->validPhotoPath)->pause(1000);

            // Set semua nilai form + submit dalam satu script synchronous
            // agar Alpine microtask tidak sempat mereset nilai di antara set dan submit
            $this->submitFormDirectly(
                $browser,
                'Pipa bocor di halaman depan rumah, air merembes ke tanah sejak kemarin pagi.',
                'Jl. Melati No. 12 RT 03',
                '1',
                (string)$this->kategori->id,
                (string)$this->wilayah->id
            );

            if (str_contains($browser->driver->getCurrentURL(), '/create')) {
                throw new \Exception('GAGAL VALIDASI FORM: ' . $browser->driver->getCurrentURL());
            }

            $browser->assertPathIs('/warga/laporan')
                    ->assertSee('Laporan berhasil dibuat')
                    ->pause(20000);
        });
    }

    // -- TC-002 --
    // Memastikan laporan gagal dikirim jika deskripsi kosong.
    public function test_TC002_gagal_deskripsi_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC002: Kategori Pipa Bocor - validasi deskripsi wajib diisi
            $browser->click('.grid button')->pause(1000)
                    ->select('wilayah_id', $this->wilayah->id)->pause(1000)
                    ->type('alamat', 'Jl. Melati No. 5 RT 02')->pause(1000);

            // Sengaja TIDAK mengisi deskripsi
            $this->setMapCoordinates($browser, '1');

            $browser->attach('@input-foto', $this->validPhotoPath)->pause(1000);
            $browser->click('@btn-kirim-laporan')->pause(3000);

            $browser->assertPathIs('/warga/laporan/create')->pause(20000);
        });
    }

    // -- TC-003 --
    // Memastikan laporan gagal jika titik lokasi peta belum ditandai.
    public function test_TC003_gagal_lokasi_map_belum_ditandai(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC003: Kategori Pipa Bocor - validasi lokasi peta wajib ditandai
            $browser->click('.grid button')->pause(1000)
                    ->select('wilayah_id', $this->wilayah->id)->pause(1000)
                    ->type('deskripsi', 'Pipa bocor di bawah jalan gang, air muncrat dan menggenangi area sekitar.')->pause(1000)
                    ->type('alamat', 'Jl. Anggrek No. 8 RT 05')->pause(1000);

            // Sengaja TIDAK set map_marked agar validasi server gagal
            $this->setMapCoordinates($browser, ''); // map_marked dibiarkan kosong

            $browser->attach('@input-foto', $this->validPhotoPath)->pause(1000);
            $browser->click('@btn-kirim-laporan')->pause(3000);

            $browser->assertPathIs('/warga/laporan/create')->pause(20000);
        });

        $this->assertDatabaseMissing('laporan', [
            'deskripsi' => 'Pipa bocor di bawah jalan gang, air muncrat dan menggenangi area sekitar.',
        ]);
    }

    // -- TC-004 --
    // Memastikan validasi client menolak foto yang ukurannya melebihi 5MB.
    public function test_TC004_gagal_foto_melebihi_batas_ukuran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC004: Kategori Pipa Bocor - validasi ukuran foto maksimal 5MB
            $browser->click('.grid button')->pause(1000)
                    ->select('wilayah_id', $this->wilayah->id)->pause(1000)
                    ->type('deskripsi', 'Pipa bocor di dalam tembok, tampak basah dan berjamur di dinding kamar.')->pause(1000);

            $browser->attach('@input-foto', $this->largePhotoPath)->pause(1000);

            $browser->assertSee('Ukuran foto maksimal 5MB')->pause(20000);
        });
    }

    // -- TC-005 --
    // Memastikan validasi client menolak foto dengan format tidak valid (bukan JPG/PNG).
    public function test_TC005_gagal_format_foto_tidak_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC005: Kategori Pipa Bocor - validasi format foto harus JPG/PNG
            $browser->click('.grid button')->pause(1000)
                    ->select('wilayah_id', $this->wilayah->id)->pause(1000)
                    ->type('deskripsi', 'Pipa bocor di dekat sambungan utama, air merembes ke lantai dapur.')->pause(1000);

            $browser->attach('@input-foto', $this->invalidDocPath)->pause(1000);

            $browser->assertSee('Format foto tidak didukung')->pause(20000);
        });
    }

    // -- TC-006 --
    // Memastikan laporan gagal jika deskripsi kurang dari 10 karakter.
    public function test_TC006_gagal_deskripsi_kurang_dari_10_karakter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC006: Kategori Pipa Bocor - validasi deskripsi minimal 10 karakter
            $browser->click('.grid button')->pause(1000)
                    ->select('wilayah_id', $this->wilayah->id)->pause(1000)
                    ->type('deskripsi', 'Bocor')->pause(1000) // 5 karakter, di bawah minimum 10
                    ->type('alamat', 'Jl. Dahlia No. 3')->pause(1000);

            $this->setMapCoordinates($browser, '1');

            $browser->click('@btn-kirim-laporan')->pause(4000);

            $browser->assertPathIs('/warga/laporan/create')->pause(20000);
        });

        $this->assertDatabaseMissing('laporan', ['deskripsi' => 'Bocor']);
    }

    // -- TC-007 --
    // Memastikan laporan gagal jika kategori masalah belum dipilih.
    public function test_TC007_gagal_kategori_belum_dipilih(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC007: Tanpa kategori - validasi kategori wajib dipilih
            // Sengaja TIDAK klik kategori apapun
            $browser->select('wilayah_id', $this->wilayah->id)->pause(1000)
                    ->type('deskripsi', 'Pipa bocor di depan rumah, air sudah mengalir ke jalan sejak semalam.')->pause(1000)
                    ->type('alamat', 'Jl. Mawar No. 7 RT 01')->pause(1000);

            $this->setMapCoordinates($browser, '1');

            $browser->click('@btn-kirim-laporan')->pause(4000);

            $browser->assertPathIs('/warga/laporan/create')->pause(20000);
        });

        $this->assertDatabaseMissing('laporan', [
            'deskripsi' => 'Pipa bocor di depan rumah, air sudah mengalir ke jalan sejak semalam.',
        ]);
    }

    // -- TC-008 --
    // Memastikan laporan berhasil dikirim meskipun tanpa melampirkan foto (foto opsional).
    public function test_TC008_berhasil_membuat_laporan_tanpa_foto(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToCreate($browser);
            $this->waitForAlpineForm($browser);

            // TC008: Pilih kategori Pipa Bocor + wilayah via UI
            $browser->click('.grid button')->pause(1000)
                    ->select('wilayah_id', $this->wilayah->id)->pause(1000);

            // Sengaja TIDAK attach foto (foto bersifat opsional)

            // Set semua nilai form + submit dalam satu script synchronous
            $this->submitFormDirectly(
                $browser,
                'Pipa bocor di halaman belakang, volume kebocoran cukup besar dan perlu segera diperbaiki.',
                'Perumahan Tirta Asri Blok C No. 5',
                '1',
                (string)$this->kategori->id,
                (string)$this->wilayah->id
            );

            if (str_contains($browser->driver->getCurrentURL(), '/create')) {
                throw new \Exception('GAGAL VALIDASI FORM: ' . $browser->driver->getCurrentURL());
            }

            $browser->assertPathIs('/warga/laporan')
                    ->assertSee('Laporan berhasil dibuat')
                    ->pause(20000);
        });
    }
}
