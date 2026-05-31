<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PBI-18 — REST API Controller untuk manajemen notifikasi in-app.
 *
 * Endpoint:
 *  GET    /api/notifications           → index()     [Read]
 *  PATCH  /api/notifications/{id}/read → markRead()  [Update]
 *  POST   /api/notifications/read-all  → readAll()   [Update Massal]
 *  DELETE /api/notifications/{id}      → destroy()   [Delete]
 *  DELETE /api/notifications/clear-all → clearAll()  [Delete Massal]
 *
 * Semua endpoint menggunakan middleware auth (session-based).
 * NFR-02: Setiap aksi hanya boleh menyentuh notifikasi milik user aktif.
 */
class NotificationController extends Controller
{
    /**
     * [Read] Ambil daftar notifikasi milik user aktif.
     * NFR-01: Query dibatasi 15 item terbaru per permintaan.
     *
     * Query params opsional:
     *   ?filter=unread    → hanya belum dibaca
     *   ?filter=read      → hanya sudah dibaca
     *   ?search=kata      → cari di title/message
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = $user->notifications()->latest();

        // Filter tab
        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Search by keyword (title atau message di dalam kolom data JSON)
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where('data', 'like', "%{$keyword}%");
        }

        $notifications = $query->limit(15)->get()->map(function ($notif) {
            $data = is_array($notif->data) ? $notif->data : json_decode($notif->data, true);
            return [
                'id'         => $notif->id,
                'type'       => $data['type']    ?? 'system',
                'title'      => $data['title']   ?? 'Notifikasi',
                'message'    => $data['message'] ?? '',
                'icon'       => $data['icon']    ?? 'bell',
                'link'       => $data['link']    ?? null,
                'read'       => !is_null($notif->read_at),
                'created_at' => $notif->created_at?->toISOString(),
            ];
        });

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * [Update] Tandai satu notifikasi sebagai telah dibaca.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notif = $request->user()->notifications()->findOrFail($id);
        $notif->markAsRead();

        return response()->json([
            'message'      => 'Notifikasi ditandai dibaca.',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * [Update Massal] Tandai semua notifikasi sebagai telah dibaca.
     */
    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message'      => 'Semua notifikasi ditandai dibaca.',
            'unread_count' => 0,
        ]);
    }

    /**
     * [Delete] Hapus satu notifikasi secara permanen.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notif = $request->user()->notifications()->findOrFail($id);
        $notif->delete();

        return response()->json([
            'message'      => 'Notifikasi dihapus.',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * [Delete Massal] Hapus seluruh notifikasi milik user aktif.
     */
    public function clearAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'message'      => 'Seluruh notifikasi berhasil dibersihkan.',
            'unread_count' => 0,
        ]);
    }
}
