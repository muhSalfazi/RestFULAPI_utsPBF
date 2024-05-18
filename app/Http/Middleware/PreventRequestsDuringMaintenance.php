<?php
namespace App\Http\Middleware;

use Closure;

class PreventRequestsDuringMaintenance
{
    public function handle($request, Closure $next)
    {
        // Middleware logic
        return $next($request);
    }
}