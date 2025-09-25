<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PenerimaanPanitia;
use App\Models\PendaftarPanitia;
use App\Models\DaftarHadirPanitia;
use App\Models\Event;
use App\Mail\QrCodeMail;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class PenerimaanPanitiaController extends Controller
{
    public function __construct()
    {
        // Pastikan middleware JWT dipanggil di route
        // $this->middleware('auth.jwt:admin,mentor');
    }

    public function index()
    {
        $data = PenerimaanPanitia::with(['pendaftarPanitia', 'daftarHadir'])->get();

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function show($id)
    {
        $penerimaan = PenerimaanPanitia::with(['pendaftarPanitia', 'daftarHadir'])->find($id);

        if (!$penerimaan) {
            return response()->json([
                'success' => false,
                'message' => 'Data penerimaan panitia tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $penerimaan
        ]);
    }

    public function update(Request $request, $id)
    {

         $penerimaan = PenerimaanPanitia::with('pendaftarPanitia.event.keuangan')->find($id);
        if (!$penerimaan) {
            return response()->json([
                'success' => false,
                'message' => 'Penerimaan panitia tidak ditemukan'
            ], 404);
        }

        $user = $request->user ?? null;

        DB::beginTransaction();
        try {
            // Update status penerimaan & updated_by
            $penerimaan->status_penerimaan = $request->status_penerimaan ?? $penerimaan->status_penerimaan;
            $penerimaan->update_by = $user ? $user->id : null;

            // Jika diterima
            if (($request->status_penerimaan ?? '') === 'diterima') {
                $penerimaan->tanggal_penerimaan = now();

                // konfirmasi_by hanya diisi sekali
                if (!$penerimaan->konfirmasi_by) {
                    $penerimaan->konfirmasi_by = $user ? $user->id : null;
                }

                $penerimaan->save();

                $pendaftar = $penerimaan->pendaftarPanitia;
                $event = $pendaftar->event;
                $kodeEvent = $event->kode_event ?? 'umum';

                // Generate kode_panitia jika belum ada
                if (!$pendaftar->kode_panitia) {
                    $lastPanitia = PendaftarPanitia::where('event_id', $event->id)
                        ->whereNotNull('kode_panitia')
                        ->orderBy('id', 'desc')
                        ->first();

                    $noUrut = 1;
                    if ($lastPanitia && preg_match('/(\d+)$/', $lastPanitia->kode_panitia, $matches)) {
                        $noUrut = (int) $matches[1] + 1;
                    }

                    $kodePanitia = $kodeEvent . '-PAN-' . str_pad($noUrut, 3, '0', STR_PAD_LEFT);

                    $pendaftar->update(['kode_panitia' => $kodePanitia]);
                }

                // Folder QR
                $dir = "qrcodes/panitia/{$kodeEvent}/";
                if (!Storage::disk('public')->exists($dir)) {
                    Storage::disk('public')->makeDirectory($dir, 0777, true);
                }

                $fileDatang = $dir . $pendaftar->kode_panitia . '_datang.png';

                // Generate QR Datang
                QrCode::format('png')->size(250)
                    ->generate(
                        route('presensi.scan', [
                            'kode_event' => $kodeEvent,
                            'role'       => 'panitia',
                            'id'         => $penerimaan->id,
                            'type'       => 'datang'
                        ]),
                        Storage::disk('public')->path($fileDatang)
                    );

                // Generate QR Pulang
                $filePulang = $dir . $pendaftar->kode_panitia . '_pulang.png';
                QrCode::format('png')->size(250)
                    ->generate(
                        route('presensi.scan', [
                            'kode_event' => $kodeEvent,
                            'role'       => 'panitia',
                            'id'         => $penerimaan->id,
                            'type'       => 'pulang'
                        ]),
                        Storage::disk('public')->path($filePulang)
                    );

                // Daftar hadir
                DaftarHadirPanitia::updateOrCreate(
                    ['penerimaan_panitia_id'=>$penerimaan->id],
                    [
                        'presensi_datang'=>'tidak hadir',
                        'presensi_pulang'=>'belum pulang',
                        'qr_code_datang'=>$fileDatang,
                        'qr_code_pulang'=>$filePulang
                    ]
                );

                // Kirim email QR
                Mail::to($pendaftar->email)->send(
                    new QrCodeMail(
                        $pendaftar,
                        Storage::disk('public')->path($fileDatang),
                        Storage::disk('public')->path($filePulang),
                        'panitia'
                    )
                );

            } else {
                $penerimaan->save(); // status lain
            }

            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Data penerimaan panitia berhasil diperbarui',
                'data'=>$penerimaan->load('pendaftarPanitia')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>'Gagal update penerimaan panitia',
                'error'=>$e->getMessage()
            ], 500);
        }
    }
}
