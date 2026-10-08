<?php

use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'password.changed' => EnsurePasswordChanged::class,
            'no-cache' => PreventBackHistory::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if ($user?->must_change_password) {
                return route('profile.edit');
            }

            $role = $user?->role;
            if ($role === 'admin') {
                return route('admin.dashboard');
            }
            if ($role === 'guru') {
                return route('guru.dashboard');
            }

            return route('siswa.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $handleTokenMismatch = function (Throwable $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch.'], 419);
            }

            if ($request->is('logout')) {
                return redirect()->route('login')->with('status', 'Anda sudah keluar.');
            }

            if (Auth::check()) {
                return redirect()->back(fallback: route('login'))->with('error', 'Halaman kedaluwarsa, silakan ulangi.');
            }

            return redirect()->route('login')->with('status', 'Sesi Anda sudah berakhir. Silakan masuk kembali.');
        };

        $exceptions->render(function (TokenMismatchException $exception, Request $request) use ($handleTokenMismatch) {
            return $handleTokenMismatch($exception, $request);
        });

        $exceptions->render(function (HttpException $exception, Request $request) use ($handleTokenMismatch) {
            if ($exception->getStatusCode() === 419 || $exception->getPrevious() instanceof TokenMismatchException) {
                return $handleTokenMismatch($exception, $request);
            }
        });
    })->create();
