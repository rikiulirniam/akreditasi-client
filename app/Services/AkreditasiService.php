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
        $url = rtrim($baseUrl ?? (string) config('services.akreditasi.base_url', 'https://akreditasi.fayra.my.id/api/v1'), '/');

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
     */
    public function getIndikators(array $query = []): Response
    {
        return $this->get('/master/indikator', $query);
    }

    /**
     * Mengambil detail indikator berdasarkan ID atau daftar indikator jika param array.
     */
    public function getIndikator(int|string|array $idOrQuery = []): Response
    {
        if (is_array($idOrQuery)) {
            return $this->getIndikators($idOrQuery);
        }

        return $this->get("/master/indikator/{$idOrQuery}");
    }

    /**
     * Menambahkan indikator baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createIndikator(array $data): Response
    {
        return $this->post('/master/indikator', $data);
    }

    /**
     * Memperbarui data indikator.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateIndikator(int|string $id, array $data): Response
    {
        return $this->put("/master/indikator/{$id}", $data);
    }

    /**
     * Menghapus indikator.
     */
    public function deleteIndikator(int|string $id): Response
    {
        return $this->delete("/master/indikator/{$id}");
    }

    /**
     * Mengambil daftar master kriteria.
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
     * Menambahkan kriteria baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createKriteria(array $data): Response
    {
        return $this->post('/master/kriteria', $data);
    }

    /**
     * Memperbarui data kriteria.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateKriteria(int|string $id, array $data): Response
    {
        return $this->put("/master/kriteria/{$id}", $data);
    }

    /**
     * Menghapus kriteria.
     */
    public function deleteKriteria(int|string $id): Response
    {
        return $this->delete("/master/kriteria/{$id}");
    }

    /**
     * Mengambil master versi instrumen akreditasi.
     */
    public function getVersiInstrumen(array $query = []): Response
    {
        return $this->get('/master/versi-instrumen', $query);
    }

    /**
     * Mengambil detail versi instrumen.
     */
    public function getVersiInstrumenDetail(int|string $id): Response
    {
        return $this->get("/master/versi-instrumen/{$id}");
    }

    /**
     * Menambahkan versi instrumen baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createVersiInstrumen(array $data): Response
    {
        return $this->post('/master/versi-instrumen', $data);
    }

    /**
     * Memperbarui versi instrumen.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateVersiInstrumen(int|string $id, array $data): Response
    {
        return $this->put("/master/versi-instrumen/{$id}", $data);
    }

    /**
     * Menghapus versi instrumen.
     */
    public function deleteVersiInstrumen(int|string $id): Response
    {
        return $this->delete("/master/versi-instrumen/{$id}");
    }

    /**
     * Mengambil daftar master instrumen akreditasi.
     *
     * @param  array<string, mixed>  $query
     */
    public function getInstrumens(array $query = []): Response
    {
        return $this->get('/master/instrumen', $query);
    }

    /**
     * Mengambil detail instrumen akreditasi.
     */
    public function getInstrumen(int|string $id): Response
    {
        return $this->get("/master/instrumen/{$id}");
    }

    /**
     * Menambahkan master instrumen baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createInstrumen(array $data): Response
    {
        return $this->post('/master/instrumen', $data);
    }

    /**
     * Memperbarui data instrumen.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateInstrumen(int|string $id, array $data): Response
    {
        return $this->put("/master/instrumen/{$id}", $data);
    }

    /**
     * Menghapus instrumen.
     */
    public function deleteInstrumen(int|string $id): Response
    {
        return $this->delete("/master/instrumen/{$id}");
    }

    /**
     * Mengambil master lembaga akreditasi (LAM INFOKOM, BAN-PT, dll).
     */
    public function getLembagaAkreditasi(array $query = []): Response
    {
        return $this->get('/master/lembaga-akreditasi', $query);
    }

    /**
     * Alias untuk getLembagaAkreditasi.
     *
     * @param  array<string, mixed>  $query
     */
    public function getLembaga(array $query = []): Response
    {
        return $this->getLembagaAkreditasi($query);
    }

    /**
     * Mengambil detail lembaga akreditasi.
     */
    public function getLembagaAkreditasiDetail(int|string $id): Response
    {
        return $this->get("/master/lembaga-akreditasi/{$id}");
    }

    /**
     * Menambahkan lembaga akreditasi baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createLembagaAkreditasi(array $data): Response
    {
        return $this->post('/master/lembaga-akreditasi', $data);
    }

    /**
     * Memperbarui lembaga akreditasi.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateLembagaAkreditasi(int|string $id, array $data): Response
    {
        return $this->put("/master/lembaga-akreditasi/{$id}", $data);
    }

    /**
     * Menghapus lembaga akreditasi.
     */
    public function deleteLembagaAkreditasi(int|string $id): Response
    {
        return $this->delete("/master/lembaga-akreditasi/{$id}");
    }

    /**
     * Mengambil master rubrik penilaian.
     */
    public function getRubriks(array $query = []): Response
    {
        return $this->get('/master/rubrik', $query);
    }

    /**
     * Mengambil detail rubrik berdasarkan ID.
     */
    public function getRubrik(int|string $id): Response
    {
        return $this->get("/master/rubrik/{$id}");
    }

    /**
     * Menambahkan rubrik baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createRubrik(array $data): Response
    {
        return $this->post('/master/rubrik', $data);
    }

    /**
     * Memperbarui rubrik penilaian.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateRubrik(int|string $id, array $data): Response
    {
        return $this->put("/master/rubrik/{$id}", $data);
    }

    /**
     * Menghapus rubrik penilaian.
     */
    public function deleteRubrik(int|string $id): Response
    {
        return $this->delete("/master/rubrik/{$id}");
    }

    /**
     * Mengambil master standar mutu.
     */
    public function getStandarMutu(array $query = []): Response
    {
        return $this->get('/master/standar-mutu', $query);
    }

    /**
     * Mengambil detail standar mutu berdasarkan ID.
     */
    public function getStandarMutuDetail(int|string $id): Response
    {
        return $this->get("/master/standar-mutu/{$id}");
    }

    /**
     * Menambahkan standar mutu baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createStandarMutu(array $data): Response
    {
        return $this->post('/master/standar-mutu', $data);
    }

    /**
     * Memperbarui standar mutu.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateStandarMutu(int|string $id, array $data): Response
    {
        return $this->put("/master/standar-mutu/{$id}", $data);
    }

    /**
     * Menghapus standar mutu.
     */
    public function deleteStandarMutu(int|string $id): Response
    {
        return $this->delete("/master/standar-mutu/{$id}");
    }

    /**
     * Mengambil master jenis pembiayaan.
     *
     * @param  array<string, mixed>  $query
     */
    public function getJenisPembiayaan(array $query = []): Response
    {
        return $this->get('/master/jenis-pembiayaan', $query);
    }

    /**
     * Mengambil detail jenis pembiayaan.
     */
    public function getJenisPembiayaanDetail(int|string $id): Response
    {
        return $this->get("/master/jenis-pembiayaan/{$id}");
    }

    /**
     * Menambahkan jenis pembiayaan baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createJenisPembiayaan(array $data): Response
    {
        return $this->post('/master/jenis-pembiayaan', $data);
    }

    /**
     * Memperbarui jenis pembiayaan.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateJenisPembiayaan(int|string $id, array $data): Response
    {
        return $this->put("/master/jenis-pembiayaan/{$id}", $data);
    }

    /**
     * Menghapus jenis pembiayaan.
     */
    public function deleteJenisPembiayaan(int|string $id): Response
    {
        return $this->delete("/master/jenis-pembiayaan/{$id}");
    }

    /*
    |--------------------------------------------------------------------------
    | Project Akreditasi Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar project akreditasi.
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
     */
    public function createProject(array $data): Response
    {
        return $this->post('/projects', $data);
    }

    /**
     * Memperbarui project akreditasi.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProject(int|string $id, array $data): Response
    {
        return $this->put("/projects/{$id}", $data);
    }

    /**
     * Menghapus project akreditasi.
     */
    public function deleteProject(int|string $id): Response
    {
        return $this->delete("/projects/{$id}");
    }

    /**
     * Menyimpan pembagian tugas tim per indikator untuk project.
     */
    public function saveMappingTim(int|string $projectId, array $data): Response
    {
        return $this->post("/projects/{$projectId}/mapping-tim", $data);
    }

    /**
     * Mengambil daftar mapping tim.
     */
    public function getMappingTim(array $query = []): Response
    {
        return $this->get('/mapping-tim', $query);
    }

    /**
     * Menghapus data mapping tim.
     */
    public function deleteMappingTim(int|string $id): Response
    {
        return $this->delete("/mapping-tim/{$id}");
    }

    /*
    |--------------------------------------------------------------------------
    | Self Assessment Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar penilaian mandiri (Self Assessment).
     */
    public function getSelfAssessments(array $query = []): Response
    {
        return $this->get('/self-assessment', $query);
    }

    /**
     * Mengambil detail atau daftar penilaian mandiri.
     */
    public function getSelfAssessment(int|string|array $idOrQuery = []): Response
    {
        if (is_array($idOrQuery)) {
            return $this->getSelfAssessments($idOrQuery);
        }

        return $this->get("/self-assessment/{$idOrQuery}");
    }

    /**
     * Menyimpan atau memperbarui penilaian mandiri (Self Assessment).
     */
    public function saveSelfAssessment(array $data): Response
    {
        return $this->post('/self-assessment', $data);
    }

    /**
     * Menghapus penilaian mandiri.
     */
    public function deleteSelfAssessment(int|string $id): Response
    {
        return $this->delete("/self-assessment/{$id}");
    }

    /*
    |--------------------------------------------------------------------------
    | SPMI - Audit Mutu Internal Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar temuan audit SPMI.
     */
    public function getSpmiAudits(array $query = []): Response
    {
        return $this->get('/spmi-audit', $query);
    }

    /**
     * Mengambil detail temuan audit SPMI atau daftar jika parameter array.
     */
    public function getSpmiAudit(int|string|array $idOrQuery = []): Response
    {
        if (is_array($idOrQuery)) {
            return $this->getSpmiAudits($idOrQuery);
        }

        return $this->get("/spmi-audit/{$idOrQuery}");
    }

    /**
     * Membuat temuan audit SPMI baru.
     */
    public function createSpmiAudit(array $data): Response
    {
        return $this->post('/spmi-audit', $data);
    }

    /**
     * Memperbarui data audit SPMI.
     */
    public function updateSpmiAudit(int|string $id, array $data): Response
    {
        return $this->put("/spmi-audit/{$id}", $data);
    }

    /**
     * Menghapus temuan audit SPMI.
     */
    public function deleteSpmiAudit(int|string $id): Response
    {
        return $this->delete("/spmi-audit/{$id}");
    }

    /**
     * Memperbarui tindak lanjut temuan audit SPMI.
     */
    public function updateTindakLanjutSpmi(int|string $id, array $data): Response
    {
        return $this->put("/spmi-audit/{$id}/tindak-lanjut", $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Sarana & Prasarana Prodi Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil daftar prasarana prodi.
     *
     * @param  array<string, mixed>  $query
     */
    public function getSarprasProdi(array $query = []): Response
    {
        return $this->get('/sarpras-prodi', $query);
    }

    /**
     * Mengambil detail prasarana prodi.
     */
    public function getSarprasProdiDetail(int|string $id): Response
    {
        return $this->get("/sarpras-prodi/{$id}");
    }

    /**
     * Menambahkan prasarana prodi.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSarprasProdi(array $data): Response
    {
        return $this->post('/sarpras-prodi', $data);
    }

    /**
     * Memperbarui data prasarana prodi.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSarprasProdi(int|string $id, array $data): Response
    {
        return $this->put("/sarpras-prodi/{$id}", $data);
    }

    /**
     * Menghapus data prasarana prodi.
     */
    public function deleteSarprasProdi(int|string $id): Response
    {
        return $this->delete("/sarpras-prodi/{$id}");
    }

    /**
     * Mengambil daftar sarana prodi.
     *
     * @param  array<string, mixed>  $query
     */
    public function getSaranaProdi(array $query = []): Response
    {
        return $this->get('/sarana-prodi', $query);
    }

    /**
     * Mengambil detail sarana prodi.
     */
    public function getSaranaProdiDetail(int|string $id): Response
    {
        return $this->get("/sarana-prodi/{$id}");
    }

    /**
     * Menambahkan sarana prodi.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSaranaProdi(array $data): Response
    {
        return $this->post('/sarana-prodi', $data);
    }

    /**
     * Memperbarui data sarana prodi.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSaranaProdi(int|string $id, array $data): Response
    {
        return $this->put("/sarana-prodi/{$id}", $data);
    }

    /**
     * Menghapus data sarana prodi.
     */
    public function deleteSaranaProdi(int|string $id): Response
    {
        return $this->delete("/sarana-prodi/{$id}");
    }
}
