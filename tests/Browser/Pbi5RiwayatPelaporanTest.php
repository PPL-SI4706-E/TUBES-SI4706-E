<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi5RiwayatPelaporanTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $warga;
    private User $wargaLain;
    private KategoriLaporan $kategori;
    private Wilayah $wilayah;

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
            ['nama_wilayah' => 'Area PBI 5'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI5',
            ]
        );

        $this->kategori = KategoriLaporan::firstOrCreate(
            ['nama_kategori' => 'Pipa Bocor'],
            [
                'deskripsi' => 'Kebocoran pipa air',
                'tarif' => 50000,
                'icon' => '💧',
                'is_active' => true,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi5@test.com'],
            [
                'name' => 'Warga PBI 5',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $this->wargaLain = User::firstOrCreate(
            ['email' => 'warga.lain.pbi5@test.com'],
            [
                'name' => 'Warga Lain PBI 5',
                'phone' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );
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

    private function loginSebagaiWarga(Browser $browser, string $email = 'warga.pbi5@test.com'): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', $email)
            ->type('password', 'password');

        $this->pilihRole($browser, 'masyarakat');

        $browser->press('Masuk')
            ->waitForLocation('/warga/laporan', 10)
            ->assertPathIs('/warga/laporan');
    }

    private function buatLaporan(User $user, array $override = []): Laporan
    {
        $laporan = Laporan::create(array_merge([
            'user_id' => $user->id,
            'wilayah_id' => $this->wilayah->id,
            'kategori_laporan_id' => $this->kategori->id,
            'judul' => 'Pipa Bocor - Test',
            'deskripsi' => 'Air bocor cukup deras di depan rumah.',
            'alamat' => 'Jalan PBI 5 Nomor 10',
            'status' => 'pending',
            'tanggal_lapor' => now(),
            'catatan_admin' => null,
        ], $override));

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => -6.973000,
            'longitude' => 107.630000,
        ]);

        return $laporan;
    }

    public function testTC01LihatDaftarRiwayatLaporan()
    {
        $laporan = $this->buatLaporan($this->warga, [
            'deskripsi' => 'Pipa bocor di halaman rumah.',
            'alamat' => 'Jalan Melati Nomor 1',
            'status' => 'diterima',
            'catatan_admin' => 'Laporan telah divalidasi admin.',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/laporan')
                ->waitForText('Riwayat Laporan', 10)
                ->assertSee('#' . $laporan->id)
                ->assertSee('Pipa Bocor')
                ->assertSee('Jalan Melati Nomor 1')
                ->assertSee('Divalidasi');
        });
    }

    public function testTC02MenambahkanUlasanRating()
    {
        $laporan = $this->buatLaporan($this->warga, [
            'status' => 'menunggu_konfirmasi',
            'deskripsi' => 'Laporan sudah ditangani petugas.',
            'alamat' => 'Jalan Ulasan Nomor 2',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/laporan')
                ->waitForText('Jalan Ulasan Nomor 2', 10);

            $browser->script("
                document.querySelectorAll('button').forEach(button => {
                    if (
                        button.innerText.includes('#{$laporan->id}') ||
                        button.innerText.includes('Detail') ||
                        button.innerText.includes('Lihat')
                    ) {
                        const row = button.closest('div') || button.closest('tr');
                        if (!row || row.innerText.includes('Jalan Ulasan Nomor 2')) {
                            button.click();
                        }
                    }
                });
            ");

            $browser->pause(1000);

            $browser->script("
                let form = document.querySelector('form[action*=\"/konfirmasi\"]');

                if (form) {
                    form.action = '/warga/laporan/{$laporan->id}/konfirmasi';

                    let action = form.querySelector('input[name=\"action\"]');
                    if (!action) {
                        action = document.createElement('input');
                        action.type = 'hidden';
                        action.name = 'action';
                        form.appendChild(action);
                    }
                    action.value = 'selesai';

                    let rating = form.querySelector('input[name=\"rating\"]');
                    if (!rating) {
                        rating = document.createElement('input');
                        rating.type = 'hidden';
                        rating.name = 'rating';
                        form.appendChild(rating);
                    }
                    rating.value = '5';

                    let komentar = form.querySelector('textarea[name=\"komentar\"], input[name=\"komentar\"]');
                    if (komentar) {
                        komentar.value = 'Pelayanan sangat baik dan cepat.';
                        komentar.dispatchEvent(new Event('input', { bubbles: true }));
                        komentar.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    form.submit();
                }
            ");

            $browser->pause(1500);

            $this->assertDatabaseHas('laporan', [
                'id' => $laporan->id,
                'status' => 'selesai',
            ]);

            $this->assertDatabaseHas('ulasan', [
                'laporan_id' => $laporan->id,
                'user_id' => $this->warga->id,
                'rating' => 5,
            ]);
        });
    }

    public function testTC03BelumMembuatLaporanRiwayatKosong()
    {
        $wargaKosong = User::create([
            'name' => 'Warga Kosong PBI 5',
            'email' => 'warga.kosong.' . time() . '@test.com',
            'phone' => '083333333333',
            'password' => Hash::make('password'),
            'role' => 'masyarakat',
            'is_active' => true,
            'wilayah_id' => $this->wilayah->id,
        ]);

        $this->browse(function (Browser $browser) use ($wargaKosong) {
            $this->loginSebagaiWarga($browser, $wargaKosong->email);

            $browser->visit('/warga/laporan')
                ->waitForText('Riwayat Laporan', 10)
                ->assertSee('Belum ada laporan')
                ->assertSee('Buat laporan pertama');
        });
    }

    public function testTC04MelihatDetailLaporan()
    {
        $laporan = $this->buatLaporan($this->warga, [
            'deskripsi' => 'Kerusakan pipa menyebabkan air keluar terus menerus.',
            'alamat' => 'Jalan Detail Nomor 4',
            'status' => 'diterima',
            'catatan_admin' => 'Admin sudah memvalidasi laporan ini.',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/laporan')
                ->waitForText('Jalan Detail Nomor 4', 10);

            $browser->script("
                document.querySelectorAll('button').forEach(button => {
                    if (button.innerText.includes('#{$laporan->id}') || button.innerText.includes('Jalan Detail Nomor 4')) {
                        button.click();
                    }
                });
            ");

            $browser->pause(1000)
                ->assertSee('Detail Alamat Rumah')
                ->assertSee('Jalan Detail Nomor 4')
                ->assertSee('Deskripsi Masalah')
                ->assertSee('Kerusakan pipa menyebabkan air keluar terus menerus.')
                ->assertSee('Catatan Admin')
                ->assertSee('Admin sudah memvalidasi laporan ini.')
                ->assertSee('-6.973000')
                ->assertSee('107.630000');
        });
    }

    public function testTC05LaporanDiurutkanTerbaruDiAtas()
    {
        $lama = $this->buatLaporan($this->warga, [
            'alamat' => 'Jalan Laporan Lama',
            'tanggal_lapor' => now()->subDays(3),
        ]);
        $lama->created_at = now()->subDays(3);
        $lama->updated_at = now()->subDays(3);
        $lama->save();

        $this->buatLaporan($this->warga, [
            'alamat' => 'Jalan Laporan Baru',
            'tanggal_lapor' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiWarga($browser);

            $browser->visit('/warga/laporan')
                ->waitForText('Jalan Laporan Baru', 10)
                ->assertSee('Jalan Laporan Lama');

            $page = $browser->driver->getPageSource();

            $posBaru = strpos($page, 'Jalan Laporan Baru');
            $posLama = strpos($page, 'Jalan Laporan Lama');

            $this->assertNotFalse($posBaru);
            $this->assertNotFalse($posLama);
            $this->assertTrue($posBaru < $posLama);
        });
    }

    public function testTC06DataLaporanUserLainTidakBocor()
    {
        $this->buatLaporan($this->warga, [
            'alamat' => 'Jalan Milik Warga Pertama',
            'deskripsi' => 'Laporan milik warga pertama.',
        ]);

        $this->buatLaporan($this->wargaLain, [
            'alamat' => 'Jalan Rahasia Warga Lain',
            'deskripsi' => 'Laporan ini tidak boleh terlihat oleh warga pertama.',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginSebagaiWarga($browser, 'warga.pbi5@test.com');

            $browser->visit('/warga/laporan')
                ->waitForText('Jalan Milik Warga Pertama', 10)
                ->assertSee('Jalan Milik Warga Pertama')
                ->assertDontSee('Jalan Rahasia Warga Lain')
                ->assertDontSee('Laporan ini tidak boleh terlihat oleh warga pertama');
        });
    }
}