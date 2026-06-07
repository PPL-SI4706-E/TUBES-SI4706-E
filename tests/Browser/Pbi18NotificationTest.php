<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\Penugasan;
use App\Models\Pembayaran;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\NewReportNotification;
use App\Notifications\TaskProgressNotification;
use Illuminate\Notifications\DatabaseNotification;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi18NotificationTest extends DuskTestCase
{
    protected User $admin;
    protected User $petugas;
    protected User $warga;
    protected Laporan $laporan;
    protected Penugasan $penugasan;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');

        $wilayah = Wilayah::create([
            'nama_wilayah' => 'Wilayah Dusk Test',
            'tipe' => 'kecamatan',
            'kode_wilayah' => 'DWD-001',
        ]);

        $kategori = KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'deskripsi' => 'Kebocoran pipa',
            'tarif' => 50000,
            'icon' => '💧',
            'is_active' => true,
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

        $this->warga = User::create([
            'name' => 'Warga Dusk',
            'email' => 'warga.dusk@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'masyarakat',
            'wilayah_id' => $wilayah->id,
            'is_active' => true,
        ]);

        $this->laporan = Laporan::create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Pipa Bocor di Gang Melati',
            'deskripsi' => 'Air merembes dari bawah tanah.',
            'alamat' => 'Jl. Melati No. 5',
            'status' => 'diterima',
            'tanggal_lapor' => now(),
        ]);

        Pembayaran::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->warga->id,
            'harga' => 50000,
            'metode_pembayaran' => 'Transfer Bank',
            'status_pembayaran' => 'Lunas',
        ]);
    }

    protected function seedPenugasan(): void
    {
        $this->penugasan = Penugasan::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugas->id,
            'tanggal_penugasan' => now()->toDateString(),
            'status_tugas' => 'Ditugaskan',
        ]);

        $this->laporan->setRelation('penugasan', $this->penugasan);
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

    public function test_TC001_badge_merah_muncul_setelah_tugas_di_assign(): void
    {
        $this->browse(function (Browser $admin) {
            $admin->loginAs($this->admin)
                ->visitRoute('admin.laporan.show', $this->laporan->id)
                ->pause(1500)
                ->assertSee('Tugaskan Petugas')
                ->click('@btn-tugaskan-petugas')
                ->waitForText('Buat Work Order #' . $this->laporan->id, 8)
                ->waitFor('#petugas-btn-' . $this->petugas->id, 5)
                ->click('#petugas-btn-' . $this->petugas->id)
                ->pause(400)
                ->click('@btn-submit-penugasan')
                ->waitForText('berhasil dibuat dan ditugaskan', 8);
        });

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->petugas->id,
            'notifiable_type' => User::class,
            'read_at' => null,
        ]);

        $this->browse(function (Browser $petugas) {
            $petugas->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000);

            $this->waitForAlpine($petugas, 8);

            $petugas->assertPresent('#petugas-notif-badge');

            $badgeText = trim($petugas->text('#petugas-notif-badge'));

            $this->assertTrue(
                is_numeric($badgeText) && (int) $badgeText >= 1
            );
        });
    }

    public function test_TC002_dropdown_scrollable_saat_notifikasi_lebih_dari_10(): void
    {
        $this->seedPenugasan();

        for ($i = 1; $i <= 15; $i++) {
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

            $isScrollable = $browser->script(
                'var el = document.getElementById("petugas-notif-list");
                 return el ? (el.scrollHeight > el.clientHeight) : false;'
            )[0];

            $this->assertTrue($isScrollable);
        });
    }

    public function test_TC003_klik_notifikasi_redirect_dan_tandai_dibaca(): void
    {
        $this->seedPenugasan();

        $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));
        $notif = $this->petugas->notifications()->first();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.index')
                ->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->click('#petugas-notif-bell')
                ->pause(1500)
                ->assertVisible('#petugas-notif-dropdown');

            $browser->script(
                'var list = document.getElementById("petugas-notif-list");
                 if (list) {
                    var clickable = list.querySelector("div.cursor-pointer, div[class*=cursor-pointer]");
                    if (clickable) clickable.click();
                 }'
            );

            $browser->pause(1200)
                ->assertPathIs('/petugas/tugas/' . $this->penugasan->id);
        });

        $this->assertDatabaseMissing('notifications', [
            'id' => $notif->id,
            'read_at' => null,
        ]);
    }

    public function test_TC004_tandai_semua_dibaca_badge_hilang_dan_database_terupdate(): void
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

            $browser->assertPresent('#petugas-notif-badge')
                ->click('#petugas-notif-bell')
                ->pause(1500)
                ->assertVisible('#petugas-notif-dropdown');

            $browser->script(
                'var dropdown = document.getElementById("petugas-notif-dropdown");
                 if (dropdown) {
                    var btn = Array.from(dropdown.querySelectorAll("button"))
                        .find(el => el.textContent.includes("Tandai semua"));
                    if (btn) btn.click();
                 }'
            );

            $browser->pause(1500);

            $isHidden = $browser->script(
                'var badge = document.getElementById("petugas-notif-badge");
                 if (!badge) return true;
                 return badge.style.display === "none" || window.getComputedStyle(badge).display === "none";'
            )[0];

            $this->assertTrue($isHidden);
        });

        $unreadCount = DatabaseNotification::where('notifiable_id', $this->petugas->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    public function test_TC005_admin_menerima_notifikasi_saat_warga_buat_laporan(): void
    {
        $this->admin->notify(new NewReportNotification($this->laporan));

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.dashboard')
                ->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->assertPresent('#admin-notif-badge');

            $browser->click('#admin-notif-bell')
                ->pause(1500)
                ->assertVisible('#admin-notif-dropdown')
                ->assertPresent('#admin-notif-list');

            $browser->script(
                'var list = document.getElementById("admin-notif-list");
                 if (list) {
                    var clickable = list.querySelector("div.cursor-pointer, div[class*=cursor-pointer]");
                    if (clickable) clickable.click();
                 }'
            );

            $browser->pause(1500);

            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);

            $this->assertTrue(
                $path === '/admin/laporan/' . $this->laporan->id
                || $path === '/admin/laporan'
                || str_contains($path, '/admin/laporan')
            );
        });
    }

    public function test_TC006_warga_menerima_notifikasi_saat_petugas_selesaikan_tugas(): void
    {
        $this->seedPenugasan();

        $this->warga->notify(new TaskProgressNotification($this->penugasan, 'Selesai', true));

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->warga)
                ->visitRoute('warga.laporan.index')
                ->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->assertPresent('#warga-notif-badge');

            $browser->click('#warga-notif-bell')
                ->pause(1500)
                ->assertVisible('#warga-notif-dropdown')
                ->assertPresent('#warga-notif-list');

            $browser->script(
                'var list = document.getElementById("warga-notif-list");
                 if (list) {
                    var clickable = list.querySelector("div.cursor-pointer, div[class*=cursor-pointer]");
                    if (clickable) clickable.click();
                 }'
            );

            $browser->pause(2000);

            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);

            $this->assertTrue(
                $path === '/warga/laporan/' . $this->laporan->id
                || $path === '/warga/laporan'
                || str_contains($path, '/warga/laporan')
            );
        });
    }

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

            $browser->script(
                'var list = document.getElementById("petugas-notif-list");
                 if (list) {
                    var buttons = Array.from(list.querySelectorAll("button"));
                    var delBtn = buttons.find(btn =>
                        btn.textContent.includes("×")
                        || btn.textContent.includes("x")
                        || btn.className.includes("red")
                        || btn.innerHTML.includes("trash")
                    );
                    if (!delBtn) delBtn = buttons[buttons.length - 1];
                    if (delBtn) delBtn.click();
                 }'
            );

            $browser->pause(1500);

            $this->assertDatabaseMissing('notifications', [
                'id' => $notif->id,
            ]);
        });
    }

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

            $browser->script(
                'var dropdown = document.getElementById("petugas-notif-dropdown");
                if (dropdown) {
                    var clearBtn = Array.from(dropdown.querySelectorAll("button"))
                        .find(el => el.textContent.includes("Bersihkan"));
                    if (clearBtn) clearBtn.click();
                }'
            );

            $browser->driver->switchTo()->alert()->accept();

            $browser->pause(2000);

            $count = DatabaseNotification::where('notifiable_id', $this->petugas->id)->count();

            $this->assertEquals(0, $count);
        });
    }

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

            $browser->script(
                'const links = Array.from(document.querySelectorAll("a"));
                 const link = links.find(a => a.textContent.includes("Lihat Semua Notifikasi"));
                 if (link) link.click();'
            );

            $browser->pause(1500);

            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);

            $this->assertTrue(
                $path === '/notifikasi'
                || str_contains($path, 'notifikasi')
            );
        });
    }
}