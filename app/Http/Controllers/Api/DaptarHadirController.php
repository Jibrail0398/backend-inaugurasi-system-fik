<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DaftarHadirPeserta;
use App\Mail\QrCodeMail;
use Illuminate\Support\Facades\Mail;

class DaftarHadirController extends Controller
{
    /**
     * Kirim QR Code daftar hadir ke email peserta berdasarkan ID daftar hadir
     */
    public function kirimEmail($id)
    {
        $daftar = DaftarHadirPeserta::with('penerimaanPeserta.pendaftarPeserta')->findOrFail($id);
        $peserta = $daftar->penerimaanPeserta->pendaftarPeserta;

        // Pastikan ada QR code
        if (!$daftar->qr_code) {
            return response()->json([
                'message' => 'QR Code belum dibuat untuk peserta ini.'
            ], 400);
        }

        // Path QR code
        $qrPath = storage_path('app/public/' . $daftar->qr_code);

        // Kirim email dengan lampiran QR Code
        Mail::to($peserta->email)->send(new QrCodeMail($peserta, $qrPath));

        return response()->json([
            'message' => 'QR Code daftar hadir berhasil dikirim',
            'email' => $peserta->email,
            'qr_code_url' => asset('storage/' . $daftar->qr_code),
        ]);
    }

    /**
     * Kirim QR Code daftar hadir ke semua peserta sekaligus
     */
    public function kirimMassal()
    {
        $daftars = DaftarHadirPeserta::with('penerimaanPeserta.pendaftarPeserta')->get();

        foreach ($daftars as $daftar) {
            $peserta = $daftar->penerimaanPeserta->pendaftarPeserta;

            if ($daftar->qr_code && $peserta->email) {
                $qrPath = storage_path('app/public/' . $daftar->qr_code);
                Mail::to($peserta->email)->send(new QrCodeMail($peserta, $qrPath));
            }
        }

        return response()->json([
            'message' => 'QR Code daftar hadir berhasil dikirim ke semua peserta'
        ]);
    }
}