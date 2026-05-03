<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubKriteriaHasil extends Model
{
    protected $table = 'sub_kriteria_hasil';

    protected $fillable = [
        'id_kriteria',
        'id_sub_kriteria',
        'nilai'
    ];
}
