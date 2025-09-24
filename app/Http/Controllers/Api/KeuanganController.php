<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use Illuminate\Http\Request;


class KeuanganController extends Controller
{
    // Tampilkan semua keuangan dengan saldo tersimpan
    public function index()
    {
        $data = Keuangan::with(['event', 'uangMasuk', 'uangKeluar'])->get();

        $data->map(function ($item) {
            $totalMasuk  = $item->uangMasuk->sum('jumlah_uang_masuk');
            $totalKeluar = $item->uangKeluar->sum('jumlah_pengeluaran');
            $saldo       = $totalMasuk - $totalKeluar;

            // simpan ke database
            $item->update(['saldo' => $saldo]);

            // tambahkan ke response juga
            $item->total_masuk  = $totalMasuk;
            $item->total_keluar = $totalKeluar;
            $item->saldo        = $saldo;

            return $item;
        });

        return response()->json([
            'success' => true,
            'data'    => $data
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

        $totalMasuk  = $keuangan->uangMasuk->sum('jumlah_uang_masuk');
        $totalKeluar = $keuangan->uangKeluar->sum('jumlah_pengeluaran');
        $saldo       = $totalMasuk - $totalKeluar;

        // simpan ke database
        $keuangan->update(['saldo' => $saldo]);

        // tambahkan ke response juga
        $keuangan->total_masuk  = $totalMasuk;
        $keuangan->total_keluar = $totalKeluar;
        $keuangan->saldo        = $saldo;

        return response()->json([
            'success' => true,
            'data'    => $keuangan
        ]);
    }
}
