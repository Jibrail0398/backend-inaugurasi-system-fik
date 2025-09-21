<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\penerimaan_peserta;
use App\Models\peserta;

class PenerimaanPeserta extends Controller
{
    // 1. Menampilkan daftar peserta pending (belum diterima)
    public function pending()
    {
        try {
            $pending = peserta::whereDoesntHave('penerimaan')->get();

            return response()->json([
                'message' => 'Daftar peserta pending berhasil diambil',
                'data' => $pending
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 2. Menampilkan daftar peserta diterima
    public function diterima()
    {
        try {
            $diterima = penerimaan_peserta::with('peserta')->get();

            return response()->json([
                'message' => 'Daftar peserta diterima berhasil diambil',
                'data' => $diterima
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 3. Menampilkan detail peserta
    public function show($id)
    {
        try {
            $peserta = peserta::with('penerimaan')->findOrFail($id);

            return response()->json([
                'message' => 'Detail peserta berhasil diambil',
                'data' => $peserta
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 4. Menerima peserta
    public function terima(Request $request, $id)
    {
        try {
            $peserta = peserta::findOrFail($id);

            $penerimaan = penerimaan_peserta::create([
                'nama'               => $peserta->nama,
                'NIM'                => $peserta->NIM,
                'bukti_pembayaran'   => $peserta->bukti_pembayaran,
                'tanggal_penerimaan' => now(),
                'id_peserta'         => $peserta->id,
                'status'             => 'diterima',
            ]);

            return response()->json([
                'message' => 'Peserta berhasil diterima!',
                'data' => $penerimaan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 5. Menolak peserta   
    public function tolak($id)
    {
        $peserta = peserta::find($id);
        if (!$peserta) {
            return response()->json([
                'message' => 'Peserta tidak ditemukan',
            ], 404);
        }

        $penerimaan = penerimaan_peserta::where('id_peserta', $id)->first();
        if ($penerimaan) {
            $penerimaan->status = 'ditolak';
            $penerimaan->save();
        } else {
            $penerimaan = penerimaan_peserta::create([
                'nama' => $peserta->nama,
                'NIM' => $peserta->NIM,
                'bukti_pembayaran' => $peserta->bukti_pembayaran,
                'tanggal_penerimaan' => now(),
                'id_peserta' => $peserta->id,
                'status' => 'ditolak',
            ]);
        }

        return response()->json([
            'message' => 'Peserta berhasil ditolak',
            'data' => $penerimaan
        ], 200);
    }
}
