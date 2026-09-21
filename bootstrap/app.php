<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $role = $request->user()?->role;
            if ($role === 'admin') return route('admin.dashboard');
            if ($role === 'guru') return route('guru.dashboard');
            return route('siswa.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
