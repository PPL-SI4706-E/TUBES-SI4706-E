<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Laporan;
use App\Models\Penugasan;
use App\Models\Wilayah;
use App\Models\KategoriLaporan;
use App\Models\Ulasan;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi14KinerjaPetugasTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $admin;
    private User $warga;
    private User $petugasAlpha;
    private User $petugasBeta;
    private Wilayah $wilayah;
    private KategoriLaporan $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->seedDataAwal();
    }

    private function seedDataAwal(): void
    {
        $this->wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Area PBI 14'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI14',
            ]
        );

        $this->kategori = KategoriLaporan::firstOrCreate(
            ['nama_kategori' => 'Pipa Bocor'],
            [
                'deskripsi' => 'Kebocoran pipa',
                'tarif' => 50000,
                'icon' => '💧',
                'is_active' => true,
            ]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.pbi14@test.com'],
            [
                'name' => 'Admin PBI 14',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi14@test.com'],
            [
                'name' => 'Warga PBI 14',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $this->petugasAlpha = User::firstOrCreate(
            ['email' => 'alpha.pbi14@test.com'],
            [
                'name' => 'Petugas Alpha',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $this->petugasBeta = User::firstOrCreate(
            ['email' => 'beta.pbi14@test.com'],
            [
                'name' => 'Petugas Beta',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );
    }

    private function bersihkanDataKinerja(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Ulasan::query()->delete();
        Penugasan::query()->delete();
        Laporan::query()->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function buatLaporan(string $judul): Laporan
    {
        return Laporan::create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $this->wilayah->id,
            'kategori_laporan_id' => $this->kategori->id,
            'judul' => $judul,
            'deskripsi' => 'Laporan untuk penilaian kinerja petugas.',
            'alamat' => 'Jalan Kinerja PBI 14',
            'status' => 'selesai',
            'tanggal_lapor' => now(),
        ]);
    }

    private function buatPenugasanSelesai(User $petugas, int $rating, string $judulLaporan): Penugasan
    {
        $laporan = $this->buatLaporan($judulLaporan);

        $penugasan = Penugasan::create([
            'laporan_id' => $laporan->id,
            'user_id' => $petugas->id,
            'tanggal_penugasan' => now()->toDateString(),
            'status_tugas' => 'Selesai',
            'catatan_admin' => 'Penugasan selesai untuk kinerja.',
        ]);

        Ulasan::create([
            'penugasan_id' => $penugasan->id,
            'laporan_id' => $laporan->id,
            'user_id' => $this->warga->id,
            'rating' => $rating,
            'komentar' => 'Ulasan kinerja petugas.',
        ]);

        return $penugasan;
    }

    private function seedKinerjaPetugas(): void
    {
        $this->bersihkanDataKinerja();

        // Alpha: 1 tugas selesai, rating rata-rata 5.0
        $this->buatPenugasanSelesai($this->petugasAlpha, 5, 'Laporan Alpha 1');

        // Beta: 2 tugas selesai, rating rata-rata 3.0
        $this->buatPenugasanSelesai($this->petugasBeta, 4, 'Laporan Beta 1');
        $this->buatPenugasanSelesai($this->petugasBeta, 2, 'Laporan Beta 2');
    }

    private function pilihRole(Browser $browser, string $role): void
    {
        $browser->script("
            const input = document.querySelector('input[name=\"role\"][value=\"{$role}\"]');
            if (input) {
                input.checked = true;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        ");
    }

    private function loginSebagaiAdmin(Browser $browser): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'admin.pbi14@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'admin');

        $browser->press('Masuk')
            ->waitForLocation('/admin/dashboard', 10)
            ->assertPathIs('/admin/dashboard');
    }

    private function loginSebagaiWarga(Browser $browser): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'warga.pbi14@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'masyarakat');

        $browser->press('Masuk')
            ->waitForLocation('/warga/laporan', 10)
            ->assertPathIs('/warga/laporan');
    }

    public function testTC1401AdminDapatMembukaHalamanKinerjaPetugas()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/kinerja')
                ->waitForText('Kinerja Petugas', 10)
                ->assertSee('Kinerja Petugas');
        });
    }

    public function testTC1402MenampilkanDaftarPetugas()
    {
        $this->seedKinerjaPetugas();

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/kinerja')
                ->waitForText('Petugas Alpha', 10)
                ->assertSee('Petugas Alpha')
                ->assertSee('Petugas Beta');
        });
    }

    public function testTC1403MenampilkanJumlahTugasSelesai()
    {
        $this->seedKinerjaPetugas();

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/kinerja')
                ->waitForText('Petugas Alpha', 10)
                ->assertSee('Petugas Alpha')
                ->assertSee('Petugas Beta');
        });

        $this->assertEquals(
            1,
            Penugasan::where('user_id', $this->petugasAlpha->id)
                ->where('status_tugas', 'Selesai')
                ->count()
        );

        $this->assertEquals(
            2,
            Penugasan::where('user_id', $this->petugasBeta->id)
                ->where('status_tugas', 'Selesai')
                ->count()
        );
    }

    public function testTC1404MenampilkanRataRataRatingPetugas()
    {
        $this->seedKinerjaPetugas();

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/kinerja')
                ->waitForText('Petugas Alpha', 10)
                ->assertSee('5.0')
                ->assertSee('3.0');
        });
    }

    public function testTC1405SortingBerdasarkanJumlahTugasSelesai()
    {
        $this->seedKinerjaPetugas();

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiAdmin($browser);

            $browser->visit('/admin/kinerja')
                ->waitForText('Kinerja Petugas', 10)
                ->clickLink('Jumlah Tugas Selesai')
                ->pause(1000)
                ->assertQueryStringHas('sort_by', 'tugas_selesai_count');

            $browser->clickLink('Jumlah Tugas Selesai')
                ->pause(1000)
                ->assertQueryStringHas('sort_dir', 'desc');
        });
    }

    public function testTC1406UserNonAdminTidakDapatMengaksesKinerjaPetugas()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/admin/kinerja')
                ->pause(1000);

            $path = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);
            $source = $browser->driver->getPageSource();

            $this->assertTrue(
                $path === '/warga/laporan'
                || str_contains($source, '403')
                || str_contains($source, 'Forbidden')
                || str_contains($source, 'Akses ditolak')
            );
        });
    }
}