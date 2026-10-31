<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sertifikasi extends Model
{
    use HasFactory;

    protected $table = 'sertifikasis';

    protected $fillable = [
        'tenaga_kependidikan_id',
        'nama_sertifikasi',
        'penerbit',
        'nomor_sertifikasi',
        'tahun',
        'keterangan',
    ];

    public function tenagaKependidikan(): BelongsTo
    {
        return $this->belongsTo(TenagaKependidikan::class, 'tenaga_kependidikan_id');
    }
}
