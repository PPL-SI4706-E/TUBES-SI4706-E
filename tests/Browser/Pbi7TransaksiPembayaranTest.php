<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi7TransaksiPembayaranTest extends DuskTestCase
{
    public function testWargaTransaksiPembayaran()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Login sebagai Warga
            $browser->visit('/login')
                    ->type('email', 'andi@gmail.com')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/warga/laporan');
                    
            // Navigasi ke halaman Tagihan / Pembayaran
            // $browser->visit('/warga/pembayaran')
            //         ->assertSee('Tagihan')
            //         ->press('Bayar Sekarang')
            //         ->assertSee('Berhasil');
        });
    }
}
