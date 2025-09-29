<?php

namespace App\Http\Controllers\Api;

use App\Models\panitia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PendaftaranPanitiaController extends Controller
{
    //Fungsi Store Data Pendaftaran Panitia
    public function daftar(Request $request)
    {
        // --- IGNORE ---
        try {
            $validatedData = $request->validate([
                'nama' => 'required|string|max:255',
                'NIM' => 'required|string|max:16|unique:panitia,NIM',
                'angkatan' => 'required|string|max:4',
                'kelas' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'nomor_whatsapp' => 'required|string|max:14',
                'email' => 'required|email|unique:panitia,email',
                'ukuran_kaos' => 'required|string|max:10',
                'nomor_darurat' => 'required|string|max:14',
                'tipe_nomor_darurat' => 'required|string|max:50',
                'divisi' => 'required|string|max:100',
                'riwayat_penyakit' => 'nullable|string|max:500',
                'komitmen1' => 'required|boolean',
                'komitmen2' => 'required|boolean',
            ], [
                'NIM.required' => 'NIM wajib diisi.',
                'NIM.unique' => 'NIM sudah terdaftar.',
                'email.required' => 'Email wajib diisi.',
                'email.unique' => 'Email sudah terdaftar.',
                'email.email' => 'Format email tidak valid.'
            ]);

            $panitia = panitia::create($validatedData);

            return response()->json([
                'message' => 'Pendaftaran berhasil!',
                'data' => $panitia
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Fungsi Get Data Pendaftaran Panitia
    public function get()
    {
        try {
            $panitia = panitia::all();
            if ($panitia->count() > 0) {
                return response()->json([
                    'message' => 'Daftar panitia berhasil diambil',
                    'data' => $panitia
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Tidak ada data panitia',
                    'data' => []
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
