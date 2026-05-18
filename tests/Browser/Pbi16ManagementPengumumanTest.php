<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi16ManagementPengumumanTest extends DuskTestCase
{
    public function testAdminKelolaPengumuman()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Login sebagai admin
            $browser->visit('/login')
                    ->type('email', 'admin@tirtabantu.id')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/admin/dashboard');
                    
            // Navigasi ke menu Pengumuman
            $browser->visit('/admin/pengumuman')
                    ->assertSee('Pengumuman');
                    
            // Test skenario CRUD pengumuman
            // ->press('Tambah Pengumuman')
            // ->type('judul', 'Pemadaman Air')
            // ->press('Simpan')
            // ->assertSee('Berhasil');
        });
    }
}
