<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    protected $table = 'keuangan';
    
    protected $fillable = [
        'saldo',
        'event_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function uangMasuk()
    {
        return $this->hasMany(UangMasuk::class, 'keuangan_id');
    }    

    public function uangKeluar()
    {
        return $this->hasMany(UangKeluar::class, 'keuangan_id');
    }
}
