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
                'message' => 'Data penerimaan peserta tidak ditemukan'
            ], 404);
        }

        // Update status pembayaran
        $status = $request->status_pembayaran ?? $penerimaan->status_pembayaran;
        $penerimaan->status_pembayaran = $status;

        if ($status === 'lunas') {
            $penerimaan->tanggal_penerimaan = now();
            $penerimaan->save();

            $pendaftar = $penerimaan->pendaftarPeserta;
            if (!$pendaftar) {
                return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan'], 404);
            }

            $event = $pendaftar->event;
            if (!$event) {
                return response()->json(['success' => false, 'message' => 'Event peserta tidak ditemukan'], 404);
            }

            // ✅ Generate kode peserta
            if (!$pendaftar->kode_peserta) {
                $lastPeserta = $event->pendaftarPeserta()
                    ->whereNotNull('kode_peserta')
                    ->orderBy('id', 'desc')
                    ->first();

                $noUrut = $lastPeserta
                    ? ((int) substr($lastPeserta->kode_peserta, strlen($event->kode_event) + 1)) + 1
                    : 1;

                $kodePeserta = $event->kode_event . '-' . str_pad($noUrut, 3, '0', STR_PAD_LEFT);

                $pendaftar->update(['kode_peserta' => $kodePeserta]);
            }

            // ✅ Buat/cek keuangan
            $keuangan = $event->keuangan;
            if (!$keuangan) {
                $keuangan = Keuangan::create([
                    'event_id' => $event->id,
                ]);
            }

            // ✅ Simpan uang masuk
            if ($event->harga_pendaftaran_peserta > 0) {
                UangMasuk::updateOrCreate(
                    [
                        'peserta_id'  => $pendaftar->id,
                        'keuangan_id' => $keuangan->id,
                    ],
                    [
                        'jumlah_uang_masuk' => $event->harga_pendaftaran_peserta,
                        'asal_pemasukan'    => $pendaftar->nama,
                        'tanggal_pemasukan' => now()->toDateString(),
                        'bukti_pemasukan'   => $pendaftar->bukti_pembayaran,
                    ]
                );
            }

            // ✅ Generate QR datang & pulang
            $kodeEvent = $event->kode_event ?? 'umum';
            $dir = "qrcodes/{$kodeEvent}";
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir, 0777, true);
            }

            $fileDatang = "{$dir}/{$pendaftar->kode_peserta}_datang.png";
            $filePulang = "{$dir}/{$pendaftar->kode_peserta}_pulang.png";

            QrCode::format('png')->size(250)
                ->generate(route('presensi.scan', ['role'=>'peserta','id'=>$penerimaan->id,'type'=>'datang']),
                    Storage::disk('public')->path($fileDatang));
            QrCode::format('png')->size(250)
                ->generate(route('presensi.scan', ['role'=>'peserta','id'=>$penerimaan->id,'type'=>'pulang']),
                    Storage::disk('public')->path($filePulang));

            DaftarHadirPeserta::updateOrCreate(
                ['penerimaan_peserta_id' => $penerimaan->id],
                [
                    'presensi_datang' => 'tidak hadir',
                    'presensi_pulang' => 'belum pulang',
                    'qr_code_datang'  => $fileDatang,
                    'qr_code_pulang'  => $filePulang
                ]
            );

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


        return response()->json([
            'success' => true,
            'message' => 'Data penerimaan berhasil diperbarui',
            'data'    => $penerimaan
        ]);
    }
}
