<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Laporan;
use App\Models\Penugasan;
use App\Models\Wilayah;
use App\Models\KategoriLaporan;
use App\Models\Ulasan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi14KinerjaPetugasTest extends DuskTestCase
{
    /** @test */
    public function pbi14_kinerja_petugas()
    {
        // 1. Setup Data
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'email' => 'admin_pbi14@example.com']);
        $warga = User::where('role', 'masyarakat')->first() ?? User::factory()->create(['role' => 'masyarakat']);
        
        $petugas1 = User::firstOrCreate(['email' => 'alpha@pbi14.com'], ['role' => 'petugas', 'name' => 'Petugas Alpha', 'password' => bcrypt('password')]);
        $petugas2 = User::firstOrCreate(['email' => 'beta@pbi14.com'], ['role' => 'petugas', 'name' => 'Petugas Beta', 'password' => bcrypt('password')]);

        $wilayah = Wilayah::first() ?? Wilayah::create(['nama_wilayah' => 'A', 'kode_wilayah' => 'A1']);
        $kategori = KategoriLaporan::first() ?? KategoriLaporan::create(['nama_kategori' => 'A', 'tarif' => 10]);
        
        $laporan = Laporan::create(['user_id' => $warga->id, 'wilayah_id' => $wilayah->id, 'kategori_laporan_id' => $kategori->id, 'judul' => 'T', 'deskripsi' => 'D', 'alamat' => 'A', 'status' => 'selesai']);

        // Petugas 1: 1 tugas, rating 5.0
        $t1 = Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $petugas1->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);
        Ulasan::create(['penugasan_id' => $t1->id, 'user_id' => $warga->id, 'laporan_id' => $laporan->id, 'rating' => 5, 'komentar' => 'bagus']);

        // Petugas 2: 2 tugas, rating 3.0 avg
        $t2 = Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $petugas2->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);
        Ulasan::create(['penugasan_id' => $t2->id, 'user_id' => $warga->id, 'laporan_id' => $laporan->id, 'rating' => 4, 'komentar' => 'ok']);
        
        $t3 = Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $petugas2->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);
        Ulasan::create(['penugasan_id' => $t3->id, 'user_id' => $warga->id, 'laporan_id' => $laporan->id, 'rating' => 2, 'komentar' => 'kurang']);

        // 2. Browser Test
        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visitRoute('admin.kinerja.index')
                    // Memastikan data tampil
                    ->assertSee('Kinerja Petugas')
                    ->assertSee('Petugas Alpha')
                    ->assertSee('Petugas Beta')
                    ->assertSee('5.0')
                    ->assertSee('3.0')
                    
                    // TC-004: Test Sorting
                    ->clickLink('Jumlah Tugas Selesai')
                    ->pause(1000)
                    ->assertQueryStringHas('sort_by', 'tugas_selesai_count')
                    
                    ->clickLink('Jumlah Tugas Selesai')
                    ->pause(1000)
                    ->assertQueryStringHas('sort_dir', 'desc');
        });
    }
}
