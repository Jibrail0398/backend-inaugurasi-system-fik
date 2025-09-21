<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keuangan extends Model
{
    use HasFactory;

    protected $table = 'keuangan';

    protected $fillable = [
        'saldo',
        'event_id',
    ];

    /**
     * Relasi ke Event
     */
   public function uangMasuk()
    {
        return $this->hasMany(UangMasuk::class);
    }

    public function uangKeluar()
    {
        return $this->hasMany(UangKeluar::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Aksesor untuk mendapatkan total uang masuk
    public function getTotalUangMasukAttribute()
    {
        return $this->uangMasuk->sum('jumlah_uang_masuk');
    }
}
