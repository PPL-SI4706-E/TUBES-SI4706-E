<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi4PelaporanBerbasisPetaTest extends DuskTestCase
{
    public function testWargaBisaMembuatLaporan()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Login sebagai Warga terlebih dahulu
            $browser->visit('/login')
                    ->type('email', 'andi@gmail.com')
                    ->type('password', 'password')
                    ->press('Masuk')
                    ->assertPathIs('/warga/laporan');
                    
            // Navigasi ke halaman buat laporan
            $browser->visit('/warga/laporan/create')
                    ->assertSee('Buat Laporan');

            // Kita harus menunggu sebentar agar tombol kategori ter-render jika lambat
            $browser->pause(1000);

            // Klik kategori pertama
            $browser->click('.grid button'); 
            
            // Isi form laporan
            $browser->type('alamat', 'Jl. Automatisasi No. 99, Bandung')
                    ->type('deskripsi', 'Ini adalah deskripsi laporan yang dikirim oleh Robot (Dusk) untuk percobaan testing otomatis.');
            
            // Biarkan Dusk memilih opsi acak yang valid
            $browser->select('wilayah_id');

            // Beri sedikit jeda agar AlpineJS merespon perubahan select
            $browser->pause(500);

            // Submit laporan
            $browser->press('Kirim Laporan')
                    ->pause(2000);

            // Cek apakah masih nyangkut di form
            if (str_contains($browser->driver->getCurrentURL(), '/create')) {
                throw new \Exception("GAGAL VALIDASI! URL: " . $browser->driver->getCurrentURL());
            }

            // Pastikan kita melihat pesan sukses
            $browser->assertSee('Laporan berhasil dibuat');
        });
    }
}
