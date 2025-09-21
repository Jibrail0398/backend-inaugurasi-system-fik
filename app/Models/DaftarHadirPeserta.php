<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DaftarHadirPeserta extends Model
{
    use HasFactory;

    protected $table = 'daftar_hadir_peserta';
    protected $fillable = [
        'penerimaan_peserta_id',
        'presensi_datang',
        'waktu_presensi_datang',
        'presensi_pulang',
        'waktu_presensi_pulang',
        'qr_code'
    ];

    public function penerimaanPeserta()
    {
        return $this->belongsTo(PenerimaanPeserta::class);
    }
    
}
