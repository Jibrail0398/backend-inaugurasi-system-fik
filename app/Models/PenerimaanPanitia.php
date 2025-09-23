<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
        'konfirmasi_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::created(function ($penerimaan) {

            $pendaftar = $penerimaan->pendaftarPanitia;
            $kodeEvent = $pendaftar->event->kode_event ?? 'umum';

            // Buat folder QR jika belum ada
            $dir = "qrcodes/{$kodeEvent}";
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir, 0777, true);
            }

            // File QR
            $fileDatang = "{$dir}/{$pendaftar->kode_panitia}_datang.png";
            $filePulang = "{$dir}/{$pendaftar->kode_panitia}_pulang.png";

            // Generate QR Datang
            QrCode::format('png')->size(250)
                ->generate(route('presensi.scan', [
                    'role' => 'panitia',
                    'id'   => $penerimaan->id,
                    'type' => 'datang'
                ]), Storage::disk('public')->path($fileDatang));

            // Generate QR Pulang
            QrCode::format('png')->size(250)
                ->generate(route('presensi.scan', [
                    'role' => 'panitia',
                    'id'   => $penerimaan->id,
                    'type' => 'pulang'
                ]), Storage::disk('public')->path($filePulang));

            // Buat daftar hadir
            $daftar = DaftarHadirPanitia::create([
                'penerimaan_panitia_id' => $penerimaan->id,
                'presensi_datang' => 'tidak hadir',
                'presensi_pulang' => 'belum pulang',
                'qr_code_datang'  => $fileDatang,
                'qr_code_pulang'  => $filePulang,
            ]);

            // Set tanggal penerimaan
            $penerimaan->tanggal_penerimaan = now();

            // Kirim email dengan 2 QR Code
            Mail::to($pendaftar->email)->send(new QrCodeMail(
                $pendaftar,
                Storage::disk('public')->path($fileDatang),
                Storage::disk('public')->path($filePulang),
                'panitia'
            ));
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

    public function konfirmator()
    {
        return $this->belongsTo(User::class, 'konfirmasi_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
