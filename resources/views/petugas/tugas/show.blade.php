@extends('layouts.petugas')
@section('title', 'Detail Tugas')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-6">
            <a href="{{ route('petugas.tugas.index') }}" class="text-sm font-medium text-sky-600 hover:text-sky-700">
                ← Kembali ke Daftar Tugas
            </a>
        </div>

        <div class="rounded-[28px] border border-sky-100 bg-white px-6 py-6 shadow-sm">
            <h1 class="text-3xl font-bold text-sky-900">Detail Tugas #{{ $penugasan->laporan?->id }}</h1>
            <p class="mt-2 text-slate-500">{{ $penugasan->laporan?->kategoriLaporan?->nama_kategori ?? 'Laporan' }}</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl bg-sky-50 p-5">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-700">Pelanggan</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">{{ $penugasan->laporan?->user?->name ?? '-' }}</p>
                    <p class="mt-1 text-slate-500">{{ $penugasan->laporan?->user?->phone ?? '-' }}</p>
                </div>
                <div class="rounded-3xl bg-sky-50 p-5">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-700">Status Tugas</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">{{ $penugasan->status_tugas }}</p>
                    <p class="mt-1 text-slate-500">{{ $penugasan->laporan?->alamat ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-3xl border border-blue-200 bg-blue-50 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Catatan Admin</p>
                <p class="mt-2 text-slate-700">{{ $penugasan->laporan?->catatan_admin ?: 'Belum ada catatan tambahan.' }}</p>
            </div>
        </div>
    </div>
@endsection
