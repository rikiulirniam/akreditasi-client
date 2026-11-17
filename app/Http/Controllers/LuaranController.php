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
        $rawDosen = $this->fetchList('dosen', ['per_page' => 100]);

        if (($rawLuaran['_status'] ?? null) === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (($rawDosen['_status'] ?? null) === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $dosenOptions = array_values(array_filter(array_map(static function (mixed $dosen): ?array {
            if (! is_array($dosen)) {
                return null;
            }

            $id = $dosen['id_dosen'] ?? ($dosen['id'] ?? null);
            $name = trim((string) ($dosen['nama_dosen'] ?? ($dosen['nama'] ?? '')));

            if (! is_numeric($id) || $name === '') {
                return null;
            }

            return ['id' => (int) $id, 'name' => $name];
        }, $rawDosen['items'] ?? [])));
        $authenticatedDosenId = $this->authenticatedDosenId();

        if ($this->isAuthenticatedDosen()) {
            $dosenOptions = array_values(array_filter(
                $dosenOptions,
                fn (array $dosen): bool => $dosen['id'] === $authenticatedDosenId
            ));

            if ($authenticatedDosenId !== null && $dosenOptions === []) {
                $user = session('user', []);
                $dosenOptions[] = [
                    'id' => $authenticatedDosenId,
                    'name' => (string) ($user['nama'] ?? ($user['name'] ?? 'Dosen')),
                ];
            }
        }

        $dosenLoadError = ($rawDosen['_status'] ?? 500) !== 200 || $dosenOptions === [];

        if (! empty($rawLuaran['items'])) {
            $items = $this->normalizeGenericLuaran($rawLuaran['items']);
            $luaranLoadError = false;
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
            $fallbackSucceeded = collect([$rawPublikasi, $rawHki, $rawBuku])
                ->contains(static fn (array $response): bool => ($response['_status'] ?? 500) === 200);
            $luaranLoadError = ($rawLuaran['_status'] ?? 500) !== 200 && ! $fallbackSucceeded;
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
            'dosenOptions' => $dosenOptions,
            'selectedDosenId' => $authenticatedDosenId,
            'dosenLoadError' => $dosenLoadError,
            'luaranLoadError' => $luaranLoadError,
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

        $idDosen = $this->requestedDosenId($request);
        if ($idDosen instanceof JsonResponse || $idDosen instanceof RedirectResponse) {
            return $idDosen;
        }

        $idJenisLuaran = $this->getJenisLuaranId($jenis);

        // Validasi dan rakit payload spesifik
        $payload = match ($jenis) {
            'Buku' => $this->validateAndBuildBukuPayload($request),
            'Jurnal' => $this->validateAndBuildJurnalPayload($request),
            'Seminar' => $this->validateAndBuildSeminarPayload($request),
            'HKI' => $this->validateAndBuildHkiPayload($request),
            'Produk' => $this->validateAndBuildProdukPayload($request),
        };

        $payload['id_dosen'] = $idDosen;
        $payload['id_jenis_luaran'] = $idJenisLuaran;
        $idKegiatan = $request->validate([
            'id_kegiatan' => ['nullable', 'integer', 'min:1'],
        ])['id_kegiatan'] ?? null;
        if ($idKegiatan !== null) {
            $payload['id_kegiatan'] = (int) $idKegiatan;
        }

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

        $idDosen = $this->requestedDosenId($request);
        if ($idDosen instanceof JsonResponse || $idDosen instanceof RedirectResponse) {
            return $idDosen;
        }

        $idJenisLuaran = $this->getJenisLuaranId($jenis);

        $payload = match ($jenis) {
            'Buku' => $this->validateAndBuildBukuPayload($request),
            'Jurnal' => $this->validateAndBuildJurnalPayload($request),
            'Seminar' => $this->validateAndBuildSeminarPayload($request),
            'HKI' => $this->validateAndBuildHkiPayload($request),
            'Produk' => $this->validateAndBuildProdukPayload($request),
        };

        $payload['id_dosen'] = $idDosen;
        $payload['id_jenis_luaran'] = $idJenisLuaran;
        $idKegiatan = $request->validate([
            'id_kegiatan' => ['nullable', 'integer', 'min:1'],
        ])['id_kegiatan'] ?? null;
        if ($idKegiatan !== null) {
            $payload['id_kegiatan'] = (int) $idKegiatan;
        }

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
            'id_dosen' => ['required', 'integer', 'min:1'],
            'judul_buku' => ['required', 'string', 'max:500'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'penerbit_buku' => ['required', 'string', 'max:255'],
            'tahun_terbit' => ['required', 'integer', 'digits:4'],
            'jenis_buku' => ['required', 'in:Buku Ajar,Buku Referensi,Buku Monograf,Buku Jurnal'],
            'bidang_ilmu' => ['nullable', 'string', 'max:255'],
            'halaman_buku' => ['nullable', 'string', 'min:1'],
            'tautan_buku' => ['nullable', 'url', 'max:2048'],
        ], [
            'judul_buku.required' => 'Judul buku wajib diisi.',
            'penerbit_buku.required' => 'Penerbit buku wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit buku wajib diisi.',
            'jenis_buku.required' => 'Jenis buku wajib dipilih.',
        ]);

        return [
            'judul_buku' => trim($val['judul_buku']),
            'isbn' => isset($val['isbn']) ? trim($val['isbn']) : null,
            'penerbit_buku' => trim($val['penerbit_buku']),
            'tahun_terbit' => (int) $val['tahun_terbit'],
            'jenis_buku' => trim($val['jenis_buku']),
            'bidang_ilmu' => isset($val['bidang_ilmu']) ? trim($val['bidang_ilmu']) : null,
            'halaman_buku' => isset($val['halaman_buku']) ? trim($val['halaman_buku']) : null,
            'tautan_buku' => $val['tautan_buku'] ?? null,
        ];
    }

    private function validateAndBuildJurnalPayload(Request $request): array
    {
        $val = $request->validate([
            'id_dosen' => ['required', 'integer', 'min:1'],
            'nama_jurnal' => ['required', 'string', 'max:500'],
            'issn' => ['nullable', 'string', 'max:100'],
            'volume' => ['nullable', 'string', 'max:100'],
            'tanggal_terbit' => ['required', 'date'],
            'doi' => ['nullable', 'string', 'max:255'],
            'nomor_edisi' => ['nullable', 'string', 'max:100'],
            'halaman_jurnal' => ['nullable', 'string', 'min:1'],
            'indeksasi' => ['nullable', 'string', 'max:100'],
            'tautan_jurnal' => ['nullable', 'url', 'max:2048'],
        ], [
            'nama_jurnal.required' => 'Nama jurnal wajib diisi.',
            'tanggal_terbit.required' => 'Tanggal terbit jurnal wajib diisi.',
        ]);

        return [
            'nama_jurnal' => trim($val['nama_jurnal']),
            'issn' => ! empty($val['issn']) ? trim($val['issn']) : null,
            'volume' => ! empty($val['volume']) ? trim($val['volume']) : null,
            'tanggal_terbit' => trim($val['tanggal_terbit']),
            'doi' => ! empty($val['doi']) ? trim($val['doi']) : null,
            'nomor_edisi' => ! empty($val['nomor_edisi']) ? trim($val['nomor_edisi']) : null,
            'halaman_jurnal' => isset($val['halaman_jurnal']) ? trim($val['halaman_jurnal']) : null,
            'indeksasi' => ! empty($val['indeksasi']) ? trim($val['indeksasi']) : null,
            'tautan_jurnal' => $val['tautan_jurnal'] ?? null,
        ];
    }

    private function validateAndBuildSeminarPayload(Request $request): array
    {
        $val = $request->validate([
            'id_dosen' => ['required', 'integer', 'min:1'],
            'nama_seminar' => ['required', 'string', 'max:500'],
            'nama_prosiding' => ['required', 'string', 'max:500'],
            'isbn_prosiding' => ['nullable', 'string', 'max:100'],
            'penyelenggara' => ['nullable', 'string', 'max:255'],
            'tanggal_seminar' => ['required', 'date'],
            'lokasi_seminar' => ['nullable', 'string', 'max:255'],
            'halaman_prosiding' => ['nullable', 'string', 'min:1'],
            'doi_prosiding' => ['nullable', 'string', 'max:255'],
            'tautan_seminar' => ['nullable', 'url', 'max:2048'],
        ], [
            'nama_seminar.required' => 'Nama seminar wajib diisi.',
            'nama_prosiding.required' => 'Nama prosiding wajib diisi.',
            'tanggal_seminar.required' => 'Tanggal seminar wajib diisi.',
        ]);

        return [
            'nama_seminar' => trim($val['nama_seminar']),
            'nama_prosiding' => trim($val['nama_prosiding']),
            'isbn_prosiding' => isset($val['isbn_prosiding']) ? trim($val['isbn_prosiding']) : null,
            'penyelenggara' => ! empty($val['penyelenggara']) ? trim($val['penyelenggara']) : null,
            'tanggal_seminar' => trim($val['tanggal_seminar']),
            'lokasi_seminar' => ! empty($val['lokasi_seminar']) ? trim($val['lokasi_seminar']) : null,
            'halaman_prosiding' => isset($val['halaman_prosiding']) ? trim($val['halaman_prosiding']) : null,
            'doi_prosiding' => isset($val['doi_prosiding']) ? trim($val['doi_prosiding']) : null,
            'tautan_seminar' => $val['tautan_seminar'] ?? null,
        ];
    }

    private function validateAndBuildHkiPayload(Request $request): array
    {
        $val = $request->validate([
            'id_dosen' => ['required', 'integer', 'min:1'],
            'judul_hak_cipta' => ['required', 'string', 'max:500'],
            'jenis_hki' => ['nullable', 'in:Hak Cipta,Paten,Paten Sederhana,Desain Industri,Rahasia Dagang,Merek/DTLST'],
            'nomor_pendaftaran_hki' => ['nullable', 'string', 'max:100'],
            'nomor_sertifikat_hki' => ['nullable', 'string', 'max:100'],
            'tanggal_pendaftaran_hki' => ['nullable', 'date'],
            'tanggal_terbit_sertifikat_hki' => ['nullable', 'date'],
            'pemegang_hki' => ['nullable', 'string', 'max:255'],
            'status_hki' => ['nullable', 'in:Draft,Terdaftar/Dalam Proses,Sertifikat Terbit/Granted,Ditolak'],
            'tautan_hki' => ['nullable', 'url', 'max:2048'],
        ], [
            'judul_hak_cipta.required' => 'Judul HKI / Ciptaan wajib diisi.',
        ]);

        return [
            'judul_hak_cipta' => trim($val['judul_hak_cipta']),
            'jenis_hki' => $val['jenis_hki'] ?? null,
            'nomor_pendaftaran_hki' => ! empty($val['nomor_pendaftaran_hki']) ? trim($val['nomor_pendaftaran_hki']) : null,
            'nomor_sertifikat_hki' => $val['nomor_sertifikat_hki'] ?? null,
            'tanggal_pendaftaran_hki' => $val['tanggal_pendaftaran_hki'] ?? null,
            'tanggal_terbit_sertifikat_hki' => $val['tanggal_terbit_sertifikat_hki'] ?? null,
            'pemegang_hki' => $val['pemegang_hki'] ?? null,
            'status_hki' => $val['status_hki'] ?? null,
            'tautan_hki' => $val['tautan_hki'] ?? null,
        ];
    }

    private function validateAndBuildProdukPayload(Request $request): array
    {
        $val = $request->validate([
            'id_dosen' => ['required', 'integer', 'min:1'],
            'nama_produk' => ['required', 'string', 'max:500'],
            'jenis_produk' => ['required', 'in:Software/Web,Mobile,Hardware,Embedded system'],
            'status_pengembangan' => ['required', 'in:Desain,Prototipe,Uji coba,Produksi,Komersialisasi'],
            'tingkat_kesiapanterapan' => ['required', 'in:TKT 1,TKT 2,TKT 3,TKT 4,TKT 5,TKT 6,TKT 7,TKT 8,TKT 9'],
            'tanggal_pembuatan' => ['required', 'date'],
            'deskripsi_produk' => ['nullable', 'string', 'max:1000'],
            'tautan_produk' => ['nullable', 'url', 'max:2048'],
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'tanggal_pembuatan.required' => 'Tanggal pembuatan produk wajib diisi.',
        ]);

        return [
            'nama_produk' => trim($val['nama_produk']),
            'jenis_produk' => $val['jenis_produk'],
            'status_pengembangan' => $val['status_pengembangan'],
            'tingkat_kesiapanterapan' => $val['tingkat_kesiapanterapan'],
            'tanggal_pembuatan' => $val['tanggal_pembuatan'],
            'deskripsi_produk' => $val['deskripsi_produk'] ?? null,
            'tautan_produk' => $val['tautan_produk'] ?? null,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE: HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    private function requestedDosenId(Request $request): int|JsonResponse|RedirectResponse
    {
        if ($this->isAuthenticatedDosen()) {
            $idDosen = $this->authenticatedDosenId();

            if ($idDosen !== null) {
                return $idDosen;
            }

            $message = 'Akun dosen belum terhubung ke data dosen.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withInput()->with('toast', ['type' => 'error', 'title' => 'Gagal', 'message' => $message]);
        }

        $validated = $request->validate([
            'id_dosen' => ['required', 'integer', 'min:1'],
        ]);

        return (int) $validated['id_dosen'];
    }

    private function isAuthenticatedDosen(): bool
    {
        $user = session('user', []);
        $roles = is_array($user) && is_array($user['roles'] ?? null) ? $user['roles'] : [];
        $primaryRole = is_array($roles[0] ?? null)
            ? ($roles[0]['name'] ?? '')
            : ($roles[0] ?? '');
        $role = is_array($user) ? ($user['role'] ?? $primaryRole) : '';

        return strtolower((string) $role) === 'dosen';
    }

    private function authenticatedDosenId(): ?int
    {
        if (! $this->isAuthenticatedDosen()) {
            return null;
        }

        $idDosen = session('user.id_dosen');

        return is_numeric($idDosen) ? (int) $idDosen : null;
    }

    private function getJenisLuaranId(string $jenis): int
    {
        return match (strtolower($jenis)) {
            'buku' => 1,
            'jurnal' => 2,
            'seminar', 'prosiding' => 3,
            'hki', 'hak cipta' => 4,
            'produk' => 5,
            default => 1,
        };
    }

    private function fetchList(string $endpoint, array $query): array
    {
        try {
            $response = $endpoint === 'dosen'
                ? $this->tridharmaService->getDosenList($query)
                : $this->tridharmaService->get($endpoint, $query);

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
                $rawJenis = $rawJenis['nama_jenis_luaran']
                    ?? ($rawJenis['nama'] ?? ($rawJenis['jenis'] ?? ($rawJenis['nama_jenis'] ?? 'Jurnal')));
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
            $url = $item['url_luaran']
                ?? $item['tautan_buku']
                ?? $item['tautan_jurnal']
                ?? $item['tautan_seminar']
                ?? $item['tautan_prosiding']
                ?? $item['tautan_hki']
                ?? $item['tautan_produk']
                ?? $item['url_buku']
                ?? $item['url_publikasi']
                ?? $item['url_hki']
                ?? $item['url_produk']
                ?? $item['url']
                ?? null;

            return [
                'id' => $item['id_luaran'] ?? ($item['id'] ?? null),
                'kategori' => strtolower($mappedJenis),
                'judul' => $judul,
                'jenis' => $mappedJenis,
                'nama_penerbit' => $penerbit,
                'tanggal_terbit' => $tanggal,
                'url' => $url,
                'nama_dosen' => $this->dosenName($item),
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
                'url' => $item['url_luaran'] ?? ($item['tautan_jurnal'] ?? ($item['tautan_seminar'] ?? ($item['tautan_prosiding'] ?? ($item['url_publikasi'] ?? ($item['url'] ?? null))))),
                'nama_dosen' => $this->dosenName($item),
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
                'url' => $item['url_luaran'] ?? ($item['tautan_hki'] ?? ($item['url_hki'] ?? ($item['url'] ?? null))),
                'nama_dosen' => $this->dosenName($item),
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
                'url' => $item['url_luaran'] ?? ($item['tautan_buku'] ?? ($item['url_buku'] ?? ($item['url'] ?? null))),
                'nama_dosen' => $this->dosenName($item),
                'extra' => $item,
            ];
        }, $items);
    }

    private function dosenName(array $item): string
    {
        $dosen = $item['dosen'] ?? [];
        $name = is_array($dosen)
            ? ($dosen['nama_dosen'] ?? ($dosen['nama'] ?? null))
            : null;
        $name ??= $item['nama_dosen'] ?? null;

        if (is_string($name) && trim($name) !== '') {
            return trim($name);
        }

        $id = $item['id_dosen'] ?? (is_array($dosen) ? ($dosen['id_dosen'] ?? ($dosen['id'] ?? null)) : null);

        return is_numeric($id) ? 'Dosen #'.$id : '-';
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
