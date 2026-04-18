<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return auth()->check() ? $this->redirectByRole() : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (! auth()->user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
            }

            return $this->redirectByRole();
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput($request->except('password'));
    }

    public function showRegister()
    {
        // Jika sudah login, redirect by role
        if (auth()->check()) {
            return $this->redirectByRole();
        }
        
        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();
        return view('auth.register', compact('wilayahs'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:user',
            'password'   => ['required', 'confirmed', Password::min(6)],
            'phone'      => 'nullable|string|max:15',
            'wilayah_id' => 'nullable|exists:wilayah,id',
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'phone'      => $data['phone'] ?? null,
            'wilayah_id' => $data['wilayah_id'] ?? null,
            'role'       => 'masyarakat',
        ]);

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil. Silakan masuk dengan akun Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    private function redirectByRole()
    {
        return match (auth()->user()->role) {
            'admin'      => redirect()->route('admin.dashboard'),
            'petugas'    => redirect()->route('petugas.tugas.index'),
            'masyarakat' => redirect()->route('warga.laporan.index'),
            default      => redirect()->route('home'),
        };
    }
}