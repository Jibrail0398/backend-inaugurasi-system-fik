<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Mail\QrCodeMail;
use App\Models\DaftarHadirPanitia;

class PenerimaanPanitia extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_panitia';

    protected $fillable = [
        'pendaftaran_panitia_id',
        'status_penerimaan',
        'tanggal_penerimaan',
    ];

    protected static function booted()
    {
        static::created(function ($penerimaan) {
            $panitia = $penerimaan->pendaftarPanitia;

            // ambil kode event dari relasi
            $kodeEvent = $panitia->event->kode_event ?? 'umum';

            // Buat folder qr_codes/{kode_event} kalau belum ada
            $dir = storage_path("app/public/qrcodes/{$kodeEvent}/");
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            // Generate QR baru
            $fileName = 'qrcode_panitia_' . $penerimaan->id . '.png';
            $filePath = $dir . $fileName;

            QrCode::format('png')->size(300)->generate(
                route('presensi.scan', ['id' => $penerimaan->id]),
                $filePath
            );

            // Simpan daftar hadir panitia
            $daftar = DaftarHadirPanitia::create([
                'penerimaan_panitia_id' => $penerimaan->id,
                'presensi_datang' => 'tidak hadir',
                'presensi_pulang' => 'belum pulang',
                'qr_code' => "qrcodes/{$kodeEvent}/" . $fileName,
            ]);

            // Kirim email dengan QR Code
            $qrPath = storage_path('app/public/' . $daftar->qr_code);
            Mail::to($panitia->email)->send(new QrCodeMail($panitia, $qrPath));
        });
    }

    public function pendaftarPanitia()
    {
        return $this->belongsTo(PendaftarPanitia::class, 'pendaftaran_panitia_id');
    }

    public function daftarHadir()
    {
        return $this->hasOne(DaftarHadirPanitia::class, 'penerimaan_panitia_id');
    }
}
