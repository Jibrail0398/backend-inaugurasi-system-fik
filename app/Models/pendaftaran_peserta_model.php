<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pendaftaran_peserta_model extends Model
{
    use HasFactory;

    protected $table = 'pendaftar_peserta';

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
        'divisi',
        'komitmen1',
        'komitmen2',
    ];
}
