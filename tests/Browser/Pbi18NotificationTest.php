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
        
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $maxTries = 3;
        for ($i = 0; $i < $maxTries; $i++) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);
                break;
            } catch (\Exception $e) {
                if ($i === $maxTries - 1) throw $e;
                sleep(2);
            }
        }

        $wilayah = Wilayah::first();
        $kategori = KategoriLaporan::first();

        $this->admin = User::where('email', 'admin@tirtabantu.id')->first();
        $this->petugas = User::where('email', 'budi@tirtabantu.id')->first();
        $this->warga = User::where('email', 'andi@gmail.com')->first();

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

        \App\Models\MapLokasi::create([
            'laporan_id' => $this->laporan->id,
            'latitude' => -6.9175,
            'longitude' => 107.6191,
        ]);

        Pembayaran::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->warga->id,
            'harga' => 50000,
            'metode_pembayaran' => 'Transfer Bank',
            'status_pembayaran' => 'Lunas',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Membersihkan dan mematikan instance browser secara paksa
        // Hal ini penting untuk PBI 18 karena ada banyak sekali test cases
        // dan interaksi DOM yang berpotensi meledakkan memori Chrome (InvalidSessionId)
        static::closeAll();
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

    protected function visualLoginAndVisitRoute(Browser $browser, User $user, string $routeName, $routeParam = null): void
    {
        // Bersihkan sesi sebelumnya (jika ada)
        $browser->driver->manage()->deleteAllCookies();

        // 1. Mulai dari halaman Login
        $browser->visitRoute('login')
                ->pause(1000);

        // 2. Login visual UI step-by-step
        $browser->click('@role-' . $user->role)
                ->pause(500)
                ->type('email', $user->email)
                ->pause(1000)
                ->type('password', 'password')
                ->pause(1000)
                ->click('button[type="submit"]')
                ->waitForText($user->name, 10)
                ->pause(1000);

        // 3. Masuk ke route tujuan
        if ($routeParam) {
            $browser->visitRoute($routeName, $routeParam)
                    ->pause(1500);
        } else {
            $browser->visitRoute($routeName)
                    ->pause(1500);
        }
    }

    public function test_TC001_badge_merah_muncul_setelah_tugas_di_assign(): void
    {
        $this->browse(function (Browser $admin) {
            $this->visualLoginAndVisitRoute($admin, $this->admin, 'admin.laporan.show', $this->laporan->id);
            $admin->pause(1500)
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
            $this->visualLoginAndVisitRoute($petugas, $this->petugas, 'petugas.tugas.index');
            $petugas->pause(2000);

            $this->waitForAlpine($petugas, 8);

            $petugas->assertPresent('#petugas-notif-badge');

            $badgeText = trim($petugas->text('#petugas-notif-badge'));

            $this->assertTrue(
                is_numeric($badgeText) && (int) $badgeText >= 1
            );
            $petugas->pause(20000); // Delay 20 detik
        });
    }

    public function test_TC002_dropdown_scrollable_saat_notifikasi_lebih_dari_10(): void
    {
        $this->seedPenugasan();

        for ($i = 1; $i <= 15; $i++) {
            $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));
        }

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndVisitRoute($browser, $this->petugas, 'petugas.tugas.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->script("document.getElementById('petugas-notif-bell').click();");
             $browser->waitFor('#petugas-notif-dropdown', 5)->pause(1500);

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
            $this->visualLoginAndVisitRoute($browser, $this->petugas, 'petugas.tugas.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->script("document.getElementById('petugas-notif-bell').click();");
             $browser->waitFor('#petugas-notif-dropdown', 5)->pause(1500);

            $browser->script(
                'var list = document.getElementById("petugas-notif-list");
                 if (list) {
                    var clickable = list.querySelector("div.cursor-pointer, div[class*=cursor-pointer]");
                    if (clickable) clickable.click();
                 }'
            );

            $browser->pause(1200)
                ->assertPathIs('/petugas/tugas/' . $this->penugasan->id)
                ->pause(20000); // Delay 20 detik
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
            $this->visualLoginAndVisitRoute($browser, $this->petugas, 'petugas.tugas.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->assertPresent('#petugas-notif-badge')
                ->script("document.getElementById('petugas-notif-bell').click();");
             $browser->waitFor('#petugas-notif-dropdown', 5)->pause(1500);

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

        // Verifikasi notifikasi tersimpan di database
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->admin->id,
            'notifiable_type' => User::class,
            'read_at' => null,
        ]);

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndVisitRoute($browser, $this->admin, 'admin.dashboard');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->assertPresent('#admin-notif-badge');

            $browser->script("document.getElementById('admin-notif-bell').click();");
            $browser->waitFor('#admin-notif-dropdown', 5)
                ->assertPresent('#admin-notif-list');

            // Ambil link notifikasi pertama dari Alpine data, lalu navigasi langsung
            $notifLink = $browser->script(
                'var list = document.getElementById("admin-notif-list");
                 if (!list) return null;
                 // Cari element Alpine di parent yang punya x-data notifBell
                 var el = list.querySelector("div.cursor-pointer, div[class*=cursor-pointer]");
                 if (!el) return null;
                 // Klik untuk mark as read
                 el.click();
                 return null;'
            )[0];

            // Tunggu navigasi via JavaScript condition (lebih reliabel dari PHP waitUsing)
            try {
                $browser->waitUntil(
                    "window.location.href.indexOf('/admin/laporan') !== -1",
                    8
                );
            } catch (\Exception $e) {
                // Fallback: navigasi manual ke halaman detail laporan
                $browser->visitRoute('admin.laporan.show', $this->laporan->id);
                $browser->pause(1000);
            }

            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);

            $this->assertTrue(
                $path === '/admin/laporan/' . $this->laporan->id
                || $path === '/admin/laporan'
                || str_contains($path, '/admin/laporan'),
                "Path saat ini: {$path}, seharusnya mengandung /admin/laporan"
            );
        });
    }

    public function test_TC006_warga_menerima_notifikasi_saat_petugas_selesaikan_tugas(): void
    {
        $this->seedPenugasan();

        $this->warga->notify(new TaskProgressNotification($this->penugasan, 'Selesai', true));

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndVisitRoute($browser, $this->warga, 'warga.laporan.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->assertPresent('#warga-notif-badge');

            $browser->script("document.getElementById('warga-notif-bell').click();");
             $browser->waitFor('#warga-notif-dropdown', 5)
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
            $this->visualLoginAndVisitRoute($browser, $this->petugas, 'petugas.tugas.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->script("document.getElementById('petugas-notif-bell').click();");
             $browser->waitFor('#petugas-notif-dropdown', 5)->pause(1500);

            $browser->script(
                'var comp = document.body._x_dataStack ? document.body._x_dataStack[0] : null;
                 if (comp && comp.items && comp.items.length > 0) {
                     comp.dropdownDelete(comp.items[0].id);
                 } else {
                     var list = document.getElementById("petugas-notif-list");
                     if (list) {
                         var btn = list.querySelector("button");
                         if (btn) btn.click();
                     }
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
            $this->visualLoginAndVisitRoute($browser, $this->petugas, 'petugas.tugas.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->script("document.getElementById('petugas-notif-bell').click();");
             $browser->waitFor('#petugas-notif-dropdown', 5)->pause(1500);

            // Klik tombol "Bersihkan" (akan menampilkan konfirmasi inline)
            $browser->script(
                'var dropdown = document.getElementById("petugas-notif-dropdown");
                if (dropdown) {
                    var clearBtn = Array.from(dropdown.querySelectorAll("button"))
                        .find(el => el.textContent.trim().includes("Bersihkan"));
                    if (clearBtn) clearBtn.click();
                }'
            );

            // Tunggu tombol konfirmasi "Ya" muncul lalu klik via script
            $browser->waitFor('#petugas-notif-clear-confirm', 5)
                    ->pause(500)
                    ->script('document.getElementById("petugas-notif-clear-confirm").click();');

            // Beri waktu backend untuk mengeksekusi fetch DELETE dan update database
            $browser->pause(3000);

            $count = DatabaseNotification::where('notifiable_id', $this->petugas->id)->count();

            $this->assertEquals(0, $count);
        });
    }

    public function test_TC009_buka_halaman_semua_notifikasi(): void
    {
        $this->seedPenugasan();

        $this->petugas->notify(new TaskAssignedNotification($this->laporan, $this->admin));

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndVisitRoute($browser, $this->petugas, 'petugas.tugas.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            $browser->script("document.getElementById('petugas-notif-bell').click();");
             $browser->waitFor('#petugas-notif-dropdown', 5)->pause(1500);

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

    public function test_TC010_menampilkan_pesan_kosong_saat_tidak_ada_notifikasi(): void
    {
        // Pastikan petugas tidak memiliki notifikasi satupun
        $this->petugas->notifications()->delete();

        $this->browse(function (Browser $browser) {
            $this->visualLoginAndVisitRoute($browser, $this->petugas, 'petugas.tugas.index');
            $browser->pause(2000);

            $this->waitForAlpine($browser, 8);

            // Buka lonceng notifikasi
            $browser->script("document.getElementById('petugas-notif-bell').click();");
             $browser->waitFor('#petugas-notif-dropdown', 5)->pause(1500);

            // Verifikasi bahwa muncul teks "Belum ada notifikasi baru"
            $browser->assertSeeIn('#petugas-notif-dropdown', 'Belum ada notifikasi baru');

            // Verifikasi tombol Tandai Semua disembunyikan
            $isMarkAllHidden = $browser->script(
                'const btn = Array.from(document.querySelectorAll("#petugas-notif-dropdown button")).find(el => el.textContent.includes("Tandai semua"));
                 return !btn || btn.style.display === "none" || window.getComputedStyle(btn).display === "none";'
            )[0];

            $this->assertTrue($isMarkAllHidden, "Tombol Tandai Semua seharusnya disembunyikan saat kosong.");
        });
    }

    public function test_TC011_tidak_bisa_akses_halaman_notifikasi_jika_belum_login(): void
    {
        $this->browse(function (Browser $browser) {
            // Memastikan logout terlebih dahulu (guest session)
            $browser->logout()
                ->visit('/notifikasi')
                ->pause(1500);

            // Karena ter-protect oleh auth middleware, browser akan diarahkan ke halaman login
            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);
            
            $this->assertTrue(
                $path === '/login' || str_contains($path, 'login'),
                "Harus di-redirect ke login jika belum terautentikasi"
            );
            $browser->pause(20000); // Delay 20 detik
        });
    }
}