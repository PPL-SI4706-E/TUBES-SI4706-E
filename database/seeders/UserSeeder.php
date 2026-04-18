<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $wilayah1 = Wilayah::first();
        $wilayah2 = Wilayah::skip(1)->first();

        $users = [
            ['name' => 'Admin Utama',  'email' => 'admin@tirtabantu.id', 'role' => 'admin',      'phone' => '081200000001', 'wilayah_id' => null,          'is_active' => true],
            ['name' => 'Budi Hartono', 'email' => 'budi@tirtabantu.id',  'role' => 'petugas',    'phone' => '081200000002', 'wilayah_id' => $wilayah1?->id, 'is_active' => true],
            ['name' => 'Siti Aminah',  'email' => 'siti@tirtabantu.id',  'role' => 'petugas',    'phone' => '081200000003', 'wilayah_id' => $wilayah2?->id, 'is_active' => true],
            ['name' => 'Andi Pratama', 'email' => 'andi@gmail.com',       'role' => 'masyarakat', 'phone' => '081200000004', 'wilayah_id' => $wilayah1?->id, 'is_active' => true],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@gmail.com',       'role' => 'masyarakat', 'phone' => '081200000005', 'wilayah_id' => $wilayah2?->id, 'is_active' => true],
            ['name' => 'Nur Halimah',  'email' => 'nur@gmail.com',        'role' => 'masyarakat', 'phone' => '081200000006', 'wilayah_id' => $wilayah1?->id, 'is_active' => true],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], array_merge($user, [
                'password' => Hash::make('password'),
            ]));
        }
    }
}