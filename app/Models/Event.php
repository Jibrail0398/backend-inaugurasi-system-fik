<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'event';

    protected $fillable = [
        'kode_event',
        'nama_event',
        'jenis',
        'tema',
        'tempat',
        'harga_pendaftaran_peserta',
        'status_pendaftaran_panitia',
        'status_pendaftaran_peserta'
    ];

    public function keuangan()
    {
        return $this->hasOne(Keuangan::class, 'event_id');
    }
}

