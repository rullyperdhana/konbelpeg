<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiGaji extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'periode',
        'sub_kegiatan',
        'gaji_pokok',
        'pajak',
        'iwp',
        'potongan_lain',
        'gaji_bersih',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
