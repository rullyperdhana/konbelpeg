<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $guarded = [];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function realisasiTpps()
    {
        return $this->hasMany(RealisasiTpp::class);
    }

    public function realisasiGajis()
    {
        return $this->hasMany(RealisasiGaji::class);
    }
}
