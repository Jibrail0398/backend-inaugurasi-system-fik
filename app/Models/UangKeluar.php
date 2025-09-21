<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UangKeluar extends Model
{
    use HasFactory;

    protected $table = 'uang_keluar';

    protected $fillable = [
        'jumlah_pengeluaran',
        'alasan_pengeluaran',
        'tanggal_pengeluaran',
        'bukti_pengeluaran',
        'keuangan_id',
    ];

    /**
     * Relasi ke Keuangan
     */
    public function keuangan()
    {
        return $this->belongsTo(Keuangan::class);
    }
}
