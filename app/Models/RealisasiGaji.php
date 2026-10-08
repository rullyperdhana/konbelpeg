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

    /**
     * Get Tunjangan Keluarga (Pasangan + Anak) untuk dasar perhitungan Tapera.
     */
    public function getTunjKeluargaAttribute(): float
    {
        return (float) ($this->raw_data['tjistri'] ?? 0) + (float) ($this->raw_data['tjanak'] ?? 0);
    }

    /**
     * Get Tunjangan Jabatan / Fungsional / Umum untuk dasar perhitungan Tapera.
     */
    public function getTunjJabatanAttribute(): float
    {
        return (float) ($this->raw_data['tjstruk'] ?? 0)
            + (float) ($this->raw_data['tjfungsi'] ?? 0)
            + (float) ($this->raw_data['tjumum'] ?? 0);
    }

    /**
     * Get Dasar Perhitungan Simpanan Tapera (Gaji Pokok + Tunjangan Keluarga + Tunjangan Jabatan).
     */
    public function getDasarTaperaAttribute(): float
    {
        $gapok = (float) ($this->gaji_pokok ?? $this->raw_data['gapok'] ?? 0);

        return $gapok + $this->tunj_keluarga + $this->tunj_jabatan;
    }

    /**
     * Get Simulasi Potongan Tapera Pegawai/ASN (2,5%).
     */
    public function getSimulasiTaperaAsnAttribute(): float
    {
        return round($this->dasar_tapera * 0.025);
    }

    /**
     * Get Simulasi Beban Iuran Tapera Pemberi Kerja/Pemda (0,5%).
     */
    public function getSimulasiTaperaPkAttribute(): float
    {
        return round($this->dasar_tapera * 0.005);
    }

    /**
     * Get Simulasi Total Iuran Tapera (3,0%).
     */
    public function getSimulasiTaperaTotalAttribute(): float
    {
        return round($this->dasar_tapera * 0.03);
    }

    /**
     * Get Kategori ASN (PNS, PPPK, atau PPPK PARUH WAKTU).
     */
    public function getKategoriAsnAttribute(): string
    {
        $statusPegawai = strtoupper((string) ($this->pegawai->status_pegawai ?? ''));
        $kelompokUpload = strtoupper((string) ($this->raw_data['kelompok_upload'] ?? ''));

        if (str_contains($statusPegawai, 'PARUH WAKTU') || str_contains($kelompokUpload, 'PARUH WAKTU')) {
            return 'PPPK PARUH WAKTU';
        }

        if ($statusPegawai === 'PPPK') {
            return 'PPPK';
        }

        return 'PNS';
    }
}
