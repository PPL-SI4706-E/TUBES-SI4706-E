@extends('layouts.admin')

@section('title', 'Buat Pengumuman')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Buat Pengumuman</h1>
        <p class="text-sm text-slate-500 mt-1">Tambahkan pengumuman baru untuk ditampilkan pada sistem TirtaBantu.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.pengumuman.store') }}">
            @csrf
            @include('admin.pengumuman._form')
        </form>
    </div>
</div>
@endsection
