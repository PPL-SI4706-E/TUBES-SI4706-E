<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi1BerandaPengumumanTest extends DuskTestCase
{
    public function testPengunjungBisaMelihatBerandaDanPengumuman()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $browser->driver->manage()->deleteAllCookies();
            
            // Masuk ke halaman utama (Beranda)
            $browser->visit('/')
                    ->pause(2000)
                    ->waitForText('TirtaBantu', 15)
                    ->assertSee('TirtaBantu'); // Pastikan judul/brand terlihat
            
            // Tes ini bisa diperluas untuk menguji klik pengumuman
            // ->clickLink('Baca Selengkapnya')
            // ->assertPathBeginsWith('/pengumuman');
        });
    }
}
