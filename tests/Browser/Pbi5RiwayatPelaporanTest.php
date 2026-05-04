<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi5RiwayatPelaporanTest extends DuskTestCase
{
    public function testWargaMelihatRiwayatLaporan()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Login sebagai Warga
            $browser->visit('/login')
                    ->type('email', 'andi@gmail.com')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/warga/laporan');
                    
            // Navigasi ke halaman Riwayat (asumsi URL index adalah halaman riwayat)
            $browser->visit('/warga/laporan')
                    ->assertSee('Riwayat Laporan')
                    ->assertSee('Status'); 
        });
    }
}
