<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangMasuk extends Model
{
    protected $table = 'uang_masuk';

    protected $fillable = [
        'jumlah_uang_masuk',
        'asal_pemasukan',
        'tanggal_pemasukan',
        'bukti_pemasukan',
        'keuangan_id',
    ];

    public function keuangan()
    {
        return $this->belongsTo(Keuangan::class, 'keuangan_id');
    }
}
