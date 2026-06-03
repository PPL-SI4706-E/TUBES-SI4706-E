<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Pbi2MasterDataTest extends DuskTestCase
{
    public function testAdminKelolaMasterData()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->driver->manage()->deleteAllCookies();
            
            // Use helper to ensure admin exists and login
            $this->loginAsAdmin($browser);

            // After login helper, we are already on /admin/dashboard
            // Navigate to Master Kategori page
            $browser->visit('/admin/master-kategori')
                    ->assertSee('Master Kategori Laporan');
        });
    }

    /**
     * Helper to login as admin (same as in KategoriLaporanTest)
     */
    protected function loginAsAdmin(Browser $browser)
    {
        // Ensure admin user exists
        User::firstOrCreate([
            'email' => 'admin@tirtabantu.id',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $browser->visit('/login')
                ->type('#email', 'admin@tirtabantu.id')
                ->type('#password', 'password')
                ->script("let r=document.querySelector('input[name=\\\"role\\\"][value=\\\"admin\\\"]'); r.checked=true; r.dispatchEvent(new Event('change',{bubbles:true}));")
                ->pause(500)
                ->press('Masuk')
                ->assertPathIs('/admin/dashboard');
    }
}

