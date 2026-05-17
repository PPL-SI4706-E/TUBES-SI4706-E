@php
    $isEdit = isset($pengumuman);
@endphp

<div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label for="category" class="block text-sm font-medium text-slate-700 mb-2">Kategori</label>
            <select id="category" name="category" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                <option value="">Pilih kategori</option>
                @foreach(['darurat' => 'Darurat', 'jadwal' => 'Jadwal', 'informasi' => 'Informasi'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', $pengumuman->category ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="priority" class="block text-sm font-medium text-slate-700 mb-2">Prioritas</label>
            <select id="priority" name="priority" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                <option value="">Pilih prioritas</option>
                @foreach(['penting' => 'Penting', 'normal' => 'Normal'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', $pengumuman->priority ?? 'normal') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('priority')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="tanggal_post" class="block text-sm font-medium text-slate-700 mb-2">Tanggal</label>
        <input type="date" id="tanggal_post" name="tanggal_post" value="{{ old('tanggal_post', isset($pengumuman) && $pengumuman->tanggal_post ? $pengumuman->tanggal_post->format('Y-m-d') : now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
        @error('tanggal_post')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="judul" class="block text-sm font-medium text-slate-700 mb-2">Judul</label>
        <input type="text" id="judul" name="judul" value="{{ old('judul', $pengumuman->judul ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Masukkan judul pengumuman">
        @error('judul')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="isi" class="block text-sm font-medium text-slate-700 mb-2">Isi Pengumuman</label>
        <textarea id="isi" name="isi" rows="6" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Tulis isi pengumuman">{{ old('isi', $pengumuman->isi ?? '') }}</textarea>
        @error('isi')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" value="1" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked(old('is_published', $pengumuman->is_published ?? true))>
        <span class="text-sm text-slate-700">Publikasikan pengumuman ini ke halaman publik</span>
    </label>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.pengumuman.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-sky-600 text-white hover:bg-sky-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengumuman' }}
        </button>
    </div>
</div>
