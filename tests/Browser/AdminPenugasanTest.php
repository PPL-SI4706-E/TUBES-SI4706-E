<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-08: Fitur Transaksi Penugasan (Admin)
 *
 * Mencakup test case:
 *  TC-001 — Berhasil menugaskan petugas
 *  TC-002 — Validasi sorting area petugas (badge Sesuai Area)
 *  TC-003 — Tombol submit terkunci jika belum pilih petugas
 */
class AdminPenugasanTest extends DuskTestCase
{
    use DatabaseMigrations;

    // ── Fixtures ──────────────────────────────────────────────────────

    private Wilayah $wilayahCianjur;
    private Wilayah $wilayahSukabumi;
    private User    $admin;
    private User    $petugasCianjur;
    private User    $petugasSukabumi;
    private Laporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat dua wilayah untuk pengujian
        $this->wilayahCianjur  = Wilayah::create(['nama_wilayah' => 'Kecamatan Cianjur',  'tipe' => 'kecamatan', 'kode_wilayah' => 'KCJ-T01']);
        $this->wilayahSukabumi = Wilayah::create(['nama_wilayah' => 'Kecamatan Sukabumi', 'tipe' => 'kecamatan', 'kode_wilayah' => 'KSB-T02']);

        $kategori = KategoriLaporan::create([
            'nama_kategori' => 'Kebocoran Pipa',
            'tarif' => 0,
        ]);

        // Admin
        $this->admin = User::create([
            'name'      => 'Admin Test',
            'email'     => 'admin.test@tirtabantu.com',
            'password'  => bcrypt('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // Petugas area Cianjur (sesuai area laporan)
        $this->petugasCianjur = User::create([
            'name'       => 'Petugas Cianjur',
            'email'      => 'petugas.cianjur@tirtabantu.com',
            'password'   => bcrypt('password'),
            'role'       => 'petugas',
            'wilayah_id' => $this->wilayahCianjur->id,
            'is_active'  => true,
        ]);

        // Petugas area Sukabumi (beda area)
        $this->petugasSukabumi = User::create([
            'name'       => 'Petugas Sukabumi',
            'email'      => 'petugas.sukabumi@tirtabantu.com',
            'password'   => bcrypt('password'),
            'role'       => 'petugas',
            'wilayah_id' => $this->wilayahSukabumi->id,
            'is_active'  => true,
        ]);

        // Laporan berstatus "diterima" di wilayah Cianjur (siap untuk ditugaskan)
        $this->laporan = Laporan::create([
            'user_id'            => $this->admin->id,
            'wilayah_id'         => $this->wilayahCianjur->id,
            'kategori_laporan_id' => $kategori->id,
            'judul'              => 'Pipa Bocor di Jalan Cianjur',
            'deskripsi'          => 'Pipa bocor mengakibatkan air terbuang.',
            'alamat'             => 'Jl. Merdeka No. 10, Cianjur',
            'status'             => 'diterima',
            'tanggal_lapor'      => now(),
        ]);
    }

    // ── TC-001: Berhasil menugaskan petugas ───────────────────────────

    /**
     * @test
     * TC-001: Admin berhasil menugaskan petugas.
     *
     * Given: Admin membuka halaman detail laporan berstatus diterima.
     * When:  Admin memilih petugas dan menekan "Tugaskan Sekarang".
     * Then:  Status laporan berubah ke dikerjakan & data tersimpan di tabel penugasan.
     */
    public function test_TC001_berhasil_menugaskan_petugas(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visitRoute('admin.laporan.show', $this->laporan->id)
                    ->assertSee('Laporan Diterima')
                    ->assertSee('Tugaskan Petugas');

            // Buka modal penugasan
            $browser->click('@btn-tugaskan-petugas')
                    ->waitForText('Buat Work Order #' . $this->laporan->id);

            // Pilih Petugas Cianjur dari list
            $browser->click('#petugas-btn-' . $this->petugasCianjur->id)
                    ->pause(400); // tunggu Alpine state update

            // Isi catatan opsional
            $browser->type('catatan_admin', 'Bawa alat las pipa.')
                    ->waitUntilEnabled('button[form=formAssign], button:contains("Tugaskan Sekarang")')
                    ->click('@btn-submit-penugasan')
                    ->waitForText('berhasil dibuat dan ditugaskan');

            // Verifikasi tampilan halaman terupdate
            $browser->assertSee('Sedang Dikerjakan')
                    ->assertSee('Petugas Cianjur')
                    ->assertDontSee('Tugaskan Petugas');
        });

        // Verifikasi data di database
        $this->assertDatabaseHas('laporan', [
            'id'     => $this->laporan->id,
            'status' => 'dikerjakan',
        ]);

        $this->assertDatabaseHas('penugasan', [
            'laporan_id'   => $this->laporan->id,
            'user_id'      => $this->petugasCianjur->id,
            'status_tugas' => 'Ditugaskan',
        ]);
    }

    // ── TC-002: Validasi sorting dan badge "Sesuai Area" ──────────────

    /**
     * @test
     * TC-002: Petugas sesuai area berada paling atas dan memiliki badge "Sesuai Area".
     *
     * Given: Laporan berlokasi di Kecamatan Cianjur.
     * When:  Admin membuka modal assign.
     * Then:  Petugas Cianjur muncul paling atas dengan badge "✓ Sesuai Area".
     *        Petugas Sukabumi berada di bawah dan tidak memiliki badge tersebut.
     */
    public function test_TC002_sorting_area_petugas_dan_badge_sesuai_area(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visitRoute('admin.laporan.show', $this->laporan->id)
                    ->click('@btn-tugaskan-petugas')
                    ->waitForText('Buat Work Order #' . $this->laporan->id);

            // Verifikasi Petugas Cianjur adalah tombol PERTAMA di list
            $browser->assertSeeIn(
                '.custom-scrollbar button:first-child',
                'Petugas Cianjur'
            );

            // Verifikasi badge "Sesuai Area" ada pada Petugas Cianjur
            $browser->with('#petugas-btn-' . $this->petugasCianjur->id, function ($btn) {
                $btn->assertSee('Sesuai Area');
            });

            // Verifikasi Petugas Sukabumi TIDAK memiliki badge "Sesuai Area"
            $browser->with('#petugas-btn-' . $this->petugasSukabumi->id, function ($btn) {
                $btn->assertDontSee('Sesuai Area');
            });
        });
    }

    // ── TC-003: Tombol submit disabled jika belum pilih petugas ──────

    /**
     * @test
     * TC-003: Tombol "Tugaskan Sekarang" tidak dapat diklik jika belum ada petugas dipilih.
     *
     * Given: Modal assign terbuka.
     * When:  Admin belum memilih petugas.
     * Then:  Tombol submit berwarna abu-abu dan berstatus disabled.
     */
    public function test_TC003_tombol_submit_disabled_sebelum_pilih_petugas(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visitRoute('admin.laporan.show', $this->laporan->id)
                    ->click('@btn-tugaskan-petugas')
                    ->waitForText('Buat Work Order #' . $this->laporan->id)
                    ->pause(300);

            // Tombol submit harus disabled (Alpine.js :disabled="!selectedPetugas")
            $browser->assertDisabled('@btn-submit-penugasan');
        });
    }
}
