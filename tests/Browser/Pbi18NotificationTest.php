<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\Penugasan;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\NewReportNotification;
use App\Notifications\TaskProgressNotification;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Notifications\DatabaseNotification;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-18 — Automated E2E Testing: Siklus Notifikasi Sistem
 *
 * Test Cases:
 *   TC-001  Notifikasi muncul (badge merah bertambah) saat tugas baru di-assign
 *   TC-002  Dropdown lonceng scrollable saat notifikasi > 10
 *   TC-003  Klik notifikasi "Tugas Baru" → redirect ke /petugas/tugas & ditandai dibaca
 *   TC-004  Tombol "Tandai semua dibaca" → semua read_at terisi, badge hilang
 *   TC-005  Notifikasi Admin muncul saat warga buat laporan baru & redirect berjalan
 *   TC-006  Notifikasi Warga muncul saat petugas menyelesaikan tugas & redirect berjalan
 *   TC-007  Hapus satu notifikasi via tombol X di dropdown & hapus dari database
 *   TC-008  Tombol "Bersihkan" menghapus semua notifikasi dari layar dan database
 *   TC-009  Klik "Lihat Semua Notifikasi" redirect ke halaman daftar notifikasi penuh
 */
class Pbi18NotificationTest extends DuskTestCase
{
    protected User $admin;
    protected User $petugas;
    protected Laporan $laporan;
    protected Penugasan $penugasan;

    // ── Setup ─────────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();

        // Menjalankan migrate:fresh secara manual di awal setiap test
        // untuk menghindari error foreign key constraints dari DatabaseMigrations
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');

        $wilayah = Wilayah::create([
            'nama_wilayah' => 'Wilayah Dusk Test',
            'tipe' => 'kecamatan',
            'kode_wilayah' => 'DWD-001',
        ]);

