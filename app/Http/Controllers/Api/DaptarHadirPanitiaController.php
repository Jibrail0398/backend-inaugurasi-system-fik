<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DaftarHadirPanitia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DaftarHadirPanitiaController extends Controller
{
    /**
     * Scan QR panitia untuk presensi otomatis.
     *
     * @param int $id ID DaftarHadirPanitia
     * @param string $type datang/pulang
     */
    public function scanPresensi($id, $type)
    {
        $daftar = DaftarHadirPanitia::with('penerimaanPanitia.pendaftarPanitia.event')->find($id);

        if (!$daftar) {
            return response()->json([
                'success' => false,
                'message' => 'Panitia tidak ditemukan',
            ], 404);
        }

        $panitia = $daftar->penerimaanPanitia->pendaftarPanitia;
        $event   = $panitia->event;
        $now     = Carbon::now();

        // Presensi datang
        if ($type === 'datang') {
            if ($daftar->presensi_datang !== 'hadir') {
                $daftar->update([
                    'presensi_datang' => 'hadir',
                    'waktu_presensi_datang' => $now,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Presensi datang berhasil dicatat',
                    'data'    => [
                        'panitia' => $panitia,
                        'event'   => $event,
                        'presensi' => $daftar,
                        'waktu'   => $now->toDateTimeString(),
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Panitia sudah presensi datang',
                'data'    => $daftar
            ], 400);
        }

        // Presensi pulang
        if ($type === 'pulang') {
            if ($daftar->presensi_datang === 'hadir' && $daftar->presensi_pulang !== 'pulang') {
                $daftar->update([
                    'presensi_pulang' => 'pulang',
                    'waktu_presensi_pulang' => $now,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Presensi pulang berhasil dicatat',
                    'data'    => [
                        'panitia' => $panitia,
                        'event'   => $event,
                        'presensi' => $daftar,
                        'waktu'   => $now->toDateTimeString(),
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Panitia belum presensi datang atau sudah presensi pulang',
                'data'    => $daftar
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tipe presensi tidak valid (gunakan datang/pulang)'
        ], 400);
    }
}
