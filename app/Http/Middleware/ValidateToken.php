<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ValidateToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token requerido.'], 401);
        }

        $cacheKey = 'token_' . md5($token);

        $valid = Cache::remember($cacheKey, 60, function () use ($token) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ])->get('http://127.0.0.1:8001/api/me');

            return $response->status() === 200;
        });

        if (!$valid) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        return $next($request);
    }
}