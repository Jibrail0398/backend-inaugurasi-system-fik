<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarHadirPanitia extends Model
{
    use HasFactory;

    protected $table = 'daptar_hadir_panitia';
    protected $fillable = [
        'penerimaan_panitia_id',
        'presensi_datang',
        'waktu_presensi_datang',
        'presensi_pulang',
        'waktu_presensi_pulang',
        'qr_code_datang',
        'qr_code_pulang'
    ];

    public function penerimaanPanitia()
    {
        return $this->belongsTo(PenerimaanPanitia::class);
    }
}
