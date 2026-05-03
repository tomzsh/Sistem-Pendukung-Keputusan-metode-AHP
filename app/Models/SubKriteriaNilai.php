<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubKriteriaNilai extends Model
{
    protected $table = 'sub_kriteria_nilai';

    protected $fillable = [
        'id_kriteria',
        'sub_kriteria_id_dari',
        'sub_kriteria_id_tujuan',
        'nilai'
    ];
}
