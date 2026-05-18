<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi3ManajemenAkunRoleTest extends DuskTestCase
{
    public function testAdminLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $browser->visit('/login')
                    ->assertSee('Masuk ke Sistem')
                    ->type('email', 'admin@tirtabantu.id')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/admin/dashboard');
        });
    }

    public function testWargaLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $browser->visit('/login')
                    ->type('email', 'andi@gmail.com')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/warga/laporan');
        });
    }

    public function testLoginDenganPasswordSalah()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $browser->visit('/login')
                    ->type('email', 'admin@tirtabantu.id')
                    ->type('password', 'salah123')
                    ->press('Masuk')
                    ->assertPathIs('/login'); // Pastikan tetap di halaman login
        });
    }

    public function testWargaRegister()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            $emailBaru = 'tester' . time() . '@gmail.com';
            
            $browser->visit('/register')
                    ->assertSee('Buat Akun Baru')
                    ->type('name', 'Warga Tester')
                    ->type('email', $emailBaru)
                    ->type('phone', '08123456789')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Buat Akun')
                    ->assertPathIs('/login')
                    ->assertSee('Pendaftaran berhasil');
        });
    }
}
