<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Role yang boleh dipilih saat LOGIN (semua role bisa login).
     */
    private const LOGIN_ROLES = ['masyarakat', 'admin', 'petugas'];

    /**
     * Role yang boleh dipilih saat SELF-REGISTER.
     * Petugas TIDAK boleh daftar mandiri (akun petugas dibuat oleh Admin).
     */
    private const REGISTER_ROLES = ['masyarakat', 'admin'];

    // ─── LOGIN ───────────────────────────────────────────────────────────
    public function showLogin()
    {
        return auth()->check() ? $this->redirectByRole() : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => ['required', Rule::in(self::LOGIN_ROLES)],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'role.required' => 'Silakan pilih jenis akun terlebih dahulu.',
            'role.in' => 'Jenis akun tidak valid.',
        ]);

        $selectedRole = $credentials['role'];

        // Cari user berdasarkan email — untuk validasi role match.
        // Kalau admin coba pilih role "Masyarakat" → ditolak (begitu sebaliknya).
        // Pesan error sengaja generik agar tidak membocorkan apakah email
        // tersebut terdaftar atau tidak.
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->role !== $selectedRole) {
            return back()
                ->withErrors([
                    'email' => 'Email, password, atau jenis akun tidak sesuai.',
                ])
                ->withInput($request->except('password'));
        }

        if (!$user->is_active) {
            return back()
                ->withErrors(['email' => 'Akun Anda telah dinonaktifkan.'])
                ->withInput($request->except('password'));
        }

        // Auth::attempt dengan constraint role — defensive double-check,
        // sehingga walaupun ada bypass di guard atas, tetap aman.
        $attempt = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'role' => $selectedRole,
        ];

        if (!Auth::attempt($attempt, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'Email, password, atau jenis akun tidak sesuai.',
                ])
                ->withInput($request->except('password'));
        }

        $request->session()->regenerate();

        if (!auth()->user()->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        return $this->redirectByRole();
    }

    // ─── REGISTER ────────────────────────────────────────────────────────
    public function showRegister()
    {
        if (auth()->check()) {
            return $this->redirectByRole();
        }

        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();
        return view('auth.register', compact('wilayahs'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:user',
            'password' => ['required', 'confirmed', Password::min(6)],
            'phone' => 'nullable|string|max:15',
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'role' => ['required', Rule::in(self::REGISTER_ROLES)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Silakan pilih jenis akun.',
            'role.in' => 'Jenis akun tidak valid. Petugas hanya dapat didaftarkan oleh Admin.',
        ]);

        // Guard tambahan agar role tidak bisa di-bypass via inspect element / curl.
        $role = in_array($data['role'], self::REGISTER_ROLES, true)
            ? $data['role']
            : 'masyarakat';

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'wilayah_id' => $data['wilayah_id'] ?? null,
            'role' => $role,
            'is_active' => true,
        ]);

        $label = $role === 'admin' ? 'Admin' : 'Masyarakat';

        return redirect()
            ->route('login')
            ->with('success', "Pendaftaran berhasil sebagai {$label}. Silakan masuk dengan akun Anda.")
            ->withInput(['email' => $data['email'], 'role' => $role]);
    }

    // ─── LOGOUT ──────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Anda telah berhasil keluar.');
    }

    // ─── HELPER ──────────────────────────────────────────────────────────
    private function redirectByRole()
    {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('petugas.tugas.index'),
            'masyarakat' => redirect()->route('warga.laporan.index'),
            default => redirect()->route('home'),
        };
    }
}