<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keuangan;
use App\Models\UangMasuk;
use App\Models\UangKeluar;
use Illuminate\Support\Facades\Validator;

class KeuanganController extends Controller
{
    // Tampilkan semua keuangan
    public function index()
    {
        $data = Keuangan::with(['event', 'uangMasuk', 'uangKeluar'])->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // Tampilkan satu keuangan per event
    public function show($id)
    {
        $keuangan = Keuangan::with(['event', 'uangMasuk', 'uangKeluar'])->find($id);

        if (!$keuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Data keuangan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $keuangan
        ]);
    }

    // Buat saldo awal keuangan
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'saldo' => 'required|integer',
            'event_id' => 'required|exists:event,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $keuangan = Keuangan::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Keuangan berhasil dibuat',
            'data' => $keuangan
        ], 201);
    }

    // Update saldo
    public function update(Request $request, $id)
    {
        $keuangan = Keuangan::find($id);

        if (!$keuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Data keuangan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'saldo' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $keuangan->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil diperbarui',
            'data' => $keuangan
        ]);
    }

    // Hapus keuangan
    public function destroy($id)
    {
        $keuangan = Keuangan::find($id);

        if (!$keuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Data keuangan tidak ditemukan'
            ], 404);
        }

        $keuangan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keuangan berhasil dihapus'
        ]);
    }

    // Hitung total saldo saat ini (saldo awal + masuk - keluar)
    public function totalSaldo($id)
    {
        $keuangan = Keuangan::with(['uangMasuk', 'uangKeluar'])->find($id);

        if (!$keuangan) {
            return response()->json([
                'success' => false,
                'message' => 'Data keuangan tidak ditemukan'
            ], 404);
        }

        $totalMasuk = $keuangan->uangMasuk->sum('jumlah_uang_masuk');
        $totalKeluar = $keuangan->uangKeluar->sum('jumlah_pengeluaran');
        $totalSaldo = ($keuangan->saldo ?? 0) + $totalMasuk - $totalKeluar;

        return response()->json([
            'success' => true,
            'total_saldo' => $totalSaldo,
            'saldo_awal' => $keuangan->saldo,
            'total_uang_masuk' => $totalMasuk,
            'total_uang_keluar' => $totalKeluar,
        ]);
    }
}
