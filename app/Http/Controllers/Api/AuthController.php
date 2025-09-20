<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    private $secret;

    public function __construct()
    {
        $this->secret = env('JWT_SECRET'); 
    }

    // API untuk login (Login dengan NIM dan password)
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('nim', 'password');

            $user = User::where('nim', $credentials['nim'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login gagal. Email atau password salah.'
                ], 401);
            }

            $payload = [
                'sub' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'iat' => time(),
                'exp' => time() + 60*60*24
            ];

            $token = JWT::encode($payload, $this->secret, 'HS256');

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang kembali.',
                'user' => $user,
                'token' => "Bearer $token"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login. Silakan coba lagi.',
            ], 500);
        }
    }

    // API untuk get data user dari token
    public function me(Request $request)
    {
        try {
            $token = str_replace('Bearer ', '', $request->bearerToken());

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak ditemukan.'
                ], 401);
            }

            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));

            $user = User::find($decoded->sub);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pengguna berhasil diambil.',
                'user' => $user
            ]);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token sudah kadaluarsa.'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    // API untuk logout
    public function logout()
    {
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Silakan hapus token di client.'
        ]);
    }
}
