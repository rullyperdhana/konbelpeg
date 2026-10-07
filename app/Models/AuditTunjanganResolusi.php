<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTunjanganResolusi extends Model
{
    protected $fillable = [
        'kategori',
        'kunci_kasus',
        'status',
        'no_sts',
        'tgl_sts',
        'nominal_pengembalian',
        'catatan',
        'user_id',
        'resolved_by_name',
    ];

    protected $casts = [
        'tgl_sts' => 'date',
        'nominal_pengembalian' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
