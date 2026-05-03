<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaNilai extends Model
{
    protected $table = 'kriteria_nilai';

    protected $fillable = [
        'kriteria_id_dari',
        'kriteria_id_tujuan',
        'nilai'
    ];
}
