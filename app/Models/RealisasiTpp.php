<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealisasiTpp extends Model
{
    protected $fillable = [
        'pegawai_id', 'periode', 'tpp_bruto', 'nominal_plt', 'tpp_netto',
        'pph_21', 'potongan_lainnya', 'iuran_iwp', 'total_dibayarkan', 'raw_data',
    ];

    protected $casts = [
        'tpp_bruto' => 'integer',
        'nominal_plt' => 'integer',
        'tpp_netto' => 'integer',
        'pph_21' => 'integer',
        'potongan_lainnya' => 'integer',
        'iuran_iwp' => 'integer',
        'total_dibayarkan' => 'integer',
        'raw_data' => 'array',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
