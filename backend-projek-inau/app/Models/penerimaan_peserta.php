<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class penerimaan_peserta extends Model
{
    //
    use HasFactory;

    protected $table = 'penerimaan_peserta';

    protected $fillable = [
        'nama',
        'NIM',
        'bukti_pembayaran',
        'tanggal_penerimaan',
        'id_peserta',
        'status',
    ];

    public function peserta()
    {
        return $this->belongsTo(peserta::class, 'id_peserta');
    }
}
