<?php

namespace App\Models;

use Database\Factories\TenagaKependidikanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenagaKependidikan extends Model
{
    /** @use HasFactory<TenagaKependidikanFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenaga_kependidikan';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'kualifikasi',
        'bidang_keahlian',
        'program_studi',
        'email',
        'status',
    ];

    /**
     * Relasi ke riwayat kualifikasi akademik.
     */
    public function kualifikasis(): HasMany
    {
        return $this->hasMany(Kualifikasi::class, 'tenaga_kependidikan_id')->orderBy('id', 'asc');
    }

    /**
     * Relasi ke sertifikasi.
     */
    public function sertifikasis(): HasMany
    {
        return $this->hasMany(Sertifikasi::class, 'tenaga_kependidikan_id')->orderBy('created_at', 'desc');
    }
}
