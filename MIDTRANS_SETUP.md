# LANGKAH-LANGKAH SETTING MIDTRANS UNTUK ANGGOTA TIM
==================================================

Untuk menyamakan konfigurasi Midtrans di komputer masing-masing anggota tim, silakan ikuti langkah-langkah berikut:

1. **Buka file `.env`** yang ada di root folder project kamu.
2. **Cari bagian paling bawah** file `.env`, lalu tambahkan dua baris berikut (menggunakan Server Key dan Client Key milik tim kita):
   ```env
   MIDTRANS_SERVER_KEY=masukkan_server_key_anda_di_sini
   MIDTRANS_CLIENT_KEY=masukkan_client_key_anda_di_sini
   ```
3. **Simpan (Save)** file `.env` tersebut.
4. **Pastikan server lokal kamu sedang berjalan** (misalnya XAMPP/Laragon Apache/MySQL, dan `php artisan serve` atau `npm run dev` jika ada).
5. **Lakukan uji coba transaksi pembayaran online** menggunakan metode Midtrans Sandbox.
