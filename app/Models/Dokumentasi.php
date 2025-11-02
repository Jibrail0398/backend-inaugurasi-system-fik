<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumentasi extends Model
{
    use HasFactory;

    protected $table = 'dokumentasi';
    
    protected $fillable = [
        'nama_dokumentasi',
        'judul',
        'deskripsi',
        'link_drive',
        'event_id',
        'create_by',
        'update_by'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'update_by');
    }
}
