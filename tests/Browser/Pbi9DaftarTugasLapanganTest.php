<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\Penugasan;
use App\Models\PenyelesaianTugas;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi9DaftarTugasLapanganTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $petugas;
    private User $warga;
    private User $admin;
    private Wilayah $wilayah;
    private KategoriLaporan $kategori;

    private string $validPhotoPath;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        $this->seedDataAwal();
        $this->buatFileTesting();
    }

    private function seedDataAwal(): void
    {
        $this->wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Area PBI 9'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI9',
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

        $this->petugas = User::firstOrCreate(
            ['email' => 'petugas.pbi9@test.com'],
            [
                'name' => 'Petugas PBI 9',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi9@test.com'],
            [
                'name' => 'Warga PBI 9',
                'phone' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.pbi9@test.com'],
            [
                'name' => 'Admin PBI 9',
                'phone' => '083333333333',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }

    private function buatFileTesting(): void
    {
        $tempDir = storage_path('app/testing');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $this->validPhotoPath = $tempDir . '/bukti_penyelesaian.jpg';

        $jpg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8PDw8QDw8PDw8PDw8PDw8PFREWFhURFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGi0lHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAgMBIgACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAABf/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/Aaf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/Aaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Aqf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IV//2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z'
        );

        file_put_contents($this->validPhotoPath, $jpg);
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

    private function loginSebagaiPetugas(Browser $browser): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'petugas.pbi9@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'petugas');

        $browser->press('Masuk')
            ->waitForLocation('/petugas/dashboard', 10)
            ->assertPathIs('/petugas/dashboard');
    }

    private function loginSebagai(Browser $browser, string $email, string $role, string $expectedPath): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', $email)
            ->type('password', 'password');

        $this->pilihRole($browser, $role);

        $browser->press('Masuk')
            ->waitForLocation($expectedPath, 10)
            ->assertPathIs($expectedPath);
    }

    private function buatLaporan(array $override = []): Laporan
    {
        $laporan = Laporan::create(array_merge([
            'user_id' => $this->warga->id,
            'wilayah_id' => $this->wilayah->id,
            'kategori_laporan_id' => $this->kategori->id,
            'judul' => 'Laporan Tugas PBI 9',
            'deskripsi' => 'Pipa bocor cukup deras dan perlu segera ditangani petugas.',
            'alamat' => 'Jalan Tugas PBI 9 Nomor 1',
            'status' => 'diterima',
            'tanggal_lapor' => now(),
            'catatan_admin' => 'Segera lakukan pemeriksaan ke lokasi.',
        ], $override));

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => -6.973000,
            'longitude' => 107.630000,
        ]);

        return $laporan;
    }

    private function buatPenugasan(string $status = 'Ditugaskan', ?Laporan $laporan = null, array $override = []): Penugasan
    {
        $laporan = $laporan ?: $this->buatLaporan();

        return Penugasan::create(array_merge([
            'laporan_id' => $laporan->id,
            'user_id' => $this->petugas->id,
            'tanggal_penugasan' => now()->toDateString(),
            'status_tugas' => $status,
            'catatan_admin' => 'Catatan penugasan dari admin.',
        ], $override));
    }

    public function testTC01PetugasBerhasilMembukaHalamanDaftarTugas()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas')
                ->waitForText('Daftar Tugas', 10)
                ->assertSee('Daftar Tugas')
                ->assertSee('Tugas Aktif')
                ->assertSee('Telah Selesai');
        });
    }

    public function testTC02SistemMenampilkanDaftarTugasAktif()
    {
        $laporan = $this->buatLaporan([
            'alamat' => 'Jalan Aktif PBI 9',
            'deskripsi' => 'Kerusakan pipa aktif untuk petugas.',
        ]);

        $penugasan = $this->buatPenugasan('Ditugaskan', $laporan);

        $this->browse(function (Browser $browser) use ($laporan, $penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas')
                ->waitForText('Jalan Aktif PBI 9', 10)
                ->assertSee('#' . $laporan->id)
                ->assertSee('Pipa Bocor')
                ->assertSee('Ditugaskan')
                ->assertSee('Jalan Aktif PBI 9')
                ->assertSee((string) now()->format('Y-m-d'));
        });
    }

    public function testTC03PetugasBerhasilMelihatDetailTugas()
    {
        $laporan = $this->buatLaporan([
            'alamat' => 'Jalan Detail Tugas PBI 9',
            'deskripsi' => 'Deskripsi detail kerusakan untuk tugas petugas.',
            'catatan_admin' => 'Catatan admin untuk detail tugas.',
        ]);

        $penugasan = $this->buatPenugasan('Ditugaskan', $laporan, [
            'catatan_admin' => 'Catatan penugasan detail dari admin.',
        ]);

        $this->browse(function (Browser $browser) use ($penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Detail Tugas', 10)
                ->assertSee('Warga PBI 9')
                ->assertSee('Jalan Detail Tugas PBI 9')
                ->assertSee('Deskripsi detail kerusakan untuk tugas petugas.')
                ->assertSee('Catatan admin untuk detail tugas.')
                ->assertSee('Catatan penugasan detail dari admin.');
        });
    }

    public function testTC04SistemMenampilkanLokasiLaporanDiPeta()
    {
        $laporan = $this->buatLaporan([
            'alamat' => 'Jalan Peta PBI 9',
        ]);

        $penugasan = $this->buatPenugasan('Ditugaskan', $laporan);

        $this->browse(function (Browser $browser) use ($penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Alamat Rumah Lengkap', 10)
                ->assertSee('-6.973')
                ->assertSee('107.63')
                ->assertSee('Buka di Google Maps');
        });
    }

    public function testTC05PetugasBerhasilMengubahStatusPekerjaan()
    {
        $penugasan = $this->buatPenugasan('Ditugaskan');

        $this->browse(function (Browser $browser) use ($penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Detail Tugas', 10);

            $browser->script("
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/petugas/tugas/{$penugasan->id}/status';

                const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                    || document.querySelector('input[name=\"_token\"]')?.value;

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrf;
                form.appendChild(tokenInput);

                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status_tugas';
                statusInput.value = 'Menuju Lokasi';
                form.appendChild(statusInput);

                document.body.appendChild(form);
                form.submit();
            ");

            $browser->pause(1500)
                ->assertPathIs('/petugas/tugas/' . $penugasan->id);

            $this->assertDatabaseHas('penugasan', [
                'id' => $penugasan->id,
                'status_tugas' => 'Menuju Lokasi',
            ]);
        });
    }

    public function testTC06PetugasBerhasilMengunggahBuktiPenyelesaian()
    {
        $laporan = $this->buatLaporan([
            'alamat' => 'Jalan Bukti PBI 9',
        ]);

        $penugasan = $this->buatPenugasan('Sedang Dikerjakan', $laporan);

        $this->browse(function (Browser $browser) use ($penugasan, $laporan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Upload Bukti Penyelesaian', 10);

            $browser->script("
                const form = document.createElement('form');
                form.method = 'POST';
                form.enctype = 'multipart/form-data';
                form.action = '/petugas/tugas/{$penugasan->id}/bukti';

                const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                    || document.querySelector('input[name=\"_token\"]')?.value;

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrf;
                form.appendChild(tokenInput);

                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'foto_bukti';
                fileInput.id = 'dusk_foto_bukti';
                form.appendChild(fileInput);

                const ketInput = document.createElement('input');
                ketInput.type = 'hidden';
                ketInput.name = 'keterangan';
                ketInput.value = 'Pipa sudah diperbaiki dan aliran air normal.';
                form.appendChild(ketInput);

                document.body.appendChild(form);
            ");

            $browser->attach('#dusk_foto_bukti', $this->validPhotoPath);

            $browser->script("
                document.querySelector('form[action=\"/petugas/tugas/{$penugasan->id}/bukti\"]').submit();
            ");

            $browser->pause(1500)
                ->assertPathIs('/petugas/tugas/' . $penugasan->id)
                ->assertSee('Bukti penyelesaian berhasil diupload')
                ->assertSee('Menunggu Konfirmasi Warga');

            $this->assertDatabaseHas('penugasan', [
                'id' => $penugasan->id,
                'status_tugas' => 'Menunggu Konfirmasi',
            ]);

            $this->assertDatabaseHas('laporan', [
                'id' => $laporan->id,
                'status' => 'menunggu_konfirmasi',
            ]);

            $this->assertDatabaseHas('penyelesaian_tugas', [
                'penugasan_id' => $penugasan->id,
                'keterangan' => 'Pipa sudah diperbaiki dan aliran air normal.',
            ]);
        });
    }

    public function testTC07PetugasGagalMengirimBuktiJikaFotoBelumDiunggah()
    {
        $penugasan = $this->buatPenugasan('Sedang Dikerjakan');

        $this->browse(function (Browser $browser) use ($penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Upload Bukti Penyelesaian', 10);

            $browser->script("
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/petugas/tugas/{$penugasan->id}/bukti';

                const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                    || document.querySelector('input[name=\"_token\"]')?.value;

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrf;
                form.appendChild(tokenInput);

                document.body.appendChild(form);
                form.submit();
            ");

            $browser->pause(1500)
                ->assertPathIs('/petugas/tugas/' . $penugasan->id)
                ->assertSee('Foto bukti wajib diupload');

            $this->assertDatabaseMissing('penyelesaian_tugas', [
                'penugasan_id' => $penugasan->id,
            ]);

            $this->assertDatabaseHas('penugasan', [
                'id' => $penugasan->id,
                'status_tugas' => 'Sedang Dikerjakan',
            ]);
        });
    }

    public function testTC08PetugasBerhasilMelihatRiwayatSelesai()
    {
        $laporan = $this->buatLaporan([
            'alamat' => 'Jalan Riwayat Selesai PBI 9',
        ]);

        $penugasan = $this->buatPenugasan('Selesai', $laporan);

        PenyelesaianTugas::create([
            'penugasan_id' => $penugasan->id,
            'foto_bukti' => 'bukti-penyelesaian/dummy.jpg',
            'tanggal_selesai' => now()->toDateString(),
            'keterangan' => 'Perbaikan selesai dengan baik.',
        ]);

        $this->browse(function (Browser $browser) use ($laporan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas')
                ->waitForText('Riwayat Selesai', 10)
                ->assertSee('#' . $laporan->id)
                ->assertSee('Pipa Bocor')
                ->assertSee('Jalan Riwayat Selesai PBI 9')
                ->assertSee('Catatan: Perbaikan selesai dengan baik.')
                ->assertSee(now()->format('Y-m-d'));
        });
    }

    public function testTC09PenggunaSelainPetugasTidakDapatMengaksesDaftarTugasPetugas()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagai(
                $browser,
                'warga.pbi9@test.com',
                'masyarakat',
                '/warga/laporan'
            );

            $browser->visit('/petugas/tugas')
                ->pause(1000);

            $currentPath = parse_url($browser->driver->getCurrentURL(), PHP_URL_PATH);
            $pageSource = $browser->driver->getPageSource();

            $this->assertTrue(
                $currentPath === '/warga/laporan'
                || str_contains($pageSource, '403')
                || str_contains($pageSource, 'Forbidden')
                || str_contains($pageSource, 'Akses ditolak')
            );
        });
    }
}