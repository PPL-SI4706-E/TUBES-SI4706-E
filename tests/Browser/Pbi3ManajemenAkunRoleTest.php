<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi3ManajemenAkunRoleTest extends DuskTestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->seedDataAwal();
    }

    private function seedDataAwal(): void
    {
        Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Area Test'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'AREA01',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@tirtabantu.id'],
            [
                'name' => 'Admin Tirta',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'andi@gmail.com'],
            [
                'name' => 'Andi Warga',
                'phone' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'budi@tirtabantu.id'],
            [
                'name' => 'Budi Petugas',
                'phone' => '083333333333',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'is_active' => true,
                'wilayah_id' => Wilayah::first()->id,
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

    private function loginSebagai(Browser $browser, string $email, string $role, string $expectedPath): void
    {
        $browser->visit('/')->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', $email)
            ->type('password', 'password');

        $this->pilihRole($browser, $role);

        $browser->press('Masuk')
            ->waitForLocation($expectedPath, 10)
            ->assertPathIs($expectedPath);
    }

    public function testTC01LihatDaftarPengguna()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagai($browser, 'admin@tirtabantu.id', 'admin', '/admin/dashboard');

            $browser->visit('/admin/users')
                ->waitForText('Manajemen Pengguna', 10)
                ->assertSee('Kelola data pengguna dan role akses')
                ->assertSee('Admin Tirta')
                ->assertSee('admin@tirtabantu.id')
                ->assertSee('Budi Petugas')
                ->assertSee('budi@tirtabantu.id')
                ->assertSee('Andi Warga')
                ->assertSee('andi@gmail.com')
                ->assertSee('Admin')
                ->assertSee('Petugas')
                ->assertSee('Masyarakat');
        });
    }

    public function testTC02TambahPenggunaBaru()
    {
        $email = 'petugasbaru' . time() . '@gmail.com';

        $this->browse(function (Browser $browser) use ($email) {
            $this->loginSebagai($browser, 'admin@tirtabantu.id', 'admin', '/admin/dashboard');

            $browser->visit('/admin/users')
                ->waitForText('Manajemen Pengguna', 10)
                ->click('@btn-tambah-pengguna')
                ->waitFor('input[name="name"]', 10)
                ->type('name', 'Petugas Baru')
                ->type('email', $email)
                ->type('password', 'password123')
                ->select('role', 'petugas')
                ->type('phone', '084444444444');

            if ($browser->element('select[name="wilayah_id"]')) {
                $browser->select('wilayah_id', (string) Wilayah::first()->id);
            }

            $browser->press('Simpan')
                ->waitForText('Pengguna berhasil ditambahkan.', 10)
                ->assertSee('Petugas Baru')
                ->assertSee($email);

            $user = User::where('email', $email)->first();

            $this->assertNotNull($user);
            $this->assertEquals('petugas', $user->role);
            $this->assertTrue(Hash::check('password123', $user->password));
        });
    }

    public function testTC03TambahPenggunaDenganEmailDuplikat()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagai($browser, 'admin@tirtabantu.id', 'admin', '/admin/dashboard');

            $browser->visit('/admin/users')
                ->waitForText('Manajemen Pengguna', 10)
                ->click('@btn-tambah-pengguna')
                ->waitFor('input[name="name"]', 10)
                ->type('name', 'Admin Duplikat')
                ->type('email', 'admin@tirtabantu.id')
                ->type('password', 'password123')
                ->select('role', 'admin')
                ->type('phone', '085555555555')
                ->press('Simpan')
                ->waitForText('Email sudah terdaftar.', 10)
                ->assertSee('Email sudah terdaftar.');
        });
    }

    public function testTC04EditDataPengguna()
    {
        $user = User::firstOrCreate(
            ['email' => 'edituser@gmail.com'],
            [
                'name' => 'User Sebelum Edit',
                'phone' => '086666666666',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
            ]
        );

        $this->browse(function (Browser $browser) use ($user) {
            $this->loginSebagai($browser, 'admin@tirtabantu.id', 'admin', '/admin/dashboard');

            $browser->visit('/admin/users')
                ->waitForText('User Sebelum Edit', 10)
                ->click('@btn-edit-user-' . $user->id)
                ->waitFor('input[name="name"]', 10)
                ->keys('input[name="name"]', ['{control}', 'a'], '{backspace}')
                ->type('name', 'User Setelah Edit')
                ->select('role', 'petugas');

            if ($browser->element('select[name="wilayah_id"]')) {
                $browser->select('wilayah_id', (string) Wilayah::first()->id);
            }

            $browser->press('Simpan')
                ->waitForText('Data pengguna berhasil diperbarui.', 10)
                ->assertSee('User Setelah Edit')
                ->assertSee('Petugas');

            $user->refresh();

            $this->assertEquals('User Setelah Edit', $user->name);
            $this->assertEquals('petugas', $user->role);
        });
    }

    public function testTC05HapusPengguna()
    {
        $user = User::create([
            'name' => 'User Akan Dihapus',
            'email' => 'hapususer' . time() . '@gmail.com',
            'phone' => '087777777777',
            'password' => Hash::make('password'),
            'role' => 'masyarakat',
            'is_active' => true,
        ]);

        $email = $user->email;

        $this->browse(function (Browser $browser) use ($user, $email) {
            $this->loginSebagai($browser, 'admin@tirtabantu.id', 'admin', '/admin/dashboard');

            $browser->visit('/admin/users')
                ->waitForText($email, 10)
                ->click('@btn-delete-user-' . $user->id);

            $browser->driver->switchTo()->alert()->accept();

            $browser->waitForText('Pengguna berhasil dihapus.', 10)
                ->assertDontSee($email);

            $this->assertDatabaseMissing('users', [
                'email' => $email,
            ]);
        });
    }

    public function testTC06SelfRegistrationMasyarakat()
    {
        $email = 'wargaregister' . time() . '@gmail.com';

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();

            $browser->visit('/register')
                ->waitForText('Buat Akun Baru', 10)
                ->click('@role-masyarakat')
                ->type('name', 'Warga Register')
                ->type('email', $email)
                ->type('phone', '088888888888')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->press('Buat Akun')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->assertSee('Pendaftaran berhasil sebagai Masyarakat');

            $user = User::where('email', $email)->first();

            $this->assertNotNull($user);
            $this->assertEquals('masyarakat', $user->role);
            $this->assertTrue(Hash::check('password123', $user->password));
        });
    }

    public function testTC07RBACMasyarakatAksesURLAdmin()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagai($browser, 'andi@gmail.com', 'masyarakat', '/warga/laporan');

            $browser->visit('/admin/dashboard')
                ->pause(1000);

            $currentPath = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);
            $pageSource = $browser->driver->getPageSource();

            $this->assertTrue(
                $currentPath === '/warga/laporan'
                || str_contains($pageSource, '403')
                || str_contains($pageSource, 'Akses ditolak')
                || str_contains($pageSource, 'Forbidden')
            );
        });
    }

    public function testTC08LoginDenganRoleTidakSesuai()
{
    $this->browse(function (Browser $browser) {

        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->script("
            localStorage.clear();
            sessionStorage.clear();
        ");

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'admin@tirtabantu.id')
            ->type('password', 'password');

        $this->pilihRole($browser, 'masyarakat');

        $browser->press('Masuk')
            ->pause(2000);

        $browser->assertPathIs('/login')
            ->assertDontSee('Dashboard')
            ->assertDontSee('Manajemen Pengguna');
    });
}
}