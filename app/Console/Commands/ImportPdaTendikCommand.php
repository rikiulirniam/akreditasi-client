<?php

namespace App\Console\Commands;

use App\Models\TenagaKependidikan;
use App\Services\TenagaKependidikanService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ImportPdaTendikCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pda:import-tendik
                            {--start-page=1 : Halaman awal yang akan diambil dari PDA Polines}
                            {--max-pages=20 : Batas maksimum halaman yang akan diambil}
                            {--sync-api : Sinkronkan juga data hasil scraping ke API Tridharma}
                            {--token= : Token Bearer API Tridharma (opsional, otomatis membaca sesi aktif jika kosong)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Impor data Tenaga Kependidikan dari PDA Polines ke tabel tenaga_kependidikan';

    /**
     * Endpoint daftar Tenaga Kependidikan PDA Polines.
     */
    protected const PDA_TENDIK_URL = 'https://pda.polines.ac.id/tendik';

    /**
     * Cache pemetaan jenjang ke ID pendidikan yang valid.
     *
     * @var array<string, int>
     */
    protected array $pendidikanMap = [];

    /**
     * Fallback ID pendidikan default yang valid.
     */
    protected int $defaultPendidikanId = 1;

    /**
     * Service API Tridharma untuk sinkronisasi opsional.
     */
    protected ?TenagaKependidikanService $apiService = null;

    /**
     * Pemetaan nip => id_tendik yang sudah ada di API Tridharma.
     *
     * @var array<string, int>
     */
    protected array $existingApiByNip = [];

    /**
     * Pemetaan signature pendidikan => id_pendidikan pada API Tridharma.
     *
     * @var array<string, int>
     */
    protected array $apiPendidikanBySig = [];

    /**
     * Apakah tabel tenaga_kependidikan lokal tersedia untuk disimpan.
     */
    protected bool $hasLocalTable = false;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startPage = max(1, (int) $this->option('start-page'));
        $maxPages = max($startPage, (int) $this->option('max-pages'));

        $this->hasLocalTable = $this->checkLocalTable();
        $this->resolvePendidikanMapping();

        if ((bool) $this->option('sync-api') || (! $this->hasLocalTable && ! app()->runningUnitTests())) {
            $this->initializeApiSync();
        }

        $this->info("Memulai impor data Tenaga Kependidikan dari PDA Polines (halaman {$startPage} s.d. {$maxPages})...");

        $totalImported = 0;
        $currentPage = $startPage;
        $targetLastPage = $maxPages;

        while ($currentPage <= $targetLastPage) {
            $response = $this->fetchPage($currentPage);

            if ($response === null || ! $response->successful()) {
                $status = $response?->status() ?? 'Connection Error';
                $this->error("Gagal mengambil halaman {$currentPage} dari PDA Polines (Status: {$status}).");

                return self::FAILURE;
            }

            $listTendik = $this->extractListTendik($response);

            if ($listTendik === null) {
                $this->error("Gagal mem-parsing props.listTendik pada halaman {$currentPage}.");

                return self::FAILURE;
            }

            if ($currentPage === $startPage && isset($listTendik['last_page'])) {
                $reportedLastPage = max(1, (int) $listTendik['last_page']);
                $targetLastPage = min($maxPages, $reportedLastPage);
            }

            $records = $this->extractRecords($listTendik);

            if ($records === []) {
                break;
            }

            if ($this->apiService !== null && ! app()->runningUnitTests()) {
                $records = $this->enrichRecordsWithDetailPendidikan($records);
            }

            foreach ($records as $item) {
                $this->upsertTendik($item);
                $totalImported++;
            }

            $this->line("Halaman {$currentPage}/{$targetLastPage} berhasil diproses (".count($records).' tendik).');
            $currentPage++;
        }

        $this->info("Selesai! Total {$totalImported} data Tenaga Kependidikan berhasil disinkronkan.");

        return self::SUCCESS;
    }

    /**
     * Mengambil satu halaman daftar tendik dari PDA Polines menggunakan HTTP/2 dan header browser desktop.
     */
    protected function fetchPage(int $page): ?Response
    {
        $url = self::PDA_TENDIK_URL."?mode=tabel&page={$page}";

        try {
            return Http::withOptions([
                'version' => 2.0,
            ])->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Referer' => self::PDA_TENDIK_URL,
            ])->timeout(30)->get($url);
        } catch (Throwable $exception) {
            $this->warn("Exception saat mengambil halaman {$page}: {$exception->getMessage()}");

            return null;
        }
    }

    /**
     * Mengekstrak objek props.listTendik baik dari respons JSON Inertia maupun atribut data-page HTML.
     *
     * @return array<string, mixed>|null
     */
    protected function extractListTendik(Response $response): ?array
    {
        $jsonPayload = $response->json();

        if (is_array($jsonPayload)) {
            $listTendik = data_get($jsonPayload, 'props.listTendik');
            if (is_array($listTendik)) {
                return $listTendik;
            }
        }

        $body = $response->body();

        if (preg_match('/data-page=(["\'])(.*?)\1/s', $body, $matches) === 1) {
            $decodedHtml = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $pageData = json_decode($decodedHtml, true);

            if (is_array($pageData)) {
                $listTendik = data_get($pageData, 'props.listTendik');
                if (is_array($listTendik)) {
                    return $listTendik;
                }
            }
        }

        return null;
    }

    /**
     * Mengambil daftar item tendik dari struktur paginator listTendik.
     *
     * @param  array<string, mixed>  $listTendik
     * @return array<int, array<string, mixed>>
     */
    protected function extractRecords(array $listTendik): array
    {
        $data = $listTendik['data'] ?? (array_is_list($listTendik) ? $listTendik : []);

        if (! is_array($data)) {
            return [];
        }

        return array_values(array_filter($data, 'is_array'));
    }

    /**
     * Menyimpan atau memperbarui satu record Tenaga Kependidikan ke tabel tenaga_kependidikan.
     *
     * @param  array<string, mixed>  $item
     */
    protected function upsertTendik(array $item): TenagaKependidikan
    {
        $namaTendik = $this->formatNamaTendik(
            $item['gelar_depan'] ?? null,
            $item['nama'] ?? null,
            $item['gelar_belakang'] ?? null,
        );

        $nip = $this->resolveNip(
            $item['nip'] ?? null,
            $item['slug'] ?? null,
            $namaTendik,
        );

        $jabatanTendik = $this->resolveJabatan($item['jabatan'] ?? null);

        $sertifikasi = $this->formatSertifikasi(
            $item['pangkat'] ?? null,
            $item['golongan'] ?? null,
            $item['penempatan_nama'] ?? $item['unit_penempatan'] ?? $item['penempatan'] ?? null,
        );

        $idPendidikan = $this->resolveIdPendidikan(
            $item['gelar_depan'] ?? null,
            $item['gelar_belakang'] ?? null,
            $item['pendidikan'] ?? ($item['pendidikan_terakhir'] ?? null),
        );

        $syncedRiwayat = $this->syncRiwayatPendidikanToApi(
            is_array($item['riwayat_pendidikan'] ?? null) ? $item['riwayat_pendidikan'] : [],
            $item['pendidikan_terakhir'] ?? ($item['pendidikan'] ?? null),
            $item['gelar_depan'] ?? null,
            $item['gelar_belakang'] ?? null,
        );

        if ($syncedRiwayat['primary_id'] !== null) {
            $idPendidikan = $syncedRiwayat['primary_id'];
        }

        $attributes = [
            'nama_tendik' => $namaTendik,
            'jabatan_tendik' => $jabatanTendik,
            'sertifikasi' => $sertifikasi,
            'id_pendidikan' => $idPendidikan,
        ];

        $model = $this->hasLocalTable
            ? TenagaKependidikan::query()->updateOrCreate(['nip' => $nip], $attributes)
            : new TenagaKependidikan(['nip' => $nip, ...$attributes]);

        $tendikApiId = $this->syncToApi([
            'id_pendidikan' => $idPendidikan,
            'nama_tendik' => $namaTendik,
            'jabatan_tendik' => $jabatanTendik,
            'nip' => $nip,
            'sertifikasi' => $sertifikasi,
        ]);

        if ($tendikApiId !== null && $syncedRiwayat['items'] !== []) {
            $this->syncTendikPendidikanCache($tendikApiId, $syncedRiwayat['items']);
        }

        return $model;
    }

    /**
     * Mengambil halaman detail setiap tendik secara paralel (HTTP/2 pool) untuk mendapatkan
     * pendidikanTerakhir dan riwayatPendidikan asli dari PDA Polines.
     *
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    protected function enrichRecordsWithDetailPendidikan(array $records): array
    {
        $slugsByIndex = [];
        foreach ($records as $idx => $record) {
            $slug = $this->cleanText($record['slug'] ?? null);
            if ($slug !== '') {
                $slugsByIndex[$idx] = $slug;
            }
        }

        if ($slugsByIndex === []) {
            return $records;
        }

        try {
            $responses = Http::pool(function (Pool $pool) use ($slugsByIndex): array {
                $requests = [];
                foreach ($slugsByIndex as $idx => $slug) {
                    $requests[(string) $idx] = $pool->as((string) $idx)
                        ->withOptions(['version' => 2.0])
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                            'Cache-Control' => 'no-cache',
                            'Pragma' => 'no-cache',
                            'Referer' => self::PDA_TENDIK_URL,
                        ])
                        ->timeout(30)
                        ->get(self::PDA_TENDIK_URL.'/'.rawurlencode($slug));
                }

                return $requests;
            });

            foreach ($slugsByIndex as $idx => $slug) {
                $res = $responses[(string) $idx] ?? null;
                if (! $res instanceof Response || ! $res->successful()) {
                    continue;
                }

                $props = $this->extractInertiaProps($res);
                if (! is_array($props)) {
                    continue;
                }

                $pendidikanTerakhir = $this->cleanText($props['pendidikanTerakhir'] ?? null);
                if ($pendidikanTerakhir !== '') {
                    $records[$idx]['pendidikan_terakhir'] = $pendidikanTerakhir;
                }

                if (isset($props['riwayatPendidikan']) && is_array($props['riwayatPendidikan'])) {
                    $records[$idx]['riwayat_pendidikan'] = array_values(array_filter($props['riwayatPendidikan'], 'is_array'));
                }
            }
        } catch (Throwable) {
            // Lanjutkan dengan data tabel jika request detail gagal
        }

        return $records;
    }

    /**
     * Mengekstrak props Inertia dari Response HTML atau JSON.
     *
     * @return array<string, mixed>|null
     */
    protected function extractInertiaProps(Response $response): ?array
    {
        $jsonPayload = $response->json();
        if (is_array($jsonPayload) && is_array($jsonPayload['props'] ?? null)) {
            return $jsonPayload['props'];
        }

        $body = $response->body();
        if (preg_match('/data-page=(["\'])(.*?)\1/s', $body, $matches) === 1) {
            $decodedHtml = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $pageData = json_decode($decodedHtml, true);
            if (is_array($pageData) && is_array($pageData['props'] ?? null)) {
                return $pageData['props'];
            }
        }

        return null;
    }

    /**
     * Menyinkronkan daftar riwayatPendidikan dari PDA ke tabel/endpoint pendidikan di API Tridharma.
     *
     * @param  array<int, array<string, mixed>>  $riwayatPendidikan
     * @return array{primary_id: int|null, items: array<int, array<string, mixed>>}
     */
    protected function syncRiwayatPendidikanToApi(
        array $riwayatPendidikan,
        mixed $pendidikanTerakhir,
        mixed $gelarDepan,
        mixed $gelarBelakang,
    ): array {
        if ($this->apiService === null) {
            return ['primary_id' => null, 'items' => []];
        }

        if ($riwayatPendidikan === []) {
            $fallbackJenjang = $this->normalizeJenjangLabel($pendidikanTerakhir)
                ?? $this->inferJenjang($gelarDepan, $gelarBelakang, $pendidikanTerakhir)
                ?? 'SMA/SMK';

            $riwayatPendidikan = [[
                'jenjang' => $fallbackJenjang,
                'sekolah' => 'Politeknik Negeri Semarang',
                'jurusan' => '-',
                'tahunLulus' => 2020,
            ]];
        }

        $syncedItems = [];
        $primaryId = null;

        foreach ($riwayatPendidikan as $index => $edu) {
            $jenjang = $this->normalizeJenjangLabel($edu['jenjang'] ?? null)
                ?? $this->normalizeJenjangLabel($pendidikanTerakhir)
                ?? $this->inferJenjang($gelarDepan, $gelarBelakang, $pendidikanTerakhir)
                ?? 'SMA/SMK';

            $sekolah = $this->cleanText($edu['sekolah'] ?? $edu['universitas'] ?? $edu['institusi'] ?? null);
            if ($sekolah === '') {
                $sekolah = '-';
            }

            $jurusan = $this->cleanText($edu['jurusan'] ?? $edu['program_studi'] ?? null);
            if ($jurusan === '') {
                $jurusan = '-';
            }

            $rawTahun = (int) ($edu['tahunLulus'] ?? $edu['tahun_lulus'] ?? 0);
            $tahunLulus = ($rawTahun >= 1950 && $rawTahun <= 2100) ? $rawTahun : 2020;

            $eduId = $this->findOrCreateApiPendidikan($jenjang, $sekolah, $jurusan, $tahunLulus);

            if ($eduId !== null) {
                if ($primaryId === null || $index === 0) {
                    $primaryId = $eduId;
                }

                $syncedItems[] = [
                    'id' => $eduId,
                    'id_pendidikan' => $eduId,
                    'jenjang' => $jenjang,
                    'universitas' => $sekolah,
                    'institusi' => $sekolah,
                    'program_studi' => $jurusan === '-' ? '' : $jurusan,
                    'tahun_lulus' => $rawTahun > 0 ? (string) $rawTahun : '',
                    'gelar' => '',
                ];
            }
        }

        return [
            'primary_id' => $primaryId,
            'items' => $syncedItems,
        ];
    }

    /**
     * Mencari atau membuat record pendidikan di API Tridharma berdasarkan signature unik.
     */
    protected function findOrCreateApiPendidikan(string $jenjang, string $universitas, string $programStudi, int $tahunLulus): ?int
    {
        if ($this->apiService === null) {
            return null;
        }

        $sig = strtolower(trim($jenjang).'|'.trim($universitas).'|'.trim($programStudi).'|'.$tahunLulus);

        if (isset($this->apiPendidikanBySig[$sig])) {
            return $this->apiPendidikanBySig[$sig];
        }

        try {
            $res = $this->apiService->createPendidikan([
                'jenjang' => $jenjang,
                'universitas' => $universitas,
                'program_studi' => $programStudi,
                'tahun_lulus' => $tahunLulus,
            ]);

            if ($res->successful()) {
                $newId = (int) ($res->json('data.id_pendidikan') ?? $res->json('data.id') ?? 0);
                if ($newId > 0) {
                    $this->apiPendidikanBySig[$sig] = $newId;
                    if (! isset($this->pendidikanMap[$jenjang])) {
                        $this->pendidikanMap[$jenjang] = $newId;
                    }

                    return $newId;
                }
            }
        } catch (Throwable) {
        }

        return $this->pendidikanMap[$jenjang] ?? $this->defaultPendidikanId;
    }

    /**
     * Menyimpan seluruh riwayat pendidikan tendik ke Cache lokal agar halaman Detail & Pendidikan sinkron.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function syncTendikPendidikanCache(int $tendikId, array $items): void
    {
        $ids = [];
        $customMap = [];

        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $ids[] = $id;
            $customMap[$id] = $item;
            Cache::put("pendidikan_tendik_{$id}", $tendikId, now()->addYears(5));
        }

        Cache::forget("tendik_deleted_pendidikan_{$tendikId}");
        Cache::put("tendik_kualifikasi_{$tendikId}", array_values(array_unique($ids)), now()->addYears(5));
        Cache::put("tendik_custom_pendidikan_{$tendikId}", $customMap, now()->addYears(5));
    }

    /**
     * Memeriksa ketersediaan tabel tenaga_kependidikan pada database lokal.
     */
    protected function checkLocalTable(): bool
    {
        try {
            return Schema::hasTable('tenaga_kependidikan');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Menyiapkan sinkronisasi ke API Tridharma menggunakan token dari opsi CLI atau sesi aktif.
     */
    protected function initializeApiSync(): void
    {
        $token = trim((string) $this->option('token'));

        if ($token === '') {
            $token = $this->resolveLatestSessionToken();
        }

        if ($token === '') {
            $this->warn('Token API tidak ditemukan; hanya menyimpan ke database lokal.');

            return;
        }

        $this->apiService = new TenagaKependidikanService(null, $token);

        try {
            $pendidikanRes = $this->apiService->getDaftarPendidikan(['per_page' => 500]);
            if ($pendidikanRes->successful()) {
                $rows = data_get($pendidikanRes->json(), 'data.data') ?? data_get($pendidikanRes->json(), 'data', []);
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        if (! is_array($row)) {
                            continue;
                        }
                        $id = (int) ($row['id_pendidikan'] ?? $row['id'] ?? 0);
                        $jenjang = $this->normalizeJenjangLabel($row['jenjang'] ?? null) ?? strtoupper(trim((string) ($row['jenjang'] ?? '')));
                        $univ = trim((string) ($row['universitas'] ?? ''));
                        $prodi = trim((string) ($row['program_studi'] ?? ''));
                        $tahun = (int) ($row['tahun_lulus'] ?? 0);

                        if ($id > 0 && $jenjang !== '') {
                            if (! isset($this->pendidikanMap[$jenjang])) {
                                $this->pendidikanMap[$jenjang] = $id;
                            }
                            $sig = strtolower($jenjang.'|'.$univ.'|'.$prodi.'|'.$tahun);
                            $this->apiPendidikanBySig[$sig] = $id;
                        }
                    }

                    $this->defaultPendidikanId = $this->pendidikanMap['SMA/SMK']
                        ?? $this->pendidikanMap['D4/S1']
                        ?? $this->pendidikanMap['S1']
                        ?? $this->pendidikanMap['S2']
                        ?? $this->defaultPendidikanId;
                }
            }

            for ($page = 1; $page <= 5; $page++) {
                $listRes = $this->apiService->getDaftarTendik(['per_page' => 100, 'page' => $page]);
                if (! $listRes->successful()) {
                    break;
                }
                $items = data_get($listRes->json(), 'data.data') ?? data_get($listRes->json(), 'data', []);
                if (! is_array($items) || $items === []) {
                    break;
                }
                foreach ($items as $existing) {
                    if (is_array($existing) && ! empty($existing['nip'])) {
                        $id = (int) ($existing['id_tendik'] ?? $existing['id'] ?? 0);
                        if ($id > 0) {
                            $rawNip = (string) $existing['nip'];
                            $this->existingApiByNip[$rawNip] = $id;
                            $dummyFromSlug = self::generateDummyNip($rawNip);
                            $this->existingApiByNip[$dummyFromSlug] = $id;
                        }
                    }
                }
                $lastPage = (int) (data_get($listRes->json(), 'data.last_page') ?? 1);
                if ($page >= $lastPage) {
                    break;
                }
            }
        } catch (Throwable $exception) {
            $this->warn("Gagal memuat data awal dari API Tridharma: {$exception->getMessage()}");
        }
    }

    /**
     * Mengirim atau memperbarui data tendik ke API Tridharma jika fitur sync-api aktif.
     *
     * @param  array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string, sertifikasi?: string}  $payload
     */
    protected function syncToApi(array $payload): ?int
    {
        if ($this->apiService === null) {
            return null;
        }

        try {
            $nip = $payload['nip'];
            if (isset($this->existingApiByNip[$nip])) {
                $existingId = $this->existingApiByNip[$nip];
                $this->apiService->perbaruiTendik($existingId, $payload);

                return $existingId;
            }

            $response = $this->apiService->tambahTendik($payload);
            if ($response->successful()) {
                $newId = (int) ($response->json('data.id_tendik') ?? $response->json('data.id') ?? 0);
                if ($newId > 0) {
                    $this->existingApiByNip[$nip] = $newId;

                    return $newId;
                }
            }
        } catch (Throwable) {
            // Abaikan kegagalan jaringan per-item agar proses impor tetap berjalan
        }

        return null;
    }

    /**
     * Mencari token Bearer terbaru yang belum kedaluwarsa dari direktori sesi lokal.
     */
    protected function resolveLatestSessionToken(): string
    {
        $sessionPath = storage_path('framework/sessions');
        if (! is_dir($sessionPath)) {
            return '';
        }

        $files = glob($sessionPath.'/*') ?: [];
        usort($files, static fn (string $a, string $b): int => (int) filemtime($b) <=> (int) filemtime($a));

        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $raw = @file_get_contents($file);
            if (! is_string($raw) || $raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                $decoded = @unserialize($raw);
            }

            if (is_array($decoded) && ! empty($decoded['access_token']) && is_string($decoded['access_token'])) {
                return $decoded['access_token'];
            }
        }

        return '';
    }

    /**
     * Menggabungkan gelar_depan, nama, dan gelar_belakang menjadi nama_tendik.
     */
    public function formatNamaTendik(mixed $gelarDepan, mixed $nama, mixed $gelarBelakang): string
    {
        $depan = $this->cleanText($gelarDepan);
        $namaPokok = $this->cleanText($nama);
        $belakang = $this->cleanText($gelarBelakang);

        $fullName = $namaPokok !== '' ? $namaPokok : 'Tenaga Kependidikan';

        if ($depan !== '') {
            $fullName = "{$depan} {$fullName}";
        }

        if ($belakang !== '') {
            $fullName = rtrim($fullName, ', ').', '.ltrim($belakang, ', ');
        }

        return $fullName;
    }

    /**
     * Mengambil NIP dari PDA, atau membuat NIP dummy 18 digit unik secara deterministik dari slug tendik jika null/kosong.
     */
    public function resolveNip(mixed $nip, mixed $slug, string $fallbackName = ''): string
    {
        $cleanNip = preg_replace('/\s+/', '', $this->cleanText($nip)) ?? '';

        if ($cleanNip !== '' && preg_match('/^\d{8,}$/', $cleanNip) === 1) {
            return $cleanNip;
        }

        $cleanSlug = Str::slug($this->cleanText($slug));
        $seed = $cleanSlug !== '' ? $cleanSlug : (Str::slug($fallbackName) ?: $fallbackName);

        return self::generateDummyNip($seed);
    }

    /**
     * Menghasilkan NIP dummy 18 digit (format YYYYMMDDYYYYMMGSSS) secara deterministik dan unik dari seed (slug/nama).
     */
    public static function generateDummyNip(string $seed): string
    {
        $trimmed = trim($seed);
        if (preg_match('/^\d{8,}$/', $trimmed) === 1) {
            return $trimmed;
        }

        $hash = md5($trimmed !== '' ? $trimmed : 'tendik-polines');

        $birthYear = 1970 + (hexdec(substr($hash, 0, 2)) % 28); // 1970..1997
        $birthMonth = 1 + (hexdec(substr($hash, 2, 2)) % 12);   // 01..12
        $birthDay = 1 + (hexdec(substr($hash, 4, 2)) % 28);     // 01..28

        $appointYear = min(2024, max($birthYear + 22, 2005 + (hexdec(substr($hash, 6, 2)) % 19)));
        $appointMonth = 1 + (hexdec(substr($hash, 8, 2)) % 12); // 01..12
        $gender = 1 + (hexdec(substr($hash, 10, 1)) % 2);       // 1..2
        $seq = 1 + (hexdec(substr($hash, 11, 5)) % 999);        // 001..999

        return sprintf(
            '%04d%02d%02d%04d%02d%d%03d',
            $birthYear,
            $birthMonth,
            $birthDay,
            $appointYear,
            $appointMonth,
            $gender,
            $seq,
        );
    }

    /**
     * Mengambil nama jabatan tendik.
     */
    public function resolveJabatan(mixed $jabatan): string
    {
        $cleanJabatan = $this->cleanText($jabatan);

        return $cleanJabatan !== '' ? $cleanJabatan : 'Tenaga Kependidikan';
    }

    /**
     * Menyusun deskripsi sertifikasi/riwayat dari pangkat, golongan, dan unit penempatan.
     */
    public function formatSertifikasi(mixed $pangkat, mixed $golongan, mixed $unitPenempatan): string
    {
        $cleanPangkat = $this->cleanText($pangkat);
        $cleanGolongan = $this->cleanText($golongan);
        $cleanUnit = $this->cleanText($unitPenempatan);

        $parts = [];

        if ($cleanPangkat !== '') {
            $parts[] = "Pangkat: {$cleanPangkat}";
        }

        if ($cleanGolongan !== '') {
            $parts[] = "Golongan: {$cleanGolongan}";
        }

        if ($cleanUnit !== '') {
            $parts[] = "Unit Penempatan: {$cleanUnit}";
        }

        return $parts !== [] ? implode(' | ', $parts) : '-';
    }

    /**
     * Memetakan daftar ID pendidikan yang valid dari tabel pendidikan jika tersedia.
     */
    protected function resolvePendidikanMapping(): void
    {
        $this->pendidikanMap = [];
        $this->defaultPendidikanId = 1;

        try {
            if (! Schema::hasTable('pendidikan')) {
                return;
            }

            $rows = DB::table('pendidikan')->get();

            if ($rows->isEmpty()) {
                return;
            }

            $firstValidId = null;

            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $rawId = $rowArray['id_pendidikan'] ?? $rowArray['id'] ?? null;

                if ($rawId === null) {
                    continue;
                }

                $id = (int) $rawId;
                if ($firstValidId === null) {
                    $firstValidId = $id;
                }

                $jenjang = strtoupper(trim((string) ($rowArray['jenjang'] ?? $rowArray['nama_pendidikan'] ?? '')));

                if ($jenjang !== '' && ! isset($this->pendidikanMap[$jenjang])) {
                    $this->pendidikanMap[$jenjang] = $id;
                }
            }

            if ($firstValidId !== null) {
                $this->defaultPendidikanId = $this->pendidikanMap['D4/S1']
                    ?? $this->pendidikanMap['S1']
                    ?? $this->pendidikanMap['D3']
                    ?? $firstValidId;
            }
        } catch (Throwable) {
            $this->defaultPendidikanId = 1;
        }
    }

    /**
     * Menentukan id_pendidikan yang valid berdasarkan gelar atau data pendidikan.
     */
    public function resolveIdPendidikan(mixed $gelarDepan, mixed $gelarBelakang, mixed $pendidikan = null): int
    {
        $jenjang = $this->inferJenjang($gelarDepan, $gelarBelakang, $pendidikan);

        if ($jenjang !== null) {
            if (isset($this->pendidikanMap[$jenjang])) {
                return $this->pendidikanMap[$jenjang];
            }

            if ($jenjang === 'D4/S1' && isset($this->pendidikanMap['S1'])) {
                return $this->pendidikanMap['S1'];
            }

            if ($jenjang === 'S1' && isset($this->pendidikanMap['D4/S1'])) {
                return $this->pendidikanMap['D4/S1'];
            }
        }

        return $this->defaultPendidikanId;
    }

    /**
     * Menebak jenjang pendidikan dari gelar depan, gelar belakang, atau string pendidikan.
     */
    protected function inferJenjang(mixed $gelarDepan, mixed $gelarBelakang, mixed $pendidikan = null): ?string
    {
        $normalizedExplicit = $this->normalizeJenjangLabel($pendidikan);
        if ($normalizedExplicit !== null) {
            return $normalizedExplicit;
        }

        $depan = strtolower($this->cleanText($gelarDepan));
        $belakang = strtolower($this->cleanText($gelarBelakang));

        if (str_contains($depan, 'dr.') || str_contains($belakang, 'ph.d')) {
            return 'S3';
        }

        if (preg_match('/\b(m\.[a-z]+|magister|m\.kom|m\.t|m\.si|m\.m|m\.pd|m\.ak|m\.sc|m\.hum|m\.b\.a)\b/i', $belakang) === 1) {
            return 'S2';
        }

        if (preg_match('/\b(s\.[a-z]+|se|s\.t|s\.kom|s\.e|s\.h|s\.hum|s\.a\.p|s\.ak|s\.pd|s\.sos|s\.si|s\.tr)\b/i', $belakang) === 1) {
            return 'D4/S1';
        }

        if (preg_match('/\b(a\.md|a\.ma|d3|d-iii)\b/i', $belakang) === 1) {
            return 'D3';
        }

        if ($depan === '' && $belakang === '') {
            return 'SMA/SMK';
        }

        return null;
    }

    /**
     * Menormalisasi label jenjang pendidikan dari PDA Polines (SMA, SMK, SLTA, S1, D4, D3, S2, S3).
     */
    public function normalizeJenjangLabel(mixed $value): ?string
    {
        $raw = strtoupper($this->cleanText($value));
        if ($raw === '') {
            return null;
        }

        if (in_array($raw, ['S3', 'S-3', 'DOKTOR'], true)) {
            return 'S3';
        }

        if (in_array($raw, ['S2', 'S-2', 'MAGISTER', 'SP-1'], true)) {
            return 'S2';
        }

        if (in_array($raw, ['D4/S1', 'S1', 'S-1', 'D4', 'D-4', 'D-IV', 'SARJANA'], true)) {
            return 'D4/S1';
        }

        if (in_array($raw, ['D3', 'D-3', 'D-III', 'D2', 'D-2', 'D-II', 'D1', 'D-1', 'D-I', 'DIPLOMA'], true)) {
            return 'D3';
        }

        if (in_array($raw, ['SMA/SMK', 'SMA', 'SMK', 'SLTA', 'MA', 'STM', 'SMEA', 'PAKET C', 'SMP', 'SLTP', 'PAKET B', 'SD', 'PAKET A'], true)) {
            return 'SMA/SMK';
        }

        return null;
    }

    /**
     * Membersihkan nilai string dari null atau tanda strip tunggal.
     */
    protected function cleanText(mixed $value): string
    {
        if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
            return '';
        }

        $trimmed = trim((string) $value);

        return $trimmed === '-' ? '' : $trimmed;
    }
}
