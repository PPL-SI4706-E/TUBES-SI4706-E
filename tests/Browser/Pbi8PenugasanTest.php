<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-08 — Automated E2E Testing: Fitur Transaksi Penugasan
 *
 * Test Cases:
 *   TC-001  Berhasil menugaskan petugas (Toast sukses, status berubah)
 *   TC-002  Validasi sorting area petugas (Sesuai wilayah berada di atas)
 *   TC-003  Tombol submit terkunci jika belum pilih petugas
 *   TC-004  Tombol penugasan hilang jika sudah ditugaskan
 *   TC-005  Tombol penugasan hilang jika laporan belum diterima
 *   TC-006  Menugaskan petugas beserta catatan work order (opsional)
 *   TC-007  Tampilan empty state saat tidak ada petugas yang tersedia
 */
class Pbi8PenugasanTest extends DuskTestCase
{
    protected User $admin;
    protected User $petugasMatch;
    protected User $petugasOther;
    protected Laporan $laporan;
    protected Wilayah $wilayahCianjur;
    protected Wilayah $wilayahBandung;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');

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
            'tarif' => 50000,
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
            'is_active' => true,
        ]);

        $this->laporan = Laporan::create([
            'user_id' => $warga->id,
            'wilayah_id' => $this->wilayahCianjur->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Pipa Bocor Besar',
            'deskripsi' => 'Air menyembur dari jalan.',
            'alamat' => 'Jl. Cianjur No 1',
            'status' => 'diterima', // Default bisa di assign
            'tanggal_lapor' => now(),
        ]);
    }

    protected function waitForAlpine(Browser $browser, int $seconds = 5): void
    {
        $browser->waitUsing($seconds, 100, function () use ($browser) {
            return $browser->script(
                'return document.querySelectorAll("[x-cloak]").length === 0
                      || Array.from(document.querySelectorAll("[x-cloak]"))
                             .every(el => el.style.display !== "none" && window.getComputedStyle(el).display !== "none");'
            )[0];
        }, 'Alpine.js tidak selesai inisialisasi.');
    }

    // -- TC-001 --
    public function test_TC001_berhasil_menugaskan_petugas()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500);

            $this->waitForAlpine($browser, 8);

            // Buka modal
            $browser->click('@btn-tugaskan-petugas')
                ->waitForText('Buat Work Order #' . $this->laporan->id, 5);

            // Pilih petugas
            $browser->waitFor('#petugas-btn-' . $this->petugasMatch->id, 5)
                ->click('#petugas-btn-' . $this->petugasMatch->id)
                ->pause(500);

            // Submit
            $browser->click('@btn-submit-penugasan')
                ->pause(2000);

            // Ekspektasi: redirect kembali & muncul toast / pesan sukses
            $browser->assertPathIs('/admin/laporan/' . $this->laporan->id)
                ->assertSee('berhasil dibuat dan ditugaskan');

            // Cek DB
            $this->assertDatabaseHas('laporan', [
                'id' => $this->laporan->id,
                'status' => 'dikerjakan'
            ]);
            $this->assertDatabaseHas('penugasan', [
                'laporan_id' => $this->laporan->id,
                'user_id' => $this->petugasMatch->id,
                'status_tugas' => 'Ditugaskan'
            ]);
        });
    }

    // -- TC-002 --
    public function test_TC002_validasi_sorting_area_petugas()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500);

            $this->waitForAlpine($browser, 8);

            $browser->click('@btn-tugaskan-petugas')
                ->waitForText('Buat Work Order #' . $this->laporan->id, 5);

            // Verifikasi petugas area Cianjur muncul paling atas & ada badge "Sesuai Area"
            $matchName = $this->petugasMatch->name;
            $otherName = $this->petugasOther->name;

            // Script untuk mengecek urutan
            $isMatchFirst = $browser->script(
                'var buttons = document.querySelectorAll("#formAssign button[id^=\'petugas-btn-\']");
                 if(buttons.length >= 2) {
                     return buttons[0].textContent.includes("' . $matchName . '") && buttons[1].textContent.includes("' . $otherName . '");
                 }
                 return false;'
            )[0];

            $this->assertTrue($isMatchFirst, "Petugas dengan area yang sesuai (Cianjur) harusnya muncul di urutan paling atas.");

            // Verifikasi badge muncul
            $browser->assertSeeIn('#petugas-btn-' . $this->petugasMatch->id, '✓ Sesuai Area');
            $browser->assertDontSeeIn('#petugas-btn-' . $this->petugasOther->id, '✓ Sesuai Area');
        });
    }

    // -- TC-003 --
    public function test_TC003_tombol_submit_terkunci_jika_belum_pilih_petugas()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500);

            $this->waitForAlpine($browser, 8);

            $browser->click('@btn-tugaskan-petugas')
                ->waitForText('Buat Work Order #' . $this->laporan->id, 5);

            // Tanpa mengklik apapun, tombol submit harusnya disabled
            $isDisabled = $browser->script(
                'var btn = document.querySelector("[dusk=\'btn-submit-penugasan\']");
                 return btn ? btn.disabled : false;'
            )[0];

            $this->assertTrue($isDisabled, "Tombol 'Tugaskan Sekarang' seharusnya disabled saat petugas belum dipilih.");
        });
    }

    // -- TC-004 --
    public function test_TC004_tombol_penugasan_hilang_jika_sudah_ditugaskan()
    {
        // Ubah laporan jadi dikerjakan & tambahkan penugasan
        $this->laporan->update(['status' => 'dikerjakan']);
        \App\Models\Penugasan::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugasMatch->id,
            'tanggal_penugasan' => now(),
            'status_tugas' => 'Ditugaskan'
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500);

            $browser->assertMissing('@btn-tugaskan-petugas');
            $browser->assertSee('Sedang Dikerjakan');
            $browser->assertSee($this->petugasMatch->name);
        });
    }

    // -- TC-005 --
    public function test_TC005_tombol_penugasan_hilang_jika_laporan_belum_diterima()
    {
        // Ubah status laporan jadi pending
        $this->laporan->update(['status' => 'pending']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500);

            $browser->assertMissing('@btn-tugaskan-petugas');
            $browser->assertSee('Validasi Terkunci'); // form validasi terkunci karena belum lunas
        });
    }

    // -- TC-006 --
    public function test_TC006_menugaskan_petugas_dengan_catatan_opsional()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500);

            $this->waitForAlpine($browser, 8);

            $browser->click('@btn-tugaskan-petugas')
                ->waitForText('Buat Work Order #' . $this->laporan->id, 5);

            // Pilih petugas (kali ini pilih yang lain)
            $browser->waitFor('#petugas-btn-' . $this->petugasOther->id, 5)
                ->click('#petugas-btn-' . $this->petugasOther->id)
                ->pause(500);

            // Isi Catatan
            $catatanKhusus = "Pastikan membawa alat gali tambahan dan pompa sedot.";
            $browser->type('catatan_admin', $catatanKhusus);

            // Submit
            $browser->click('@btn-submit-penugasan')
                ->pause(2000);

            // Ekspektasi: redirect kembali & muncul toast / pesan sukses
            $browser->assertPathIs('/admin/laporan/' . $this->laporan->id)
                ->assertSee('berhasil dibuat dan ditugaskan');

            // Cek DB
            $this->assertDatabaseHas('penugasan', [
                'laporan_id' => $this->laporan->id,
                'user_id' => $this->petugasOther->id,
                'catatan_admin' => $catatanKhusus
            ]);
        });
    }

    // -- TC-007 --
    public function test_TC007_tampilan_saat_tidak_ada_petugas_tersedia()
    {
        // Ubah semua petugas menjadi role lain agar kosong
        User::where('role', 'petugas')->update(['role' => 'masyarakat']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500);

            $this->waitForAlpine($browser, 8);

            $browser->click('@btn-tugaskan-petugas')
                ->waitForText('Buat Work Order #' . $this->laporan->id, 5);

            // Ekspektasi: Muncul teks empty state
            $browser->assertSee('Tidak ada petugas tersedia.');

            // Tombol submit terkunci
            $isDisabled = $browser->script(
                'var btn = document.querySelector("[dusk=\'btn-submit-penugasan\']");
                 return btn ? btn.disabled : false;'
            )[0];
            $this->assertTrue($isDisabled, "Tombol submit harus disabled jika list petugas kosong.");
        });
    }
}
