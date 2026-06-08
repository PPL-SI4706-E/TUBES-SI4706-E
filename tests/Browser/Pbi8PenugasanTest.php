<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Laporan;
use App\Models\Wilayah;
use App\Models\KategoriLaporan;
use App\Models\Penugasan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi8PenugasanTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $admin;
    private User $petugasMatch;
    private User $petugasOther;
    private Laporan $laporan;
    private Wilayah $wilayahCianjur;
    private Wilayah $wilayahBandung;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);

        $this->seedData();
    }

    private function seedData(): void
    {
        $this->wilayahCianjur = Wilayah::first();
        $this->wilayahBandung = Wilayah::skip(1)->first();
        $kategori = KategoriLaporan::first();

        $this->admin = User::where('email', 'admin@tirtabantu.id')->first();
        $this->petugasMatch = User::where('email', 'budi@tirtabantu.id')->first();
        $this->petugasOther = User::where('email', 'siti@tirtabantu.id')->first();
        $warga = User::where('email', 'andi@gmail.com')->first();

        $this->laporan = Laporan::create([
            'user_id' => $warga->id,
            'wilayah_id' => $this->wilayahCianjur->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Pipa Bocor Besar',
            'deskripsi' => 'Air menyembur dari jalan.',
            'alamat' => 'Jl. Cianjur No 1',
            'status' => 'diterima',
            'tanggal_lapor' => now(),
        ]);

        \App\Models\MapLokasi::create([
            'laporan_id' => $this->laporan->id,
            'latitude' => -6.9175,
            'longitude' => 107.6191,
        ]);

        Pembayaran::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $warga->id,
            'harga' => 50000,
            'metode_pembayaran' => 'Transfer Bank',
            'status_pembayaran' => 'Lunas',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::closeAll(); // Membunuh instance browser agar tidak terjadi tumpukan memori (InvalidSessionId)
    }

    private function tungguModalPenugasan(Browser $browser): Browser
    {
        return $browser
            ->pause(1000) // Jeda agar dosen bisa melihat tombol sebelum diklik
            ->click('@btn-tugaskan-petugas')
            ->waitForText('Buat Work Order #' . $this->laporan->id, 10)
            ->pause(1000); // Jeda melihat pop up terbuka
    }

    protected function visualLoginAndGoToLaporan(Browser $browser)
    {
        // Bersihkan sesi sebelumnya (jika ada)
        $browser->driver->manage()->deleteAllCookies();

        // 1. Mulai dari Home (Beranda)
        $browser->visit('/')
                ->pause(1000)
                ->clickLink('Masuk')
                ->pause(1000);

        // 2. Login visual UI step-by-step
        $browser->click('@role-admin')
                ->pause(500)
                ->type('email', $this->admin->email)
                ->pause(1000)
                ->type('password', 'password')
                ->pause(1000)
                ->click('button[type="submit"]')
                ->pause(1500);

        // 2. Mampir ke daftar laporan Admin dulu (Berlaku untuk test ke-2 dst)
        $browser->visit('/admin/laporan')
                ->pause(1000);

        // 3. Masuk ke halaman detail spesifik
        $browser->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1000);
    }

    public function test_TC001_berhasil_menugaskan_petugas()
    {
        $this->browse(function (Browser $browser) {
            $this->visualLoginAndGoToLaporan($browser);
            $browser->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $browser->waitFor('#petugas-btn-' . $this->petugasMatch->id, 10)
                ->click('#petugas-btn-' . $this->petugasMatch->id)
                ->pause(500)
                ->click('@btn-submit-penugasan')
                ->pause(1500);

            $browser->assertPathIs('/admin/laporan/' . $this->laporan->id);

            $this->assertDatabaseHas('laporan', [
                'id' => $this->laporan->id,
                'status' => 'dikerjakan',
            ]);

            $this->assertDatabaseHas('penugasan', [
                'laporan_id' => $this->laporan->id,
                'user_id' => $this->petugasMatch->id,
                'status_tugas' => 'Ditugaskan',
            ]);
            $browser->pause(20000); // Delay 20 detik
        });
    }

    public function test_TC002_validasi_sorting_area_petugas()
    {
        $this->browse(function (Browser $browser) {
            $this->visualLoginAndGoToLaporan($browser);
            $browser->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $isMatchFirst = $browser->script("
                const buttons = document.querySelectorAll('#formAssign button[id^=\"petugas-btn-\"]');
                if (buttons.length < 2) return false;
                return buttons[0].textContent.includes('Sesuai Area')
                    && !buttons[buttons.length - 1].textContent.includes('Sesuai Area');
            ")[0];

            $this->assertTrue($isMatchFirst);

            $browser->assertSeeIn('#petugas-btn-' . $this->petugasMatch->id, 'Sesuai Area')
                ->assertDontSeeIn('#petugas-btn-' . $this->petugasOther->id, 'Sesuai Area')
                ->pause(20000); // Delay 20 detik
        });
    }

    public function test_TC003_tombol_submit_terkunci_jika_belum_pilih_petugas()
    {
        $this->browse(function (Browser $browser) {
            $this->visualLoginAndGoToLaporan($browser);
            $browser->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $isDisabled = $browser->script("
                const btn = document.querySelector('[dusk=\"btn-submit-penugasan\"]');
                return btn ? btn.disabled : false;
            ")[0];

            $this->assertTrue($isDisabled);
            $browser->pause(20000); // Delay 20 detik
        });
    }

    public function test_TC004_tombol_penugasan_hilang_jika_sudah_ditugaskan()
    {
        $this->laporan->update(['status' => 'dikerjakan']);

        Penugasan::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugasMatch->id,
            'tanggal_penugasan' => now()->toDateString(),
            'status_tugas' => 'Ditugaskan',
        ]);

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndGoToLaporan($browser);
            $browser->waitForText('Sedang Dikerjakan', 10)
                ->assertMissing('@btn-tugaskan-petugas')
                ->assertSee($this->petugasMatch->name)
                ->pause(20000); // Delay 20 detik
        });
    }

    public function test_TC005_tombol_penugasan_hilang_jika_laporan_belum_diterima()
    {
        $this->laporan->update(['status' => 'pending']);

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndGoToLaporan($browser);
            $browser->pause(1000)
                ->assertMissing('@btn-tugaskan-petugas')
                ->pause(20000); // Delay 20 detik
        });
    }

    public function test_TC006_menugaskan_petugas_dengan_catatan_opsional()
    {
        $catatanKhusus = 'Pastikan membawa alat gali tambahan dan pompa sedot.';

        $this->browse(function (Browser $browser) use ($catatanKhusus) {
            $this->visualLoginAndGoToLaporan($browser);
            $browser->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $browser->waitFor('#petugas-btn-' . $this->petugasOther->id, 10)
                ->click('#petugas-btn-' . $this->petugasOther->id)
                ->type('catatan_admin', $catatanKhusus)
                ->pause(2000) // Jeda panjang agar dosen bisa melihat teks catatan yang diketik
                ->click('@btn-submit-penugasan')
                ->pause(1500);

            $browser->assertPathIs('/admin/laporan/' . $this->laporan->id);

            $this->assertDatabaseHas('penugasan', [
                'laporan_id' => $this->laporan->id,
                'user_id' => $this->petugasOther->id,
                'catatan_admin' => $catatanKhusus,
            ]);
            $browser->pause(20000); // Delay 20 detik
        });
    }

    public function test_TC007_tampilan_saat_tidak_ada_petugas_tersedia()
    {
        User::where('role', 'petugas')->update(['role' => 'masyarakat']);

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndGoToLaporan($browser);
            $browser->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $browser->assertSee('Tidak ada petugas tersedia.')
                ->pause(1500); // Jeda agar dosen bisa membaca tulisan error-nya

            $isDisabled = $browser->script("
                const btn = document.querySelector('[dusk=\"btn-submit-penugasan\"]');
                return btn ? btn.disabled : false;
            ")[0];

            $this->assertTrue($isDisabled);
            $browser->pause(20000); // Delay 20 detik
        });
    }
}