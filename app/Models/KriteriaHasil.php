<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaHasil extends Model
{
    protected $table = 'kriteria_hasil';

    protected $fillable = [
        'kriteria_id',
        'nilai'
    ];

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class);
    }
}
