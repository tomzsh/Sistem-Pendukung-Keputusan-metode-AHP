<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubKriteria extends Model
{
    protected $table = 'sub_kriteria';

    protected $fillable = [
        'kriteria_id',
        'nama'
    ];

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class);
    }

    public function hasil()
    {
        return $this->hasOne(SubKriteriaHasil::class,'id_sub_kriteria');
    }
}
