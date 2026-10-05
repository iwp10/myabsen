<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->sudahTidakAktif()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.')
                ->withErrors(['username' => 'Akun ini sudah tidak aktif. Silakan hubungi admin sekolah.']);
        }

        return $next($request);
    }
}
