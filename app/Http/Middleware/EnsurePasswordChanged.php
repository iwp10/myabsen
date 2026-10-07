<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Jangan menginterupsi jika akun sudah tidak aktif (biarkan RoleMiddleware menangani pemutusan sesi)
        if ($user->sudahTidakAktif()) {
            return $next($request);
        }

        if ($user->must_change_password) {
            // Hanya route profil, ganti password, dan logout yang boleh diakses
            if ($request->routeIs('profile.edit', 'password.update', 'logout')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                abort(403, 'Anda harus mengganti password awal sebelum melanjutkan.');
            }

            return redirect()->route('profile.edit')
                ->with('warning', 'Anda harus mengganti password awal sebelum melanjutkan.');
        }

        return $next($request);
    }
}
