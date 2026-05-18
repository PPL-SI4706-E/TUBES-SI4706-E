<form method="GET" action="{{ route('admin.laporan.index') }}"
      class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    @csrf

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <label for="keyword" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Pencarian</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <i data-lucide="search" class="h-4 w-4"></i>
                </span>
                <input
                    id="keyword"
                    type="text"
                    name="keyword"
                    value="{{ old('keyword', request('keyword')) }}"
                    maxlength="100"
                    placeholder="Nama warga, alamat, atau nomor laporan"
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-4 text-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                >
            </div>
            @error('keyword')
                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="status_bayar" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status Bayar</label>
            <select
                id="status_bayar"
                name="status_bayar"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
            >
                <option value="">Semua Status</option>
                <option value="lunas" {{ old('status_bayar', request('status_bayar')) === 'lunas' ? 'selected' : '' }}>Lunas</option>
                <option value="belum_lunas" {{ old('status_bayar', request('status_bayar')) === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                <option value="menunggu_verifikasi" {{ old('status_bayar', request('status_bayar')) === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
            </select>
            @error('status_bayar')
                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="bulan_awal" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan Awal</label>
            <input
                id="bulan_awal"
                type="month"
                name="bulan_awal"
                value="{{ old('bulan_awal', request('bulan_awal')) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
            >
            @error('bulan_awal')
                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="bulan_akhir" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan Akhir</label>
            <input
                id="bulan_akhir"
                type="month"
                name="bulan_akhir"
                value="{{ old('bulan_akhir', request('bulan_akhir')) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
            >
            @error('bulan_akhir')
                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="wilayah_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Wilayah</label>
            <select
                id="wilayah_id"
                name="wilayah_id"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
            >
                <option value="">Semua Wilayah</option>
                @foreach($wilayahs as $wilayah)
                    <option value="{{ $wilayah->id }}" {{ (string) old('wilayah_id', request('wilayah_id')) === (string) $wilayah->id ? 'selected' : '' }}>
                        {{ $wilayah->nama_wilayah }}
                    </option>
                @endforeach
            </select>
            @error('wilayah_id')
                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-3">
            <label for="kategori_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</label>
            <select
                id="kategori_id"
                name="kategori_id"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
            >
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ (string) old('kategori_id', request('kategori_id')) === (string) $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
            @error('kategori_id')
                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end gap-2 lg:col-span-9 lg:justify-end">
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-sky-700"
            >
                <i data-lucide="filter" class="h-4 w-4"></i>
                Terapkan Filter
            </button>
            <a href="{{ route('admin.laporan.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-200">
                <i data-lucide="x" class="h-4 w-4"></i>
                Reset Filter
            </a>
        </div>
    </div>
</form>
