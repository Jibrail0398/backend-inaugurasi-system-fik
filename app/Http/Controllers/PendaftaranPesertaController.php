<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pendaftaran_peserta_model;

class PendaftaranPesertaController extends Controller
{
    public function daftar(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'NIM' => 'required|string|max:16',
            'email' => 'required|email|unique:pendaftar_peserta,email',
            'nomor_whatsapp' => 'required|string|max:14',
            'angkatan' => 'required|string|max:4',
            'kelas' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'ukuran_kaos' => 'required|string|max:10',
            'nomor_darurat' => 'required|string|max:14',
            'tipe_nomor_darurat' => 'required|string|max:50',
            'riwayat_penyakit' => 'nullable|string|max:500',
            'divisi' => 'required|string|max:100',
            'komitmen1' => 'required|in:ya,tidak',
            'komitmen2' => 'required|in:ya,tidak',
        ]);

        pendaftaran_peserta_model::create($validatedData);

        return response()->json(['message' => 'Pendaftaran berhasil!'], 201);
    }

    public function get()
    {
        $peserta = pendaftaran_peserta_model::all();
        return response()->json($peserta);
    }
}
