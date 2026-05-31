<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ReportExportController;
use App\Http\Controllers\Admin\KinerjaController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\MasterKategoriController;
use App\Http\Controllers\Admin\MasterWilayahController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Masyarakat\LaporanController as WargaLaporanController;
use App\Http\Controllers\Masyarakat\PembayaranController as WargaPembayaranController;
use App\Http\Controllers\Masyarakat\UlasanController;
use App\Http\Controllers\NotificationWebController;
use App\Http\Controllers\Api\NotificationController; // Di-import untuk API routes yang butuh session
use App\Http\Controllers\Petugas\TugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// ── Public (tanpa login) ──────────────────────────────────────────────────────
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pengumuman/{id}', [PublicController::class, 'pengumumanDetail'])->name('pengumuman.detail');
Route::post('/testimoni', [PublicController::class, 'storeTestimoni'])->name('testimoni.store');
Route::put('/testimoni/{testimoni}', [PublicController::class, 'updateTestimoni'])->name('testimoni.update');
Route::delete('/testimoni/{testimoni}', [PublicController::class, 'destroyTestimoni'])->name('testimoni.destroy');

// Midtrans Localhost Workaround
Route::get('/midtrans/finish', [PublicController::class, 'midtransFinish'])->name('midtrans.finish');

// ── Auth — FR-03 | PB3 ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── PBI-18: Halaman Notifikasi (semua role terautentikasi) ────────────────────
Route::middleware('auth')->get('/notifikasi', [NotificationWebController::class, 'index'])->name('notifikasi.index');

// ── PBI-18: Notifikasi API (auth session) ─────────────────────────────────────
// Kita meletakkan route API di web.php agar dapat membaca session auth()->user()
Route::middleware('auth')->prefix('api/notifications')->name('api.notifications.')->group(function () {
    Route::get('/',            [NotificationController::class, 'index'])->name('index');       // Read
    Route::post('/read-all',   [NotificationController::class, 'readAll'])->name('readAll');   // Update Massal
    Route::delete('/clear-all',[NotificationController::class, 'clearAll'])->name('clearAll'); // Delete Massal
    Route::patch('/{id}/read', [NotificationController::class, 'markRead'])->name('markRead'); // Update
    Route::delete('/{id}',     [NotificationController::class, 'destroy'])->name('destroy');   // Delete
});

