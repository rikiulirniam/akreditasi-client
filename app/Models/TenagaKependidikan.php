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
        'nama_tendik',
        'nip',
        'jabatan',
        'jabatan_tendik',
        'sertifikasi',
        'id_pendidikan',
        'kualifikasi',
        'bidang_keahlian',
        'program_studi',
        'email',
        'status',
    ];

    /**
     * Get the primary key for the model, supporting both id and id_tendik.
     */
    public function getKeyName(): string
    {
        if (array_key_exists('id_tendik', $this->attributes) && ! array_key_exists('id', $this->attributes)) {
            return 'id_tendik';
        }

        return $this->primaryKey;
    }

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
