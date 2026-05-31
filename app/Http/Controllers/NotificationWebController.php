<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * PBI-18 — Web Controller untuk halaman /notifikasi.
 * Render halaman Blade; data dimuat secara async via API JS fetch.
 */
class NotificationWebController extends Controller
{
    public function index(Request $request)
    {
        // Kirim unread count awal ke view (SSR) agar badge navbar langsung ter-render
        $unreadCount = $request->user()->unreadNotifications()->count();
        return view('notifikasi.index', compact('unreadCount'));
    }
}
