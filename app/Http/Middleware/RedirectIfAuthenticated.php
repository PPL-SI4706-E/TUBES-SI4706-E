<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect by role sesuai dengan role user
                return match (auth()->user()->role) {
                    'admin'      => redirect()->route('admin.dashboard'),
                    'petugas'    => redirect()->route('petugas.tugas.index'),
                    'masyarakat' => redirect()->route('warga.laporan.index'),
                    default      => redirect()->route('home'),
                };
            }
        }

        return $next($request);
    }
}
