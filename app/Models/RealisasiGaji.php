<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiGaji extends Model
{
    use HasFactory;

    public const JENIS_GAJI_INDUK = 'Gaji Induk';

    public const JENIS_GAJI_SUSULAN = 'Gaji Susulan';

    public const JENIS_GAJI_KEKURANGAN = 'Gaji Kekurangan';

    public const JENIS_GAJI_TERUSAN = 'Gaji Terusan';

    public const JENIS_GAJI_THR = 'Gaji THR';

    public const JENIS_GAJI_13 = 'Gaji 13';

    public const DAFTAR_JENIS_GAJI = [
        self::JENIS_GAJI_INDUK,
        self::JENIS_GAJI_SUSULAN,
        self::JENIS_GAJI_KEKURANGAN,
        self::JENIS_GAJI_TERUSAN,
        self::JENIS_GAJI_THR,
        self::JENIS_GAJI_13,
    ];

    protected $fillable = [
        'pegawai_id',
        'periode',
        'jenis_gaji',
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

    /**
     * Get Potongan IWP 2% (Jaminan Pemeliharaan Kesehatan / Jamkes / BPJS Kesehatan).
     */
    public function getIwpJamkesAttribute(): float
    {
        return (float) ($this->raw_data['piwp2'] ?? 0);
    }

    /**
     * Get Potongan IWP 8% (Pensiun & Tabungan Hari Tua ke PT Taspen).
     */
    public function getIwpPensiunAttribute(): float
    {
        return (float) ($this->raw_data['piwp8'] ?? 0);
    }

    /**
     * Get Potongan Askes tambahan (jika ada).
     */
    public function getPaskesAttribute(): float
    {
        return (float) ($this->raw_data['paskes'] ?? 0);
    }

    /**
     * Get Total Pemotongan Jaminan Kesehatan pada Gaji (IWP 2% + Paskes).
     */
    public function getTotalJamkesAttribute(): float
    {
        return $this->iwp_jamkes + $this->paskes;
    }
}
