<?php

namespace App\Http\Controllers;

use App\Models\TestimoniPublik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicTestimoniController extends Controller
{
    private const SESSION_KEY = 'public_testimoni_id';

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'pesan' => 'required|string|min:10|max:1000',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'pesan.required' => 'Pesan testimoni wajib diisi.',
            'pesan.min' => 'Pesan testimoni minimal 10 karakter.',
        ]);

        $testimoni = TestimoniPublik::create([
            ...$data,
            'status' => TestimoniPublik::STATUS_PENDING,
        ]);

        $request->session()->put(self::SESSION_KEY, $testimoni->id);

        return redirect()
            ->to(route('home') . '#testimoni')
            ->with('success', 'Testimoni berhasil dikirim dan sedang menunggu validasi admin.');
    }

    public function update(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        if (! $this->canManage($request, $testimoni)) {
            return redirect()
                ->to(route('home') . '#testimoni')
                ->withErrors(['testimoni' => 'Batas waktu edit testimoni sudah berakhir atau sesi Anda tidak cocok.']);
        }

        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'pesan' => 'required|string|min:10|max:1000',
        ]);

        $testimoni->update([
            ...$data,
            'status' => TestimoniPublik::STATUS_PENDING,
            'validated_at' => null,
        ]);

        return redirect()
            ->to(route('home') . '#testimoni')
            ->with('success', 'Testimoni berhasil diperbarui dan dikirim ulang untuk validasi admin.');
    }

    public function destroy(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        if (! $this->canManage($request, $testimoni)) {
            return redirect()
                ->to(route('home') . '#testimoni')
                ->withErrors(['testimoni' => 'Batas waktu hapus testimoni sudah berakhir atau sesi Anda tidak cocok.']);
        }

        $testimoni->delete();
        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->to(route('home') . '#testimoni')
            ->with('success', 'Testimoni berhasil ditarik kembali.');
    }

    public static function sessionKey(): string
    {
        return self::SESSION_KEY;
    }

    private function canManage(Request $request, TestimoniPublik $testimoni): bool
    {
        return (int) $request->session()->get(self::SESSION_KEY) === (int) $testimoni->id
            && $testimoni->isEditableUntil();
    }
}
