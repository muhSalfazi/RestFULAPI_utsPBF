<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Request;

class AllowGoogleTokenRequest
{
    public function handle($request, Closure $next)
    {
        // Periksa apakah URL request adalah https://oauth2.googleapis.com/token
        if (Request::is('https://oauth2.googleapis.com/token')) {
            // Jika ya, lanjutkan ke proses selanjutnya tanpa memvalidasi token JWT
            return $next($request);
        }

        // Jika bukan, lanjutkan dengan validasi token JWT seperti biasa
        return app(\App\Http\Middleware\CheckJWT::class)->handle($request, $next);
    }
}