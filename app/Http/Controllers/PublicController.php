<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\TestimoniPublik;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicController extends Controller
{
    public function home()
    {
        $sessionToken = session('public_testimoni_token');

        $pengumumanList = Pengumuman::query()
            ->latest('tanggal_post')
            ->latest()
            ->get()
            ->map(function (Pengumuman $pengumuman) {
                return [
                    'id' => $pengumuman->id,
                    'judul' => $pengumuman->judul,
                    'isi' => $pengumuman->isi,
                    'tgl_posting' => optional($pengumuman->tanggal_post)->format('Y-m-d'),
                    'penting' => $pengumuman->is_penting,
                    'kategori' => $pengumuman->kategori,
                ];
            })
            ->all();

        $approvedTestimonials = TestimoniPublik::query()
            ->where('status', 'approved')
            ->latest('approved_at')
            ->latest()
            ->limit(6)
            ->get();

        $myTestimonial = $sessionToken
            ? TestimoniPublik::query()
                ->where('session_token', $sessionToken)
                ->latest()
                ->first()
            : null;

        return view('public.home', compact('approvedTestimonials', 'myTestimonial', 'pengumumanList'));
    }

    public function pengumumanDetail($id)
    {
        return view('public.pengumuman-detail', ['id' => $id]);
    }

    public function storeTestimoni(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $sessionToken = $request->session()->get('public_testimoni_token', Str::random(40));

        $testimoni = TestimoniPublik::query()->create([
            ...$data,
            'status' => 'pending',
            'session_token' => $sessionToken,
            'editable_until' => now()->addMinutes(5),
        ]);

        $request->session()->put('public_testimoni_token', $sessionToken);
        $request->session()->flash('success', 'Testimoni Anda berhasil dikirim dan sedang menunggu persetujuan admin.');
        $request->session()->flash('testimoni_id', $testimoni->id);

        return redirect(route('home') . '#testimoni');
    }

    public function updateTestimoni(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        $this->ensureTestimoniEditable($request, $testimoni);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $testimoni->update([
            ...$data,
            'status' => 'pending',
            'approved_at' => null,
            'catatan_admin' => null,
        ]);

        return redirect(route('home') . '#testimoni-saya')->with('success', 'Testimoni Anda berhasil diperbarui.');
    }

    public function destroyTestimoni(Request $request, TestimoniPublik $testimoni): RedirectResponse
    {
        $this->ensureTestimoniEditable($request, $testimoni);

        $testimoni->delete();
        $request->session()->forget('public_testimoni_token');

        return redirect(route('home') . '#testimoni')->with('success', 'Testimoni Anda berhasil dihapus.');
    }

    protected function ensureTestimoniEditable(Request $request, TestimoniPublik $testimoni): void
    {
        $sessionToken = (string) $request->session()->get('public_testimoni_token', '');

        if (! $testimoni->isEditableFor($sessionToken)) {
            throw ValidationException::withMessages([
                'pesan' => 'Waktu edit atau hapus testimoni sudah habis atau sesi Anda tidak cocok.',
            ]);
        }
    }
}
