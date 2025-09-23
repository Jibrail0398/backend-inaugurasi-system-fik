<?php

namespace App\Http\Controllers\Api;
use App\Models\penerimaan_panitia;
use App\Models\panitia;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class PenerimaanPanitiaController extends Controller
    // Menampilkan semua panitia (diterima dan ditolak)

{
    // Menampilkan semua panitia yang ditolak
    public function semuaDitolak()
    {
        try {
            $ditolak = penerimaan_panitia::with('panitia')
                ->where('status_penerimaan', 'ditolak')
                ->get();

            return response()->json([
                'message' => 'Daftar panitia yang ditolak berhasil diambil',
                'data' => $ditolak
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //
    public function pending()
    {
        try {
            $pending = panitia::whereDoesntHave('penerimaanPanitia')->get();

            return response()->json([
                'message' => 'Daftar panitia pending berhasil diambil',
                'data' => $pending
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function diterima()
    {
        try {
            $diterima = penerimaan_panitia::with('panitia')->get();

            return response()->json([
                'message' => 'Daftar panitia diterima berhasil diambil',
                'data' => $diterima
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $panitia = panitia::with('penerimaanPanitia')->findOrFail($id);

            return response()->json([
                'message' => 'Detail panitia berhasil diambil',
                'data' => $panitia
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function terima(Request $request, $id)
    {
        try {
            $panitia = panitia::findOrFail($id);

            $penerimaan = new penerimaan_panitia([
                'nama' => $panitia->nama,
                'NIM' => $panitia->NIM,
                'tanggal_penerimaan' => now(),
                'status_penerimaan' => 'diterima',
                'id_panitia' => $panitia->id,
            ]);
            $panitia->penerimaanPanitia()->save($penerimaan);

            return response()->json([
                'message' => 'Panitia berhasil diterima',
                'data' => $penerimaan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function tolak(Request $request, $id)
    {
        try {
            $panitia = panitia::findOrFail($id);

            $penerimaan = new penerimaan_panitia([
                'nama' => $panitia->nama,
                'NIM' => $panitia->NIM,
                'tanggal_penerimaan' => now(),
                'status_penerimaan' => 'ditolak',
                'id_panitia' => $panitia->id,
            ]);
            $panitia->penerimaanPanitia()->save($penerimaan);

            return response()->json([
                'message' => 'Panitia berhasil ditolak',
                'data' => $penerimaan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function semuaPanitia()
    {
        try {
            $semua = penerimaan_panitia::with('panitia')->get();

            return response()->json([
                'message' => 'Daftar semua panitia berhasil diambil',
                'data' => $semua
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
