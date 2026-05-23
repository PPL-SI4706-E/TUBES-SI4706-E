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
                    ->assertSee('Selamat Datang')
                    // Klik div pembungkus yang mendampingi input radio admin karena input aslinya disembunyikan (sr-only)
                    ->click('input[value="admin"] + div')
                    ->type('email', 'admin@tirtabantu.id')
                    ->type('password', 'password')
                    ->press('Masuk Sekarang')
                    ->assertPathIs('/admin/dashboard')
                    ->assertSee('Dashboard Admin');
            
            // Verifikasi deskripsi ringkasan dashboard
            $browser->assertSee('Ringkasan sistem pelaporan dan distribusi air bersih');
            
            // Periksa apakah menampilkan data kosong (empty state) atau data statistik
            $bodyText = $browser->text('body');
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
