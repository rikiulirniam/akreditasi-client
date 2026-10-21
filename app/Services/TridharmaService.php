<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class TridharmaService
{
    protected string $baseUrl;

    protected ?string $token = null;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.tridharma.base_url', 'https://tridharma.fayra.my.id/api'), '/');
        $this->token = $token;
    }

    public function withToken(?string $token = null): static
    {
        $clone = clone $this;
        $clone->token = $token ?? (string) session('access_token');

        return $clone;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function http(?string $token = null): PendingRequest
    {
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');

        $client = Http::acceptJson()
            ->asJson()
            ->timeout(30);

        if (! empty($resolvedToken)) {
            $client = $client->withToken($resolvedToken);
        }

        return $client;
    }

    protected function buildUrl(string $endpoint): string
    {
        $trimmed = ltrim($endpoint, '/');

        if (str_starts_with($trimmed, 'api/')) {
            $trimmed = substr($trimmed, 4);
        }

        return "{$this->baseUrl}/{$trimmed}";
    }

    /*
    |--------------------------------------------------------------------------
    | Generic HTTP Request Helpers
    |--------------------------------------------------------------------------
    */

    public function get(string $endpoint, array $query = [], ?string $token = null): Response
    {
        return $this->http($token)->get($this->buildUrl($endpoint), $query);
    }

    public function post(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->http($token)->post($this->buildUrl($endpoint), $data);
    }

    public function put(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->http($token)->put($this->buildUrl($endpoint), $data);
    }

    public function delete(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->http($token)->delete($this->buildUrl($endpoint), $data);
    }

    /*
    |==========================================================================
    | MODUL: DOSEN (/api/dosen/*)
    |==========================================================================
    */

    public function getDosenList(array $query = [], ?string $token = null): Response
    {
        return $this->get('dosen', $query, $token);
    }

    public function getDosenById(int|string $id, ?string $token = null): Response
    {
        return $this->get("dosen/{$id}", [], $token);
    }

    public function createDosen(array $data, ?string $token = null): Response
    {
        return $this->post('dosen', $data, $token);
    }

    public function updateDosen(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("dosen/{$id}", $data, $token);
    }

    public function deleteDosen(int|string $id, ?string $token = null): Response
    {
        return $this->delete("dosen/{$id}", [], $token);
    }

    public function restoreDosen(int|string $id, ?string $token = null): Response
    {
        return $this->post("dosen/{$id}/restore", [], $token);
    }

    public function importDosenExcel(UploadedFile $file, ?string $token = null): Response
    {
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');
        $client = Http::acceptJson()->timeout(60);

        if (! empty($resolvedToken)) {
            $client = $client->withToken($resolvedToken);
        }

        return $client->attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post($this->buildUrl('dosen/import'));
    }

    /*
    |==========================================================================
    | MODUL: SERTIFIKASI & TENDIK (/api/sertifikasi/*, /api/tendik/*)
    |==========================================================================
    */

    public function getSertifikasiList(array $query = [], ?string $token = null): Response
    {
        return $this->get('sertifikasi', $query, $token);
    }

    public function getSertifikasiById(int|string $id, ?string $token = null): Response
    {
        return $this->get("sertifikasi/{$id}", [], $token);
    }

    public function createSertifikasi(array $data, ?string $token = null): Response
    {
        return $this->post('sertifikasi', $data, $token);
    }

    public function updateSertifikasi(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("sertifikasi/{$id}", $data, $token);
    }

    public function deleteSertifikasi(int|string $id, ?string $token = null): Response
    {
        return $this->delete("sertifikasi/{$id}", [], $token);
    }

    public function getTendikList(array $query = [], ?string $token = null): Response
    {
        return $this->get('tendik', $query, $token);
    }

    /*
    |==========================================================================
    | MASTER DATA (Jabatan Akademik, Pendidikan, Bidang Keahlian)
    |==========================================================================
    */

    public function getJabatanAkademikList(array $query = [], ?string $token = null): Response
    {
        return $this->get('jabatan-akademik', $query, $token);
    }

    public function getPendidikanList(array $query = [], ?string $token = null): Response
    {
        return $this->get('pendidikan', $query, $token);
    }

    public function getBidangKeahlianList(array $query = [], ?string $token = null): Response
    {
        return $this->get('bidang-keahlian', $query, $token);
    }
}
