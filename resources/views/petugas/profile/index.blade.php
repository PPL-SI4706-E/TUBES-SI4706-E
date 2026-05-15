@extends('layouts.petugas')

@section('title', 'Profil Saya')

@section('content')
    <section class="mx-auto max-w-5xl space-y-8">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-slate-900">Manajemen Profil</h1>
            <p class="mt-2 text-lg text-slate-500">Kelola informasi pribadi dan keamanan akun Anda di sini.</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.35fr,0.65fr]">
            <form
                method="POST"
                action="{{ route('petugas.profile.update') }}"
                enctype="multipart/form-data"
                class="overflow-hidden rounded-[1.8rem] border border-sky-100 bg-white shadow-sm"
                x-data="{ previewUrl: '{{ $profile['avatar_url'] }}', selectedFileName: '' }"
            >
                @csrf

                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="flex items-center gap-3 text-2xl font-semibold text-slate-900">
                        <i data-lucide="user-round" class="h-6 w-6 text-blue-600"></i>
                        Informasi Pribadi
                    </h2>
                </div>

                <div class="space-y-6 px-6 py-6">
                    <div class="flex flex-col gap-5 md:flex-row md:items-center">
                        <div class="flex h-28 w-28 overflow-hidden rounded-full bg-slate-100 shadow-inner ring-4 ring-slate-50">
                            <img :src="previewUrl" src="{{ $profile['avatar_url'] }}" alt="Foto Profil" class="h-full w-full object-cover">
                        </div>
                        <div class="space-y-2">
                            <div>
                                <p class="text-2xl font-semibold text-slate-900">Foto Profil</p>
                                <p class="mt-1 text-sm text-slate-500">JPG, JPEG, atau PNG. Maks 2MB.</p>
                            </div>
                            <label class="inline-flex cursor-pointer items-center rounded-xl bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
                                Pilih Foto Baru
                                <input
                                    type="file"
                                    name="avatar"
                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                    class="hidden"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (!file) return;
                                        selectedFileName = file.name;
                                        previewUrl = URL.createObjectURL(file);
                                    "
                                >
                            </label>
                            <p x-show="selectedFileName" x-text="selectedFileName" class="text-sm text-slate-500"></p>
                            @error('avatar')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-lg font-semibold text-slate-800">Nama Lengkap</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $profile['name']) }}"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-lg font-semibold text-slate-800">Email</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $profile['email']) }}"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            >
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-lg font-semibold text-slate-800">Nomor HP</label>
                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone', $profile['phone']) }}"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            >
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-lg font-semibold text-slate-800">Area Kerja (Hanya Petugas)</label>
                        <input
                            type="text"
                            value="{{ $profile['wilayah'] }}"
                            readonly
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-500"
                        >
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-2xl bg-[#2563eb] px-6 py-3 text-base font-semibold text-white transition hover:bg-[#1d4ed8]"
                        >
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>

            <form
                method="POST"
                action="{{ route('petugas.profile.password') }}"
                class="h-fit overflow-hidden rounded-[1.8rem] border border-sky-100 bg-white shadow-sm"
            >
                @csrf

                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="flex items-center gap-3 text-2xl font-semibold text-slate-900">
                        <i data-lucide="shield" class="h-6 w-6 text-blue-600"></i>
                        Keamanan
                    </h2>
                </div>

                <div class="space-y-4 px-6 py-6">
                    <div>
                        <label class="mb-2 block text-lg font-semibold text-slate-800">Password Lama</label>
                        <input
                            type="password"
                            name="current_password"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            placeholder="Masukkan password lama"
                        >
                        @if ($errors->passwordUpdate->has('current_password'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->passwordUpdate->first('current_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="mb-2 block text-lg font-semibold text-slate-800">Password Baru</label>
                        <input
                            type="password"
                            name="new_password"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            placeholder="Minimal 8 karakter"
                        >
                        @if ($errors->passwordUpdate->has('new_password'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->passwordUpdate->first('new_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="mb-2 block text-lg font-semibold text-slate-800">Konfirmasi Password Baru</label>
                        <input
                            type="password"
                            name="new_password_confirmation"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                            placeholder="Ulangi password baru"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-800 px-6 py-3 text-base font-semibold text-white transition hover:bg-slate-900"
                    >
                        <i data-lucide="key-round" class="h-4 w-4"></i>
                        Ganti Password
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
