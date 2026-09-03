<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $guarded = [];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }
}
