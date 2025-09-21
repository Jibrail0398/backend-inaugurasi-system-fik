<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Mail\QrCodeMail;
use App\Models\DaftarHadirPeserta;
use Illuminate\Support\Facades\Storage;

class PenerimaanPeserta extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_peserta';
    protected $fillable = ['status_pembayaran','tanggal_penerimaan','pendaptar_peserta_id'];

    protected static function booted()
    {
        static::updating(function ($penerimaan) {

            // Kalau status pembayaran berubah jadi "lunas"
            if ($penerimaan->isDirty('status_pembayaran') && $penerimaan->status_pembayaran === 'lunas') {

                $pendaftar = $penerimaan->pendaftarPeserta;
                $kodeEvent = $pendaftar->event->kode_event ?? 'umum';

                // Buat folder QR jika belum ada
                $dir = "qrcodes/{$kodeEvent}";
                if (!Storage::disk('public')->exists($dir)) {
                    Storage::disk('public')->makeDirectory($dir, 0777, true);
                }

                // File QR
                $fileDatang = "{$dir}/{$pendaftar->kode_peserta}_datang.png";
                $filePulang = "{$dir}/{$pendaftar->kode_peserta}_pulang.png";

                // Generate QR Code
                QrCode::format('png')->size(250)
                    ->generate(route('presensi.scan', [
                        'role' => 'peserta',
                        'id'   => $penerimaan->id,
                        'type' => 'datang'
                    ]), Storage::disk('public')->path($fileDatang));

                QrCode::format('png')->size(250)
                    ->generate(route('presensi.scan', [
                        'role' => 'peserta',
                        'id'   => $penerimaan->id,
                        'type' => 'pulang'
                    ]), Storage::disk('public')->path($filePulang));

                // Buat atau update daftar hadir
                $daftar = DaftarHadirPeserta::updateOrCreate(
                    ['penerimaan_peserta_id' => $penerimaan->id],
                    [
                        'presensi_datang' => 'tidak hadir',
                        'presensi_pulang' => 'belum pulang',
                        'qr_code_datang'  => $fileDatang,
                        'qr_code_pulang'  => $filePulang
                    ]
                );

                // Kirim email
                try {
                    Mail::to($pendaftar->email)->send(
                        new QrCodeMail(
                            $pendaftar,
                            Storage::disk('public')->path($fileDatang),
                            Storage::disk('public')->path($filePulang)
                        )
                    );
                } catch (\Exception $e) {
                    \Log::error("Gagal kirim email QR Code: ".$e->getMessage());
                }

                // Set tanggal penerimaan
                $penerimaan->tanggal_penerimaan = now();
            }
        });
    }

    // Relasi ke peserta
    public function pendaftarPeserta()
    {
         return $this->belongsTo(PendaftarPeserta::class, 'pendaptar_peserta_id');
    }

    // Relasi ke daftar hadir
    public function daftarHadir()
    {
        return $this->hasOne(DaftarHadirPeserta::class, 'penerimaan_peserta_id');
    }
}
