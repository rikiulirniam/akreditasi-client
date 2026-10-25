<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AkreditasiService
{
    protected string $baseUrl;

    protected ?string $token = null;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $url = rtrim($baseUrl ?? (string) config('services.akreditasi.base_url', 'https://akreditasi.fayra.my.id'), '/');

        // Normalisasi URL untuk memastikan endpoint mengarah ke /api/v1
        if (! str_contains($url, '/api/v1')) {
            $url .= '/api/v1';
        }

        $this->baseUrl = $url;
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
            ->withoutVerifying()
            ->timeout(20);

        if (! empty($resolvedToken)) {
            $client = $client->withToken($resolvedToken);
        }

        return $client;
    }

    protected function buildUrl(string $endpoint): string
    {
        $trimmed = ltrim($endpoint, '/');

        if (str_starts_with($trimmed, 'api/v1/')) {
            $trimmed = substr($trimmed, 7);
        } elseif (str_starts_with($trimmed, 'api/')) {
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
        return $this->sendRequest('get', $endpoint, $query, $token);
    }

    public function post(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->sendRequest('post', $endpoint, $data, $token);
    }

    public function put(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->sendRequest('put', $endpoint, $data, $token);
    }

    public function delete(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->sendRequest('delete', $endpoint, $data, $token);
    }

    protected function sendRequest(string $method, string $endpoint, array $params = [], ?string $token = null): Response
    {
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');
        $url = $this->buildUrl($endpoint);

        $client = $this->http($resolvedToken);

        try {
            return match (strtolower($method)) {
                'post' => $client->post($url, $params),
                'put' => $client->put($url, $params),
                'delete' => $client->delete($url, $params),
                default => $client->get($url, $params),
            };
        } catch (\Throwable $e) {
            Log::warning('Akreditasi API Request Failed: '.$e->getMessage(), [
                'method' => $method,
                'url' => $url,
            ]);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Master Data Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar master indikator akreditasi.
     *
     * @param  array<string, mixed>  $query
     */
    public function getIndikators(array $query = []): Response
    {
        return $this->get('/master/indikator', $query);
    }

    /**
     * Mengambil detail indikator berdasarkan ID.
     */
    public function getIndikator(int|string $id): Response
    {
        return $this->get("/master/indikator/{$id}");
    }

    /**
     * Mengambil daftar master kriteria.
     *
     * @param  array<string, mixed>  $query
     */
    public function getKriteria(array $query = []): Response
    {
        return $this->get('/master/kriteria', $query);
    }

    /**
     * Mengambil detail kriteria berdasarkan ID.
     */
    public function getKriteriaDetail(int|string $id): Response
    {
        return $this->get("/master/kriteria/{$id}");
    }

    /**
     * Mengambil master versi instrumen akreditasi.
     *
     * @param  array<string, mixed>  $query
     */
    public function getVersiInstrumen(array $query = []): Response
    {
        return $this->get('/master/versi-instrumen', $query);
    }

    /**
     * Mengambil master lembaga akreditasi (LAM INFOKOM, BAN-PT, dll).
     *
     * @param  array<string, mixed>  $query
     */
    public function getLembagaAkreditasi(array $query = []): Response
    {
        return $this->get('/master/lembaga-akreditasi', $query);
    }

    /**
     * Mengambil master rubrik penilaian.
     *
     * @param  array<string, mixed>  $query
     */
    public function getRubriks(array $query = []): Response
    {
        return $this->get('/master/rubrik', $query);
    }

    /**
     * Mengambil master standar mutu.
     *
     * @param  array<string, mixed>  $query
     */
    public function getStandarMutu(array $query = []): Response
    {
        return $this->get('/master/standar-mutu', $query);
    }

    /*
    |--------------------------------------------------------------------------
    | Project Akreditasi Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar project akreditasi.
     *
     * @param  array<string, mixed>  $query
     */
    public function getProjects(array $query = []): Response
    {
        return $this->get('/projects', $query);
    }

    /**
     * Mengambil detail project akreditasi.
     */
    public function getProject(int|string $id): Response
    {
        return $this->get("/projects/{$id}");
    }

    /**
     * Membuat project akreditasi baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProject(array $data): Response
    {
        return $this->post('/projects', $data);
    }

    /**
     * Menyimpan pembagian tugas tim per indikator untuk project.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveMappingTim(int|string $projectId, array $data): Response
    {
        return $this->post("/projects/{$projectId}/mapping-tim", $data);
    }

    /**
     * Mengambil daftar mapping tim.
     *
     * @param  array<string, mixed>  $query
     */
    public function getMappingTim(array $query = []): Response
    {
        return $this->get('/mapping-tim', $query);
    }

    /*
    |--------------------------------------------------------------------------
    | Self Assessment Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar penilaian mandiri (Self Assessment).
     *
     * @param  array<string, mixed>  $query
     */
    public function getSelfAssessments(array $query = []): Response
    {
        return $this->get('/self-assessment', $query);
    }

    /**
     * Menyimpan atau memperbarui penilaian mandiri (Self Assessment).
     *
     * @param  array<string, mixed>  $data
     */
    public function saveSelfAssessment(array $data): Response
    {
        return $this->post('/self-assessment', $data);
    }

    /**
     * Mengambil detail penilaian mandiri.
     */
    public function getSelfAssessment(int|string $id): Response
    {
        return $this->get("/self-assessment/{$id}");
    }

    /*
    |--------------------------------------------------------------------------
    | SPMI - Audit Mutu Internal Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar temuan audit SPMI.
     *
     * @param  array<string, mixed>  $query
     */
    public function getSpmiAudits(array $query = []): Response
    {
        return $this->get('/spmi-audit', $query);
    }

    /**
     * Mengambil detail temuan audit SPMI.
     */
    public function getSpmiAudit(int|string $id): Response
    {
        return $this->get("/spmi-audit/{$id}");
    }

    /**
     * Membuat temuan audit SPMI baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSpmiAudit(array $data): Response
    {
        return $this->post('/spmi-audit', $data);
    }

    /**
     * Memperbarui data audit SPMI.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSpmiAudit(int|string $id, array $data): Response
    {
        return $this->put("/spmi-audit/{$id}", $data);
    }

    /**
     * Memperbarui tindak lanjut temuan audit SPMI.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTindakLanjutSpmi(int|string $id, array $data): Response
    {
        return $this->put("/spmi-audit/{$id}/tindak-lanjut", $data);
    }
}
