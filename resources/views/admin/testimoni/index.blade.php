@extends('layouts.admin')
@section('title', 'Moderasi Testimoni')

@section('content')
<div>
    <h1 class="text-sky-900 mb-1" style="font-size: 1.5rem; font-weight: 700;">Moderasi Testimoni Publik</h1>
    <p class="text-slate-500 mb-6" style="font-size: 0.85rem;">Validasi pesan pengunjung sebelum ditampilkan di landing page TirtaBantu.</p>

    <div class="bg-white rounded-xl border border-sky-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" style="font-size: 0.83rem;">
                <thead class="bg-sky-50">
                    <tr class="text-left text-sky-900">
                        <th class="px-4 py-3 font-semibold">Pengirim</th>
                        <th class="px-4 py-3 font-semibold">Pesan</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Dikirim</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimoni as $item)
                        <tr class="border-t border-sky-50 align-top">
                            <td class="px-4 py-4">
                                <p class="text-sky-900" style="font-weight: 600;">{{ $item->nama }}</p>
                                <p class="text-slate-500">{{ $item->email ?: 'Tanpa email' }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600 max-w-md">{{ $item->pesan }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $badge = match ($item->status_validasi) {
                                        'disetujui' => 'bg-emerald-100 text-emerald-700',
                                        'ditolak' => 'bg-red-100 text-red-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <span class="{{ $badge }} px-3 py-1 rounded-full inline-block" style="font-size: 0.72rem; font-weight: 600;">
                                    {{ ucfirst($item->status_validasi) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-500">{{ $item->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    @foreach(['disetujui' => 'Setujui', 'ditolak' => 'Tolak', 'pending' => 'Pendingkan'] as $status => $label)
                                        <form method="POST" action="{{ route('admin.testimoni.update-status', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_validasi" value="{{ $status }}">
                                            <button type="submit" class="px-3 py-2 rounded-lg border border-slate-200 hover:border-sky-300 hover:bg-sky-50 transition-colors">
                                                {{ $label }}
                                            </button>
                                        </form>
                                    @endforeach
                                    <form method="POST" action="{{ route('admin.testimoni.destroy', $item) }}" onsubmit="return confirm('Hapus testimoni ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">Belum ada testimoni publik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-sky-100">
            {{ $testimoni->links() }}
        </div>
    </div>
</div>
@endsection
