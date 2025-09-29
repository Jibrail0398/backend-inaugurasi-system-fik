<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class peserta extends Model
{
    // Model Peserta yang digunakan untuk menyimpan data peserta
    use HasFactory;

    protected $table = 'peserta';

    protected $fillable = [
        'nama',
        'NIM',
        'email',
        'nomor_whatsapp',
        'angkatan',
        'kelas',
        'tanggal_lahir',
        'ukuran_kaos',
        'nomor_darurat',
        'tipe_nomor_darurat',
        'riwayat_penyakit',
        'bukti_pembayaran',
        'divisi',
        'komitmen1',
        'komitmen2',
    ];
        // Relasi ke penerimaan_peserta
        public function penerimaan()
        {
            return $this->hasOne(penerimaan_peserta::class, 'id_peserta');
        }
}
