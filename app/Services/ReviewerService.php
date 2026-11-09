<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
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
     * Mengambil semua project akreditasi.
     */
    public function getProjects(array $query = []): Response
    {
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
     * Mengambil data pembagian indikator pada semua project.
     */
    public function getTeamAssignments(array $query = []): Response
    {
        return $this->akreditasiService->getMappingTim($query);
    }

    /**
     * Mengambil master indikator akreditasi dari API.
     */
    public function getIndikators(array $query = []): Response
    {
        return $this->akreditasiService->getIndikators($query);
    }

    /**
     * Alias berbahasa Inggris untuk menjaga kompatibilitas pemanggil lama.
     */
    public function getIndicators(array $query = []): Response
    {
        return $this->getIndikators($query);
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
}
