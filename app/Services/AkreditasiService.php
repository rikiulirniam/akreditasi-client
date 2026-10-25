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
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.akreditasi.base_url', 'https://akreditasi.fayra.my.id/api/v1'), '/');
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

    protected function sendRequest(string $method, string $endpoint, array $payload = [], ?string $token = null): Response
    {
        $url = $this->buildUrl($endpoint);
        $client = $this->http($token);

        try {
            return match (strtolower($method)) {
                'get' => $client->get($url, $payload),
                'post' => $client->post($url, $payload),
                'put' => $client->put($url, $payload),
                'delete' => $client->delete($url, $payload),
                default => throw new \InvalidArgumentException("Metode HTTP [{$method}] tidak didukung."),
            };
        } catch (\Throwable $e) {
            Log::error("AkreditasiService {$method} error on {$url}: {$e->getMessage()}");
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Project Akreditasi Endpoints
    |--------------------------------------------------------------------------
    */

    public function getProjects(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/projects', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data projects: {$e->getMessage()}");
        }

        return [];
    }

    public function getProject(int|string $id, ?string $token = null): ?array
    {
        try {
            $response = $this->get("/projects/{$id}", [], $token);
            if ($response->successful()) {
                return $response->json('data') ?? null;
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil detail project {$id}: {$e->getMessage()}");
        }

        return null;
    }

    public function createProject(array $payload, ?string $token = null): array
    {
        $response = $this->post('/projects', $payload, $token);
        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'data' => $response->json('data'),
            'message' => $response->json('message') ?? 'Terjadi kesalahan saat membuat project.',
            'errors' => $response->json('errors'),
        ];
    }

    public function deleteProject(int|string $id, ?string $token = null): array
    {
        $response = $this->delete("/projects/{$id}", [], $token);
        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'message' => $response->json('message') ?? 'Project berhasil dihapus.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Master Data Endpoints
    |--------------------------------------------------------------------------
    */

    public function getLembaga(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/master/lembaga-akreditasi', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data lembaga akreditasi: {$e->getMessage()}");
        }

        return [];
    }

    public function getVersiInstrumen(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/master/versi-instrumen', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data versi instrumen: {$e->getMessage()}");
        }

        return [];
    }

    public function getKriteria(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/master/kriteria', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data kriteria: {$e->getMessage()}");
        }

        return [];
    }

    public function getIndikator(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/master/indikator', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data indikator: {$e->getMessage()}");
        }

        return [];
    }

    public function getMappingTim(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/mapping-tim', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data mapping tim: {$e->getMessage()}");
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | SPMI & Self Assessment Endpoints
    |--------------------------------------------------------------------------
    */

    public function getSpmiAudit(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/spmi-audit', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data SPMI audit: {$e->getMessage()}");
        }

        return [];
    }

    public function getSelfAssessment(array $query = [], ?string $token = null): array
    {
        try {
            $response = $this->get('/self-assessment', $query, $token);
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data self assessment: {$e->getMessage()}");
        }

        return [];
    }
}
