<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\peserta;

class PendaftaranPesertaController extends Controller
{

    //Fungsi Store Data Pendaftaran Peserta
    public function daftar(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama' => 'required|string|max:255',
                'NIM' => 'required|string|max:16|unique:peserta,NIM',
                'email' => 'required|email|unique:peserta,email',
                'nomor_whatsapp' => 'required|string|max:14',
                'angkatan' => 'required|string|max:4',
                'kelas' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'ukuran_kaos' => 'required|string|max:10',
                'nomor_darurat' => 'required|string|max:14',
                'tipe_nomor_darurat' => 'required|string|max:50',
                'riwayat_penyakit' => 'nullable|string|max:500',
                'bukti_pembayaran' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ], [
                'NIM.required' => 'NIM wajib diisi.',
                'NIM.unique' => 'NIM sudah terdaftar.',
                'email.required' => 'Email wajib diisi.',
                'email.unique' => 'Email sudah terdaftar.',
                'email.email' => 'Format email tidak valid.'
            ]);

            $peserta = peserta::create($validatedData);

            return response()->json([
                'message' => 'Pendaftaran berhasil!',
                'data' => $peserta
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

    //Fungsi Get Data Pendaftaran Peserta
    public function get()
    {
        try {
            $peserta = peserta::all();
            if ($peserta->count() > 0) {
                return response()->json([
                    'message' => 'Daftar peserta berhasil diambil',
                    'data' => $peserta
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Belum ada peserta yang terdaftar',
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
