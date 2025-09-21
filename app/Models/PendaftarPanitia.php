<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Event;
use App\Models\PenerimaanPanitia;


class PendaftarPanitia extends Model
{
    use HasFactory;

    protected $table = 'pendaptar_panitia';
    protected $fillable = [
        'event_id',
        'kode_panitia',
        'nama',
        'NIM',
        'email',
        'nomor_whatapp',
        'angkatan',
        'kelas',
        'tanggal_lahir',
        'ukuran_kaos',
        'nomor_darurat',
        'tipe_nomor_darurat',
        'riwayat_penyakit',
        'divisi',
        'komitmen1',
        'komitmen2'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function penerimaan()
    {
        return $this->hasOne(PenerimaanPanitia::class, 'pendaftar_panitia_id');
    }
}
