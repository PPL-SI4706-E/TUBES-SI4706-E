<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SidebarController extends Controller
{
    public function avatar(User $user)
    {
        if (! $user->avatar || ! Storage::disk('public')->exists($user->avatar)) {
            return redirect('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0ea5e9&color=fff');
        }

        return response()->file(Storage::disk('public')->path($user->avatar));
    }

    public function profile()
    {
        $user = auth()->user()->load('wilayah');

        return view('petugas.profile.index', [
            'petugas' => [
                'name' => $user->name,
                'role' => 'Petugas Lapangan',
                'initial' => strtoupper(substr($user->name, 0, 1)),
                'avatar_url' => $user->avatar_url,
            ],
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'wilayah' => $user->wilayah?->nama_wilayah ?: 'Belum ditentukan',
                'avatar_url' => $user->avatar_url,
            ],
            'notificationCount' => 1,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('user', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:15'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.mimes' => 'Foto profil harus berformat JPG, JPEG, atau PNG.',
            'avatar.max' => 'Ukuran foto profil maksimal 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai.',
            ], 'passwordUpdate')->withInput();
        }

        $user->update([
            'password' => $validated['new_password'],
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function notifications()
    {
        $user = auth()->user();

        $notifications = collect([
            [
                'title' => 'Tugas Baru',
                'message' => 'Anda ditugaskan ke Laporan #1006 (Pipa Tersumbat) di Jl. Raya Sumedang.',
                'time' => '14 menit yang lalu',
                'is_unread' => true,
            ],
            [
                'title' => 'Update Status Tugas',
                'message' => 'Laporan #1002 masih berstatus Sedang Dikerjakan. Jangan lupa kirim bukti setelah selesai.',
                'time' => '1 jam yang lalu',
                'is_unread' => false,
            ],
            [
                'title' => 'Pengingat Admin',
                'message' => 'Pastikan alamat dan lokasi pelanggan dicek ulang sebelum menutup tugas di lapangan.',
                'time' => 'Kemarin',
                'is_unread' => false,
            ],
        ]);

        return view('petugas.notifications.index', [
            'petugas' => [
                'name' => $user->name,
                'role' => 'Petugas Lapangan',
                'initial' => strtoupper(substr($user->name, 0, 1)),
                'avatar_url' => $user->avatar_url,
            ],
            'notifications' => $notifications,
            'notificationCount' => $notifications->where('is_unread', true)->count(),
        ]);
    }
}
