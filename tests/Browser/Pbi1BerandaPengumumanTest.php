<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Pengumuman;
use App\Models\TestimoniPublik;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi1BerandaPengumumanTest extends DuskTestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Refresh database for a clean test environment once per execution
        Artisan::call('migrate:fresh');

        // Create an admin user to associate with announcements
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@tirtabantu.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function testMenampilkanInformasiUmumBeranda()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->driver->manage()->deleteAllCookies();

            $browser->visit('/')
                    ->pause(1500)
                    // Skenario 1: Halaman beranda publik tanpa login
                    ->assertPathIs('/')
                    ->assertSee('TirtaBantu')
                    // Skenario 2: Navbar Publik
                    ->assertVisible('header')
                    ->assertSee('Pengumuman')
                    ->assertSee('Tarif Layanan')
                    ->assertSee('Fitur')
                    ->assertSee('Alur Pelaporan')
                    ->assertSee('Testimoni')
                    ->assertSee('Kontak')
                    ->assertSee('Masuk')
                    // Skenario 3: Hero Section
                    ->assertSee('SDG 6 - Air Bersih dan Sanitasi Layak')
                    ->assertSee('Sistem Informasi Manajemen Pelaporan & Distribusi Air Bersih')
                    ->assertSee('Mulai Lapor')
                    ->assertSee('Pelajari Fitur')
                    ->assertSee('Laporan Ditangani')
                    ->assertSee('Tingkat Penyelesaian')
                    ->assertSee('Petugas Aktif')
                    // Skenario 7: Tarif Layanan
                    ->assertSee('Tarif Layanan TirtaBantu')
                    ->assertSee('Pipa Bocor')
                    ->assertSee('Air Keruh / Berbau')
                    ->assertSee('Permintaan Tangki Air')
                    ->assertSee('Pipa Tersumbat')
                    ->assertSee('Sambungan Baru')
                    // Skenario 8: Fitur Lengkap
                    ->assertSee('Fitur Lengkap TirtaBantu')
                    ->assertSee('Pelaporan Detail')
                    ->assertSee('Peta Lokasi Real-time')
                    ->assertSee('Validasi Admin')
                    // Skenario 9: Alur Pelaporan
                    ->assertSee('Alur Pelaporan TirtaBantu')
                    ->assertSee('Masyarakat Melapor')
                    ->assertSee('Admin Memvalidasi')
                    ->assertSee('Penugasan Petugas')
                    // Skenario 11: Kontak & Footer
                    ->assertSee('Tentang TirtaBantu')
                    ->assertSee('Hubungi Kami')
                    ->assertSee('Kantor PDAM TirtaBantu')
                    ->assertSee('cs@tirtabantu.id')
                    ->assertSee('Masuk ke Sistem')
                    ->assertSee('Mendukung SDG 6 - Air Bersih dan Sanitasi Layak untuk Semua.');
        });
    }

    public function testMenampilkanPengumumanTerbaruDanDetail()
    {
        // 1. Create a normal announcement (not urgent/penting)
        $infoPengumuman = Pengumuman::create([
            'user_id' => $this->admin->id,
            'judul' => 'Pemeliharaan Pipa Rutin Wilayah Cianjur',
            'isi' => 'Akan diadakan pemeliharaan pipa air bersih rutin pada hari Sabtu mulai pukul 08:00 WIB.',
            'kategori' => 'info',
            'is_penting' => false,
            'tanggal_post' => now(),
        ]);

        // 2. Create an urgent announcement to act as featured
        $daruratPengumuman = Pengumuman::create([
            'user_id' => $this->admin->id,
            'judul' => 'Pipa Induk Pecah di Jalan Raya Cipanas',
            'isi' => 'Pipa induk pecah menyebabkan pasokan air bersih terhenti sementara di wilayah Cipanas dan sekitarnya.',
            'kategori' => 'darurat',
            'is_penting' => true,
            'tanggal_post' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($daruratPengumuman) {
            $browser->visit('/')
                    ->pause(1500)
                    // Skenario 4: Menampilkan pengumuman terbaru dalam bentuk card
                    ->assertSee('Pengumuman & Info Gangguan')
                    // Check featured/urgent
                    ->assertSee('Pipa Induk Pecah di Jalan Raya Cipanas')
                    ->assertSee('DARURAT')
                    // Check other
                    ->assertSee('Pemeliharaan Pipa Rutin Wilayah Cianjur')
                    ->assertSee('INFORMASI')
                    
                    // Skenario 5: Membuka detail salah satu pengumuman
                    // Klik 'Baca Selengkapnya' yang mengarah ke pengumuman pertama (urgent)
                    ->clickLink('Baca Selengkapnya')
                    ->pause(1500)
                    // Pastikan berada di halaman detail pengumuman
                    ->assertPathIs('/pengumuman/' . $daruratPengumuman->id)
                    ->assertSee('Kembali ke pengumuman')
                    ->assertSee('Pipa Induk Pecah di Jalan Raya Cipanas')
                    ->assertSee('DARURAT')
                    ->assertSee('Pipa induk pecah menyebabkan pasokan air bersih terhenti sementara di wilayah Cipanas dan sekitarnya.');
        });
    }
}
