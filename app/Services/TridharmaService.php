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

    protected function url(string $endpoint): string
    {
        return $this->buildUrl($endpoint);
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
    | MODUL: TENAGA KEPENDIDIKAN (/api/tendik/*)
    |==========================================================================
    */

    /** @param array<string, int|string> $query */
    public function getTendikList(array $query = [], ?string $token = null): Response
    {
        return $this->http($token)->get($this->url('tendik'), $query);
    }

    public function getTendik(int|string $id, ?string $token = null): Response
    {
        return $this->http($token)->get($this->url("tendik/{$id}"));
    }

    /** @param array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string} $payload */
    public function createTendik(array $payload, ?string $token = null): Response
    {
        return $this->http($token)->post($this->url('tendik'), $payload);
    }

    /** @param array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string} $payload */
    public function updateTendik(int|string $id, array $payload, ?string $token = null): Response
    {
        return $this->http($token)->put($this->url("tendik/{$id}"), $payload);
    }

    public function deleteTendik(int|string $id, ?string $token = null): Response
    {
        return $this->http($token)->delete($this->url("tendik/{$id}"));
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

    public function getPendidikanById(int|string $id, ?string $token = null): Response
    {
        return $this->get("pendidikan/{$id}", [], $token);
    }

    public function createPendidikan(array $data, ?string $token = null): Response
    {
        return $this->post('pendidikan', $data, $token);
    }

    public function updatePendidikan(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("pendidikan/{$id}", $data, $token);
    }

    public function deletePendidikan(int|string $id, ?string $token = null): Response
    {
        return $this->delete("pendidikan/{$id}", [], $token);
    }

    public function getBidangKeahlianList(array $query = [], ?string $token = null): Response
    {
        return $this->get('bidang-keahlian', $query, $token);
    }

    /*
    |==========================================================================
    | MODUL: SERTIFIKASI (/api/sertifikasi/*)
    |==========================================================================
    */

    /** @param array<string, int|string> $query */
    public function getSertifikasiList(array $query = [], ?string $token = null): Response
    {
        return $this->http($token)->get($this->url('sertifikasi'), $query);
    }

    public function getSertifikasiById(int|string $id, ?string $token = null): Response
    {
        return $this->http($token)->get($this->url("sertifikasi/{$id}"));
    }

    public function getSertifikasi(int|string $id, ?string $token = null): Response
    {
        return $this->getSertifikasiById($id, $token);
    }

    /** @param array<string, int|string|null> $payload */
    public function createSertifikasi(array $payload, ?UploadedFile $evidenceFile = null, ?string $token = null): Response
    {
        if (func_num_args() === 1) {
            return $this->post('sertifikasi', $payload, $token);
        }

        $request = Http::acceptJson()->timeout(30);
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');
        if ($resolvedToken !== '') {
            $request = $request->withToken($resolvedToken);
        }

        $multipart = [];
        foreach ($payload as $name => $value) {
            if ($value !== null && $value !== '') {
                $multipart[] = [
                    'name' => $name,
                    'contents' => (string) $value,
                ];
            }
        }

        if ($evidenceFile !== null) {
            $multipart[] = [
                'name' => 'evidence_file',
                'contents' => fopen($evidenceFile->getRealPath(), 'rb'),
                'filename' => $evidenceFile->getClientOriginalName(),
            ];
        }

        return $request->send('POST', $this->url('sertifikasi'), ['multipart' => $multipart]);
    }

    /** @param array<string, int|string|null> $payload */
    public function updateSertifikasi(int|string $id, array $payload, ?string $token = null): Response
    {
        return $this->http($token)->put($this->url("sertifikasi/{$id}"), $payload);
    }

    public function deleteSertifikasi(int|string $id, ?string $token = null): Response
    {
        return $this->http($token)->delete($this->url("sertifikasi/{$id}"));
    }
}
