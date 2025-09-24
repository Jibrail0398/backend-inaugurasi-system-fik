<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Keuangan;
use App\Models\UangMasuk;
use App\Models\UangKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keuangan extends Model
{
    use HasFactory;

    protected $table = 'keuangan';

    protected $fillable = [
        'saldo',
        'event_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

}
