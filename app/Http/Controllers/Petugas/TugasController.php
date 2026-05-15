<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penugasan;
use App\Models\PenyelesaianTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TugasController extends Controller
{
    private const PROGRESS_STEPS = [
        'Ditugaskan',
        'Menuju Lokasi',
        'Sedang Dikerjakan',
        'Menunggu Konfirmasi Warga',
        'Selesai',
    ];

    private const STATUS_TRANSITIONS = [
        'Ditugaskan' => 'Menuju Lokasi',
        'Menuju Lokasi' => 'Sedang Dikerjakan',
    ];

    public function index()
    {
        $user = auth()->user();

        $assignments = Penugasan::with([
            'laporan.kategoriLaporan',
            'laporan.ulasan',
            'laporan.user',
            'laporan.wilayah',
            'laporan.mapLokasi',
            'penyelesaianTugas',
        ])
            ->where('user_id', $user->id)
            ->latest('tanggal_penugasan')
            ->latest('id')
            ->get();

        [$activeTasks, $completedTasks] = $this->transformAssignments($assignments);

        if ($activeTasks->isEmpty() && $completedTasks->isEmpty()) {
            [$activeTasks, $completedTasks] = $this->dummyTasksFromSession();
        }

        return view('petugas.tugas.index', [
            'petugas' => [
                'name' => $user->name ?: 'Budi Hartono',
                'role' => 'Petugas Lapangan',
                'initial' => strtoupper(substr($user->name ?: 'Budi Hartono', 0, 1)),
                'avatar_url' => $user->avatar_url,
            ],
            'summary' => [
                'active' => $activeTasks->count(),
                'completed' => $completedTasks->count(),
            ],
            'activeTasks' => $activeTasks,
            'completedTasks' => $completedTasks,
            'progressSteps' => self::PROGRESS_STEPS,
            'openUploadModalId' => old('penugasan_id'),
        ]);
    }

    public function show($penugasan)
    {
        return view('petugas.tugas.show', ['id' => $penugasan]);
    }

    public function updateStatus(Request $request, $id)
    {
        if (! Penugasan::where('user_id', auth()->id())->whereKey($id)->exists()) {
            return $this->updateDummyStatus($request, $id);
        }

        $assignment = Penugasan::with('laporan')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $request->validate([
            'status' => ['required', Rule::in(array_values(self::STATUS_TRANSITIONS))],
        ]);

        $currentStatus = $assignment->status_tugas ?: 'Ditugaskan';
        $expectedNextStatus = self::STATUS_TRANSITIONS[$currentStatus] ?? null;

        if (! $expectedNextStatus || $request->status !== $expectedNextStatus) {
            return back()->with('error', 'Perubahan status tidak valid untuk tugas ini.');
        }

        $assignment->status_tugas = $request->status;
        $assignment->save();

        if ($assignment->laporan && $assignment->laporan->status !== 'selesai') {
            $assignment->laporan->status = 'dikerjakan';
            $assignment->laporan->save();
        }

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }

    public function uploadBukti(Request $request, $id)
    {
        if (! Penugasan::where('user_id', auth()->id())->whereKey($id)->exists()) {
            return $this->uploadDummyProof($request, $id);
        }

        $assignment = Penugasan::with(['laporan', 'penyelesaianTugas'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if (($assignment->status_tugas ?: 'Ditugaskan') !== 'Sedang Dikerjakan') {
            return back()->with('error', 'Bukti hanya dapat dikirim saat tugas sedang dikerjakan.');
        }

        $validated = $request->validate([
            'penugasan_id' => ['required', 'integer'],
            'foto_bukti' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'catatan_perbaikan' => ['nullable', 'string'],
        ], [
            'foto_bukti.required' => 'Foto bukti perbaikan wajib diunggah.',
            'foto_bukti.image' => 'File bukti harus berupa gambar.',
            'foto_bukti.mimes' => 'Format foto bukti harus JPG, JPEG, atau PNG.',
            'foto_bukti.max' => 'Ukuran foto bukti maksimal 2MB.',
        ]);

        $photoPath = $request->file('foto_bukti')->store('bukti-penyelesaian', 'public');

        if ($assignment->penyelesaianTugas?->foto_bukti) {
            Storage::disk('public')->delete($assignment->penyelesaianTugas->foto_bukti);
        }

        PenyelesaianTugas::updateOrCreate(
            ['penugasan_id' => $assignment->id],
            [
                'foto_bukti' => $photoPath,
                'tanggal_selesai' => now()->toDateString(),
                'keterangan' => $validated['catatan_perbaikan'] ?? null,
            ]
        );

        $assignment->status_tugas = 'Menunggu Konfirmasi Warga';
        $assignment->foto_bukti = $photoPath;
        $assignment->save();

        if ($assignment->laporan && $assignment->laporan->status !== 'selesai') {
            $assignment->laporan->status = 'dikerjakan';
            $assignment->laporan->save();
        }

        return back()->with('success', 'Bukti penyelesaian berhasil dikirim. Tugas kini menunggu konfirmasi warga.');
    }

    private function transformAssignments(Collection $assignments): array
    {
        $activeTasks = collect();
        $completedTasks = collect();

        foreach ($assignments as $assignment) {
            $task = $this->mapAssignment($assignment);

            if ($task['status'] === 'Selesai') {
                $completedTasks->push($task);
                continue;
            }

            $activeTasks->push($task);
        }

        return [$activeTasks->values(), $completedTasks->values()];
    }

    private function mapAssignment(Penugasan $assignment): array
    {
        $report = $assignment->laporan;
        $completion = $assignment->penyelesaianTugas;
        $review = $report?->ulasan;
        $customer = $report?->user;
        $location = $report?->mapLokasi;
        $status = $assignment->status_tugas ?: 'Ditugaskan';
        $latitude = $location?->latitude ? (float) $location->latitude : -6.875000;
        $longitude = $location?->longitude ? (float) $location->longitude : 106.771000;
        $shortAddress = $report?->alamat ?? 'Alamat belum tersedia';
        $fullAddress = $this->buildFullAddress($report?->alamat, $report?->wilayah?->nama_wilayah);

        return [
            'penugasan_id' => $assignment->id,
            'id' => $report?->id ?? $assignment->id,
            'number' => '#' . str_pad((string) ($report?->id ?? $assignment->id), 4, '0', STR_PAD_LEFT),
            'category' => $report?->kategoriLaporan?->nama_kategori ?? $report?->judul ?? 'Laporan Gangguan',
            'status' => $status,
            'status_class' => $this->statusClass($status),
            'date' => optional($assignment->tanggal_penugasan ?? $report?->tanggal_lapor)?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'completed_date' => optional($completion?->tanggal_selesai)->format('Y-m-d'),
            'address' => $shortAddress,
            'full_address' => $fullAddress,
            'customer_name' => $customer?->name ?? 'Dewi Lestari',
            'customer_phone' => $customer?->phone ?? '08555666777',
            'description' => $report?->deskripsi ?? 'Deskripsi masalah belum tersedia.',
            'admin_note' => $report?->catatan_admin ?? 'Tidak ada catatan tambahan dari admin.',
            'repair_note' => $completion?->keterangan,
            'rating' => $review?->rating,
            'progress_index' => $this->progressIndex($status),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'coordinates' => number_format($latitude, 6, '.', '') . ', ' . number_format($longitude, 6, '.', ''),
            'google_maps_url' => 'https://www.google.com/maps?q=' . $latitude . ',' . $longitude,
            'next_status' => self::STATUS_TRANSITIONS[$status] ?? null,
            'can_upload' => $status === 'Sedang Dikerjakan',
            'is_waiting_confirmation' => $status === 'Menunggu Konfirmasi Warga',
            'is_dummy' => false,
        ];
    }

    private function progressIndex(string $status): int
    {
        return match ($status) {
            'Ditugaskan' => 0,
            'Menuju Lokasi' => 1,
            'Sedang Dikerjakan' => 2,
            'Menunggu Konfirmasi Warga' => 3,
            'Selesai' => 4,
            default => 0,
        };
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            'Sedang Dikerjakan' => 'bg-violet-100 text-violet-700',
            'Menuju Lokasi' => 'bg-sky-100 text-sky-700',
            'Menunggu Konfirmasi Warga' => 'bg-amber-100 text-amber-700',
            'Selesai' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    private function buildFullAddress(?string $address, ?string $region): array
    {
        $lineOne = $address ?: 'Alamat belum tersedia';
        $lineTwo = $region
            ? 'RT 01/RW 02, Kel. ' . $region . ', Kec. ' . $region
            : 'RT 01/RW 02, Kel. Cibadak, Kec. Cibadak';

        return [$lineOne, $lineTwo];
    }

    private function dummyTasks(): array
    {
        $activeTasks = collect([
            [
                'penugasan_id' => 1002,
                'id' => 1002,
                'number' => '#1002',
                'category' => 'Air Keruh / Berbau',
                'status' => 'Sedang Dikerjakan',
                'status_class' => 'bg-violet-100 text-violet-700',
                'date' => '2026-03-02',
                'completed_date' => null,
                'address' => 'Jl. Kenanga No. 7A No. 7A, RT 01/RW 02, Cibadak',
                'full_address' => [
                    'Jl. Kenanga No. 7A No. 7A',
                    'RT 01/RW 02, Kel. Cibadak, Kec. Cibadak',
                ],
                'customer_name' => 'Dewi Lestari',
                'customer_phone' => '08555666777',
                'description' => 'Air yang keluar dari keran berwarna cokelat dan berbau tanah sejak 2 hari lalu. Seluruh keran di rumah sama.',
                'admin_note' => 'Kirim petugas untuk cek sumber kontaminasi.',
                'repair_note' => null,
                'rating' => null,
                'progress_index' => 2,
                'latitude' => -6.875000,
                'longitude' => 106.771000,
                'coordinates' => '-6.875000, 106.771000',
                'google_maps_url' => 'https://www.google.com/maps?q=-6.875000,106.771000',
                'next_status' => null,
                'can_upload' => true,
                'is_waiting_confirmation' => false,
                'is_dummy' => true,
            ],
        ]);

        $completedTasks = collect([
            [
                'penugasan_id' => 1001,
                'id' => 1001,
                'number' => '#1001',
                'category' => 'Pipa Bocor',
                'status' => 'Selesai',
                'status_class' => 'bg-emerald-100 text-emerald-700',
                'date' => '2026-02-10',
                'completed_date' => '2026-02-10',
                'address' => 'Jl. Merdeka No. 12 No. 12, Sukamaju',
                'full_address' => ['Jl. Merdeka No. 12 No. 12, Sukamaju', ''],
                'customer_name' => 'Andi Pratama',
                'customer_phone' => '081200000004',
                'description' => '',
                'admin_note' => '',
                'repair_note' => 'Pipa retak sepanjang 30cm, sudah diganti dengan pipa baru.',
                'rating' => 5,
                'progress_index' => 4,
                'latitude' => -6.914744,
                'longitude' => 107.609810,
                'coordinates' => '-6.914744, 107.609810',
                'google_maps_url' => 'https://www.google.com/maps?q=-6.914744,107.609810',
                'next_status' => null,
                'can_upload' => false,
                'is_waiting_confirmation' => false,
                'is_dummy' => true,
            ],
            [
                'penugasan_id' => 1005,
                'id' => 1005,
                'number' => '#1005',
                'category' => 'Pipa Bocor',
                'status' => 'Selesai',
                'status_class' => 'bg-emerald-100 text-emerald-700',
                'date' => '2026-01-21',
                'completed_date' => '2026-01-21',
                'address' => 'Jl. Mawar No. 3 No. 3, Cibadak',
                'full_address' => ['Jl. Mawar No. 3 No. 3, Cibadak', ''],
                'customer_name' => 'Nur Halimah',
                'customer_phone' => '081200000006',
                'description' => '',
                'admin_note' => '',
                'repair_note' => 'Pipa utama diganti 2 meter. Aliran normal kembali.',
                'rating' => 5,
                'progress_index' => 4,
                'latitude' => -6.874100,
                'longitude' => 106.773000,
                'coordinates' => '-6.874100, 106.773000',
                'google_maps_url' => 'https://www.google.com/maps?q=-6.874100,106.773000',
                'next_status' => null,
                'can_upload' => false,
                'is_waiting_confirmation' => false,
                'is_dummy' => true,
            ],
            [
                'penugasan_id' => 1006,
                'id' => 1006,
                'number' => '#1006',
                'category' => 'Pipa Tersumbat',
                'status' => 'Selesai',
                'status_class' => 'bg-emerald-100 text-emerald-700',
                'date' => '2026-03-06',
                'completed_date' => '2026-03-06',
                'address' => 'Jl. Raya Sumedang No. 10 No. 10, Mekarjaya',
                'full_address' => ['Jl. Raya Sumedang No. 10 No. 10, Mekarjaya', ''],
                'customer_name' => 'Dewi Lestari',
                'customer_phone' => '08555666777',
                'description' => '',
                'admin_note' => '',
                'repair_note' => 'Pipa tersumbat kerak, sudah dibersihkan dan aliran lancar.',
                'rating' => null,
                'progress_index' => 4,
                'latitude' => -6.903000,
                'longitude' => 107.784500,
                'coordinates' => '-6.903000, 107.784500',
                'google_maps_url' => 'https://www.google.com/maps?q=-6.903000,107.784500',
                'next_status' => null,
                'can_upload' => false,
                'is_waiting_confirmation' => false,
                'is_dummy' => true,
            ],
            [
                'penugasan_id' => 1009,
                'id' => 1009,
                'number' => '#1009',
                'category' => 'Pipa Bocor',
                'status' => 'Selesai',
                'status_class' => 'bg-emerald-100 text-emerald-700',
                'date' => '2026-01-16',
                'completed_date' => '2026-01-16',
                'address' => 'Jl. Anggrek No. 8 No. 8, Mekarjaya',
                'full_address' => ['Jl. Anggrek No. 8 No. 8, Mekarjaya', ''],
                'customer_name' => 'Siti Aminah',
                'customer_phone' => '081200000003',
                'description' => '',
                'admin_note' => '',
                'repair_note' => 'Sambungan pipa diperbaiki, lem dan klem baru.',
                'rating' => 5,
                'progress_index' => 4,
                'latitude' => -6.901500,
                'longitude' => 107.781100,
                'coordinates' => '-6.901500, 107.781100',
                'google_maps_url' => 'https://www.google.com/maps?q=-6.901500,107.781100',
                'next_status' => null,
                'can_upload' => false,
                'is_waiting_confirmation' => false,
                'is_dummy' => true,
            ],
            [
                'penugasan_id' => 1010,
                'id' => 1010,
                'number' => '#1010',
                'category' => 'Sambungan Baru',
                'status' => 'Selesai',
                'status_class' => 'bg-emerald-100 text-emerald-700',
                'date' => '2026-03-09',
                'completed_date' => '2026-03-09',
                'address' => 'Jl. Merdeka No. 30 No. 30, Sukamaju',
                'full_address' => ['Jl. Merdeka No. 30 No. 30, Sukamaju', ''],
                'customer_name' => 'Andi Pratama',
                'customer_phone' => '081200000004',
                'description' => '',
                'admin_note' => '',
                'repair_note' => 'Survey jalur selesai, menunggu material.',
                'rating' => null,
                'progress_index' => 4,
                'latitude' => -6.920000,
                'longitude' => 107.610000,
                'coordinates' => '-6.920000, 107.610000',
                'google_maps_url' => 'https://www.google.com/maps?q=-6.920000,107.610000',
                'next_status' => null,
                'can_upload' => false,
                'is_waiting_confirmation' => false,
                'is_dummy' => true,
            ],
        ]);

        return [$activeTasks, $completedTasks];
    }

    private function dummyTasksFromSession(): array
    {
        $tasks = Session::get('petugas_dummy_tasks');

        if (! $tasks) {
            [$defaultActiveTasks, $defaultCompletedTasks] = $this->dummyTasks();

            $tasks = [
                'active' => $defaultActiveTasks->values()->all(),
                'completed' => $defaultCompletedTasks->values()->all(),
            ];

            Session::put('petugas_dummy_tasks', $tasks);
        }

        return [
            collect($tasks['active'] ?? []),
            collect($tasks['completed'] ?? []),
        ];
    }

    private function updateDummyStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_values(self::STATUS_TRANSITIONS))],
        ]);

        [$activeTasks, $completedTasks] = $this->dummyTasksFromSession();
        $taskIndex = $activeTasks->search(fn ($task) => (string) $task['penugasan_id'] === (string) $id);

        if ($taskIndex === false) {
            return back()->with('error', 'Tugas dummy tidak ditemukan.');
        }

        $task = $activeTasks[$taskIndex];
        $currentStatus = $task['status'] ?? 'Ditugaskan';
        $expectedNextStatus = self::STATUS_TRANSITIONS[$currentStatus] ?? null;

        if (! $expectedNextStatus || $request->status !== $expectedNextStatus) {
            return back()->with('error', 'Perubahan status tidak valid untuk tugas ini.');
        }

        $task['status'] = $request->status;
        $task['status_class'] = $this->statusClass($request->status);
        $task['progress_index'] = $this->progressIndex($request->status);
        $task['next_status'] = self::STATUS_TRANSITIONS[$request->status] ?? null;
        $task['can_upload'] = $request->status === 'Sedang Dikerjakan';
        $task['is_waiting_confirmation'] = false;

        $activeTasks[$taskIndex] = $task;

        Session::put('petugas_dummy_tasks', [
            'active' => $activeTasks->values()->all(),
            'completed' => $completedTasks->values()->all(),
        ]);

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }

    private function uploadDummyProof(Request $request, $id)
    {
        $validated = $request->validate([
            'penugasan_id' => ['required', 'integer'],
            'foto_bukti' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'catatan_perbaikan' => ['nullable', 'string'],
        ], [
            'foto_bukti.required' => 'Foto bukti perbaikan wajib diunggah.',
            'foto_bukti.image' => 'File bukti harus berupa gambar.',
            'foto_bukti.mimes' => 'Format foto bukti harus JPG, JPEG, atau PNG.',
            'foto_bukti.max' => 'Ukuran foto bukti maksimal 2MB.',
        ]);

        [$activeTasks, $completedTasks] = $this->dummyTasksFromSession();
        $taskIndex = $activeTasks->search(fn ($task) => (string) $task['penugasan_id'] === (string) $id);

        if ($taskIndex === false) {
            return back()->with('error', 'Tugas dummy tidak ditemukan.');
        }

        $task = $activeTasks[$taskIndex];

        if (($task['status'] ?? 'Ditugaskan') !== 'Sedang Dikerjakan') {
            return back()->with('error', 'Bukti hanya dapat dikirim saat tugas sedang dikerjakan.');
        }

        $photoPath = $request->file('foto_bukti')->store('bukti-penyelesaian', 'public');

        $task['status'] = 'Menunggu Konfirmasi Warga';
        $task['status_class'] = $this->statusClass('Menunggu Konfirmasi Warga');
        $task['progress_index'] = $this->progressIndex('Menunggu Konfirmasi Warga');
        $task['next_status'] = null;
        $task['can_upload'] = false;
        $task['is_waiting_confirmation'] = true;
        $task['repair_note'] = $validated['catatan_perbaikan'] ?? null;
        $task['proof_photo_path'] = $photoPath;

        $activeTasks[$taskIndex] = $task;

        Session::put('petugas_dummy_tasks', [
            'active' => $activeTasks->values()->all(),
            'completed' => $completedTasks->values()->all(),
        ]);

        return back()->with('success', 'Bukti penyelesaian berhasil dikirim. Tugas kini menunggu konfirmasi warga.');
    }
}
