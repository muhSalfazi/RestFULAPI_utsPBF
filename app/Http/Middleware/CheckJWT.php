<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckJWT
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token sudah habis masa berlakunya'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token tidak disediakan'], 401);
        }

        return $next($request);
    }
}