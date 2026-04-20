<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users    = User::with('wilayah')->orderBy('role')->orderBy('name')->paginate(15);
        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();
        return view('admin.users.index', compact('users', 'wilayahs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:user',
            'password'   => ['required', Password::min(6)],
            'role'       => 'required|in:admin,petugas,masyarakat',
            'phone'      => 'nullable|string|max:15',
            'wilayah_id' => 'nullable|exists:wilayah,id',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'role.required'     => 'Peran pengguna wajib dipilih.',
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = true;

        User::create($data);
        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:user,email,' . $user->id,
            'role'       => 'required|in:admin,petugas,masyarakat',
            'phone'      => 'nullable|string|max:15',
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'is_active'  => 'boolean',
        ]);

        if ($user->isAdmin() && $data['role'] !== 'admin') {
            $adminCount = User::where('role', 'admin')->where('is_active', true)->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat mengubah peran: sistem membutuhkan setidaknya satu Admin aktif.');
            }
        }

        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $request->validate(['password' => Password::min(6)]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->where('is_active', true)->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus Admin terakhir yang aktif.');
            }
        }

        if ($user->laporans()->count() > 0 || $user->penugasanSebagaiPetugas()->count() > 0) {
            $user->update(['is_active' => false]);
            return back()->with('success', 'Akun pengguna telah dinonaktifkan (memiliki data terkait).');
        }

        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}