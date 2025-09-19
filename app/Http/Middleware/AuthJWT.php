<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Models\User;

class AuthJWT
{
    public function handle(Request $request, Closure $next)
    {
        $token = str_replace('Bearer ', '', $request->bearerToken());
        if (!$token) {
            return response()->json(['success'=>false,'message'=>'Token tidak ditemukan'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            $user = User::find($decoded->sub);

            if (!$user) {
                return response()->json(['success'=>false,'message'=>'User tidak ditemukan'], 404);
            }

            $request->user = $user;

            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['success'=>false,'message'=>'Token sudah kadaluarsa'], 401);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Token tidak valid'], 401);
        }
    }
}
