<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class penerimaan_panitia extends Model
{
    //
    use HasFactory;

    protected $table = 'penerimaan_panitia';

    protected $fillable = [
        'nama',
        'NIM',
        'tanggal_penerimaan',
        'id_panitia',
        'status_penerimaan',
    ];

    public function panitia()
    {
        return $this->belongsTo(panitia::class, 'id_panitia');
    }
}