        $kategori = KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'tarif' => 50000,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Dusk',
            'email' => 'admin.dusk@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::create([
            'name' => 'Petugas Dusk',
            'email' => 'petugas.dusk@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'wilayah_id' => $wilayah->id,
            'is_active' => true,
        ]);

        $warga = User::create([
            'name' => 'Warga Dusk',
            'email' => 'warga.dusk@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'masyarakat',
            'is_active' => true,
        ]);

        $this->laporan = Laporan::create([
            'user_id' => $warga->id,
            'wilayah_id' => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Pipa Bocor di Gang Melati',
            'deskripsi' => 'Air merembes dari bawah tanah.',
            'alamat' => 'Jl. Melati No. 5',
            'status' => 'diterima',
            'tanggal_lapor' => now(),
        ]);

        \App\Models\Pembayaran::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $warga->id,
            'harga' => 50000,
            'metode_pembayaran' => 'Transfer Bank',
            'status_pembayaran' => 'Lunas',
        ]);

    }

    /**
     * Helper: buat penugasan palsu agar link notifikasi valid
     */
    protected function seedPenugasan()
    {
        $this->penugasan = \App\Models\Penugasan::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugas->id,
            'tanggal_penugasan' => now(),
            'status_tugas' => 'Ditugaskan',
        ]);
        $this->laporan->setRelation('penugasan', $this->penugasan);
    }

    /**
     * Helper: tunggu hingga Alpine.js selesai inisialisasi
     * (indikasi: atribut x-cloak hilang dari semua elemen).
     */
    protected function waitForAlpine(Browser $browser, int $seconds = 5): void
    {
        // Tunggu hingga tidak ada lagi elemen [x-cloak] yang masih display:none
        $browser->waitUsing($seconds, 100, function () use ($browser) {
            return $browser->script(
                'return document.querySelectorAll("[x-cloak]").length === 0
                      || Array.from(document.querySelectorAll("[x-cloak]"))
                             .every(el => el.style.display !== "none" && window.getComputedStyle(el).display !== "none");'
            )[0];
        }, 'Alpine.js tidak selesai inisialisasi dalam ' . $seconds . ' detik.');
    }

    // ── TC-001 ────────────────────────────────────────────────────────────────

    /**
     * TC-001: Notifikasi muncul saat tugas baru dibuat (badge merah bertambah).
     *
     * Given : Admin berhasil assign Petugas Dusk ke sebuah laporan berstatus "diterima".
     * When  : Petugas Dusk login dan membuka halaman daftar tugas.
     * Then  : Badge merah muncul di ikon lonceng dengan angka ≥ 1.
     */
    public function test_TC001_badge_merah_muncul_setelah_tugas_di_assign(): void
    {
        // ── Step 1: Admin assign petugas via browser UI ───────────────────────
        $this->browse(function (Browser $admin) {
            $admin->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500) // Tunggu Alpine.js & Lucide icons
                ->screenshot('tc001-debug');

            // Verifikasi tombol "Tugaskan Petugas" muncul (hanya saat status = diterima)
            $admin->assertSee('Tugaskan Petugas')
                ->click('@btn-tugaskan-petugas')
                ->waitForText('Buat Work Order #' . $this->laporan->id, 8);

            // Pilih petugas dari modal
            $admin->waitFor('#petugas-btn-' . $this->petugas->id, 5)
                ->click('#petugas-btn-' . $this->petugas->id)
                ->pause(400); // tunggu Alpine state selectedPetugas update

            // Submit form penugasan
            $admin->click('@btn-submit-penugasan')
                ->waitForText('berhasil dibuat dan ditugaskan', 8);
        });

        // Verifikasi notifikasi tersimpan di database dengan read_at null
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->petugas->id,
            'notifiable_type' => User::class,
            'read_at' => null,
        ]);

        // ── Step 2: Petugas login → verifikasi badge muncul di header ─────────
        $this->browse(function (Browser $petugas) {
            $petugas->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000); // Tunggu Alpine.js fetchUnread() via API

            $this->waitForAlpine($petugas, 8);

            // Badge harus terlihat (Alpine menghapus x-cloak setelah init)
            $petugas->assertPresent('#petugas-notif-badge');

            // Ambil teks badge — harus angka > 0
            $badgeText = trim($petugas->text('#petugas-notif-badge'));
            $this->assertTrue(
                is_numeric($badgeText) && (int) $badgeText >= 1,
                "Badge seharusnya menampilkan angka ≥ 1, tapi malah: [{$badgeText}]"
            );
        });
    }

    // ── TC-002 ────────────────────────────────────────────────────────────────

    /**
     * TC-002: Dropdown lonceng memiliki tinggi tetap & scrollable saat > 10 notifikasi.
     *
     * Given : 15 notifikasi sudah ada di database untuk Petugas Dusk.
     * When  : Petugas membuka dropdown lonceng.
     * Then  : Container list memiliki overflow-y scroll, tidak memanjang tak terbatas.
     */
    public function test_TC002_dropdown_scrollable_saat_notifikasi_lebih_dari_10(): void
    {
        $this->seedPenugasan();

        // Buat 15 notifikasi sekaligus langsung via notify()
        for ($i = 1; $i <= 15; $i++) {
            $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));
        }

        $this->assertEquals(15, $this->petugas->notifications()->count());

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000); // Tunggu Alpine.js init + fetchUnread()

            $this->waitForAlpine($browser, 8);

            // Klik bell untuk buka dropdown
            $browser->screenshot('tc002-debug')
                ->click('#petugas-notif-bell')
                ->pause(1500); // tunggu fetchDropdown() via API selesai

            // Dropdown panel harus muncul
            $browser->assertVisible('#petugas-notif-dropdown');

            // Verifikasi container list bisa di-scroll
            // (scrollHeight > clientHeight artinya konten lebih panjang dari container)
            $isScrollable = $browser->script(
                'var el = document.getElementById("petugas-notif-list");
                 return el ? (el.scrollHeight > el.clientHeight) : false;'
            )[0];

            $this->assertTrue(
                $isScrollable,
                'List notifikasi seharusnya bisa di-scroll (scrollHeight > clientHeight) saat ada 15 notifikasi.'
            );
        });
    }

    // ── TC-003 ────────────────────────────────────────────────────────────────

    /**
     * TC-003: Klik notifikasi "Tugas Baru" → redirect ke /petugas/tugas & ditandai dibaca.
     *
     * Given : 1 notifikasi TaskAssigned ada untuk Petugas Dusk.
     * When  : Petugas klik item notifikasi tersebut di dropdown.
     * Then  : Halaman berpindah ke /petugas/tugas DAN notifikasi tersebut
     *         memiliki read_at yang terisi di database.
     */
    public function test_TC003_klik_notifikasi_redirect_dan_tandai_dibaca(): void
    {
        $this->seedPenugasan();

        // Buat 1 notifikasi
        $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));
        $notif = $this->petugas->notifications()->first();

        $this->assertNull($notif->read_at, 'Notifikasi seharusnya belum dibaca sebelum diklik.');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000); // Tunggu Alpine.js init

            $this->waitForAlpine($browser, 8);

            // Buka dropdown lonceng
            $browser->click('#petugas-notif-bell')
                ->pause(1500); // tunggu fetchDropdown() selesai

            // Verifikasi dropdown terbuka dan ada isi
            $browser->assertVisible('#petugas-notif-dropdown')
                ->assertPresent('#petugas-notif-list');

            // Klik area klikable item pertama di notif list
            // Item dirender via Alpine x-for, target div yang memiliki @click handler
            $browser->script(
                'var list = document.getElementById("petugas-notif-list");
                 if (list) {
                     // Cari div dengan event click di dalam list
                     var clickable = list.querySelector("div.cursor-pointer, div[class*=\"cursor-pointer\"]");
                     if (clickable) { clickable.click(); }
                 }'
            );

            $browser->pause(1200); // Tunggu async mark-read + redirect

            // Verifikasi halaman berpindah ke /petugas/tugas/{id}
            $browser->assertPathIs('/petugas/tugas/' . $this->penugasan->id);
        });

        // Verifikasi notifikasi sudah ditandai dibaca di database
        $this->assertDatabaseMissing('notifications', [
            'id' => $notif->id,
            'read_at' => null,
        ]);
    }

    // ── TC-004 ────────────────────────────────────────────────────────────────

    /**
     * TC-004: Tombol "Tandai semua dibaca" → semua read_at terisi, badge hilang.
     *
     * Given : 3 notifikasi belum dibaca ada untuk Petugas Dusk.
     * When  : Petugas membuka dropdown & klik "Tandai semua".
     * Then  : Badge angka di lonceng hilang (unreadCount = 0) DAN
     *         semua record di tabel notifications memiliki read_at yang terisi.
     */
    public function test_TC004_tandai_semua_dibaca_badge_hilang_dan_database_terupdate(): void
    {
        $this->seedPenugasan();

        // Buat 3 notifikasi belum dibaca
        for ($i = 0; $i < 3; $i++) {
            $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));
        }

        $this->assertEquals(3, $this->petugas->unreadNotifications()->count());

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000); // Tunggu Alpine.js init + fetchUnread()

            $this->waitForAlpine($browser, 8);

            // Verifikasi badge ada sebelum aksi
            $browser->assertPresent('#petugas-notif-badge');

            // Buka dropdown lonceng
            $browser->click('#petugas-notif-bell')
                ->pause(1500); // tunggu fetchDropdown() selesai

            $browser->assertVisible('#petugas-notif-dropdown');

            // Klik tombol "Tandai semua" (menggunakan selector button pertama)
            $browser->waitForText('Tandai semua', 5)
                ->click('#petugas-notif-dropdown button:first-of-type')
                ->pause(1000); // tunggu API /api/notifications/read-all selesai

            // Verifikasi badge TIDAK lagi terlihat (hilang karena unreadCount = 0)
            // x-show="unreadCount > 0" → jika 0, elemen disembunyikan (display:none)
            $isHidden = $browser->script(
                'var badge = document.getElementById("petugas-notif-badge");
                 if (!badge) return true;
                 return badge.style.display === "none" || window.getComputedStyle(badge).display === "none";'
            )[0];

            $this->assertTrue($isHidden, 'Badge angka seharusnya tersembunyi setelah "Tandai semua dibaca" diklik.');
        });

        // Verifikasi semua notifikasi di database sudah memiliki read_at
        $unreadCount = DatabaseNotification::where('notifiable_id', $this->petugas->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(
            0,
            $unreadCount,
            "Seharusnya 0 notifikasi belum dibaca di database, tapi masih ada {$unreadCount}."
        );
    }

    // ── TC-005 ────────────────────────────────────────────────────────────────

    /**
     * TC-005: Notifikasi Admin muncul saat warga buat laporan baru & redirect berjalan.
     */
    public function test_TC005_admin_menerima_notifikasi_saat_warga_buat_laporan(): void
    {
        // 1. Kirim notifikasi ke admin (mensimulasikan warga buat laporan baru)
        $this->admin->notify(new NewReportNotification($this->laporan));

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.dashboard')
                ->pause(2000); // Tunggu Alpine.js

            $this->waitForAlpine($browser, 8);

            // Verifikasi badge ada di panel Admin
            $browser->assertPresent('#admin-notif-badge');

            // Buka dropdown lonceng admin
            $browser->click('#admin-notif-bell')
                ->pause(1500);

            $browser->assertVisible('#admin-notif-dropdown')
                ->assertPresent('#admin-notif-list');

            // Klik item pertama
            $browser->script(
                'var list = document.getElementById("admin-notif-list");
                 if (list) {
                     var clickable = list.querySelector("div.cursor-pointer, div[class*=\"cursor-pointer\"]");
                     if (clickable) { clickable.click(); }
                 }'
            );

            $browser->pause(1200);

            // Verifikasi halaman berpindah ke detail laporan di Admin
            $browser->assertPathIs('/admin/laporan/' . $this->laporan->id);
        });
    }

    // ── TC-006 ────────────────────────────────────────────────────────────────

    /**
     * TC-006: Notifikasi Warga muncul saat petugas menyelesaikan tugas.
     */
    public function test_TC006_warga_menerima_notifikasi_saat_petugas_selesaikan_tugas(): void
    {
        $this->seedPenugasan();

        $warga = User::find($this->laporan->user_id);

        // 1. Kirim notifikasi ke warga (mensimulasikan petugas klik selesai)
        $warga->notify(new TaskProgressNotification($this->penugasan, 'Selesai', true));

        $this->browse(function (Browser $browser) use ($warga) {
            $browser->loginAs($warga)
                ->visitRoute('warga.laporan.index')
                ->pause(2000); // Tunggu Alpine.js

            $this->waitForAlpine($browser, 8);

            // Verifikasi badge ada di panel Warga
            $browser->assertPresent('#warga-notif-badge');

            // Buka dropdown lonceng warga
            $browser->click('#warga-notif-bell')
                ->pause(1500);

            $browser->assertVisible('#warga-notif-dropdown')
                ->assertPresent('#warga-notif-list');

            // Klik item pertama
            $browser->script(
                'var list = document.getElementById("warga-notif-list");
                 if (list) {
                     var clickable = list.querySelector("div.cursor-pointer, div[class*=\"cursor-pointer\"]");
                     if (clickable) { clickable.click(); }
                 }'
            );

            $browser->pause(4000);

            // Verifikasi halaman berpindah ke detail laporan di Warga
            $browser->assertPathIs('/warga/laporan/' . $this->laporan->id);
        });
    }

    // ── TC-007 ────────────────────────────────────────────────────────────────

    /**
     * TC-007: Hapus satu notifikasi via tombol X di dropdown & hapus dari database
     */
    public function test_TC007_hapus_satu_notifikasi(): void
    {
        $this->seedPenugasan();
        $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));
        $notif = $this->petugas->notifications()->first();

        $this->browse(function (Browser $browser) use ($notif) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->click('#petugas-notif-bell')
                ->pause(1500)
                ->assertVisible('#petugas-notif-dropdown');

            // Eksekusi klik pada tombol delete (X) di item pertama
            $browser->script(
                'var list = document.getElementById("petugas-notif-list");
                 if (list) {
                     var delBtn = list.querySelector("button[class*=\"hover:text-red-500\"]");
                     if (delBtn) { delBtn.click(); }
                 }'
            );

            $browser->pause(1200); // tunggu API /api/notifications/{id} DELETE

            // Verifikasi notifikasi hilang dari database
            $this->assertDatabaseMissing('notifications', [
                'id' => $notif->id,
            ]);
        });
    }

    // ── TC-008 ────────────────────────────────────────────────────────────────

    /**
     * TC-008: Tombol "Bersihkan" menghapus semua notifikasi dari layar dan database
     */
    public function test_TC008_bersihkan_semua_notifikasi(): void
    {
        $this->seedPenugasan();
        for ($i = 0; $i < 3; $i++) {
            $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->click('#petugas-notif-bell')
                ->pause(1500)
                ->assertVisible('#petugas-notif-dropdown');

            // Klik tombol "Bersihkan"
            $browser->script(
                'var dropdown = document.getElementById("petugas-notif-dropdown");
                 if (dropdown) {
                     var clearBtn = Array.from(dropdown.querySelectorAll("button")).find(el => el.textContent.includes("Bersihkan"));
                     if (clearBtn) { clearBtn.click(); }
                 }'
            );

            // Terima dialog alert javascript (confirm)
            $browser->pause(500)
                ->acceptDialog()
                ->pause(1500); // tunggu API /api/notifications/clear-all DELETE

            // Verifikasi pesan kosong muncul
            $browser->assertSeeIn('#petugas-notif-dropdown', 'Belum ada notifikasi baru');

            // Verifikasi database kosong untuk petugas ini
            $count = DatabaseNotification::where('notifiable_id', $this->petugas->id)->count();
            $this->assertEquals(0, $count, "Seluruh notifikasi seharusnya dihapus dari database.");
        });
    }

    // ── TC-009 ────────────────────────────────────────────────────────────────

    /**
     * TC-009: Klik "Lihat Semua Notifikasi" redirect ke halaman daftar notifikasi penuh
     */
    public function test_TC009_buka_halaman_semua_notifikasi(): void
    {
        $this->seedPenugasan();
        $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->click('#petugas-notif-bell')
                ->pause(1500)
                ->assertVisible('#petugas-notif-dropdown');

            // Klik link "Lihat Semua Notifikasi ->"
            $browser->clickLink('Lihat Semua Notifikasi →')
                ->pause(1000);

            // Verifikasi redirect ke halaman /notifikasi
            $browser->assertPathIs('/notifikasi');
        });
    }
}
