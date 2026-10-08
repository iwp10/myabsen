<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PreventBackHistory
{
    /**
     * Menambahkan header no-cache pada halaman HTML yang memerlukan otentikasi
     * agar browser tidak menyajikan halaman dari cache saat tombol Back ditekan setelah logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Jangan mengutak-atik unduhan berkas (Excel/PDF) atau streamed response
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        $contentDisposition = (string) $response->headers->get('Content-Disposition');
        if (str_contains($contentDisposition, 'attachment')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type');
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
