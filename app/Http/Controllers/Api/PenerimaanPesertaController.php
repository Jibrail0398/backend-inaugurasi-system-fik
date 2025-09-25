<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenerimaanPeserta;
use App\Models\DaftarHadirPeserta;
use App\Models\UangMasuk;
use App\Models\Keuangan;
use App\Mail\QrCodeMail;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PenerimaanPesertaController extends Controller
{
    public function index()
    {
        $penerimaan = PenerimaanPeserta::with(['pendaftarPeserta.event', 'daftarHadir'])->get();

        return response()->json([
            'success' => true,
            'data'    => $penerimaan
        ]);
    }

    public function show($id)
    {
        $penerimaan = PenerimaanPeserta::with(['pendaftarPeserta.event', 'daftarHadir'])->find($id);

        if (!$penerimaan) {
            return response()->json([
                'success' => false,
                'message' => 'Penerimaan peserta tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $penerimaan
        ]);
    }

    public function update(Request $request, $id)
    {
        $penerimaan = PenerimaanPeserta::with('pendaftarPeserta.event.keuangan')->find($id);
        if (!$penerimaan) {
            return response()->json([
                'success' => false,
                'message' => 'Penerimaan peserta tidak ditemukan'
            ], 404);
        }
        $user = $request->user ?? null; 

        DB::beginTransaction();
        try {
            $status = $request->status_pembayaran ?? $penerimaan->status_pembayaran;
            $penerimaan->status_pembayaran = $status;
            $penerimaan->update_by = $user ? $user->id : null;

            if ($status === 'lunas') {
                $penerimaan->tanggal_penerimaan = now();
                if (!$penerimaan->konfirmasi_by) {
                    $penerimaan->konfirmasi_by = $user ? $user->id : null;
                }
                $penerimaan->save();

                $pendaftar = $penerimaan->pendaftarPeserta;
                $event = $pendaftar->event;

                // Generate kode peserta jika belum ada
                if (!$pendaftar->kode_peserta) {
                    $lastPeserta = $event->pendaftarPeserta()
                        ->whereNotNull('kode_peserta')
                        ->orderBy('id', 'desc')
                        ->first();

                    $noUrut = 1;
                    if ($lastPeserta && preg_match('/(\d+)$/', $lastPeserta->kode_peserta, $matches)) {
                        $noUrut = (int)$matches[1] + 1;
                    }

                    $kodePeserta = $event->kode_event . '-' . str_pad($noUrut, 3, '0', STR_PAD_LEFT);
                    $pendaftar->update(['kode_peserta' => $kodePeserta]);
                }

                // Cek keuangan
                $keuangan = $event->keuangan;
                if (!$keuangan) {
                    $keuangan = Keuangan::create(['event_id' => $event->id]);
                }

                // Simpan uang masuk
                if ($event->harga_pendaftaran_peserta > 0) {
                    UangMasuk::updateOrCreate(
                        ['peserta_id' => $pendaftar->id, 'keuangan_id' => $keuangan->id],
                        [
                            'jumlah_uang_masuk' => $event->harga_pendaftaran_peserta,
                            'asal_pemasukan'    => "Daftar Event {$event->kode_event} {$pendaftar->nama}",
                            'tanggal_pemasukan' => now()->toDateString(),
                            'bukti_pemasukan'   => $pendaftar->bukti_pembayaran,
                        ]
                    );
                }

                // QR datang & pulang
                $kodeEvent = $event->kode_event ?? 'umum';
                $dir = "qrcodes/{$kodeEvent}";
                if (!Storage::disk('public')->exists($dir)) {
                    Storage::disk('public')->makeDirectory($dir, 0777, true);
                }

                $fileDatang = "{$dir}/{$pendaftar->kode_peserta}_datang.png";
                $filePulang = "{$dir}/{$pendaftar->kode_peserta}_pulang.png";

                // URL dengan tambahan kode_event
                $urlDatang = route('presensi.scan', [
                    'kode_event' => $event->kode_event,
                    'role'       => 'peserta',
                    'id'         => $penerimaan->id,
                    'type'       => 'datang'
                ]);
                $urlPulang = route('presensi.scan', [
                    'kode_event' => $event->kode_event,
                    'role'       => 'peserta',
                    'id'         => $penerimaan->id,
                    'type'       => 'pulang'
                ]);

                // Generate QR Code dengan URL berisi kode_event
                QrCode::format('png')->size(250)
                    ->generate($urlDatang, Storage::disk('public')->path($fileDatang));
                QrCode::format('png')->size(250)
                    ->generate($urlPulang, Storage::disk('public')->path($filePulang));

                DaftarHadirPeserta::updateOrCreate(
                    ['penerimaan_peserta_id' => $penerimaan->id],
                    [
                        'presensi_datang' => 'tidak hadir',
                        'presensi_pulang' => 'belum pulang',
                        'qr_code_datang'  => $fileDatang,
                        'qr_code_pulang'  => $filePulang
                    ]
                );

                // Kirim email QR
                try {
                    Mail::to($pendaftar->email)->send(new QrCodeMail(
                        $pendaftar,
                        Storage::disk('public')->path($fileDatang),
                        Storage::disk('public')->path($filePulang)
                    ));
                } catch (\Exception $e) {
                    \Log::error("Gagal kirim email QR Code: " . $e->getMessage());
                }
            } else {
                $penerimaan->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data penerimaan peserta berhasil diperbarui',
                'data'    => $penerimaan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal update penerimaan peserta',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
