<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReviewerService
{
    public function __construct(
        protected ?AkreditasiService $akreditasiService = null,
        protected ?AkademikService $akademikService = null,
    ) {
        $this->akreditasiService = $akreditasiService ?? app(AkreditasiService::class);
        $this->akademikService = $akademikService ?? app(AkademikService::class);
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getDummyProjects(): ?array
    {
        $file = resource_path('reviewer-dummy-data.php');

        if (! is_file($file)) {
            return null;
        }

        $projects = require $file;

        if (! is_array($projects) || ! array_is_list($projects) || count(array_filter($projects, 'is_array')) !== count($projects)) {
            throw new \UnexpectedValueException('File data dummy reviewer harus berisi daftar project.');
        }

        return $projects;
    }

    /**
     * Mengambil daftar project akreditasi.
     * Mencoba endpoint /reviewer/projects (kompatibilitas test/mock),
     * lalu fallback ke endpoint standar akreditasi /projects.
     */
    public function getProjects(array $query = []): Response
    {
        try {
            $reviewerUrl = $this->apiUrl().'/reviewer/projects';
            $response = $this->http()->get($reviewerUrl, $query);
            if ($response->successful()) {
                return $response;
            }
        } catch (\Throwable $e) {
            // Lanjut ke endpoint utama jika endpoint khusus tidak tersedia
        }

        return $this->akreditasiService->getProjects($query);
    }

    /**
     * Mengambil detail project berdasarkan ID.
     */
    public function getProject(int|string $id): Response
    {
        return $this->akreditasiService->getProject($id);
    }

    /**
     * Mengambil master indikator akreditasi dari API.
     */
    public function getIndikators(array $query = []): Response
    {
        return $this->akreditasiService->getIndikators($query);
    }

    /**
     * Mengambil master rubrik penilaian dari API.
     */
    public function getRubriks(array $query = []): Response
    {
        return $this->akreditasiService->getRubriks($query);
    }

    /**
     * Mengambil daftar self assessment (penilaian mandiri) dari API.
     */
    public function getSelfAssessments(array $query = []): Response
    {
        return $this->akreditasiService->getSelfAssessments($query);
    }

    /**
     * Menyimpan penilaian mandiri ke API.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveSelfAssessment(array $data): Response
    {
        return $this->akreditasiService->saveSelfAssessment($data);
    }

    /**
     * Mengambil mapping nama Program Studi dari AkademikService.
     *
     * @return array<int|string, string>
     */
    public function getProdiMap(): array
    {
        try {
            $response = $this->akademikService->getProdiList();
            if ($response->successful()) {
                $prodis = $response->json('data') ?? $response->json() ?? [];
                $map = [];
                if (is_array($prodis)) {
                    foreach ($prodis as $p) {
                        if (is_array($p) && isset($p['id'])) {
                            $map[$p['id']] = $p['nama_prodi'] ?? $p['nama'] ?? $p['nama_program_studi'] ?? 'Program Studi';
                        }
                    }
                }

                return $map;
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal memuat prodi map dari akademik: '.$e->getMessage());
        }

        return [];
    }

    protected function http(): PendingRequest
    {
        $request = Http::acceptJson()
            ->withoutVerifying()
            ->timeout(20);
        $token = (string) session('access_token');

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        return $request;
    }

    protected function apiUrl(): string
    {
        $baseUrl = rtrim((string) config('services.akreditasi.base_url'), '/');

        return str_ends_with($baseUrl, '/api') ? $baseUrl : $baseUrl.'/api';
    }
}
