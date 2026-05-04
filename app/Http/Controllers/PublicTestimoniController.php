<?php

namespace App\Http\Controllers;

use App\Models\TestimoniPublik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicTestimoniController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'pesan' => ['required', 'string', 'min:10', 'max:600'],
        ]);

        $plainToken = Str::random(40);

        $testimoni = TestimoniPublik::create([
            'nama' => $data['nama'],
            'email' => $data['email'] ?? null,
            'pesan' => $data['pesan'],
            'status_validasi' => 'pending',
            'edit_token' => Hash::make($plainToken),
            'editable_until' => now()->addMinutes(5),
        ]);

        $request->session()->put("testimoni_guest.{$testimoni->id}", $plainToken);

        return redirect()
            ->to(route('home') . '#testimoni')
            ->with('success', 'Testimoni berhasil dikirim dan sedang menunggu validasi admin.');
    }

    public function update(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        if (! $this->canManage($request, $testimoni)) {
            return redirect()
                ->route('home')
                ->withErrors(['testimoni' => 'Masa edit testimoni sudah berakhir atau sesi Anda tidak valid.']);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'pesan' => ['required', 'string', 'min:10', 'max:600'],
        ]);

        $testimoni->update([
            'nama' => $data['nama'],
            'email' => $data['email'] ?? null,
            'pesan' => $data['pesan'],
            'status_validasi' => 'pending',
            'validated_at' => null,
        ]);

        return redirect()
            ->to(route('home') . '#kelola-testimoni')
            ->with('success', 'Testimoni berhasil diperbarui dan dikirim ulang untuk validasi admin.');
    }

    public function destroy(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        if (! $this->canManage($request, $testimoni)) {
            return redirect()
                ->route('home')
                ->withErrors(['testimoni' => 'Masa hapus testimoni sudah berakhir atau sesi Anda tidak valid.']);
        }

        $request->session()->forget("testimoni_guest.{$testimoni->id}");
        $testimoni->delete();

        return redirect()
            ->to(route('home') . '#kelola-testimoni')
            ->with('success', 'Testimoni berhasil dihapus.');
    }

    protected function canManage(Request $request, TestimoniPublik $testimoni): bool
    {
        $sessionToken = $request->session()->get("testimoni_guest.{$testimoni->id}");

        return $testimoni->isEditable()
            && is_string($sessionToken)
            && Hash::check($sessionToken, $testimoni->edit_token ?? '');
    }
}
