<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi12ProfilPenggunaTest extends DuskTestCase
{
    protected static bool $migrated = false;

    private User $user;
    private Wilayah $wilayah;

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
            ['nama_wilayah' => 'Area PBI 12'],
            [
                'tipe' => 'kecamatan',
                'kode_wilayah' => 'PBI12',
            ]
        );

        $this->user = User::firstOrCreate(
            ['email' => 'user.pbi12@test.com'],
            [
                'name' => 'User PBI 12',
                'phone' => '081111111111',
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

        $this->validPhotoPath = $tempDir . '/profile_pbi12.jpg';

        $jpg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8PDw8QDw8PDw8PDw8PDw8PFREWFhURFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGi0lHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAgMBIgACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAABf/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/Aaf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/Aaf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Aqf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IV//2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z'
        );

        file_put_contents($this->validPhotoPath, $jpg);

        $this->largePhotoPath = $tempDir . '/profile_besar_pbi12.jpg';
        file_put_contents($this->largePhotoPath, str_repeat('A', 10 * 1024 * 1024));

        $this->invalidDocPath = $tempDir . '/profile_invalid_pbi12.pdf';
        File::put($this->invalidDocPath, 'Ini file PDF palsu untuk test profil.');
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

    private function loginSebagaiUser(Browser $browser): void
    {
        $browser->visit('/')
            ->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitForText('Masuk ke Sistem', 10)
            ->type('email', 'user.pbi12@test.com')
            ->type('password', 'password');

        $this->pilihRole($browser, 'masyarakat');

        $browser->press('Masuk')
            ->waitForLocation('/warga/laporan', 10)
            ->assertPathIs('/warga/laporan');
    }

    private function submitUpdateProfilViaJs(Browser $browser, array $data, ?string $filePath = null): void
    {
        $name = $data['name'] ?? $this->user->name;
        $email = $data['email'] ?? $this->user->email;
        $phone = $data['phone'] ?? $this->user->phone;

        $browser->script("
            const form = document.createElement('form');
            form.method = 'POST';
            form.enctype = 'multipart/form-data';
            form.action = '/profile';

            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                || document.querySelector('input[name=\"_token\"]')?.value;

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            form.appendChild(methodInput);

            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'name';
            nameInput.value = '{$name}';
            form.appendChild(nameInput);

            const emailInput = document.createElement('input');
            emailInput.type = 'hidden';
            emailInput.name = 'email';
            emailInput.value = '{$email}';
            form.appendChild(emailInput);

            const phoneInput = document.createElement('input');
            phoneInput.type = 'hidden';
            phoneInput.name = 'phone';
            phoneInput.value = '{$phone}';
            form.appendChild(phoneInput);

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'avatar';
            fileInput.id = 'dusk_avatar';
            form.appendChild(fileInput);

            document.body.appendChild(form);
        ");

        if ($filePath) {
            $browser->attach('#dusk_avatar', $filePath);
        }

        $browser->script("
            document.querySelector('form[action=\"/profile\"]').submit();
        ");
    }

    private function submitPasswordViaJs(Browser $browser, string $currentPassword, string $newPassword, string $confirmPassword): void
    {
        $browser->script("
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/profile/password';

            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                || document.querySelector('input[name=\"_token\"]')?.value;

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            form.appendChild(methodInput);

            const currentInput = document.createElement('input');
            currentInput.type = 'hidden';
            currentInput.name = 'old_password';
            currentInput.value = '{$currentPassword}';
            form.appendChild(currentInput);

            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = '{$newPassword}';
            form.appendChild(passwordInput);

            const confirmInput = document.createElement('input');
            confirmInput.type = 'hidden';
            confirmInput.name = 'password_confirmation';
            confirmInput.value = '{$confirmPassword}';
            form.appendChild(confirmInput);

            document.body.appendChild(form);
            form.submit();
        ");
    }

    public function testTC1201BerhasilMelihatProfil()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiUser($browser);

            $browser->visit('/profile')
                ->waitForText('Profil', 10)
                ->assertInputValue('name', 'User PBI 12')
                ->assertInputValue('phone', '081111111111');
        });
    }

    public function testTC1202BerhasilUpdateProfil()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiUser($browser);

            $browser->visit('/profile')
                ->waitForText('Profil', 10);

            $this->submitUpdateProfilViaJs($browser, [
                'name' => 'Dwi Kurniawan',
                'email' => 'dwi.pbi12@test.com',
                'phone' => '08123456789',
            ], $this->validPhotoPath);

            $browser->pause(1500)
                ->assertPathIs('/profile')
                ->assertSee('Profil berhasil diperbarui');

            $this->user->refresh();

            $this->assertEquals('Dwi Kurniawan', $this->user->name);
            $this->assertEquals('dwi.pbi12@test.com', $this->user->email);
            $this->assertEquals('08123456789', $this->user->phone);
            $this->assertNotNull($this->user->avatar);

            // Restore user data
            $this->user->update([
                'name' => 'User PBI 12',
                'email' => 'user.pbi12@test.com',
                'phone' => '081111111111',
            ]);
        });
    }

    public function testTC1203BerhasilMenggantiPassword()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiUser($browser);

            $browser->visit('/profile')
                ->waitForText('Profil', 10);

            $this->submitPasswordViaJs($browser, 'password', 'password123', 'password123');

            $browser->pause(1500)
                ->assertPathIs('/profile');

            $this->user->refresh();

            $this->assertTrue(Hash::check('password123', $this->user->password));

            // Restore password for subsequent tests
            $this->user->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);
        });
    }

    public function testTC1204GagalGantiPasswordKarenaPasswordLamaSalah()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiUser($browser);

            $browser->visit('/profile')
                ->waitForText('Profil', 10);

            $this->submitPasswordViaJs($browser, 'salah123', 'password123', 'password123');

            $browser->pause(1500)
                ->assertPathIs('/profile');

            $this->user->refresh();

            $this->assertTrue(Hash::check('password', $this->user->password));
        });
    }

    public function testTC1205GagalUpdateProfilKarenaDataTidakValid()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiUser($browser);

            $browser->visit('/profile')
                ->waitForText('Profil', 10);

            $this->submitUpdateProfilViaJs($browser, [
                'name' => '',
                'email' => 'user.pbi12@test.com',
                'phone' => '081111111111',
            ]);

            $browser->pause(1500)
                ->assertPathIs('/profile');

            $this->user->refresh();

            $this->assertEquals('User PBI 12', $this->user->name);
        });
    }

    public function testTC1206GagalUploadFotoKarenaUkuranTerlaluBesar()
    {
        $this->browse(function (Browser $browser) {
            $this->loginSebagaiUser($browser);

            $browser->visit('/profile')
                ->waitForText('Profil', 10);

            $this->user->refresh();
            $avatarSebelum = $this->user->avatar;

            $this->submitUpdateProfilViaJs($browser, [
                'name' => 'User PBI 12',
                'email' => 'user.pbi12@test.com',
                'phone' => '081111111111',
            ], $this->largePhotoPath);

            $browser->pause(1500)
                ->assertPathIs('/profile');

            $this->user->refresh();

            $this->assertEquals($avatarSebelum, $this->user->avatar);
        });
    }
}