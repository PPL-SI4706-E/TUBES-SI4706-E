<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi2MasterDataTest extends DuskTestCase
{
    public function testAdminKelolaMasterData()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Login sebagai admin
            $browser->visit('/login')
                    ->type('email', 'admin@tirtabantu.id')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/admin/dashboard');
                    
            // Navigasi ke menu Master Data (misal: Kategori Laporan)
            // Sesuaikan dengan URL asli aplikasi Anda
            // $browser->visit('/admin/kategori')
            //         ->assertSee('Kategori Laporan');
        });
    }
}
