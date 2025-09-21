<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'event';

    protected $fillable = [
        'kode_event',
        'nama_event',
        'jenis',
        'tema',
        'tempat',
        'harga_pendaftaran_peserta',
        'status_pendaftaran_panitia',
        'status_pendaftaran_peserta',
    ];

    // Relasi ke pendaftar peserta
    public function pendaftarPeserta()
    {
        return $this->hasMany(PendaftarPeserta::class, 'event_id');
    }
    public function keuangan()
    {
        return $this->hasOne(Keuangan::class);
    }
    // Relasi ke pendaftar panitia
    public function pendaftarPanitia()
    {
        return $this->hasMany(PendaftarPanitia::class, 'event_id'); 
    }
    
}
