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
        'created_by',
        'updated_by',
        'delete_by',
    ];

    protected $casts = [
        'harga_pendaftaran_peserta' => 'integer',
    ];

    // Relasi ke pendaftar peserta
    public function pendaftarPeserta()
    {
        return $this->hasMany(PendaftarPeserta::class, 'event_id');
    }

    // Relasi ke pendaftar panitia
    public function pendaftarPanitia()
    {
        return $this->hasMany(PendaftarPanitia::class, 'event_id'); 
    }

    // Relasi ke keuangan
    public function keuangan()
    {
        return $this->hasOne(Keuangan::class);
    }

    // Relasi ke user yang membuat event
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke user yang terakhir mengupdate event
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relasi ke user yang menghapus event (jika pakai soft delete)
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

}
