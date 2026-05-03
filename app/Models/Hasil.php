<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hasil extends Model
{
    protected $table = 'hasil';

    protected $fillable = [
        'id_alternatif',
        'nilai'
    ];

    public function alternatif()
    {
        return $this->belongsTo(Alternatif::class,'id_alternatif');
    }
}
