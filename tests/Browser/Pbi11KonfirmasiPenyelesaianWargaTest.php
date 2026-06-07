<?php

namespace Tests\Browser;

use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\MapLokasi;
use App\Models\Penugasan;
use App\Models\PenyelesaianTugas;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi11KonfirmasiPenyelesaianWargaTest extends DuskTestCase {
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup minimal data for testing
        
        $this->warga = User::firstOrCreate(
            ['email' => 'wargatest@gmail.com'],
            [
                'name' => 'Warga Test',
                'password' => bcrypt('password'),
                'role' => 'masyarakat',
            ]
        );
        
        $this->petugas = User::firstOrCreate(
            ['email' => 'petugastest@gmail.com'],
            [
                'name' => 'Petugas Test',
                'password' => bcrypt('password'),
                'role' => 'petugas',
            ]
        );

        $wilayah = Wilayah::firstOrCreate(['nama_wilayah' => 'Cibiru']);
        $kategori = KategoriLaporan::firstOrCreate([
            'nama_kategori' => 'Jalan Rusak',
        ], [
            'tarif' => 0
        ]);

        // Create report with status 'menunggu_konfirmasi'
        $this->laporan = Laporan::create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Jalan Rusak Parah',
            'deskripsi' => 'Deskripsi yang sangat panjang',
            'alamat' => 'Alamat test',
            'status' => 'menunggu_konfirmasi',
            'tanggal_lapor' => now(),
        ]);

        MapLokasi::create([
            'laporan_id' => $this->laporan->id,
            'latitude' => -6.9,
            'longitude' => 107.6,
        ]);

        // Create Penugasan & Penyelesaian to simulate waiting for confirmation
        $penugasan = Penugasan::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->petugas->id,
            'status_tugas' => 'Menunggu Konfirmasi',
            'tanggal_penugasan' => now(),
        ]);

        PenyelesaianTugas::create([
            'penugasan_id' => $penugasan->id,
            'foto_bukti' => 'bukti_selesai.jpg',
            'tanggal_selesai' => now()->toDateString(),
            'keterangan' => 'Sudah diperbaiki.',
        ]);
    }

    public function test_TC11_01_KonfirmasiSelesai()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->warga)
                    ->visitRoute('warga.laporan.show', $this->laporan->id)
                    ->assertSee('Konfirmasi Penyelesaian')
                    // Mock click rating 5 (sets hidden input 'rating' to 5 using Javascript, as x-model is used)
                    ->script("document.querySelector('input[name=\"rating\"]').value = 5; document.querySelector('input[name=\"rating\"]').dispatchEvent(new Event('input'));");
                    
            $browser->press('Selesai')
                    ->acceptDialog()
                    ->waitForText('Terima kasih! Konfirmasi dan ulasan Anda telah tersimpan.')
                    ->assertRouteIs('warga.laporan.show', $this->laporan->id)
                    ->assertSee('Selesai');
        });
    }

    public function test_TC11_02_TolakHasilRevisi()
    {
        $this->browse(function (Browser $browser) {
            // Kita recreate laporan untuk test ini agar status reset
            $this->laporan->update(['status' => 'menunggu_konfirmasi']);
            $this->laporan->penugasan->update(['status_tugas' => 'Menunggu Konfirmasi']);
            
            $browser->loginAs($this->warga)
                    ->visitRoute('warga.laporan.show', $this->laporan->id)
                    ->assertSee('Konfirmasi Penyelesaian')
                    ->type('komentar', 'Masih ada lubang yang belum ditutup.')
                    ->press('Revisi')
                    ->acceptDialog()
                    ->waitForText('Permintaan revisi telah dikirim ke petugas.')
                    ->assertRouteIs('warga.laporan.show', $this->laporan->id)
                    ->assertSee('Dikerjakan');
        });
    }

    public function test_TC11_03_KonfirmasiSelesaiTanpaRating()
    {
        $this->browse(function (Browser $browser) {
            $this->laporan->update(['status' => 'menunggu_konfirmasi']);

            $browser->loginAs($this->warga)
                    ->visitRoute('warga.laporan.show', $this->laporan->id)
                    ->assertSee('Konfirmasi Penyelesaian')
                    ->press('Selesai')
                    ->acceptDialog()
                    ->pause(1500);

            $this->laporan->refresh();

            $this->assertEquals('menunggu_konfirmasi', $this->laporan->status);

            $this->assertDatabaseMissing('ulasan', [
                'laporan_id' => $this->laporan->id,
                'user_id' => $this->warga->id,
            ]);
        });
    }

    public function test_TC11_04_KonfirmasiRevisiTanpaKomentar()
    {
        $this->browse(function (Browser $browser) {
            $this->laporan->update(['status' => 'menunggu_konfirmasi']);

            $browser->loginAs($this->warga)
                    ->visitRoute('warga.laporan.show', $this->laporan->id)
                    ->assertSee('Konfirmasi Penyelesaian')
                    ->clear('komentar')
                    ->press('Revisi')
                    ->acceptDialog()
                    ->pause(1500);

            $this->laporan->refresh();

            $this->assertEquals('menunggu_konfirmasi', $this->laporan->status);

            $this->assertDatabaseMissing('ulasan', [
                'laporan_id' => $this->laporan->id,
                'user_id' => $this->warga->id,
            ]);
        });
    }
}