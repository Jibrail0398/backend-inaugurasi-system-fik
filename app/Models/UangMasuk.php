<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Keuangan;
use App\Models\PendaftarPeserta;


class UangMasuk extends Model
{
    use HasFactory;

    protected $table = 'uang_masuk';

    protected $fillable = [
        'jumlah_uang_masuk',
        'asal_pemasukan',
        'tanggal_pemasukan',
        'bukti_pemasukan',
        'keuangan_id',
        'peserta_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi ke Keuangan
     */
    public function keuangan()
    {
        return $this->belongsTo(Keuangan::class, 'keuangan_id');
    }


    /**
     * Relasi ke PendaftarPeserta
     */
    public function peserta()
    {
        return $this->belongsTo(PendaftarPeserta::class, 'peserta_id');
    }
}
