<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Akses ditolak. Halaman ini hanya dapat diakses oleh Admin.');
        }

        if (! auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        return $next($request);
    }
}