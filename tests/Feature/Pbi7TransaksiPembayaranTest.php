<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Laporan;
use App\Models\KategoriLaporan;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Pbi7TransaksiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $laporan;
    protected $pembayaran;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'masyarakat', 'is_active' => true]);
        
        $kategori = KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'tarif' => 50000
        ]);

        $this->laporan = Laporan::create([
            'user_id' => $this->user->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Pipa depan rumah bocor',
            'deskripsi' => 'Air menggenang di jalan',
            'alamat' => 'Jalan Kenangan No. 12',
            'latitude' => '-6.914744',
            'longitude' => '107.609810',
            'status' => 'pending',
            'tanggal_lapor' => now()
        ]);

        $this->pembayaran = Pembayaran::create([
            'laporan_id' => $this->laporan->id,
            'user_id' => $this->user->id,
            'harga' => 50000,
            'status_pembayaran' => 'Menunggu'
        ]);
    }

    public function test_warga_dapat_melihat_dasbor_tagihan_aktif()
    {
        $response = $this->actingAs($this->user)->get(route('warga.pembayaran.index'));

        $response->assertStatus(200);
        $response->assertSee('Total Tagihan');
        $response->assertSee($this->laporan->judul);
        $response->assertSee('50.000');
    }

    public function test_upload_bukti_pembayaran_berhasil()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('bukti.jpg', 1024, 'image/jpeg');

        $response = $this->actingAs($this->user)->post(route('warga.pembayaran.upload', $this->pembayaran->id), [
            'metode_pembayaran' => 'Transfer Bank',
            'bukti_transaksi' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('pembayaran', [
            'id' => $this->pembayaran->id,
            'status_pembayaran' => 'Terverifikasi',
            'metode_pembayaran' => 'Transfer Bank'
        ]);
    }

    public function test_upload_bukti_ditolak_karena_format_tidak_valid()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)->post(route('warga.pembayaran.upload', $this->pembayaran->id), [
            'metode_pembayaran' => 'Transfer Bank',
            'bukti_transaksi' => $file
        ]);

        $response->assertSessionHasErrors('bukti_transaksi');
        $this->assertDatabaseHas('pembayaran', [
            'id' => $this->pembayaran->id,
            'status_pembayaran' => 'Menunggu'
        ]);
    }

    public function test_upload_bukti_ditolak_karena_ukuran_terlalu_besar()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('bukti_besar.jpg', 6000, 'image/jpeg'); // 6MB (Batas 5MB)

        $response = $this->actingAs($this->user)->post(route('warga.pembayaran.upload', $this->pembayaran->id), [
            'metode_pembayaran' => 'Transfer Bank',
            'bukti_transaksi' => $file
        ]);

        $response->assertSessionHasErrors('bukti_transaksi');
    }

    public function test_tagihan_otomatis_kadaluarsa_jika_lewat_24_jam()
    {
        // Set usia tagihan > 24 jam menggunakan DB untuk melewati timestamp autoupdate
        \Illuminate\Support\Facades\DB::table('pembayaran')
            ->where('id', $this->pembayaran->id)
            ->update(['created_at' => now()->subHours(25)]);

        $response = $this->actingAs($this->user)->get(route('warga.pembayaran.index'));

        $this->assertDatabaseHas('pembayaran', [
            'id' => $this->pembayaran->id,
            'status_pembayaran' => 'Kadaluarsa'
        ]);
        
        $response->assertSee('Hangus');
    }

    public function test_midtrans_webhook_memperbarui_status_pembayaran()
    {
        $response = $this->postJson('/api/midtrans/webhook', [
            'order_id' => 'pembayaran-' . $this->pembayaran->id . '-' . time(),
            'transaction_status' => 'settlement'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pembayaran', [
            'id' => $this->pembayaran->id,
            'status_pembayaran' => 'Lunas'
        ]);
    }

    public function test_midtrans_webhook_membatalkan_pembayaran_expire()
    {
        $response = $this->postJson('/api/midtrans/webhook', [
            'order_id' => 'pembayaran-' . $this->pembayaran->id . '-' . time(),
            'transaction_status' => 'expire'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pembayaran', [
            'id' => $this->pembayaran->id,
            'status_pembayaran' => 'Ditolak'
        ]);
    }
}