// ── Profile (semua role) ──────────────────────────────────────────────────────
Route::middleware('auth')->prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
    Route::get('/',         'show')->name('show');
    Route::patch('/',       'update')->name('update');
    Route::patch('/password', 'updatePassword')->name('password');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // FR-02 | PB2 — Master Wilayah
    Route::prefix('master-wilayah')->name('master-wilayah.')->controller(MasterWilayahController::class)->group(function () {
        Route::get('/',                   'index')->name('index');
        Route::post('/',                  'store')->name('store');
        Route::put('/{masterWilayah}',    'update')->name('update');
        Route::delete('/{masterWilayah}', 'destroy')->name('destroy');
    });

    // FR-02 | PB2 — Master Kategori
    Route::prefix('master-kategori')->name('master-kategori.')->controller(MasterKategoriController::class)->group(function () {
        Route::get('/',                    'index')->name('index');
        Route::post('/',                   'store')->name('store');
        Route::put('/{masterKategori}',    'update')->name('update');
        Route::delete('/{masterKategori}', 'destroy')->name('destroy');
    });

    // FR-03 | PB3 — User Management
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/',          'index')->name('index');
        Route::post('/',         'store')->name('store');
        Route::put('/{user}',    'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    // TODO: FR-06 — Kelola Laporan (Sprint 1)
    Route::prefix('laporan')->name('laporan.')->controller(AdminLaporanController::class)->group(function () {
        Route::get('/',                        'index')->name('index');
        Route::get('/peta',                    'peta')->name('peta');
        Route::get('/{laporan}',               'show')->name('show');
        Route::post('/{laporan}/validasi',     'validasi')->name('validasi');
        Route::post('/{laporan}/assign',       'assign')->name('assign');
    });

    // FR-07 — Kelola Pembayaran (Sprint 1)
    Route::prefix('pembayaran')->name('pembayaran.')->controller(AdminPembayaranController::class)->group(function () {
        Route::get('/',                        'index')->name('index');
        Route::post('/',                       'store')->name('store');
        Route::post('/{pembayaran}/verify',    'verify')->name('verify');
    });

    // FR-14 — Kinerja Petugas (Sprint 2)
    Route::get('/kinerja', [KinerjaController::class, 'index'])->name('kinerja.index');

    // FR-15 — Export Data (Sprint 2) — PBI 15
    Route::prefix('export')->name('export.')->controller(ExportController::class)->group(function () {
        Route::get('/kinerja', 'kinerja')->name('kinerja');
    });

    // PBI-15 — Export Laporan PDF & Excel (filter-aware)
    Route::prefix('laporan/export')->name('laporan.export.')->controller(ReportExportController::class)->group(function () {
        Route::get('/pdf',   'exportPdf'  )->name('pdf');
        Route::get('/excel', 'exportExcel')->name('excel');
    });

    // TODO: FR-16 — Pengumuman (Sprint 1)
    Route::prefix('pengumuman')->name('pengumuman.')->controller(PengumumanController::class)->group(function () {
        Route::get('/',                    'index')->name('index');
        Route::get('/create',              'create')->name('create');
        Route::post('/',                   'store')->name('store');
        Route::get('/{pengumuman}/edit',   'edit')->name('edit');
        Route::put('/{pengumuman}',        'update')->name('update');
        Route::delete('/{pengumuman}',     'destroy')->name('destroy');
    });

    Route::prefix('testimoni')->name('testimoni.')->controller(\App\Http\Controllers\Admin\TestimoniPublikController::class)->group(function () {
        Route::get('/',                  'index')->name('index');
        Route::patch('/{testimoni}/approve', 'approve')->name('approve');
        Route::patch('/{testimoni}/reject',  'reject')->name('reject');
        Route::delete('/{testimoni}',    'destroy')->name('destroy');
    });
});

// ── Warga ─────────────────────────────────────────────────────────────────────
Route::prefix('warga')->name('warga.')->middleware(['auth', 'masyarakat'])->group(function () {

    // TODO: FR-04 & FR-05 — Laporan (Sprint 1)
    Route::prefix('laporan')->name('laporan.')->controller(WargaLaporanController::class)->group(function () {
        Route::get('/',          'index')->name('index');
        Route::get('/create',    'create')->name('create');
        Route::post('/',         'store')->name('store');
        Route::get('/{laporan}', 'show')->name('show');
        Route::post('/{laporan}/konfirmasi', 'konfirmasi')->name('konfirmasi');
    });

    // FR-07 — Pembayaran (Sprint 1)
    Route::prefix('pembayaran')->name('pembayaran.')->controller(WargaPembayaranController::class)->group(function () {
        Route::get('/',                     'index')->name('index');
        Route::post('/{pembayaran}/upload', 'uploadBukti')->name('upload');
        Route::get('/{pembayaran}/snap-token', [WargaPembayaranController::class, 'snapToken'])->name('pembayaran.snapToken');
    });

    // TODO: FR-12 — Ulasan (Sprint 2)
    Route::post('/laporan/{laporan}/ulasan', [UlasanController::class, 'store'])->name('ulasan.store');
});

// ── Petugas ───────────────────────────────────────────────────────────────────
Route::prefix('petugas')->name('petugas.')->middleware(['auth', 'petugas'])->group(function () {

    // FR-Dashboard Petugas
    Route::get('/dashboard', [\App\Http\Controllers\Petugas\DashboardController::class, 'index'])->name('dashboard');

    // TODO: FR-09 & FR-10 — Daftar Tugas (Sprint 2)
    Route::prefix('tugas')->name('tugas.')->controller(TugasController::class)->group(function () {
        Route::get('/',                          'index')->name('index');
        Route::get('/{penugasan}',               'show')->name('show');
        Route::post('/{penugasan}/status',       'updateStatus')->name('status');
        Route::post('/{penugasan}/bukti',        'uploadBukti')->name('bukti');
    });
});
