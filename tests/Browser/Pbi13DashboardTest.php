<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi13DashboardTest extends DuskTestCase
{
    /** @test */
    public function testAdminDashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Login sebagai admin
            $browser->visit('/login')
                    ->assertSee('Masuk ke Sistem');

            // Pilih role admin (radio tersembunyi)
            $browser->script("let r=document.querySelector('input[name=\"role\"][value=\"admin\"]'); r.checked=true; r.dispatchEvent(new Event('change',{bubbles:true}));");

            // Isi email, password, dan kirim
            $browser->type('#email', 'admin@tirtabantu.id')
                    ->type('#password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/admin/dashboard')
                    ->assertSee('Dashboard Admin');
            
            // Verifikasi deskripsi ringkasan dashboard
            $browser->assertSee('Ringkasan sistem pelaporan dan distribusi air bersih');
            
            // Periksa apakah menampilkan data kosong (empty state) atau data statistik
            $bodyText = $browser->script("return document.body.innerText;")[0];
            if (str_contains($bodyText, 'Data statistik tidak tersedia')) {
                $browser->assertSee('Belum ada laporan yang masuk ke dalam sistem untuk dianalisis.');
            } else {
                $browser->assertSee('Total Pendapatan')
                        ->assertSee('Rasio Penyelesaian Laporan')
                        ->assertSee('Laporan Perlu Tindakan')
                        ->assertSee('Laporan Terbaru')
                        ->assertSee('Persebaran Wilayah');
            }
        });
    }
}
