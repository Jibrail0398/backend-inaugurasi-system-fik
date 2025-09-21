<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Event;
use App\Models\PenerimaanPeserta;

class PendaftarPeserta extends Model
{
    use HasFactory;

    protected $table = 'pendaptar_peserta';

    protected $fillable = [
        'event_id',
        'kode_peserta',
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
        'bukti_pembayaran',
    ];



    // Aksesor untuk mendapatkan harga pendaftaran dari event terkait
    public function getHargaPendaftaranAttribute()
    {
        return $this->event->harga_pendaftaran_peserta ?? 0;
    }

    // Relasi ke PenerimaanPeserta
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    // Relasi ke PenerimaanPeserta
    public function penerimaanPeserta()
    {
        return $this->hasOne(PenerimaanPeserta::class, 'pendaptar_peserta_id');
    }
}
