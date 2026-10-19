<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class TridharmaService
{
    public function __construct(
        protected ?string $baseUrl = null,
        protected ?string $token = null,
    ) {}

    protected function http(): PendingRequest
    {
        $client = Http::acceptJson()
            ->asJson()
            ->timeout(20);

        $token = $this->token ?? (string) session('access_token');
        if ($token !== '') {
            $client = $client->withToken($token);
        }

        return $client;
    }

    protected function url(string $endpoint): string
    {
        $configuredBaseUrl = $this->baseUrl ?? (string) config('services.tridharma.base_url');
        $baseUrl = rtrim($configuredBaseUrl !== '' ? $configuredBaseUrl : 'https://tridharma.fayra.my.id/api', '/');

        return $baseUrl.'/'.ltrim($endpoint, '/');
    }

    /** @param array<string, int|string> $query */
    public function getTendikList(array $query = []): Response
    {
        return $this->http()->get($this->url('tendik'), $query);
    }

    public function getTendik(int|string $id): Response
    {
        return $this->http()->get($this->url("tendik/{$id}"));
    }

    /** @param array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string} $payload */
    public function createTendik(array $payload): Response
    {
        return $this->http()->post($this->url('tendik'), $payload);
    }

    /** @param array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string} $payload */
    public function updateTendik(int|string $id, array $payload): Response
    {
        return $this->http()->put($this->url("tendik/{$id}"), $payload);
    }

    public function deleteTendik(int|string $id): Response
    {
        return $this->http()->delete($this->url("tendik/{$id}"));
    }

    public function getPendidikanList(): Response
    {
        return $this->http()->get($this->url('pendidikan'));
    }

    /** @param array<string, int|string> $query */
    public function getSertifikasiList(array $query = []): Response
    {
        return $this->http()->get($this->url('sertifikasi'), $query);
    }

    /** @param array<string, int|string|null> $payload */
    public function createSertifikasi(array $payload, ?UploadedFile $evidenceFile = null, bool $asMultipart = false): Response
    {
        if ($evidenceFile === null && ! $asMultipart) {
            return $this->http()->post($this->url('sertifikasi'), $payload);
        }

        $request = Http::acceptJson()->timeout(30);
        $token = $this->token ?? (string) session('access_token');
        if ($token !== '') {
            $request = $request->withToken($token);
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

    public function getSertifikasi(int|string $id): Response
    {
        return $this->http()->get($this->url("sertifikasi/{$id}"));
    }

    public function getSertifikasiById(int|string $id): Response
    {
        return $this->getSertifikasi($id);
    }

    /** @param array<string, int|string|null> $payload */
    public function updateSertifikasi(int|string $id, array $payload): Response
    {
        return $this->http()->put($this->url("sertifikasi/{$id}"), $payload);
    }

    public function deleteSertifikasi(int|string $id): Response
    {
        return $this->http()->delete($this->url("sertifikasi/{$id}"));
    }
}
