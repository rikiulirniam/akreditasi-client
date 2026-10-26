<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
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

    /*
    |==========================================================================
    | SINKRONISASI SERTIFIKASI TENAGA KEPENDIDIKAN
    |==========================================================================
    */

    /**
     * Mengambil daftar lengkap riwayat sertifikasi tenaga kependidikan, disinkronkan antara
     * API Tridharma dan cache lokal (tanpa modifikasi database).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTendikSertifikasiList(int|string $tendikId): array
    {
        $tendikId = (int) $tendikId;

        $customKey = "tendik_custom_sertifikasi_{$tendikId}";
        $deletedKey = "tendik_deleted_sertifikasi_{$tendikId}";

        $customItems = Cache::get($customKey, []);
        if (! is_array($customItems)) {
            $customItems = [];
        }

        $deletedItems = Cache::get($deletedKey, []);
        if (! is_array($deletedItems)) {
            $deletedItems = [];
        }
        $deletedItems = array_map('strval', $deletedItems);

        $candidates = [];

        // 1. Ambil sertifikasi custom dari cache lokal
        foreach ($customItems as $item) {
            if (is_array($item)) {
                $candidates[] = $item;
            }
        }

        // 2. Ambil dari API Tridharma
        try {
            $resp = $this->getSertifikasiList([
                'tipe_pemilik' => 'tendik',
                'id_tendik' => $tendikId,
                'per_page' => 100,
                'page' => 1,
            ]);

            if ($resp->successful() && $resp->json('success') !== false) {
                $payload = $resp->json();
                $data = is_array($payload) ? ($payload['data'] ?? []) : [];
                $records = is_array($data) && is_array($data['data'] ?? null)
                    ? $data['data']
                    : (is_array($data) && array_is_list($data) ? $data : []);

                foreach ($records as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    if (isset($item['id_tendik']) && (int) $item['id_tendik'] !== $tendikId) {
                        continue;
                    }
                    $candidates[] = $item;
                }
            }
        } catch (\Throwable) {
        }

        // 3. Normalisasi, filter deleted, dan deduplikasi
        $result = [];
        $seen = [];

        foreach ($candidates as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = $item['id'] ?? $item['id_sertifikasi'] ?? null;
            $nama = trim((string) ($item['jenis_sertifikasi'] ?? ''));
            $tahun = trim((string) ($item['tahun_sertifikasi'] ?? ''));
            $nomor = trim((string) ($item['nomor_sertifikat'] ?? ''));

            if ($id !== null && in_array((string) $id, $deletedItems, true)) {
                continue;
            }

            $sig = md5(strtolower($nama.'|'.$tahun.'|'.$nomor));
            if (in_array($sig, $deletedItems, true)) {
                continue;
            }

            $dedupKey = $id ? "id_{$id}" : "sig_{$sig}";
            if (isset($seen[$dedupKey])) {
                continue;
            }
            $seen[$dedupKey] = true;

            $item['id'] = $id ?? hexdec(substr($sig, 0, 7));
            $item['id_tendik'] = $tendikId;
            $item['jenis_sertifikasi'] = $nama !== '' ? $nama : 'Sertifikasi';
            $item['tahun_sertifikasi'] = $tahun;

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Menyimpan sertifikasi baru untuk tendik, memanggil API dan menyinkronkan ke cache lokal.
     *
     * @param  array<string, mixed>  $payload
     */
    public function storeTendikSertifikasi(int|string $tendikId, array $payload, ?UploadedFile $evidenceFile = null, bool $asMultipart = false): Response
    {
        $tendikId = (int) $tendikId;
        $payload['id_tendik'] = $tendikId;
        $payload['tipe_pemilik'] = 'tendik';
        if (empty($payload['url_sertifikasi'])) {
            $payload['url_sertifikasi'] = $payload['url_drive'] ?? 'https://drive.google.com/';
        }

        $response = ($asMultipart || $evidenceFile !== null)
            ? $this->createSertifikasi($payload, $evidenceFile)
            : $this->createSertifikasi($payload);

        $customKey = "tendik_custom_sertifikasi_{$tendikId}";
        $custom = Cache::get($customKey, []);
        if (! is_array($custom)) {
            $custom = [];
        }

        $newId = null;
        if ($response->successful()) {
            $data = $response->json('data', []);
            $newId = is_array($data) ? ($data['id'] ?? $data['id_sertifikasi'] ?? null) : null;
        }

        $payload['id'] = $newId ?? time();
        $custom[] = $payload;
        Cache::put($customKey, $custom, now()->addYears(5));

        return $response;
    }

    /**
     * Menghapus sertifikasi tendik dan mencatatnya di blacklist cache lokal.
     */
    public function deleteTendikSertifikasi(int|string $tendikId, int|string $sertifikasiId): Response
    {
        $tendikId = (int) $tendikId;
        $sertifikasiId = (int) $sertifikasiId;

        $deletedKey = "tendik_deleted_sertifikasi_{$tendikId}";
        $deleted = Cache::get($deletedKey, []);
        if (! is_array($deleted)) {
            $deleted = [];
        }
        if (! in_array((string) $sertifikasiId, $deleted, true)) {
            $deleted[] = (string) $sertifikasiId;
            Cache::put($deletedKey, $deleted, now()->addYears(5));
        }

        $customKey = "tendik_custom_sertifikasi_{$tendikId}";
        $custom = Cache::get($customKey, []);
        if (is_array($custom)) {
            $custom = array_values(array_filter($custom, function ($item) use ($sertifikasiId) {
                $id = $item['id'] ?? $item['id_sertifikasi'] ?? null;

                return (string) $id !== (string) $sertifikasiId;
            }));
            Cache::put($customKey, $custom, now()->addYears(5));
        }

        return $this->deleteSertifikasi($sertifikasiId);
    }

    /**
     * Memperbarui sertifikasi tendik dan menyinkronkan ke cache lokal.
     *
     * @param  array<string, mixed>  $payload
     */
    public function updateTendikSertifikasi(int|string $tendikId, int|string $sertifikasiId, array $payload): Response
    {
        $tendikId = (int) $tendikId;
        $sertifikasiId = (int) $sertifikasiId;
        $payload['id_tendik'] = $tendikId;
        $payload['tipe_pemilik'] = 'tendik';

        $response = $this->updateSertifikasi($sertifikasiId, $payload);

        $customKey = "tendik_custom_sertifikasi_{$tendikId}";
        $custom = Cache::get($customKey, []);
        if (is_array($custom)) {
            foreach ($custom as &$item) {
                $id = $item['id'] ?? $item['id_sertifikasi'] ?? null;
                if ((string) $id === (string) $sertifikasiId) {
                    $item = array_merge($item, $payload);
                    break;
                }
            }
            Cache::put($customKey, $custom, now()->addYears(5));
        }

        return $response;
    }

    /**
     * Menggabungkan data sertifikasi dari API dengan cache lokal untuk tendik tertentu.
     *
     * @param  array<int, array<string, mixed>>  $apiRecords
     * @return array<int, array<string, mixed>>
     */
    public function mergeTendikSertifikasi(int|string $tendikId, array $apiRecords): array
    {
        $tendikId = (int) $tendikId;
        $customKey = "tendik_custom_sertifikasi_{$tendikId}";
        $deletedKey = "tendik_deleted_sertifikasi_{$tendikId}";

        $customItems = Cache::get($customKey, []);
        if (! is_array($customItems)) {
            $customItems = [];
        }

        $deletedItems = Cache::get($deletedKey, []);
        if (! is_array($deletedItems)) {
            $deletedItems = [];
        }
        $deletedItems = array_map('strval', $deletedItems);

        $merged = [];
        $seen = [];

        foreach ($customItems as $item) {
            if (! is_array($item)) {
                continue;
            }
            $id = $item['id'] ?? $item['id_sertifikasi'] ?? null;
            if ($id !== null && in_array((string) $id, $deletedItems, true)) {
                continue;
            }
            $sig = md5(strtolower(($item['jenis_sertifikasi'] ?? '').'|'.($item['tahun_sertifikasi'] ?? '').'|'.($item['nomor_sertifikat'] ?? '')));
            if (in_array($sig, $deletedItems, true)) {
                continue;
            }
            $key = $id ? "id_{$id}" : "sig_{$sig}";
            $seen[$key] = true;
            $merged[] = $item;
        }

        foreach ($apiRecords as $item) {
            if (! is_array($item)) {
                continue;
            }
            $id = $item['id'] ?? $item['id_sertifikasi'] ?? null;
            if ($id !== null && in_array((string) $id, $deletedItems, true)) {
                continue;
            }
            $sig = md5(strtolower(($item['jenis_sertifikasi'] ?? '').'|'.($item['tahun_sertifikasi'] ?? '').'|'.($item['nomor_sertifikat'] ?? '')));
            if (in_array($sig, $deletedItems, true)) {
                continue;
            }
            $key = $id ? "id_{$id}" : "sig_{$sig}";
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $merged[] = $item;
        }

        return $merged;
    }

    /*
    |==========================================================================
    | SINKRONISASI PENDIDIKAN TENAGA KEPENDIDIKAN
    |==========================================================================
    */

    /**
     * Mengambil daftar lengkap riwayat pendidikan tenaga kependidikan, disinkronkan antara
     * API Tridharma dan cache lokal (tanpa modifikasi database).
     *
     * @param  array<string, mixed>|null  $tendikApiRecord
     * @return array<int, array{id: int, id_pendidikan: int, universitas: string, institusi: string, program_studi: string, jenjang: string, tahun_lulus: string, gelar: string}>
     */
    public function getTendikPendidikanList(int|string $tendikId, ?array $tendikApiRecord = null): array
    {
        $tendikId = (int) $tendikId;
        if ($tendikApiRecord === null) {
            try {
                $resp = $this->getTendik($tendikId);
                if ($resp->successful()) {
                    $tendikApiRecord = $resp->json('data', []);
                }
            } catch (\Throwable) {
            }
        }
        $record = is_array($tendikApiRecord) ? $tendikApiRecord : [];

        // Track this tendik ID in registry
        $allTendikIds = Cache::get('all_cached_tendik_ids', []);
        if (! in_array($tendikId, $allTendikIds, true)) {
            $allTendikIds[] = $tendikId;
            Cache::put('all_cached_tendik_ids', $allTendikIds, now()->addYears(5));
        }

        $cacheKey = "tendik_kualifikasi_{$tendikId}";
        $customKey = "tendik_custom_pendidikan_{$tendikId}";
        $deletedKey = "tendik_deleted_pendidikan_{$tendikId}";

        $cachedIds = Cache::get($cacheKey, []);
        if (! is_array($cachedIds)) {
            $cachedIds = [];
        }
        $cachedIds = array_map('intval', array_filter($cachedIds));

        $customItems = Cache::get($customKey, []);
        if (! is_array($customItems)) {
            $customItems = [];
        }

        $deletedItems = Cache::get($deletedKey, []);
        if (! is_array($deletedItems)) {
            $deletedItems = [];
        }
        $deletedItems = array_map('strval', $deletedItems);

        // 1. Gather all raw candidate items
        $candidates = [];

        // Candidates from customItems
        foreach ($customItems as $item) {
            if (is_array($item)) {
                $candidates[] = $item;
            }
        }

        // Candidates from cachedIds (fetch from API if not yet in candidates)
        $knownIdsInCandidates = array_filter(array_column($candidates, 'id'));
        foreach ($cachedIds as $pId) {
            if (in_array($pId, $knownIdsInCandidates, true)) {
                continue;
            }
            try {
                $resp = $this->getPendidikanById($pId);
                if ($resp->successful()) {
                    $p = $resp->json('data', []);
                    if (is_array($p)) {
                        $p['id'] = $pId;
                        $candidates[] = $p;
                    }
                }
            } catch (\Throwable) {
            }
        }

        // Candidates from API record: kualifikasi / kualifikasis
        $rawKualifikasi = $record['kualifikasi'] ?? $record['kualifikasis'] ?? [];
        if (is_array($rawKualifikasi) && array_is_list($rawKualifikasi)) {
            foreach ($rawKualifikasi as $k) {
                if (is_array($k)) {
                    $k['id'] = $k['id'] ?? $k['id_pendidikan'] ?? null;
                    if (empty($k['id'])) {
                        $sig = md5(($k['universitas'] ?? $k['institusi'] ?? '').'|'.($k['program_studi'] ?? '').'|'.($k['jenjang'] ?? '').'|'.($k['tahun_lulus'] ?? ''));
                        $k['id'] = hexdec(substr($sig, 0, 7));
                    }
                    $candidates[] = $k;
                }
            }
        }

        // Candidates from API record: pendidikan object
        if (isset($record['pendidikan']) && is_array($record['pendidikan']) && (! empty($record['pendidikan']['jenjang']) || ! empty($record['pendidikan']['universitas']))) {
            $p = $record['pendidikan'];
            $p['id'] = $p['id'] ?? $p['id_pendidikan'] ?? $record['id_pendidikan'] ?? null;
            if (empty($p['id'])) {
                $sig = md5(($p['universitas'] ?? $p['institusi'] ?? '').'|'.($p['program_studi'] ?? '').'|'.($p['jenjang'] ?? '').'|'.($p['tahun_lulus'] ?? ''));
                $p['id'] = hexdec(substr($sig, 0, 7));
            }
            $candidates[] = $p;
        }

        // Also check if id_pendidikan is on record
        $apiPendidikanId = $record['id_pendidikan'] ?? null;
        if ($apiPendidikanId !== null) {
            $apiPendidikanId = (int) $apiPendidikanId;
            if (! in_array($apiPendidikanId, $cachedIds, true)) {
                $cachedIds[] = $apiPendidikanId;
            }
            if (! in_array($apiPendidikanId, array_filter(array_column($candidates, 'id')), true)) {
                try {
                    $resp = $this->getPendidikanById($apiPendidikanId);
                    if ($resp->successful()) {
                        $p = $resp->json('data', []);
                        if (is_array($p)) {
                            $p['id'] = $apiPendidikanId;
                            $candidates[] = $p;
                        }
                    }
                } catch (\Throwable) {
                }
            }
        }

        // 2. Normalize, filter deleted, and deduplicate
        $normalized = [];
        $seenSignatures = [];
        $validCachedIds = [];

        foreach ($candidates as $item) {
            if (! is_array($item)) {
                continue;
            }

            $rawJenjang = trim((string) ($item['jenjang'] ?? $item['nama_pendidikan'] ?? ''));
            $rawGelar = trim((string) ($item['gelar'] ?? ''));
            $isJenjangCode = in_array(strtoupper($rawGelar), ['D3', 'D4', 'S1', 'D4/S1', 'S2', 'S3', 'PROFESI']);
            $jenjang = $rawJenjang !== '' ? $rawJenjang : ($isJenjangCode ? $rawGelar : '');
            $gelar = ($rawGelar !== '' && ! $isJenjangCode) ? $rawGelar : self::defaultGelarForJenjang($jenjang);
            $univ = trim((string) ($item['universitas'] ?? $item['institusi'] ?? ''));
            $prodi = trim((string) ($item['program_studi'] ?? ''));
            $tahun = trim((string) ($item['tahun_lulus'] ?? ''));
            $id = (int) ($item['id'] ?? $item['id_pendidikan'] ?? 0);

            if ($jenjang === '' && $univ === '' && $gelar === '') {
                continue;
            }

            $sig = md5(strtolower($univ.'|'.$prodi.'|'.$jenjang.'|'.$tahun));

            // Filter out deleted items
            if (in_array((string) $id, $deletedItems, true) || in_array($sig, $deletedItems, true)) {
                continue;
            }

            // Deduplicate
            if (isset($seenSignatures[$sig])) {
                continue;
            }
            $seenSignatures[$sig] = true;

            $recordData = [
                'id' => $id,
                'id_pendidikan' => $id,
                'universitas' => $univ,
                'institusi' => $univ,
                'program_studi' => $prodi,
                'jenjang' => $jenjang,
                'tahun_lulus' => $tahun,
                'gelar' => $gelar,
            ];

            $normalized[] = $recordData;
            if ($id > 0) {
                $validCachedIds[] = $id;
                $customItems[$id] = $recordData;
                Cache::put("pendidikan_tendik_{$id}", $tendikId, now()->addYears(5));
            }
        }

        // Keep caches updated
        Cache::put($cacheKey, array_values(array_unique($validCachedIds)), now()->addYears(5));
        Cache::put($customKey, $customItems, now()->addYears(5));

        // 3. Sort by Jenjang Rank, then Tahun Lulus
        $jenjangRank = [
            'SMA' => 1, 'SMK' => 1,
            'D1' => 2, 'D2' => 3, 'D3' => 4,
            'D4' => 5, 'S1' => 5, 'D4/S1' => 5,
            'PROFESI' => 6,
            'S2' => 7, 'S2 TERAPAN' => 7,
            'S3' => 8, 'S3 TERAPAN' => 8,
        ];

        usort($normalized, function (array $a, array $b) use ($jenjangRank) {
            $rankA = $jenjangRank[strtoupper(trim($a['jenjang'] ?? ''))] ?? 99;
            $rankB = $jenjangRank[strtoupper(trim($b['jenjang'] ?? ''))] ?? 99;

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            return ((int) ($a['tahun_lulus'] ?? 0)) <=> ((int) ($b['tahun_lulus'] ?? 0));
        });

        return $normalized;
    }

    /**
     * Menambahkan pendidikan baru untuk tendik dan menyinkronkannya ke cache & API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function storeTendikPendidikan(int|string $tendikId, array $data): array
    {
        $tendikId = (int) $tendikId;

        // 1. Ensure existing records are loaded and preserved in cache
        $this->getTendikPendidikanList($tendikId);

        $jenjang = trim((string) ($data['jenjang'] ?? ''));
        $univ = trim((string) ($data['universitas'] ?? $data['institusi'] ?? ''));
        $prodi = trim((string) ($data['program_studi'] ?? ''));
        $tahun = trim((string) ($data['tahun_lulus'] ?? ''));
        $gelar = trim((string) ($data['gelar'] ?? self::defaultGelarForJenjang($jenjang)));

        // Create via API
        $payload = array_filter([
            'universitas' => $univ,
            'institusi' => $univ,
            'program_studi' => $prodi,
            'jenjang' => $jenjang,
            'tahun_lulus' => $tahun !== '' ? (int) $tahun : null,
            'gelar' => $gelar !== '' ? $gelar : null,
        ], fn ($v) => $v !== null && $v !== '');

        $newId = null;
        try {
            $resp = $this->createPendidikan($payload);
            if ($resp->successful()) {
                $newId = $resp->json('data.id_pendidikan')
                    ?? $resp->json('data.id')
                    ?? $resp->json('id_pendidikan')
                    ?? $resp->json('id')
                    ?? null;
            }
        } catch (\Throwable) {
        }

        if (! $newId) {
            $newId = time() + random_int(100, 999);
        }
        $newId = (int) $newId;

        $newItem = [
            'id' => $newId,
            'id_pendidikan' => $newId,
            'universitas' => $univ,
            'institusi' => $univ,
            'program_studi' => $prodi,
            'jenjang' => $jenjang,
            'tahun_lulus' => $tahun,
            'gelar' => $gelar,
        ];

        // 2. Remove from deleted list if present
        $deletedKey = "tendik_deleted_pendidikan_{$tendikId}";
        $deletedItems = Cache::get($deletedKey, []);
        $sig = md5(strtolower($univ.'|'.$prodi.'|'.$jenjang.'|'.$tahun));
        $deletedItems = array_values(array_filter($deletedItems, fn ($d) => (string) $d !== (string) $newId && (string) $d !== $sig));
        Cache::put($deletedKey, $deletedItems, now()->addYears(5));

        // 3. Add to cache
        $cacheKey = "tendik_kualifikasi_{$tendikId}";
        $customKey = "tendik_custom_pendidikan_{$tendikId}";
        $cachedIds = Cache::get($cacheKey, []);
        if (! in_array($newId, $cachedIds, true)) {
            $cachedIds[] = $newId;
        }
        Cache::put($cacheKey, array_values(array_unique($cachedIds)), now()->addYears(5));

        $customItems = Cache::get($customKey, []);
        $customItems[$newId] = $newItem;
        Cache::put($customKey, $customItems, now()->addYears(5));

        Cache::put("pendidikan_tendik_{$newId}", $tendikId, now()->addYears(5));

        // 4. Update Tendik in API
        try {
            $this->updateTendik($tendikId, ['id_pendidikan' => $newId]);
        } catch (\Throwable) {
        }

        return $newItem;
    }

    /**
     * Menghapus pendidikan untuk tendik, menyinkronkan penghapusan ke cache dan API.
     */
    public function deleteTendikPendidikan(int|string $tendikId, int|string $pendidikanId): bool
    {
        $tendikId = (int) $tendikId;
        $pendidikanId = (int) $pendidikanId;

        $cacheKey = "tendik_kualifikasi_{$tendikId}";
        $customKey = "tendik_custom_pendidikan_{$tendikId}";
        $deletedKey = "tendik_deleted_pendidikan_{$tendikId}";

        // 1. Get the current list to find the item's signature
        $currentItems = $this->getTendikPendidikanList($tendikId);
        $deletedItems = Cache::get($deletedKey, []);
        if (! is_array($deletedItems)) {
            $deletedItems = [];
        }

        $deletedItems[] = (string) $pendidikanId;

        foreach ($currentItems as $item) {
            if ((int) ($item['id'] ?? 0) === $pendidikanId) {
                $univ = $item['universitas'] ?? $item['institusi'] ?? '';
                $prodi = $item['program_studi'] ?? '';
                $jenjang = $item['jenjang'] ?? '';
                $tahun = $item['tahun_lulus'] ?? '';
                $sig = md5(strtolower($univ.'|'.$prodi.'|'.$jenjang.'|'.$tahun));
                $deletedItems[] = $sig;
                break;
            }
        }

        Cache::put($deletedKey, array_values(array_unique($deletedItems)), now()->addYears(5));

        // 2. Remove from cachedIds and customItems
        $cachedIds = Cache::get($cacheKey, []);
        $cachedIds = array_values(array_filter($cachedIds, fn ($id) => (int) $id !== $pendidikanId));
        Cache::put($cacheKey, $cachedIds, now()->addYears(5));

        $customItems = Cache::get($customKey, []);
        unset($customItems[$pendidikanId]);
        Cache::put($customKey, $customItems, now()->addYears(5));

        // 3. Delete from API
        try {
            $this->deletePendidikan($pendidikanId);
        } catch (\Throwable) {
        }

        // 4. Update Tendik in API with remaining education or null
        $remaining = $this->getTendikPendidikanList($tendikId);
        $newLinkedId = ! empty($remaining) ? (int) $remaining[count($remaining) - 1]['id'] : null;
        try {
            $this->updateTendik($tendikId, ['id_pendidikan' => $newLinkedId]);
        } catch (\Throwable) {
        }

        return true;
    }

    /**
     * Memperbarui pendidikan untuk tendik.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTendikPendidikan(int|string $tendikId, int|string $pendidikanId, array $data): bool
    {
        $tendikId = (int) $tendikId;
        $pendidikanId = (int) $pendidikanId;

        $jenjang = trim((string) ($data['jenjang'] ?? ''));
        $univ = trim((string) ($data['universitas'] ?? $data['institusi'] ?? ''));
        $prodi = trim((string) ($data['program_studi'] ?? ''));
        $tahun = trim((string) ($data['tahun_lulus'] ?? ''));
        $gelar = trim((string) ($data['gelar'] ?? self::defaultGelarForJenjang($jenjang)));

        $payload = array_filter([
            'universitas' => $univ,
            'institusi' => $univ,
            'program_studi' => $prodi,
            'jenjang' => $jenjang,
            'tahun_lulus' => $tahun !== '' ? (int) $tahun : null,
            'gelar' => $gelar !== '' ? $gelar : null,
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $this->updatePendidikan($pendidikanId, $payload);
        } catch (\Throwable) {
        }

        $customKey = "tendik_custom_pendidikan_{$tendikId}";
        $customItems = Cache::get($customKey, []);
        $customItems[$pendidikanId] = [
            'id' => $pendidikanId,
            'id_pendidikan' => $pendidikanId,
            'universitas' => $univ,
            'institusi' => $univ,
            'program_studi' => $prodi,
            'jenjang' => $jenjang,
            'tahun_lulus' => $tahun,
            'gelar' => $gelar,
        ];
        Cache::put($customKey, $customItems, now()->addYears(5));

        return true;
    }

    public static function defaultGelarForJenjang(string $jenjang): string
    {
        $j = strtoupper(trim($jenjang));
        if (str_contains($j, 'S3')) {
            return 'Dr';
        }
        if (str_contains($j, 'S2')) {
            return 'M.Kom';
        }
        if (str_contains($j, 'S1') || str_contains($j, 'D4')) {
            return 'S.Kom';
        }
        if (str_contains($j, 'D3')) {
            return 'A.Md';
        }

        return '';
    }

    /*
    |==========================================================================
    | MODUL: STATISTIK & DASHBOARD (/api/v1/*)
    |==========================================================================
    */

    public function getDosenStatistik(?string $token = null): Response
    {
        return $this->http($token)->get($this->url('v1/dosen/statistik'));
    }

    /** @param array<string, mixed> $query */
    public function getTridharmaSummary(array $query = [], ?string $token = null): Response
    {
        return $this->http($token)->get($this->url('v1/tridharma/summary'), $query);
    }

    /** @param array<string, mixed> $query */
    public function getKerjasamaAktif(array $query = [], ?string $token = null): Response
    {
        return $this->http($token)->get($this->url('v1/kerjasama/aktif'), $query);
    }

    /** @param array<string, mixed> $query */
    public function getTridharmaTren(array $query = [], ?string $token = null): Response
    {
        return $this->http($token)->get($this->url('v1/tren-tridharma'), $query);
    }
}
