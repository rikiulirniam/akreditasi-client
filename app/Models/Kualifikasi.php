<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kualifikasi extends Model
{
    use HasFactory;

    protected $table = 'kualifikasis';

    protected $fillable = [
        'tenaga_kependidikan_id',
        'gelar',
        'jenjang',
        'institusi',
        'program_studi',
        'tahun_lulus',
    ];

    public function tenagaKependidikan(): BelongsTo
    {
        return $this->belongsTo(TenagaKependidikan::class, 'tenaga_kependidikan_id');
    }
}
