# Public Testimoni Landing Page Design

## Tujuan

Menambahkan fitur "Buku Tamu / Testimoni Publik" pada landing page TirtaBantu agar pengunjung tanpa login dapat mengirim pesan, saran, atau kesan yang akan tampil di beranda setelah divalidasi admin.

## Ruang Lingkup

- Pengunjung publik dapat membuat testimoni dengan nama, email opsional, dan pesan.
- Testimoni baru disimpan dengan status `pending`.
- Hanya testimoni `approved` yang tampil di landing page.
- Pengunjung dapat mengedit dan menghapus testimoni miliknya selama 5 menit setelah pengiriman.
- Hak edit/hapus untuk pengunjung non-login dikontrol dengan token kepemilikan yang disimpan di session browser.
- Admin dapat melihat daftar testimoni, menyetujui, menolak, dan menghapus testimoni.

## Arsitektur

- Tambahkan tabel baru `testimoni_publiks` untuk menyimpan data testimoni dan metadata validasi.
- Tambahkan model `TestimoniPublik`.
- Tambahkan endpoint publik untuk `store`, `update`, dan `destroy`.
- Tambahkan endpoint admin untuk daftar dan validasi testimoni.
- Landing page mengambil dua kelompok data: testimoni `approved` untuk ditampilkan publik dan testimoni milik session aktif untuk panel kendali edit/hapus.

## Data Model

Kolom utama:

- `nama`
- `email` nullable
- `pesan`
- `status` enum sederhana string: `pending`, `approved`, `rejected`
- `catatan_admin` nullable
- `approved_at` nullable
- `editable_until`
- `session_token`
- timestamps

## Alur Pengunjung

1. Pengunjung membuka landing page.
2. Pengunjung mengisi form testimoni.
3. Sistem memvalidasi input lalu menyimpan testimoni dengan status `pending`.
4. Sistem menyimpan `session_token` testimoni ke session browser.
5. Selama waktu sekarang belum melewati `editable_until`, pengunjung dapat mengedit atau menghapus testimoni miliknya.
6. Setelah 5 menit, hak edit/hapus berakhir.

## Alur Admin

1. Admin membuka halaman kelola testimoni.
2. Admin meninjau testimoni `pending`.
3. Admin dapat menyetujui atau menolak testimoni.
4. Jika disetujui, `approved_at` diisi dan testimoni tampil di landing page.
5. Admin dapat menghapus testimoni kapan pun.

## Tampilan Landing Page

- Tambahkan section baru setelah area fitur/alur agar tetap terlihat sebagai social proof.
- Bagian kiri: penjelasan fitur dan daftar testimoni approved dalam grid cards responsif.
- Bagian kanan: form input testimoni.
- Jika session memiliki testimoni yang masih bisa diubah, tampilkan kartu status "testimoni Anda" dengan tombol edit/hapus.

## Penanganan Error

- Validasi input wajib untuk `nama` dan `pesan`.
- `email` divalidasi hanya jika diisi.
- Update/delete publik harus gagal jika token session tidak cocok.
- Update/delete publik harus gagal jika melewati batas 5 menit.

## Testing

- Feature test untuk submit testimoni publik.
- Feature test bahwa landing page hanya menampilkan testimoni approved.
- Feature test bahwa pemilik session bisa update/delete sebelum 5 menit.
- Feature test bahwa update/delete ditolak setelah 5 menit.
- Feature test bahwa admin bisa approve testimoni.
