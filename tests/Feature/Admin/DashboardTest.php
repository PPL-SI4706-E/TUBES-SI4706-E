<?php

namespace Tests\Feature\Admin;

use App\Models\Laporan;
use App\Models\User;
use App\Models\KategoriLaporan;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->warga = User::factory()->create([
            'role' => 'masyarakat',
        ]);
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function non_admin_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->warga)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_dashboard_and_see_empty_state_when_no_reports()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        
        $response->assertStatus(200);
        $response->assertSee('Data statistik tidak tersedia');
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['rasio_penyelesaian']);
    }

    /** @test */
    public function admin_can_see_statistics_when_reports_exist()
    {
        $wilayah = Wilayah::create([
            'nama_wilayah' => 'Sukamaju',
            'kode_wilayah' => 'SKM-001',
        ]);

        $kategori = KategoriLaporan::create([
            'nama_kategori' => 'Pipa Bocor',
            'tarif' => 50000,
        ]);

        // Create 2 reports, 1 completed
        Laporan::create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Laporan 1',
            'deskripsi' => 'Pipa bocor',
            'alamat' => 'Jl. Melati',
            'status' => 'selesai',
            'tanggal_lapor' => now(),
        ]);

        Laporan::create([
            'user_id' => $this->warga->id,
            'wilayah_id' => $wilayah->id,
            'kategori_laporan_id' => $kategori->id,
            'judul' => 'Laporan 2',
            'deskripsi' => 'Air keruh',
            'alamat' => 'Jl. Mawar',
            'status' => 'pending',
            'tanggal_lapor' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        
        $response->assertStatus(200);
        $response->assertDontSee('Data statistik tidak tersedia');
        $response->assertSee('Total Laporan');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['selesai']);
        $this->assertEquals(50.0, $stats['rasio_penyelesaian']);
    }
}
