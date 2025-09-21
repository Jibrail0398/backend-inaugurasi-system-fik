<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenerimaanPeserta;
use App\Models\PenerimaanPanitia;

class PresensiController extends Controller
{
public function scan(Request $request)
    {
        $request->validate([
            'role' => 'required|in:peserta,panitia',
            'type' => 'required|in:datang,pulang',
            'id'   => 'nullable|integer',
            'kode' => 'nullable|string',
        ]);

        // Tentukan model sesuai role
        if ($request->role === 'peserta') {
            $query = PenerimaanPeserta::with(['daftarHadir', 'pendaftarPeserta']);
            
            if ($request->filled('id')) {
                $penerimaan = $query->find($request->id);
            } elseif ($request->filled('kode')) {
                $penerimaan = $query->whereHas('pendaftarPeserta', function($q) use ($request) {
                    $q->where('kode_peserta', $request->kode);
                })->first();
            }
        } else { // panitia
            $query = PenerimaanPanitia::with(['daftarHadir', 'pendaftaranPanitia']);
            
            if ($request->filled('id')) {
                $penerimaan = $query->find($request->id);
            } elseif ($request->filled('kode')) {
                $penerimaan = $query->whereHas('pendaftaranPanitia', function($q) use ($request) {
                    $q->where('kode_panitia', $request->kode);
                })->first();
            }
        }

        if (!$penerimaan) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($request->role) . ' tidak ditemukan'
            ], 404);
        }

        $daftar = $penerimaan->daftarHadir;

        if (!$daftar) {
            return response()->json([
                'success' => false,
                'message' => 'Data daftar hadir belum tersedia'
            ], 404);
        }

        // Update presensi
        if ($request->type === 'datang') {
            if ($daftar->presensi_datang !== 'hadir') {
                $daftar->update([
                    'presensi_datang' => 'hadir',
                    'waktu_presensi_datang' => now()
                ]);
                $message = 'Presensi datang berhasil dicatat';
            } else {
                $message = 'Sudah presensi datang';
            }
        } else { // pulang
            if ($daftar->presensi_pulang !== 'pulang') {
                $daftar->update([
                    'presensi_pulang' => 'pulang',
                    'waktu_presensi_pulang' => now()
                ]);
                $message = 'Presensi pulang berhasil dicatat';
            } else {
                $message = 'Sudah presensi pulang';
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $daftar
        ]);
    }
}
