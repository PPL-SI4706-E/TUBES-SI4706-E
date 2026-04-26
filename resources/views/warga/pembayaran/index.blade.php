@extends('layouts.warga')

@section('title', 'Pembayaran')

@section('content')
    <div x-data="{ 
        openModal: false, 
        selectedMetode: 'Transfer Bank', 
        selectedTagihan: { id: null, harga: 0, laporan_id: null, judul: '' } 
    }">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Pembayaran</h1>
            <p class="text-slate-500">Kelola tagihan biaya perbaikan dan layanan air</p>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center gap-2 text-slate-400 mb-3">
                    <i data-lucide="receipt" class="w-4 h-4 uppercase"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Tagihan</span>
                </div>
                <div class="text-3xl font-black text-slate-700 leading-none">{{ $stats['total_tagihan'] }}</div>
            </div>
            
            <div class="bg-[#fff5f5] p-5 rounded-2xl border border-red-50 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center gap-2 text-[#e53e3e] mb-3">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-red-400">Belum Dibayar</span>
                </div>
                <div class="text-2xl font-black text-[#9b2c2c] leading-none">Rp {{ number_format($stats['belum_dibayar'], 0, ',', '.') }}</div>
            </div>

            <div class="bg-[#fffaf0] p-5 rounded-2xl border border-amber-50 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center gap-2 text-amber-500 mb-3">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-amber-500/70">Menunggu Verifikasi</span>
                </div>
                <div class="text-3xl font-black text-[#975a16] leading-none">{{ $stats['menunggu_verif'] }}</div>
            </div>

            <div class="bg-[#f0fff4] p-5 rounded-2xl border border-emerald-50 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center gap-2 text-emerald-500 mb-3">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-emerald-600/70">Sudah Lunas</span>
                </div>
                <div class="text-2xl font-black text-[#22543d] leading-none">Rp {{ number_format($stats['sudah_lunas'], 0, ',', '.') }}</div>
            </div>
        </div>


        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4 text-slate-700 font-semibold">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
                <h2>Tagihan Belum Dibayar</h2>
            </div>

            @if($tagihanAktif->count() > 0)
                <div class="grid grid-cols-1 gap-6">
                    @foreach($tagihanAktif as $tagihan)
                        <div
                            class="bg-white border border-red-100 rounded-3xl p-6 shadow-sm relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6 group hover:shadow-xl hover:shadow-red-500/5 transition-all duration-300">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-1">
                                    <span
                                        class="bg-sky-50 text-sky-600 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest shrink-0">Tagihan
                                        #{{ $tagihan->id }}</span>
                                    <span class="text-slate-800 font-bold text-base truncate">{{ $tagihan->laporan->judul }}</span>
                                </div>
                                <p class="text-slate-500 text-xs mb-3 leading-relaxed max-w-2xl truncate">
                                    Biaya perbaikan laporan <b>#{{ $tagihan->laporan->id }}</b> di 
                                    <span class="text-slate-600 font-semibold">{{ $tagihan->laporan->alamat }}</span>
                                </p>
                                <div class="flex items-center gap-4">
                                    <div class="bg-red-50/50 px-3 py-1.5 rounded-xl border border-red-100 flex items-center gap-2">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-red-500"></i>
                                        <div>
                                            <p class="text-[9px] text-red-400 font-bold uppercase leading-none mb-0.5">Jatuh Tempo</p>
                                            <p class="text-[10px] font-black text-red-600 tabular-nums leading-none">
                                                {{ $tagihan->created_at->addHours(24)->format('d M, H:i') }} WIB</p>
                                        </div>
                                    </div>
                                    <div class="hidden md:flex flex-col">
                                         <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Status Laporan
                                         </p>
                                         <span class="text-xs font-bold {{ $tagihan->status_pembayaran === 'Ditolak' ? 'text-red-600' : 'text-slate-600' }} flex items-center gap-1.5">
                                             <div class="w-1.5 h-1.5 rounded-full {{ $tagihan->status_pembayaran === 'Ditolak' ? 'bg-red-500 animate-pulse' : 'bg-amber-500' }}"></div>
                                             {{ $tagihan->status_pembayaran === 'Ditolak' ? 'Pembayaran Ditolak (Upload Ulang)' : 'Menunggu Pembayaran' }}
                                         </span>
                                     </div>
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center md:items-end gap-2 w-full md:w-auto shrink-0 bg-slate-50 md:bg-transparent p-4 md:p-0 rounded-2xl">
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider text-right">Total Tagihan:</div>
                                <div class="text-3xl font-black text-slate-800 mb-1 leading-none">Rp
                                    {{ number_format($tagihan->harga, 0, ',', '.') }}</div>
                                <button @click="
                                            selectedTagihan = { 
                                                id: {{ $tagihan->id }}, 
                                                harga: '{{ number_format($tagihan->harga, 0, ',', '.') }}', 
                                                laporan_id: {{ $tagihan->laporan->id }}, 
                                                judul: '{{ addslashes($tagihan->laporan->judul) }}' 
                                            }; 
                                            openModal = true
                                        "
                                    class="w-full md:w-auto bg-sky-600 hover:bg-sky-700 text-white font-black py-3 px-8 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-sky-200 hover:scale-105 active:scale-95 text-sm uppercase italic tracking-wider">
                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                    BAYAR SEKARANG
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl p-10 text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-400"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Tidak ada tagihan aktif saat ini.</p>
                </div>
            @endif
        </div>


        <div>
            <div class="flex items-center gap-2 mb-4 text-slate-700 font-semibold">
                <i data-lucide="history" class="w-5 h-5 text-sky-500"></i>
                <h2>Riwayat Pembayaran</h2>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Laporan</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Metode</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tgl Bayar
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($riwayat as $item)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sky-600 font-bold">#{{ $item->laporan->id }}</span>
                                            <i data-lucide="external-link" class="w-3 h-3 text-slate-300"></i>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-700 leading-tight">Biaya perbaikan</p>
                                        <p class="text-xs text-slate-400">{{ $item->laporan->judul }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold text-slate-700">Rp
                                            {{ number_format($item->harga, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-xs font-medium text-slate-500">{{ $item->metode_pembayaran ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 italic text-sm text-slate-400">
                                        {{ $item->updated_at->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClass = match ($item->status_pembayaran) {
                                                'Lunas' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'Terverifikasi' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                'Ditolak' => 'bg-red-50 text-red-600 border-red-100',
                                                'Kadaluarsa' => 'bg-slate-100 text-slate-400 border-slate-200',
                                                default => 'bg-slate-50 text-slate-600 border-slate-100',
                                            };
                                            $statusLabel = match ($item->status_pembayaran) {
                                                'Terverifikasi' => 'Proses Verifikasi',
                                                'Kadaluarsa' => 'Hangus',
                                                default => $item->status_pembayaran,
                                            };
                                        @endphp
                                        <span
                                            class="px-2.5 py-1 rounded-full border {{ $statusClass }} text-[10px] font-bold uppercase tracking-wide">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Belum ada riwayat
                                        pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <div x-show="openModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak
            x-transition>
            <div @click.away="openModal = false"
                class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">

                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shadow-sm">
                    <h3 class="font-bold text-slate-800">Pembayaran Laporan #<span
                            x-text="selectedTagihan.laporan_id"></span></h3>
                    <button @click="openModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>


                <div class="flex-1 overflow-y-auto p-6 space-y-6">

                    <div
                        class="bg-gradient-to-br from-sky-50 to-white border border-sky-100 rounded-3xl p-6 text-center shadow-sm">
                        <p class="text-[10px] font-bold text-sky-500 uppercase tracking-widest mb-1">Total Pembayaran</p>
                        <p class="text-4xl font-black text-sky-700 mb-1">Rp <span x-text="selectedTagihan.harga"></span></p>
                        <p class="text-[11px] text-sky-400 font-medium px-4" x-text="selectedTagihan.judul"></p>
                    </div>


                    <div class="bg-slate-50 rounded-[2rem] p-6 border border-slate-100 relative overflow-hidden">
                        <div x-show="selectedMetode === 'Transfer Bank'" x-transition>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Tujuan Transfer:
                            </p>
                            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200">
                                <div>
                                    <p class="text-xs text-slate-400 font-medium mb-1">Bank BRI</p>
                                    <p class="text-lg font-mono font-bold text-slate-700 tracking-tighter">1234 5678 9012
                                        3456</p>
                                    <p class="text-[10px] text-slate-500 font-bold mt-1 uppercase leading-none">a.n. PDAM
                                        TirtaBantu</p>
                                </div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/BRI_2020.svg/512px-BRI_2020.svg.png"
                                    class="h-4 opacity-80" alt="BRI">
                            </div>
                        </div>

                        <div x-show="selectedMetode === 'QRIS / E-Wallet'" x-transition class="text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Scan QRIS
                                Berikut:</p>
                            <div class="bg-white p-4 rounded-3xl inline-block border border-slate-200 shadow-sm mb-3">
                                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=TIRTABANTU-INV-' + selectedTagihan.laporan_id + '-' + selectedTagihan.id"
                                    class="w-[180px] h-[180px]" alt="QRIS Code">
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium italic">Silakan scan menggunakan E-Wallet atau
                                M-Banking Anda</p>
                        </div>

                        <div x-show="selectedMetode === 'Tunai di Kantor'" x-transition
                            class="py-4 text-center">
                            <div class="w-12 h-12 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="info" class="w-6 h-6 text-sky-600"></i>
                            </div>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed px-6">Silakan lakukan pembayaran
                                sesuai instruksi metode pilihan Anda, lalu unggah buktinya di bawah.</p>
                        </div>
                    </div>

                    <form :action="'{{ url('warga/pembayaran') }}/' + selectedTagihan.id + '/upload'" method="POST"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div x-show="selectedMetode !== 'Tunai di Kantor'" x-transition>
                            <div class="flex items-center justify-between mb-3 px-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Upload Bukti
                                    Transfer</label>
                                <span
                                    class="text-[9px] text-sky-500 font-bold bg-sky-50 px-2 py-0.5 rounded-full uppercase">WAJIB
                                    DIISI</span>
                            </div>

                            <div class="relative group">
                                <input type="file" name="bukti_transaksi" id="bukti_transaksi"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    :required="selectedMetode !== 'Tunai di Kantor'"
                                    @change="const file = $event.target.files[0]; if(file) { if(file.size > 5*1024*1024) { alert('Ukuran file terlalu besar, maksimal 5 MB'); $event.target.value = ''; return; } const reader = new FileReader(); reader.onload = (e) => { $refs.preview.src = e.target.result; $refs.placeholder.classList.add('hidden'); $refs.previewContainer.classList.remove('hidden'); }; reader.readAsDataURL(file); }">

                                <div
                                    class="border-2 border-dashed border-slate-200 rounded-[2rem] p-6 text-center transition-all duration-300 group-hover:border-sky-400 group-hover:bg-sky-50/30">
                                    <div x-ref="placeholder" class="py-4">
                                        <div
                                            class="w-14 h-14 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                            <i data-lucide="camera" class="w-6 h-6 text-sky-600"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-700 mb-1">Klik atau seret foto bukti ke sini
                                        </p>
                                        <p class="text-[9px] text-slate-400">Pastikan foto jelas (JPG/PNG, Maks. 5MB)</p>
                                    </div>
                                    <div x-ref="previewContainer" class="hidden relative inline-block mx-auto">
                                        <img x-ref="preview" src=""
                                            class="max-h-40 rounded-2xl shadow-md border-4 border-white">
                                        <div
                                            class="absolute -top-2 -right-2 bg-emerald-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-sm">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 px-1">Metode
                                Pembayaran:</label>
                            <input type="hidden" name="metode_pembayaran" :value="selectedMetode">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <template x-for="metode in ['Transfer Bank', 'QRIS / E-Wallet', 'Tunai di Kantor']">
                                    <button type="button" @click="selectedMetode = metode"
                                        class="p-4 border-[1.5px] rounded-2xl text-left transition-all duration-200 flex items-center gap-4 relative overflow-hidden"
                                        :class="selectedMetode === metode ? 'border-sky-500 bg-sky-50/50 ring-4 ring-sky-500/5' : 'border-slate-100 hover:border-sky-200 hover:bg-slate-50'">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                            :class="selectedMetode === metode ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-400'">
                                            <template x-if="metode === 'Transfer Bank'"><i data-lucide="building-2"
                                                    class="w-4 h-4"></i></template>
                                            <template x-if="metode === 'QRIS / E-Wallet'"><i data-lucide="qr-code"
                                                    class="w-4 h-4"></i></template>
                                            <template x-if="metode === 'Tunai di Kantor'"><i data-lucide="building"
                                                    class="w-4 h-4"></i></template>
                                        </div>
                                        <span class="text-[10px] font-bold tracking-tight leading-tight"
                                            :class="selectedMetode === metode ? 'text-sky-700' : 'text-slate-600'"
                                            x-text="metode"></span>

                                        <div x-show="selectedMetode === metode" class="absolute top-1 right-1">
                                            <div class="bg-sky-500 w-2 h-2 rounded-full shadow-sm"></div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full bg-sky-600 hover:bg-sky-700 text-white font-black py-4 rounded-[1.5rem] transition-all shadow-xl shadow-sky-100 flex items-center justify-center gap-2 group">
                                <span
                                    x-text="selectedMetode === 'Tunai di Kantor' ? 'Konfirmasi Bayar di Kantor' : 'Kirim Pembayaran'"></span>
                                <i data-lucide="arrow-right"
                                    class="w-4 h-4 text-sky-200 group-hover:translate-x-1 transition-transform"></i>
                            </button>
                            <p class="text-[9px] text-center text-slate-400 mt-4 px-6 uppercase tracking-wider font-bold"
                                x-text="selectedMetode === 'Tunai di Kantor' ? 'Silakan datangi kantor PDAM terdekat untuk melunasi tagihan ini' : 'Pastikan data yang diunggah sudah benar dan sesuai instruksi'">
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:initialized', () => {
            lucide.createIcons();
        });
    </script>
@endsection