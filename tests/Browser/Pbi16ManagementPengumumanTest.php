<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi16ManagementPengumumanTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $admin;
    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.pbi16@test.com'],
            [
                'name' => 'Admin PBI 16',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi16@test.com'],
            [
                'name' => 'Warga PBI 16',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
            ]
        );
    }

    private function pilihRole(Browser $browser, string $role): void
    {
        $browser->script("
            const input = document.querySelector('input[name=\"role\"][value=\"{$role}\"]');
            if (input) {
                input.checked = true;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        ");
    }

    private function loginSebagaiAdmin(Browser $browser): void
    {
        $browser->visit('/')->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'admin.pbi16@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'admin');

        $browser->press('Masuk')
            ->waitForLocation('/admin/dashboard', 10)
            ->assertPathIs('/admin/dashboard');
    }

    private function submitPengumumanViaJs(
        Browser $browser,
        string $action,
        string $method,
        string $judul,
        string $isi,
        string $kategori = 'info',
        ?string $tanggalPost = null,
        bool $isPenting = false
    ): void {
        $tanggalPost = $tanggalPost ?? now()->toDateString();
        $checked = $isPenting ? 'true' : 'false';

        $browser->script("
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{$action}';

            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                || document.querySelector('input[name=\"_token\"]')?.value;

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            if ('{$method}' !== 'POST') {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = '{$method}';
                form.appendChild(methodInput);
            }

            const judulInput = document.createElement('input');
            judulInput.type = 'hidden';
            judulInput.name = 'judul';
            judulInput.value = '{$judul}';
            form.appendChild(judulInput);

            const isiInput = document.createElement('input');
            isiInput.type = 'hidden';
            isiInput.name = 'isi';
            isiInput.value = '{$isi}';
            form.appendChild(isiInput);

            const kategoriInput = document.createElement('input');
            kategoriInput.type = 'hidden';
            kategoriInput.name = 'kategori';
            kategoriInput.value = '{$kategori}';
            form.appendChild(kategoriInput);

            const tanggalInput = document.createElement('input');
            tanggalInput.type = 'hidden';
            tanggalInput.name = 'tanggal_post';
            tanggalInput.value = '{$tanggalPost}';
            form.appendChild(tanggalInput);

            if ({$checked}) {
                const pentingInput = document.createElement('input');
                pentingInput.type = 'hidden';
                pentingInput.name = 'is_penting';
                pentingInput.value = '1';
                form.appendChild(pentingInput);
            }

            document.body.appendChild(form);
            form.submit();
        ");
    }

    public function testTC1601AdminMembukaHalamanPengumuman()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/pengumuman')
                ->waitForText('Pengumuman', 10)
                ->assertSee('Kelola info gangguan distribusi air dan pengumuman publik');
        });
    }

    public function testTC1602AdminMembuatPengumumanBaru()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/pengumuman')
                ->waitForText('Form Pengumuman Baru', 10);

            $this->submitPengumumanViaJs(
                $browser,
                '/admin/pengumuman',
                'POST',
                'Pengumuman PBI 16',
                'Ini adalah isi pengumuman untuk pengujian PBI 16.',
                'info',
                now()->toDateString(),
                true
            );

            $browser->pause(1000)
                ->assertPathIs('/admin/pengumuman');

            $this->assertDatabaseHas('pengumuman', [
                'judul' => 'Pengumuman PBI 16',
                'isi' => 'Ini adalah isi pengumuman untuk pengujian PBI 16.',
                'kategori' => 'info',
                'user_id' => $this->admin->id,
            ]);
        });
    }

    public function testTC1603AdminEditPengumuman()
    {
        $pengumuman = Pengumuman::create([
            'judul' => 'Pengumuman Lama PBI 16',
            'isi' => 'Isi lama pengumuman.',
            'kategori' => 'info',
            'tanggal_post' => now()->toDateString(),
            'is_penting' => false,
            'user_id' => $this->admin->id,
        ]);

        $this->browse(function (Browser $browser) use ($pengumuman) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/pengumuman')
                ->waitForText('Pengumuman Lama PBI 16', 10);

            $this->submitPengumumanViaJs(
                $browser,
                '/admin/pengumuman/' . $pengumuman->id,
                'PUT',
                'Pengumuman Baru PBI 16',
                'Isi pengumuman sudah diperbarui.',
                'gangguan',
                now()->toDateString(),
                true
            );

            $browser->pause(1000)
                ->assertPathIs('/admin/pengumuman');

            $this->assertDatabaseHas('pengumuman', [
                'id' => $pengumuman->id,
                'judul' => 'Pengumuman Baru PBI 16',
                'isi' => 'Isi pengumuman sudah diperbarui.',
                'kategori' => 'gangguan',
                'is_penting' => true,
            ]);
        });
    }

    public function testTC1604AdminHapusPengumuman()
    {
        $pengumuman = Pengumuman::create([
            'judul' => 'Pengumuman Hapus PBI 16',
            'isi' => 'Isi pengumuman yang akan dihapus.',
            'kategori' => 'info',
            'tanggal_post' => now()->toDateString(),
            'is_penting' => false,
            'user_id' => $this->admin->id,
        ]);

        $this->browse(function (Browser $browser) use ($pengumuman) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/pengumuman')
                ->waitForText('Pengumuman Hapus PBI 16', 10);

            $browser->script("
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/pengumuman/{$pengumuman->id}';

                const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                    || document.querySelector('input[name=\"_token\"]')?.value;

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrf;
                form.appendChild(tokenInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            ");

            $browser->pause(1000)
                ->assertPathIs('/admin/pengumuman');

            $this->assertDatabaseMissing('pengumuman', [
                'id' => $pengumuman->id,
            ]);
        });
    }

    public function testTC1605ValidasiFieldWajib()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $jumlahAwal = Pengumuman::count();

            $browser->visit('/admin/pengumuman')
                ->waitForText('Form Pengumuman Baru', 10);

            $this->submitPengumumanViaJs(
                $browser,
                '/admin/pengumuman',
                'POST',
                '',
                '',
                'info',
                now()->toDateString(),
                false
            );

            $browser->pause(1000)
                ->assertPathIs('/admin/pengumuman');

            $this->assertEquals($jumlahAwal, Pengumuman::count());
        });
    }

    public function testTC1606UserNonAdminTidakBisaAksesPengumumanAdmin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                ->waitForText('Masuk ke Sistem', 10)
                ->type('email', 'warga.pbi16@test.com')
                ->type('password', 'password');

            $this->pilihRole($browser, 'masyarakat');

            $browser->press('Masuk')
                ->waitForLocation('/warga/laporan', 10)
                ->assertPathIs('/warga/laporan');

            $browser->visit('/admin/pengumuman')
                ->pause(1000);

            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);
            $source = $browser->driver->getPageSource();

            $this->assertTrue(
                $path === '/warga/laporan'
                || str_contains($source, '403')
                || str_contains($source, 'Forbidden')
                || str_contains($source, 'Akses ditolak')
            );
        });
    }
}