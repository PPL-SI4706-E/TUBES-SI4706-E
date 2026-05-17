<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna.
     */
    public function show()
    {
        return view('profile.index', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update data profil pengguna (nama, email, phone, avatar).
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:user,email,' . $user->id,
            'phone'  => 'nullable|string|max:15',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah digunakan oleh pengguna lain.',
            'phone.max'      => 'Nomor HP maksimal 15 karakter.',
            'avatar.image'   => 'File harus berupa gambar.',
            'avatar.mimes'   => 'Format foto harus JPG, JPEG, atau PNG.',
            'avatar.max'     => 'Ukuran foto maksimal 2MB.',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Hapus foto lama jika ada
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update password pengguna.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi.',
            'password.required'     => 'Password baru wajib diisi.',
            'password.min'          => 'Password baru minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $user = auth()->user();

        // Verifikasi password lama
        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => 'Password lama tidak sesuai.',
            ]);
        }

        // Password baru tidak boleh sama dengan password lama
        if ($request->old_password === $request->password) {
            throw ValidationException::withMessages([
                'password' => 'Password baru tidak boleh sama dengan password lama.',
            ]);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }
}
