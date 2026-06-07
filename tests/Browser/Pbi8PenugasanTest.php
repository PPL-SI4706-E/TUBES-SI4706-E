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

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->seedData();
    }

    private function seedData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Pembayaran::query()->delete();
        Penugasan::query()->delete();
        Laporan::query()->delete();
        User::query()->delete();
        Wilayah::query()->delete();
        KategoriLaporan::query()->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->wilayahCianjur = Wilayah::create([
            'nama_wilayah' => 'Cianjur',
            'tipe' => 'kecamatan',
            'kode_wilayah' => 'CJR-01',
        ]);

        $this->wilayahBandung = Wilayah::create([
            'nama_wilayah' => 'Bandung',
            'tipe' => 'kecamatan',
            'kode_wilayah' => 'BDG-01',
        ]);

        $kategori = KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'deskripsi' => 'Kebocoran pipa',
            'tarif' => 50000,
            'icon' => '💧',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin PBI 8',
            'email' => 'admin.pbi8@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugasMatch = User::create([
            'name' => 'Petugas Cianjur',
            'email' => 'petugas.cianjur@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'wilayah_id' => $this->wilayahCianjur->id,
            'is_active' => true,
        ]);

        $this->petugasOther = User::create([
            'name' => 'Petugas Bandung',
            'email' => 'petugas.bandung@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'wilayah_id' => $this->wilayahBandung->id,
            'is_active' => true,
        ]);

        $warga = User::create([
            'name' => 'Warga Pelapor',
            'email' => 'warga.pelapor@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'masyarakat',
            'wilayah_id' => $this->wilayahCianjur->id,
            'is_active' => true,
        ]);

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

        Pembayaran::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $warga->id,
            'harga' => 50000,
            'metode_pembayaran' => 'Transfer Bank',
            'status_pembayaran' => 'Lunas',
        ]);
    }

    private function tungguModalPenugasan(Browser $browser): Browser
    {
        return $browser
            ->click('@btn-tugaskan-petugas')
            ->waitForText('Buat Work Order #' . $this->laporan->id, 10);
    }

    public function test_TC001_berhasil_menugaskan_petugas()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->waitForText('Tugaskan Petugas', 10);

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
        });
    }

    public function test_TC002_validasi_sorting_area_petugas()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $isMatchFirst = $browser->script("
                const buttons = document.querySelectorAll('#formAssign button[id^=\"petugas-btn-\"]');
                if (buttons.length < 2) return false;
                return buttons[0].textContent.includes('Petugas Cianjur')
                    && buttons[1].textContent.includes('Petugas Bandung');
            ")[0];

            $this->assertTrue($isMatchFirst);

            $browser->assertSeeIn('#petugas-btn-' . $this->petugasMatch->id, 'Sesuai Area')
                ->assertDontSeeIn('#petugas-btn-' . $this->petugasOther->id, 'Sesuai Area');
        });
    }

    public function test_TC003_tombol_submit_terkunci_jika_belum_pilih_petugas()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $isDisabled = $browser->script("
                const btn = document.querySelector('[dusk=\"btn-submit-penugasan\"]');
                return btn ? btn.disabled : false;
            ")[0];

            $this->assertTrue($isDisabled);
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
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->waitForText('Sedang Dikerjakan', 10)
                ->assertMissing('@btn-tugaskan-petugas')
                ->assertSee($this->petugasMatch->name);
        });
    }

    public function test_TC005_tombol_penugasan_hilang_jika_laporan_belum_diterima()
    {
        $this->laporan->update(['status' => 'pending']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1000)
                ->assertMissing('@btn-tugaskan-petugas');
        });
    }

    public function test_TC006_menugaskan_petugas_dengan_catatan_opsional()
    {
        $catatanKhusus = 'Pastikan membawa alat gali tambahan dan pompa sedot.';

        $this->browse(function (Browser $browser) use ($catatanKhusus) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $browser->waitFor('#petugas-btn-' . $this->petugasOther->id, 10)
                ->click('#petugas-btn-' . $this->petugasOther->id)
                ->type('catatan_admin', $catatanKhusus)
                ->click('@btn-submit-penugasan')
                ->pause(1500);

            $browser->assertPathIs('/admin/laporan/' . $this->laporan->id);

            $this->assertDatabaseHas('penugasan', [
                'laporan_id' => $this->laporan->id,
                'user_id' => $this->petugasOther->id,
                'catatan_admin' => $catatanKhusus,
            ]);
        });
    }

    public function test_TC007_tampilan_saat_tidak_ada_petugas_tersedia()
    {
        User::where('role', 'petugas')->update(['role' => 'masyarakat']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->waitForText('Tugaskan Petugas', 10);

            $this->tungguModalPenugasan($browser);

            $browser->assertSee('Tidak ada petugas tersedia.');

            $isDisabled = $browser->script("
                const btn = document.querySelector('[dusk=\"btn-submit-penugasan\"]');
                return btn ? btn.disabled : false;
            ")[0];

            $this->assertTrue($isDisabled);
        });
    }
}