<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class panitia extends Model
{
    use HasFactory;

    protected $table = 'panitia';

    protected $fillable = [
        'nama',
        'NIM',
        'angkatan',
        'kelas',
        'tanggal_lahir',
        'ukuran_kaos',
        'email',
        'nomor_whatsapp',
        'nomor_darurat',
        'tipe_nomor_darurat',
        'riwayat_penyakit',
        'divisi',
        'jabatan',
        'komitmen1',
        'komitmen2',
    ];

    public function penerimaanPanitia()
    {
        return $this->hasMany(penerimaan_panitia::class, 'id_panitia');
    }
}
