<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    protected $table = 'kriteria';

    protected $fillable = [
        'kode',
        'nama'
    ];

    public function subKriteria()
    {
        return $this->hasMany(SubKriteria::class);
    }

    public function hasil()
    {
        return $this->hasOne(KriteriaHasil::class);
    }
}
