<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimgajiKeluarga extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tgllhr' => 'date',
        'tglsks' => 'date',
        'tglnikah' => 'date',
        'tglcerai' => 'date',
        'tglwafat' => 'date',
    ];

    /**
     * Hitung umur saat ini berdasarkan tanggal lahir.
     */
    public function getUsiaAttribute(): ?int
    {
        return $this->tgllhr ? (int) $this->tgllhr->diffInYears(now()) : null;
    }

    /**
     * Cek apakah berhak mendapatkan tunjangan.
     */
    public function getIsTertunjangAttribute(): bool
    {
        return $this->kdtunjang === '2';
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
