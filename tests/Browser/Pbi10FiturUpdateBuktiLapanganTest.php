<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Penugasan;
use App\Models\User;
use App\Models\Wilayah;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi10FiturUpdateBuktiLapanganTest extends DuskTestCase
{
    protected User $petugas;
    protected User $warga;
    protected Laporan $laporan;
    protected Penugasan $penugasan;
    protected string $smallImage;
    protected string $largeImage;
    protected string $pdfFile;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');

        $wilayah = Wilayah::create([
            'nama_wilayah' => 'Cianjur',
            'tipe' => 'kecamatan',
            'kode_wilayah' => 'CJR-01',
        ]);

        $kategori = KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'tarif' => 50000,
        ]);

        $this->petugas = User::create([
            'name' => 'Petugas Lapangan',
            'email' => 'petugas.lapangan@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'wilayah_id' => $wilayah->id,
            'is_active' => true,
        ]);

        $this->warga = User::create([
            'name' => 'Warga Pelapor',
            'email' => 'warga.pelapor@tirtabantu.com',
            'password' => bcrypt('password'),
            'role' => 'masyarakat',
            'is_active' => true,
        ]);

        $this->laporan = Laporan::create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Kebocoran Pipa',
            'deskripsi' => 'Air meluap di jalan raya utama.',
            'alamat' => 'Jl. Merdeka No 12',
            'status' => 'dikerjakan',
            'tanggal_lapor' => now(),
        ]);

        $this->penugasan = Penugasan::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugas->id,
            'tanggal_penugasan' => now(),
            'status_tugas' => 'Sedang Dikerjakan'
        ]);

        // Generate files
        $tempDir = sys_get_temp_dir();
        $this->smallImage = $tempDir . DIRECTORY_SEPARATOR . 'dusk_small.jpg';
        $this->largeImage = $tempDir . DIRECTORY_SEPARATOR . 'dusk_large.jpg';
        $this->pdfFile = $tempDir . DIRECTORY_SEPARATOR . 'dusk_document.pdf';

        // 1. Small valid image (100KB)
        if (!file_exists($this->smallImage)) {
            $im = imagecreatetruecolor(100, 100);
            $color = imagecolorallocate($im, 0, 0, 255);
            imagefill($im, 0, 0, $color);
            imagejpeg($im, $this->smallImage);
            imagedestroy($im);
        }

        // 2. Large image (>5MB)
        if (!file_exists($this->largeImage)) {
            copy($this->smallImage, $this->largeImage);
            $fp = fopen($this->largeImage, 'ab');
            if ($fp) {
                fwrite($fp, str_repeat("\0", 6 * 1024 * 1024)); // 6MB
                fclose($fp);
            }
        }

        // 3. Forbidden format file
        if (!file_exists($this->pdfFile)) {
            file_put_contents($this->pdfFile, '%PDF-1.4 ... dummy content');
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->smallImage)) {
            @unlink($this->smallImage);
        }
        if (file_exists($this->largeImage)) {
            @unlink($this->largeImage);
        }
        if (file_exists($this->pdfFile)) {
            @unlink($this->pdfFile);
        }
        parent::tearDown();
    }

    /**
     * TC-10-01: Selesaikan Tugas
     * Pilih tugas aktif -> Klik 'Selesaikan' -> Upload foto -> Kirim.
     * Status berubah jadi 'Menunggu Konfirmasi', foto tersimpan.
     */
    public function test_TC1001_selesaikan_tugas_dan_upload_foto_berhasil()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.show', $this->penugasan->id)
                ->pause(1000)
                ->script("document.getElementById('foto_bukti').classList.remove('hidden');");

            $browser->attach('foto_bukti', $this->smallImage)
                ->type('keterangan', 'Pekerjaan selesai, pipa telah diperbaiki dan diuji.')
                ->click('form[action*="bukti"] button[type="submit"]')
                ->pause(2000);

            // Assert redirected back with success
            $browser->assertPathIs('/petugas/tugas/' . $this->penugasan->id)
                ->assertSee('Bukti penyelesaian berhasil diupload');

            // Cek DB
            $this->assertDatabaseHas('penugasan', [
                'id' => $this->penugasan->id,
                'status_tugas' => 'Menunggu Konfirmasi'
            ]);

            $this->assertDatabaseHas('laporan', [
                'id' => $this->laporan->id,
                'status' => 'menunggu_konfirmasi'
            ]);

            $this->assertDatabaseHas('penyelesaian_tugas', [
                'penugasan_id' => $this->penugasan->id,
                'keterangan' => 'Pekerjaan selesai, pipa telah diperbaiki dan diuji.'
            ]);
        });
    }

    /**
     * TC-10-02: Ukuran Foto > 5MB
     * Upload foto ukuran 7MB -> Klik 'Kirim'.
     * Muncul pesan error NFR-02, data tidak disimpan.
     */
    public function test_TC1002_gagal_upload_foto_ukuran_lebih_dari_5mb()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.show', $this->penugasan->id)
                ->pause(1000)
                ->script("document.getElementById('foto_bukti').classList.remove('hidden');");

            $browser->attach('foto_bukti', $this->largeImage)
                ->pause(1000)
                // SweetAlert2 client side check
                ->assertSee('File Terlalu Besar')
                ->assertSee('Ukuran foto maksimal 5MB.')
                ->click('.swal2-confirm')
                ->pause(500);

            // Mencoba kirim form (harusnya kosong/error karena input di-clear oleh Alpine)
            $browser->click('form[action*="bukti"] button[type="submit"]')
                ->pause(2000)
                ->assertSee('Foto bukti wajib diupload.');

            // Pastikan tidak ada data baru di tabel penyelesaian
            $this->assertDatabaseMissing('penyelesaian_tugas', [
                'penugasan_id' => $this->penugasan->id
            ]);
        });
    }

    /**
     * TC-10-03: Upload Tanpa Foto
     * Petugas menekan tombol 'Selesaikan Tugas' -> Mengosongkan isian foto -> Klik 'Kirim Bukti'.
     * Sistem menolak pengiriman dan menampilkan pesan "Foto bukti wajib diupload".
     */
    public function test_TC1003_gagal_upload_tanpa_foto()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.show', $this->penugasan->id)
                ->pause(1000)
                ->type('keterangan', 'Mencoba upload tanpa melampirkan foto.')
                ->click('form[action*="bukti"] button[type="submit"]')
                ->pause(2000)
                ->assertSee('Foto bukti wajib diupload.');

            $this->assertDatabaseMissing('penyelesaian_tugas', [
                'penugasan_id' => $this->penugasan->id
            ]);
        });
    }

    /**
     * TC-10-04: Validasi Format File Terlarang
     * Petugas mengunggah file non-gambar. Klik 'Kirim Bukti'.
     * Sistem menampilkan pesan error "Format file tidak didukung. Gunakan .jpg, .jpeg, atau .png".
     */
    public function test_TC1004_gagal_upload_format_file_terlarang()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visitRoute('petugas.tugas.show', $this->penugasan->id)
                ->pause(1000)
                ->script("document.getElementById('foto_bukti').classList.remove('hidden');");

            $browser->attach('foto_bukti', $this->pdfFile)
                ->pause(1000)
                // SweetAlert2 client side check
                ->assertSee('Format Tidak Valid')
                ->assertSee('Gunakan JPG, JPEG, atau PNG.')
                ->click('.swal2-confirm')
                ->pause(500);

            $browser->click('form[action*="bukti"] button[type="submit"]')
                ->pause(2000)
                ->assertSee('Foto bukti wajib diupload.');

            $this->assertDatabaseMissing('penyelesaian_tugas', [
                'penugasan_id' => $this->penugasan->id
            ]);
        });
    }
}
