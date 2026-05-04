<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi6ValidasiLaporanTest extends DuskTestCase
{
    public function testAdminValidasiLaporan()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Login sebagai admin
            $browser->visit('/login')
                    ->type('email', 'admin@tirtabantu.id')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/admin/dashboard');
                    
            // Masuk ke daftar laporan masuk
            $browser->visit('/admin/laporan')
                    ->assertSee('Daftar Laporan');
                    
            // Test skenario validasi (tergantung implementasi tombol di web Anda)
            // ->press('Validasi')
            // ->assertSee('Berhasil divalidasi');
        });
    }
}
