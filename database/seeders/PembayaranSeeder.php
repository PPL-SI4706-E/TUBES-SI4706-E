<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure there is a user to attach the payment to
        $user = DB::table('users')->where('email', 'admin@example.com')->first();
        if (! $user) {
            $userId = DB::table('users')->insertGetId([
                'name'       => 'Demo User',
                'email'      => 'admin@example.com',
                'password'   => Hash::make('password'), // password: password
                'role'       => 'masyarakat',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->id;
        }

        // Ensure there is at least one tagihan (bill) to pay
        $tagihan = DB::table('tagihan')->first();
        if (! $tagihan) {
            $tagihanId = DB::table('tagihan')->insertGetId([
                'nama'               => 'Tagihan Demo',
                'jumlah'             => 50000, // contoh Rp 50.000
                'tanggal_jatuh_tempo'=> Carbon::now()->addDays(7),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } else {
            $tagihanId = $tagihan->id;
        }

        // Insert a pembayaran record linked to the user and tagihan
        DB::table('pembayaran')->insert([
            'user_id'           => $userId,
            'tagihan_id'        => $tagihanId,
            'laporan_id'        => null,
            'harga'             => $tagihan->jumlah ?? 50000,
            'metode_pembayaran'=> null, // belum dipilih, QRIS akan ditetapkan nanti
            'status_pembayaran'=> 'Menunggu',
            'qr_code_generate' => null,
            'bukti_transaksi'   => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
