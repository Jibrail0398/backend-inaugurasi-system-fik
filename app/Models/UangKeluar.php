<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangKeluar extends Model
{
    protected $table = 'uang_keluar';

    protected $fillable = [
        'jumlah_pengeluaran',
        'alasan_pengeluaran',
        'tanggal_pengeluaran',
        'bukti_pengeluaran',
        'keuangan_id',
    ];

    public function keuangan()
    {
        return $this->belongsTo(Keuangan::class, 'keuangan_id');
    }
}
