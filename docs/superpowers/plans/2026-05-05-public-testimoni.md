# Public Testimoni Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun fitur buku tamu atau testimoni publik pada landing page dengan approval admin serta edit/hapus terbatas 5 menit untuk pengunjung non-login.

**Architecture:** Fitur disimpan dalam tabel baru `testimoni_publiks`, dikelola oleh controller publik dan admin yang terpisah, lalu diintegrasikan ke landing page dan halaman admin. Kepemilikan pengunjung ditentukan oleh token session browser agar tetap aman tanpa akun login.

**Tech Stack:** Laravel 10, Blade, PHPUnit Feature Tests, session driver array untuk testing.

---

### Task 1: Data Layer

**Files:**
- Create: `database/migrations/2026_05_05_000000_create_testimoni_publiks_table.php`
- Create: `app/Models/TestimoniPublik.php`
- Test: `tests/Feature/PublicTestimoniTest.php`

- [ ] Tulis test gagal untuk penyimpanan testimoni publik dan kolom default status.
- [ ] Jalankan test untuk memastikan gagal.
- [ ] Implementasikan migration dan model minimal.
- [ ] Jalankan test sampai lolos.

### Task 2: Public CRUD

**Files:**
- Modify: `app/Http/Controllers/PublicController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PublicTestimoniTest.php`

- [ ] Tulis test gagal untuk create, update, dan delete berbasis session token.
- [ ] Jalankan test untuk memastikan gagal.
- [ ] Implementasikan endpoint publik dan validasi.
- [ ] Jalankan test sampai lolos.

### Task 3: Admin Approval

**Files:**
- Create: `app/Http/Controllers/Admin/TestimoniPublikController.php`
- Modify: `routes/web.php`
- Create: `resources/views/admin/testimoni/index.blade.php`
- Test: `tests/Feature/AdminTestimoniPublikTest.php`

- [ ] Tulis test gagal untuk daftar admin dan approval testimoni.
- [ ] Jalankan test untuk memastikan gagal.
- [ ] Implementasikan controller admin, route, dan view sederhana.
- [ ] Jalankan test sampai lolos.

### Task 4: Landing Page Integration

**Files:**
- Modify: `app/Http/Controllers/PublicController.php`
- Modify: `resources/views/public/home.blade.php`
- Test: `tests/Feature/PublicTestimoniTest.php`

- [ ] Tulis test gagal untuk hanya menampilkan testimoni approved di landing page.
- [ ] Jalankan test untuk memastikan gagal.
- [ ] Integrasikan data dan UI section testimoni ke landing page.
- [ ] Jalankan test sampai lolos.

### Task 5: Final Verification

**Files:**
- Verify only

- [ ] Jalankan seluruh feature test terkait testimoni publik.
- [ ] Jalankan suite test yang relevan untuk memastikan tidak ada regresi utama.
- [ ] Rapikan implementasi bila perlu tanpa mengubah perilaku.
