@extends('layouts.petugas')

@section('title', 'Notifikasi')

@section('content')
    <section class="mx-auto max-w-4xl space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900">Notifikasi</h1>
                <p class="mt-2 text-lg text-slate-500">Lihat pembaruan tugas dan pengingat penting untuk petugas lapangan.</p>
            </div>
            <div class="inline-flex items-center rounded-2xl bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700">
                {{ $notificationCount }} notifikasi belum dibaca
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($notifications as $notification)
                <article class="rounded-[1.8rem] border {{ $notification['is_unread'] ? 'border-sky-200 bg-white shadow-sm' : 'border-slate-200 bg-white/80' }} px-5 py-5">
                    <div class="flex gap-4">
                        <div class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $notification['is_unread'] ? 'bg-sky-100 text-sky-600' : 'bg-slate-100 text-slate-500' }}">
                            <i data-lucide="bell" class="h-5 w-5"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                <h2 class="text-xl font-semibold text-slate-900">{{ $notification['title'] }}</h2>
                                <span class="text-sm text-slate-400">{{ $notification['time'] }}</span>
                            </div>
                            <p class="mt-2 text-slate-600">{{ $notification['message'] }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.8rem] border border-dashed border-slate-200 bg-white/70 px-6 py-10 text-center text-slate-500 shadow-sm">
                    Belum ada notifikasi.
                </div>
            @endforelse
        </div>
    </section>
@endsection
