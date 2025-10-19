<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sertifikat extends Model
{
    protected $table = 'sertifikat';

    protected $fillable = [
        'nama_sertifikat',
        'jenis_sertifikat',
        'link_drive',
        'event_id',
        'create_by',
        'update_by',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
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
