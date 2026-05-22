<?php

namespace Tests\Feature\Admin;

use App\Models\Laporan;
use App\Models\Penugasan;
use App\Models\Ulasan;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\KategoriLaporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KinerjaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->warga = User::factory()->create(['role' => 'masyarakat']);
    }

    /** @test */
    public function tc_001_tabel_kinerja_petugas_tampil_dengan_data_lengkap()
    {
        $petugas1 = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas A']);
        $petugas2 = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas B']);
        $petugas3 = User::factory()->create(['role' => 'petugas', 'name' => 'Petugas C']);

        $wilayah = Wilayah::create(['nama_wilayah' => 'A', 'kode_wilayah' => 'A1']);
        $kategori = KategoriLaporan::create(['nama_kategori' => 'A', 'tarif' => 10]);

        $laporan = Laporan::create(['user_id' => $this->warga->id, 'wilayah_id' => $wilayah->id, 'kategori_laporan_id' => $kategori->id, 'judul' => 'T', 'deskripsi' => 'D', 'alamat' => 'A', 'status' => 'selesai']);

        // Petugas 1 punya 2 tugas selesai
        Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $petugas1->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);
        Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $petugas1->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);

        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin)->get(route('admin.kinerja.index'));

        $response->assertStatus(200);
        $response->assertSee('Petugas A');
        $response->assertSee('Petugas B');
        $response->assertSee('Petugas C');
        
        $petugasList = $response->viewData('petugasList');
        $this->assertCount(3, $petugasList);
        $p1 = $petugasList->firstWhere('name', 'Petugas A');
        $this->assertEquals(2, $p1->tugas_selesai_count);
    }

    /** @test */
    public function tc_002_rata_rata_rating_dihitung_dengan_benar()
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $wilayah = Wilayah::create(['nama_wilayah' => 'A', 'kode_wilayah' => 'A1']);
        $kategori = KategoriLaporan::create(['nama_kategori' => 'A', 'tarif' => 10]);
        $laporan = Laporan::create(['user_id' => $this->warga->id, 'wilayah_id' => $wilayah->id, 'kategori_laporan_id' => $kategori->id, 'judul' => 'T', 'deskripsi' => 'D', 'alamat' => 'A', 'status' => 'selesai']);

        $ratings = [5, 4, 4, 3, 4];
        
        foreach ($ratings as $r) {
            $penugasan = Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $petugas->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);
            Ulasan::create([
                'penugasan_id' => $penugasan->id, 
                'user_id' => $this->warga->id, 
                'laporan_id' => $laporan->id, 
                'rating' => $r, 
                'komentar' => 'test'
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.kinerja.index'));
        $petugasList = $response->viewData('petugasList');
        $p = $petugasList->first();

        $this->assertEquals(4.0, $p->rata_rata_rating);
    }

    /** @test */
    public function tc_003_empty_state_muncul_saat_tidak_ada_data()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kinerja.index'));
        $response->assertStatus(200);
        $response->assertSee('Belum ada data kinerja petugas');
    }

    /** @test */
    public function tc_004_sorting_kolom_berfungsi()
    {
        $p1 = User::factory()->create(['role' => 'petugas', 'name' => 'A Petugas']);
        $p2 = User::factory()->create(['role' => 'petugas', 'name' => 'B Petugas']);
        
        $wilayah = Wilayah::create(['nama_wilayah' => 'A', 'kode_wilayah' => 'A1']);
        $kategori = KategoriLaporan::create(['nama_kategori' => 'A', 'tarif' => 10]);
        $laporan = Laporan::create(['user_id' => $this->warga->id, 'wilayah_id' => $wilayah->id, 'kategori_laporan_id' => $kategori->id, 'judul' => 'T', 'deskripsi' => 'D', 'alamat' => 'A', 'status' => 'selesai']);

        // p1 -> 1 tugas
        Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $p1->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);
        
        // p2 -> 5 tugas
        for($i=0; $i<5; $i++) {
            Penugasan::create(['laporan_id' => $laporan->id, 'user_id' => $p2->id, 'status_tugas' => 'selesai', 'tanggal_penugasan' => now()]);
        }

        // Descending sort by tugas
        $response = $this->actingAs($this->admin)->get(route('admin.kinerja.index', ['sort_by' => 'tugas_selesai_count', 'sort_dir' => 'desc']));
        $list = $response->viewData('petugasList');
        
        $this->assertEquals('B Petugas', $list->first()->name);
        $this->assertEquals('A Petugas', $list->last()->name);
    }
}
