<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class TridharmaService
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $configuredBaseUrl = trim($baseUrl ?? (string) config('services.tridharma.base_url'));

        if ($configuredBaseUrl === '') {
            throw new InvalidArgumentException('TRIDHARMA_API_URL must be configured.');
        }

        $this->baseUrl = rtrim($configuredBaseUrl, '/');
    }

    protected function http(): PendingRequest
    {
        $client = Http::acceptJson()
            ->asJson()
            ->timeout(20);

        $token = (string) session('access_token');

        if ($token !== '') {
            $client = $client->withToken($token);
        }

        return $client;
    }

    protected function buildUrl(string $endpoint): string
    {
        return $this->baseUrl.'/'.ltrim($endpoint, '/');
    }

    public function getSertifikasiList(array $query = []): Response
    {
        return $this->http()->get($this->buildUrl('sertifikasi'), $query);
    }

    public function getSertifikasiById(int|string $id): Response
    {
        return $this->http()->get($this->buildUrl("sertifikasi/{$id}"));
    }

    public function createSertifikasi(array $data): Response
    {
        return $this->http()->post($this->buildUrl('sertifikasi'), $data);
    }

    public function updateSertifikasi(int|string $id, array $data): Response
    {
        return $this->http()->put($this->buildUrl("sertifikasi/{$id}"), $data);
    }

    public function deleteSertifikasi(int|string $id): Response
    {
        return $this->http()->delete($this->buildUrl("sertifikasi/{$id}"));
    }
}
