<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\Penugasan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi10PenyelesaianTugasTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $petugas;
    private User $warga;
    private Wilayah $wilayah;
    private KategoriLaporan $kategori;

    private string $validPhotoPath;
    private string $largePhotoPath;
    private string $invalidDocPath;

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
            ['nama_wilayah' => 'Area PBI 10'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI10',
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
            ['email' => 'petugas.pbi10@test.com'],
            [
                'name' => 'Petugas PBI 10',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['email' => 'warga.pbi10@test.com'],
            [
                'name' => 'Warga PBI 10',
                'phone' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'masyarakat',
                'is_active' => true,
                'wilayah_id' => $this->wilayah->id,
            ]
        );
    }

    private function buatFileTesting(): void
    {
        $tempDir = storage_path('app/testing');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $this->validPhotoPath = $tempDir . '/bukti_pbi10.jpg';

        $jpg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8PDw8QDw8PDw8PDw8PDw8PFREWFhURFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGi0lHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAgMBIgACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAABf/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/Aaf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/Aaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Aqf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IV//2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z'
        );

        file_put_contents($this->validPhotoPath, $jpg);

        $this->largePhotoPath = $tempDir . '/foto_besar_pbi10.jpg';
        file_put_contents($this->largePhotoPath, str_repeat('A', 7 * 1024 * 1024));

        $this->invalidDocPath = $tempDir . '/dokumen_pbi10.pdf';
        File::put($this->invalidDocPath, 'Ini file PDF palsu untuk test format invalid.');
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
            ->type('email', 'petugas.pbi10@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'petugas');

        $browser->press('Masuk')
            ->waitForLocation('/petugas/dashboard', 10)
            ->assertPathIs('/petugas/dashboard');
    }

    private function buatLaporan(): Laporan
    {
        $laporan = Laporan::create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $this->wilayah->id,
            'kategori_laporan_id' => $this->kategori->id,
            'judul' => 'Laporan Penyelesaian PBI 10',
            'deskripsi' => 'Pipa bocor sudah perlu ditangani dan diselesaikan.',
            'alamat' => 'Jalan Penyelesaian PBI 10',
            'status' => 'diterima',
            'tanggal_lapor' => now(),
            'catatan_admin' => 'Silakan lakukan perbaikan lapangan.',
        ]);

        MapLokasi::create([
            'laporan_id' => $laporan->id,
            'latitude' => -6.973000,
            'longitude' => 107.630000,
        ]);

        return $laporan;
    }

    private function buatPenugasan(): Penugasan
    {
        $laporan = $this->buatLaporan();

        return Penugasan::create([
            'laporan_id' => $laporan->id,
            'user_id' => $this->petugas->id,
            'tanggal_penugasan' => now()->toDateString(),
            'status_tugas' => 'Sedang Dikerjakan',
            'catatan_admin' => 'Segera selesaikan tugas ini.',
        ]);
    }

    private function submitBukti(Browser $browser, Penugasan $penugasan, ?string $filePath): void
    {
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
            ketInput.value = 'Perbaikan selesai dan aliran air kembali normal.';
            form.appendChild(ketInput);

            document.body.appendChild(form);
        ");

        if ($filePath) {
            $browser->attach('#dusk_foto_bukti', $filePath);
        }

        $browser->script("
            document.querySelector('form[action=\"/petugas/tugas/{$penugasan->id}/bukti\"]').submit();
        ");
    }

    public function testTC1001SelesaikanTugas()
    {
        $penugasan = $this->buatPenugasan();
        $laporanId = $penugasan->laporan_id;

        $this->browse(function (Browser $browser) use ($penugasan, $laporanId) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Detail Tugas', 10);

            $this->submitBukti($browser, $penugasan, $this->validPhotoPath);

            $browser->pause(1500)
                ->assertPathIs('/petugas/tugas/' . $penugasan->id)
                ->assertSee('Menunggu Konfirmasi');

            $this->assertDatabaseHas('penugasan', [
                'id' => $penugasan->id,
                'status_tugas' => 'Menunggu Konfirmasi',
            ]);

            $this->assertDatabaseHas('laporan', [
                'id' => $laporanId,
                'status' => 'menunggu_konfirmasi',
            ]);

            $this->assertDatabaseHas('penyelesaian_tugas', [
                'penugasan_id' => $penugasan->id,
                'keterangan' => 'Perbaikan selesai dan aliran air kembali normal.',
            ]);
        });
    }

    public function testTC1002UkuranFotoLebihDari5MB()
    {
        $penugasan = $this->buatPenugasan();

        $this->browse(function (Browser $browser) use ($penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Detail Tugas', 10);

            $this->submitBukti($browser, $penugasan, $this->largePhotoPath);

            $browser->pause(1500)
                ->assertPathIs('/petugas/tugas/' . $penugasan->id);

            $this->assertDatabaseMissing('penyelesaian_tugas', [
                'penugasan_id' => $penugasan->id,
            ]);

            $this->assertDatabaseHas('penugasan', [
                'id' => $penugasan->id,
                'status_tugas' => 'Sedang Dikerjakan',
            ]);
        });
    }

    public function testTC1003UploadTanpaFoto()
    {
        $penugasan = $this->buatPenugasan();

        $this->browse(function (Browser $browser) use ($penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Detail Tugas', 10);

            $this->submitBukti($browser, $penugasan, null);

            $browser->pause(1500)
                ->assertPathIs('/petugas/tugas/' . $penugasan->id)
                ->assertSee('Foto bukti wajib diupload');

            $this->assertDatabaseMissing('penyelesaian_tugas', [
                'penugasan_id' => $penugasan->id,
            ]);
        });
    }

    public function testTC1004ValidasiFormatFileTerlarang()
    {
        $penugasan = $this->buatPenugasan();

        $this->browse(function (Browser $browser) use ($penugasan) {
            $this->loginSebagaiPetugas($browser);

            $browser->visit('/petugas/tugas/' . $penugasan->id)
                ->waitForText('Detail Tugas', 10);

            $this->submitBukti($browser, $penugasan, $this->invalidDocPath);

            $browser->pause(1500)
                ->assertPathIs('/petugas/tugas/' . $penugasan->id);

            $this->assertDatabaseMissing('penyelesaian_tugas', [
                'penugasan_id' => $penugasan->id,
            ]);

            $this->assertDatabaseHas('penugasan', [
                'id' => $penugasan->id,
                'status_tugas' => 'Sedang Dikerjakan',
            ]);
        });
    }
}