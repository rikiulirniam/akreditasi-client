<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LuaranController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    /**
     * Halaman daftar Luaran (Buku, Jurnal, Seminar, HKI, Produk).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $page = (int) $request->query('page', 1);
        $search = trim((string) $request->query('search', ''));
        $jenis = trim((string) $request->query('jenis', ''));

        $perPage = 10;

        $queryBase = ['page' => $page, 'per_page' => $perPage];
        if (! empty($search)) {
            $queryBase['search'] = $search;
        }

        // 1. Coba ambil dari endpoint utama GET /api/luaran
        $rawLuaran = $this->fetchList('luaran', $queryBase);
        $items = [];

        if (! empty($rawLuaran['items'])) {
            $items = $this->normalizeGenericLuaran($rawLuaran['items']);
        } else {
            // Fallback: gabungkan publikasi, hki, buku jika backend belum satukan GET /api/luaran
            $rawPublikasi = $this->fetchList('publikasi', $queryBase);
            $rawHki = $this->fetchList('hki', $queryBase);
            $rawBuku = $this->fetchList('buku', $queryBase);

            foreach ([$rawPublikasi, $rawHki, $rawBuku] as $resp) {
                if (isset($resp['_status']) && $resp['_status'] === 401) {
                    return redirect()->route('login')->with('toast', [
                        'type' => 'warning',
                        'title' => 'Sesi Berakhir',
                        'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
                    ]);
                }
            }

            $publikasiItems = $this->normalizePublikasi($rawPublikasi['items'] ?? []);
            $hkiItems = $this->normalizeHki($rawHki['items'] ?? []);
            $bukuItems = $this->normalizeBuku($rawBuku['items'] ?? []);

            $items = array_merge($publikasiItems, $hkiItems, $bukuItems);
        }

        // Filter 5 jenis: Buku, Jurnal, Seminar, HKI, Produk
        if (! empty($jenis)) {
            $items = array_values(array_filter($items, function (array $item) use ($jenis): bool {
                return strcasecmp((string) ($item['jenis'] ?? ''), $jenis) === 0;
            }));
        }

        // Sort by tanggal_terbit desc
        usort($items, function (array $a, array $b): int {
            $dateA = strtotime((string) ($a['tanggal_terbit'] ?? '1970-01-01')) ?: 0;
            $dateB = strtotime((string) ($b['tanggal_terbit'] ?? '1970-01-01')) ?: 0;

            return $dateB <=> $dateA;
        });

        // Paginasi manual
        $totalAll = count($items);
        $lastPage = max(1, (int) ceil($totalAll / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;
        $luaranList = array_slice($items, $offset, $perPage);

        $pagination = [
            'total' => $totalAll,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
        ];

        return view('luaran.index', [
            'luaranList' => $luaranList,
            'pagination' => $pagination,
            'search' => $search,
            'selectedJenis' => $jenis,
            'jenisOptions' => $this->getJenisOptions(),
        ]);
    }

    /**
     * Simpan data Luaran baru ke endpoint utama: POST /api/luaran
     * Menerima 5 jenis: Buku, Jurnal, Seminar, HKI, Produk.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $rawJenis = trim((string) ($request->input('jenis_luaran') ?? $request->input('jenis_luaran_kategori', '')));

        $jenis = match (strtolower($rawJenis)) {
            'buku' => 'Buku',
            'jurnal' => 'Jurnal',
            'seminar' => 'Seminar',
            'hki' => 'HKI',
            'produk' => 'Produk',
            default => null,
        };

        if ($jenis === null) {
            $errorMsg = 'Pilih salah satu dari 5 jenis luaran: Buku, Jurnal, Seminar, HKI, atau Produk.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }

            return back()->withInput()->with('toast', ['type' => 'error', 'title' => 'Gagal', 'message' => $errorMsg]);
        }

        // Validasi dan rakit payload spesifik
        $payload = match ($jenis) {
            'Buku' => $this->validateAndBuildBukuPayload($request),
            'Jurnal' => $this->validateAndBuildJurnalPayload($request),
            'Seminar' => $this->validateAndBuildSeminarPayload($request),
            'HKI' => $this->validateAndBuildHkiPayload($request),
            'Produk' => $this->validateAndBuildProdukPayload($request),
        };

        // Pastikan atribut jenis_luaran wajib ada
        $payload['jenis_luaran'] = $jenis;

        // Kirim request ke endpoint utama: POST /api/luaran
        $response = $this->tridharmaService->post('luaran', $payload);

        // Fallback otomatis jika endpoint /api/luaran belum ada / mengembalikan 404
        if ($response->status() === 404) {
            $fallbackEndpoint = match ($jenis) {
                'Buku' => 'buku',
                'HKI' => 'hki',
                default => 'publikasi',
            };
            $response = $this->tridharmaService->post($fallbackEndpoint, $payload);
        }

        $body = $response->json();
        $isSuccess = $response->successful() && ($body['success'] ?? true);
        $message = $body['message'] ?? ($isSuccess ? "Data {$jenis} berhasil ditambahkan." : "Gagal menambahkan data {$jenis}.");

        if ($isSuccess) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $body['data'] ?? [],
                ]);
            }

            return redirect()->route('luaran.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $message,
            ]);
        }

        $errorMessage = $message;
        if (! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                $errorMessage = $firstError;
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'errors' => $body['errors'] ?? [],
            ], $response->status() >= 400 ? $response->status() : 422);
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    /**
     * Update data Luaran.
     */
    public function update(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $rawJenis = trim((string) ($request->input('jenis_luaran') ?? $request->input('jenis_luaran_kategori', '')));

        $jenis = match (strtolower($rawJenis)) {
            'buku' => 'Buku',
            'jurnal' => 'Jurnal',
            'seminar' => 'Seminar',
            'hki' => 'HKI',
            'produk' => 'Produk',
            default => 'Jurnal',
        };

        $payload = match ($jenis) {
            'Buku' => $this->validateAndBuildBukuPayload($request),
            'Jurnal' => $this->validateAndBuildJurnalPayload($request),
            'Seminar' => $this->validateAndBuildSeminarPayload($request),
            'HKI' => $this->validateAndBuildHkiPayload($request),
            'Produk' => $this->validateAndBuildProdukPayload($request),
        };

        $payload['jenis_luaran'] = $jenis;

        $response = $this->tridharmaService->put("luaran/{$id}", $payload);

        if ($response->status() === 404) {
            $fallbackEndpoint = match ($jenis) {
                'Buku' => "buku/{$id}",
                'HKI' => "hki/{$id}",
                default => "publikasi/{$id}",
            };
            $response = $this->tridharmaService->put($fallbackEndpoint, $payload);
        }

        $body = $response->json();
        $isSuccess = $response->successful() && ($body['success'] ?? true);
        $message = $body['message'] ?? ($isSuccess ? "Data {$jenis} berhasil diperbarui." : "Gagal memperbarui data {$jenis}.");

        if ($isSuccess) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $body['data'] ?? [],
                ]);
            }

            return redirect()->route('luaran.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $message,
            ]);
        }

        $errorMessage = $message;
        if (! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                $errorMessage = $firstError;
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'errors' => $body['errors'] ?? [],
            ], $response->status() >= 400 ? $response->status() : 422);
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    /**
     * Hapus data Luaran.
     */
    public function destroy(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $jenis = strtolower(trim((string) $request->input('jenis', 'publikasi')));
        $response = $this->tridharmaService->delete("luaran/{$id}");

        if ($response->status() === 404) {
            $fallbackEndpoint = match ($jenis) {
                'buku' => "buku/{$id}",
                'hki' => "hki/{$id}",
                default => "publikasi/{$id}",
            };
            $response = $this->tridharmaService->delete($fallbackEndpoint);
        }

        $body = $response->json();
        $isSuccess = $response->successful() && ($body['success'] ?? true);
        $message = $body['message'] ?? ($isSuccess ? 'Data luaran berhasil dihapus.' : 'Gagal menghapus data luaran.');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $isSuccess,
                'message' => $message,
            ], $isSuccess ? 200 : 422);
        }

        return redirect()->route('luaran.index')->with('toast', [
            'type' => $isSuccess ? 'success' : 'error',
            'title' => $isSuccess ? 'Berhasil' : 'Gagal',
            'message' => $message,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE: PAYLOAD BUILDERS DENGAN VALIDASI
    // ══════════════════════════════════════════════════════════════════════════

    private function validateAndBuildBukuPayload(Request $request): array
    {
        $val = $request->validate([
            'judul_buku' => ['required', 'string', 'max:500'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'nama_penerbit' => ['required', 'string', 'max:255'],
            'tahun_terbit' => ['required', 'string', 'max:50'],
            'jenis_buku' => ['required', 'string', 'max:100'],
            'bidang_ilmu' => ['nullable', 'string', 'max:255'],
            'total_halaman' => ['nullable', 'integer', 'min:1'],
            'url_buku' => ['nullable', 'string', 'max:1000'],
        ], [
            'judul_buku.required' => 'Judul buku wajib diisi.',
            'nama_penerbit.required' => 'Penerbit buku wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit buku wajib diisi.',
            'jenis_buku.required' => 'Jenis buku wajib dipilih.',
        ]);

        return array_filter([
            'judul' => trim($val['judul_buku']),
            'judul_luaran' => trim($val['judul_buku']),
            'judul_buku' => trim($val['judul_buku']),
            'isbn' => ! empty($val['isbn']) ? trim($val['isbn']) : null,
            'nama_penerbit' => trim($val['nama_penerbit']),
            'penerbit_buku' => trim($val['nama_penerbit']),
            'tahun_terbit' => trim($val['tahun_terbit']),
            'tanggal_terbit' => trim($val['tahun_terbit']),
            'tahun' => (int) substr(trim($val['tahun_terbit']), 0, 4) ?: null,
            'jenis_buku' => trim($val['jenis_buku']),
            'bidang_ilmu' => ! empty($val['bidang_ilmu']) ? trim($val['bidang_ilmu']) : null,
            'total_halaman' => isset($val['total_halaman']) ? (int) $val['total_halaman'] : null,
            'url_luaran' => ! empty($val['url_buku']) ? trim($val['url_buku']) : null,
            'url_buku' => ! empty($val['url_buku']) ? trim($val['url_buku']) : null,
            'tautan_buku' => ! empty($val['url_buku']) ? trim($val['url_buku']) : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function validateAndBuildJurnalPayload(Request $request): array
    {
        $val = $request->validate([
            'nama_jurnal' => ['required', 'string', 'max:500'],
            'issn' => ['nullable', 'string', 'max:100'],
            'volume' => ['nullable', 'string', 'max:100'],
            'tanggal_terbit' => ['required', 'string', 'max:50'],
            'doi' => ['nullable', 'string', 'max:255'],
            'nomor_edisi' => ['nullable', 'string', 'max:100'],
            'total_halaman' => ['nullable', 'integer', 'min:1'],
            'indeksasi' => ['nullable', 'string', 'max:100'],
            'url_publikasi' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_jurnal.required' => 'Nama jurnal wajib diisi.',
            'tanggal_terbit.required' => 'Tanggal terbit jurnal wajib diisi.',
        ]);

        return array_filter([
            'judul' => trim($val['nama_jurnal']),
            'judul_luaran' => trim($val['nama_jurnal']),
            'judul_publikasi' => trim($val['nama_jurnal']),
            'nama_jurnal' => trim($val['nama_jurnal']),
            'nama_penerbit' => trim($val['nama_jurnal']),
            'jenis_publikasi' => 'Jurnal',
            'issn' => ! empty($val['issn']) ? trim($val['issn']) : null,
            'volume' => ! empty($val['volume']) ? trim($val['volume']) : null,
            'tanggal_terbit' => trim($val['tanggal_terbit']),
            'doi' => ! empty($val['doi']) ? trim($val['doi']) : null,
            'nomor_edisi' => ! empty($val['nomor_edisi']) ? trim($val['nomor_edisi']) : null,
            'total_halaman' => isset($val['total_halaman']) ? (int) $val['total_halaman'] : null,
            'indeksasi' => ! empty($val['indeksasi']) ? trim($val['indeksasi']) : null,
            'url_luaran' => ! empty($val['url_publikasi']) ? trim($val['url_publikasi']) : null,
            'url_publikasi' => ! empty($val['url_publikasi']) ? trim($val['url_publikasi']) : null,
            'tautan_jurnal' => ! empty($val['url_publikasi']) ? trim($val['url_publikasi']) : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function validateAndBuildSeminarPayload(Request $request): array
    {
        $val = $request->validate([
            'nama_seminar' => ['required', 'string', 'max:500'],
            'nama_prosiding' => ['required', 'string', 'max:500'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'penyelenggara' => ['nullable', 'string', 'max:255'],
            'tanggal_seminar' => ['required', 'string', 'max:50'],
            'lokasi_seminar' => ['nullable', 'string', 'max:255'],
            'total_halaman' => ['nullable', 'integer', 'min:1'],
            'doi' => ['nullable', 'string', 'max:255'],
            'url_publikasi' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_seminar.required' => 'Nama seminar wajib diisi.',
            'nama_prosiding.required' => 'Nama prosiding wajib diisi.',
            'tanggal_seminar.required' => 'Tanggal seminar wajib diisi.',
        ]);

        return array_filter([
            'judul' => trim($val['nama_seminar']),
            'judul_luaran' => trim($val['nama_seminar']),
            'judul_publikasi' => trim($val['nama_seminar']),
            'nama_seminar' => trim($val['nama_seminar']),
            'nama_prosiding' => trim($val['nama_prosiding']),
            'nama_penerbit' => trim($val['nama_prosiding']),
            'jenis_publikasi' => 'Seminar',
            'isbn' => ! empty($val['isbn']) ? trim($val['isbn']) : null,
            'penyelenggara' => ! empty($val['penyelenggara']) ? trim($val['penyelenggara']) : null,
            'tanggal_seminar' => trim($val['tanggal_seminar']),
            'tanggal_terbit' => trim($val['tanggal_seminar']),
            'lokasi_seminar' => ! empty($val['lokasi_seminar']) ? trim($val['lokasi_seminar']) : null,
            'total_halaman' => isset($val['total_halaman']) ? (int) $val['total_halaman'] : null,
            'doi' => ! empty($val['doi']) ? trim($val['doi']) : null,
            'doi_prosiding' => ! empty($val['doi']) ? trim($val['doi']) : null,
            'url_luaran' => ! empty($val['url_publikasi']) ? trim($val['url_publikasi']) : null,
            'url_publikasi' => ! empty($val['url_publikasi']) ? trim($val['url_publikasi']) : null,
            'tautan_seminar' => ! empty($val['url_publikasi']) ? trim($val['url_publikasi']) : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function validateAndBuildHkiPayload(Request $request): array
    {
        $val = $request->validate([
            'judul_hak_cipta' => ['required', 'string', 'max:500'],
            'nama_penerbit' => ['required', 'string', 'max:255'],
            'tanggal_terbit' => ['required', 'string', 'max:50'],
            'jenis_hki' => ['nullable', 'string', 'max:100'],
            'nomor_pendaftaran_hki' => ['nullable', 'string', 'max:100'],
            'status_hki' => ['nullable', 'string', 'max:50'],
            'url_hki' => ['nullable', 'string', 'max:1000'],
        ], [
            'judul_hak_cipta.required' => 'Judul HKI / Ciptaan wajib diisi.',
            'nama_penerbit.required' => 'Lembaga penerbit wajib diisi.',
            'tanggal_terbit.required' => 'Tanggal terbit / daftar wajib diisi.',
        ]);

        return array_filter([
            'judul' => trim($val['judul_hak_cipta']),
            'judul_luaran' => trim($val['judul_hak_cipta']),
            'judul_hak_cipta' => trim($val['judul_hak_cipta']),
            'nama_penerbit' => trim($val['nama_penerbit']),
            'jenis_hki' => ! empty($val['jenis_hki']) ? trim($val['jenis_hki']) : 'Hak Cipta',
            'nomor_pendaftaran_hki' => ! empty($val['nomor_pendaftaran_hki']) ? trim($val['nomor_pendaftaran_hki']) : null,
            'status_hki' => ! empty($val['status_hki']) ? trim($val['status_hki']) : 'Terdaftar',
            'tanggal_terbit' => trim($val['tanggal_terbit']),
            'url_luaran' => ! empty($val['url_hki']) ? trim($val['url_hki']) : null,
            'url_hki' => ! empty($val['url_hki']) ? trim($val['url_hki']) : null,
            'tautan_hki' => ! empty($val['url_hki']) ? trim($val['url_hki']) : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function validateAndBuildProdukPayload(Request $request): array
    {
        $val = $request->validate([
            'nama_produk' => ['required', 'string', 'max:500'],
            'penerbit_produk' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'tanggal_terbit' => ['required', 'string', 'max:50'],
            'url_produk' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'penerbit_produk.required' => 'Instansi / Penyelenggara wajib diisi.',
            'tanggal_terbit.required' => 'Tanggal terbit / rilis wajib diisi.',
        ]);

        return array_filter([
            'judul' => trim($val['nama_produk']),
            'judul_luaran' => trim($val['nama_produk']),
            'nama_produk' => trim($val['nama_produk']),
            'nama_penerbit' => trim($val['penerbit_produk']),
            'penerbit_produk' => trim($val['penerbit_produk']),
            'deskripsi' => ! empty($val['deskripsi']) ? trim($val['deskripsi']) : null,
            'tanggal_terbit' => trim($val['tanggal_terbit']),
            'url_luaran' => ! empty($val['url_produk']) ? trim($val['url_produk']) : null,
            'url_produk' => ! empty($val['url_produk']) ? trim($val['url_produk']) : null,
            'tautan_produk' => ! empty($val['url_produk']) ? trim($val['url_produk']) : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE: HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    private function fetchList(string $endpoint, array $query): array
    {
        try {
            $response = $this->tridharmaService->get($endpoint, $query);

            if ($response->status() === 401) {
                return ['items' => [], '_status' => 401];
            }

            $body = $response->json();
            $data = $body['data'] ?? [];

            $items = match (true) {
                is_array($data) && isset($data['data']) && is_array($data['data']) => $data['data'],
                is_array($data) && array_is_list($data) => $data,
                default => [],
            };

            return ['items' => $items, '_status' => $response->status()];
        } catch (\Throwable $e) {
            Log::warning("LuaranController: gagal fetch {$endpoint}", ['error' => $e->getMessage()]);

            return ['items' => [], '_status' => 500];
        }
    }

    private function normalizeGenericLuaran(array $items): array
    {
        return array_map(function (array $item): array {
            $rawJenis = $item['jenis_luaran'] ?? ($item['jenis'] ?? ($item['jenis_publikasi'] ?? 'Jurnal'));

            // Cek jika $rawJenis berupa Array (misal objek relasi JSON dari API)
            if (is_array($rawJenis)) {
                $rawJenis = $rawJenis['nama'] ?? ($rawJenis['jenis'] ?? ($rawJenis['nama_jenis'] ?? 'Jurnal'));
            }

            $lower = strtolower((string) $rawJenis);
        
            $mappedJenis = match (true) {
                str_contains($lower, 'buku') => 'Buku',
                str_contains($lower, 'seminar') || str_contains($lower, 'prosiding') => 'Seminar',
                str_contains($lower, 'hki') || str_contains($lower, 'hak cipta') || str_contains($lower, 'paten') => 'HKI',
                str_contains($lower, 'produk') => 'Produk',
                default => 'Jurnal',
            };

            $judul = $item['judul_luaran'] ?? ($item['judul'] ?? ($item['judul_buku'] ?? ($item['nama_jurnal'] ?? ($item['nama_seminar'] ?? ($item['judul_hak_cipta'] ?? ($item['nama_produk'] ?? '-'))))));
            $penerbit = $item['nama_penerbit'] ?? ($item['penerbit_buku'] ?? ($item['nama_prosiding'] ?? ($item['penerbit'] ?? ($item['penyelenggara'] ?? '-'))));
            $tanggal = $item['tanggal_terbit'] ?? ($item['tahun_terbit'] ?? ($item['tanggal_seminar'] ?? ($item['tahun'] ?? null)));
            $url = $item['url_luaran'] ?? ($item['url_buku'] ?? ($item['url_publikasi'] ?? ($item['url_hki'] ?? ($item['url_produk'] ?? ($item['url'] ?? null)))));

            return [
                'id' => $item['id_luaran'] ?? ($item['id'] ?? null),
                'kategori' => strtolower($mappedJenis),
                'judul' => $judul,
                'jenis' => $mappedJenis,
                'nama_penerbit' => $penerbit,
                'tanggal_terbit' => $tanggal,
                'url' => $url,
                'extra' => $item,
            ];
        }, $items);
    }

    private function normalizePublikasi(array $items): array
    {
        return array_map(function (array $item): array {
            $rawJenis = $item['jenis_publikasi'] ?? ($item['jenis'] ?? 'Jurnal');
            $jenis = is_array($rawJenis) ? ($rawJenis['nama_jenis_publikasi'] ?? 'Jurnal') : (string) $rawJenis;

            $lower = strtolower($jenis);
            $mappedJenis = match (true) {
                str_contains($lower, 'seminar') || str_contains($lower, 'prosiding') => 'Seminar',
                str_contains($lower, 'produk') => 'Produk',
                str_contains($lower, 'buku') => 'Buku',
                str_contains($lower, 'hki') || str_contains($lower, 'hak cipta') || str_contains($lower, 'paten') => 'HKI',
                default => 'Jurnal',
            };

            $penerbit = $item['nama_prosiding'] ?? ($item['nama_penerbit'] ?? ($item['penerbit'] ?? ($item['penyelenggara'] ?? '-')));
            if ($penerbit === '-' && ! empty($item['nama_jurnal'])) {
                $penerbit = $item['nama_jurnal'];
            }

            return [
                'id' => $item['id_publikasi'] ?? ($item['id'] ?? null),
                'kategori' => strtolower($mappedJenis),
                'judul' => $item['judul_publikasi'] ?? ($item['nama_seminar'] ?? ($item['nama_jurnal'] ?? ($item['judul'] ?? '-'))),
                'jenis' => $mappedJenis,
                'nama_penerbit' => $penerbit,
                'tanggal_terbit' => $item['tanggal_terbit'] ?? ($item['tanggal_seminar'] ?? ($item['tahun_terbit'] ?? ($item['tahun'] ?? null))),
                'url' => $item['url_publikasi'] ?? ($item['url'] ?? null),
                'extra' => $item,
            ];
        }, $items);
    }

    private function normalizeHki(array $items): array
    {
        return array_map(function (array $item): array {
            return [
                'id' => $item['id_hki'] ?? ($item['id'] ?? null),
                'kategori' => 'hki',
                'judul' => $item['judul_hak_cipta'] ?? ($item['judul'] ?? '-'),
                'jenis' => 'HKI',
                'nama_penerbit' => $item['nomor_pendaftaran_hki'] ?? ($item['nama_penerbit'] ?? 'Direktorat Jenderal Kekayaan Intelektual'),
                'tanggal_terbit' => $item['tanggal_terbit'] ?? ($item['tahun'] ?? null),
                'url' => $item['url_hki'] ?? ($item['url'] ?? null),
                'extra' => $item,
            ];
        }, $items);
    }

    private function normalizeBuku(array $items): array
    {
        return array_map(function (array $item): array {
            return [
                'id' => $item['id_buku'] ?? ($item['id'] ?? null),
                'kategori' => 'buku',
                'judul' => $item['judul_buku'] ?? ($item['judul'] ?? '-'),
                'jenis' => 'Buku',
                'nama_penerbit' => $item['penerbit_buku'] ?? ($item['nama_penerbit'] ?? ($item['penerbit'] ?? '-')),
                'tanggal_terbit' => $item['tahun_terbit'] ?? ($item['tanggal_terbit'] ?? ($item['tahun'] ?? null)),
                'url' => $item['url_buku'] ?? ($item['url'] ?? null),
                'extra' => $item,
            ];
        }, $items);
    }

    private function getJenisOptions(): array
    {
        return [
            ['value' => '', 'label' => 'Semua Jenis'],
            ['value' => 'Buku', 'label' => 'Buku'],
            ['value' => 'Jurnal', 'label' => 'Jurnal'],
            ['value' => 'Seminar', 'label' => 'Seminar'],
            ['value' => 'HKI', 'label' => 'HKI'],
            ['value' => 'Produk', 'label' => 'Produk'],
        ];
    }
}
