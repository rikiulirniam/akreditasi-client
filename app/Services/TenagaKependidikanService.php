<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;

class TenagaKependidikanService extends TridharmaService
{
    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        parent::__construct(
            $baseUrl ?? (string) config('services.tridharma.base_url', env('TRIDHARMA_API_URL', env('API_BASE_URL', 'https://tridharma.fayra.my.id/api'))),
            $token
        );
    }

    /**
     * Mengambil daftar tenaga kependidikan dari API eksternal.
     *
     * @param  array<string, int|string>  $query
     */
    public function getDaftarTendik(array $query = []): Response
    {
        return $this->getTendikList($query);
    }

    /**
     * Mengambil detail data satu tenaga kependidikan.
     */
    public function getDetailTendik(int|string $id): Response
    {
        return $this->getTendik($id);
    }

    /**
     * Menambahkan data tenaga kependidikan baru.
     *
     * @param  array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string}  $payload
     */
    public function tambahTendik(array $payload): Response
    {
        return $this->createTendik($payload);
    }

    /**
     * Memperbarui data tenaga kependidikan.
     *
     * @param  array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string}  $payload
     */
    public function perbaruiTendik(int|string $id, array $payload): Response
    {
        return $this->updateTendik($id, $payload);
    }

    /**
     * Menghapus data tenaga kependidikan.
     */
    public function hapusTendik(int|string $id): Response
    {
        return $this->deleteTendik($id);
    }

    /**
     * Mengambil daftar master jenjang pendidikan/kualifikasi.
     */
    public function getDaftarPendidikan(): Response
    {
        return $this->getPendidikanList();
    }

    /**
     * Mengambil daftar sertifikasi untuk tenaga kependidikan.
     *
     * @param  array<string, int|string>  $query
     */
    public function getDaftarSertifikasi(array $query = []): Response
    {
        return $this->getSertifikasiList($query);
    }

    /**
     * Menambahkan data sertifikasi baru.
     *
     * @param  array<string, int|string|null>  $payload
     */
    public function tambahSertifikasi(array $payload, ?UploadedFile $evidenceFile = null): Response
    {
        return $this->createSertifikasi($payload, $evidenceFile);
    }

    /**
     * Memperbarui data sertifikasi.
     *
     * @param  array<string, int|string|null>  $payload
     */
    public function perbaruiSertifikasi(int|string $id, array $payload): Response
    {
        return $this->updateSertifikasi($id, $payload);
    }

    /**
     * Menghapus data sertifikasi.
     */
    public function hapusSertifikasi(int|string $id): Response
    {
        return $this->deleteSertifikasi($id);
    }
}
