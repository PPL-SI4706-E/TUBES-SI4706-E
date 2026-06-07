# TirtaBantu 💧

TirtaBantu adalah sebuah Sistem Manajemen Laporan Air Bersih yang memfasilitasi pelaporan masyarakat terkait masalah air bersih (seperti pipa bocor, kualitas air buruk, dll), hingga penugasan petugas lapangan dan verifikasi pembayaran.

Sistem ini dikembangkan menggunakan **Laravel 11**, **Tailwind CSS**, dan **Alpine.js**, serta dilengkapi dengan pengujian *End-to-End* (*E2E*) menggunakan **Laravel Dusk**.

---

## 🚀 Fitur Utama

### 👤 Masyarakat (Warga)
- Pendaftaran akun mandiri.
- Membuat laporan masalah air berdasarkan wilayah.
- Melacak status laporan secara real-time.
- Melakukan pembayaran (mendukung *Payment Gateway* Midtrans).
- Memberikan ulasan dan rating kepada petugas lapangan setelah tugas selesai.
- Menerima notifikasi otomatis atas perkembangan laporan.

### 👷 Petugas Lapangan
- Menerima tugas/work order yang ditugaskan oleh admin.
- Mengubah status pekerjaan (menuju lokasi, sedang dikerjakan, dll).
- Mengunggah foto bukti penyelesaian perbaikan.
- Melihat daftar tugas yang belum selesai dan riwayat pekerjaan.

### 👑 Admin
- Dashboard interaktif dengan statistik laporan dan pendapatan.
- Mengelola data master (Wilayah & Kategori Laporan).
- Memverifikasi laporan masyarakat dan menugaskannya kepada petugas yang sesuai dengan area.
- Mengelola data user (Masyarakat, Petugas, Admin).
- Memverifikasi bukti pembayaran manual.
- Melihat Kinerja Petugas (jumlah tugas selesai, rata-rata rating) dan mencetak laporannya.
- Mencetak (Export Excel/PDF) laporan keseluruhan dan laporan pembayaran.

---

## 🛠 Persyaratan Sistem

Pastikan sistem Anda memenuhi persyaratan berikut sebelum menjalankan aplikasi:
- **PHP** >= 8.2
- **Composer** (untuk instalasi dependensi PHP)
- **Node.js & npm** (untuk instalasi dependensi frontend / Vite)
- **MySQL** / **MariaDB**
- **Git**

---

## ⚙️ Cara Instalasi & Menjalankan Aplikasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal (Development Environment):

### 1. Clone Repository
```bash
git clone https://github.com/PPL-SI4706-E/TUBES-SI4706-E.git
cd TUBES-SI4706-E
```

### 2. Install Dependensi PHP & Frontend
```bash
composer install
npm install
```

### 3. Konfigurasi Environment File
Salin file `.env.example` menjadi `.env`.
```bash
cp .env.example .env
```
Buka file `.env` dan atur konfigurasi database (sesuaikan dengan lokal Anda):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tirtabantu
DB_USERNAME=root
DB_PASSWORD=
```

*(Opsional)* Jika Anda ingin mencoba fitur pembayaran otomatis dengan Midtrans, atur juga bagian konfigurasi Midtrans di dalam `.env`:
```env
MIDTRANS_SERVER_KEY=server_key_anda
MIDTRANS_CLIENT_KEY=client_key_anda
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Jalankan Migrasi & Seeder Database
Siapkan database Anda di MySQL, lalu jalankan perintah berikut untuk membuat struktur tabel dan mengisi data awal (Admin default, kategori, wilayah, dll).
```bash
php artisan migrate:fresh --seed
```

### 6. Jalankan Server Development
Buka dua tab terminal. 

Di terminal pertama, jalankan Vite untuk melakukan kompilasi aset (Tailwind & Alpine) secara langsung:
```bash
npm run dev
```

Di terminal kedua, jalankan server PHP Laravel:
```bash
php artisan serve
```

Aplikasi dapat diakses melalui browser pada alamat: **http://127.0.0.1:8000**

---

## 🧪 Pengujian E2E (Laravel Dusk)

Aplikasi ini menggunakan Laravel Dusk untuk E2E testing yang melakukan simulasi pengguna di browser Chrome secara otomatis. Pengujian memerlukan database yang terpisah dari environment development agar data lokal Anda tidak terhapus.

### Langkah-langkah Menjalankan Testing:

1. **Buat Database Testing**
   Buat database baru di MySQL lokal Anda dengan nama `tirtabantu_testing`.

2. **Buat File Environment Testing**
   Salin file `.env` menjadi `.env.dusk.local`.
   ```bash
   cp .env .env.dusk.local
   ```
   Buka file `.env.dusk.local` dan sesuaikan koneksi databasenya, serta ubah APP_PORT agar tidak bentrok dengan server utama:
   ```env
   APP_URL=http://dusk.local:8080
   APP_PORT=8080
   DB_DATABASE=tirtabantu_testing
   ```

3. **Jalankan Dusk**
   Cukup eksekusi perintah berikut untuk memulai testing otomatis:
   ```bash
   php artisan dusk
   ```
   *(Sistem telah dikonfigurasi melalui `phpunit.dusk.xml` untuk secara otomatis menggunakan `.env.dusk.local`, menjalankan server PHP di port `8080`, dan melakukan refresh pada database `tirtabantu_testing`).*

---

## 🔐 Akun Default untuk Testing

Jika Anda telah menjalankan seeder (`php artisan db:seed` atau `migrate:fresh --seed`), Anda dapat menggunakan akun berikut untuk masuk:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@tirtabantu.com | password |
| **Petugas** | petugas@tirtabantu.com | password |
| **Warga** | warga@tirtabantu.com | password |

*(Petugas dan Warga lainnya dapat didaftarkan secara manual melalui menu registrasi atau panel Admin).*

---

**© 2026 TirtaBantu Team. Hak Cipta Dilindungi.**
