<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LuaranController extends Controller
{
    /** @var array<string, string> */
    private const JENIS_KEGIATAN = [
        'penelitian' => 'Penelitian',
        'pkm' => 'PkM',
    ];

    /**
     * Kategori luaran ala SINTA beserta kolom yang ditampilkan.
     *
     * Setiap kolom: label, daftar key kandidat pada data API (diambil yang pertama terisi).
     * Kolom "Penulis" dan "Tahun" ditambahkan otomatis oleh controller.
     *
     * @var array<string, array{label: string, columns: array<int, array{0: string, 1: array<int, string>}>, dates: array<int, string>, link: string}>
     */
    private const KATEGORI = [
        'artikel' => [
            'label' => 'Artikel',
            'columns' => [
                ['Judul / Jurnal', ['nama_jurnal']],
                ['Indeksasi', ['indeksasi']],
                ['DOI', ['doi']],
            ],
            'dates' => ['tanggal_terbit'],
            'link' => 'tautan_jurnal',
        ],
        'buku' => [
            'label' => 'Buku',
            'columns' => [
                ['Judul Buku', ['judul_buku']],
                ['Penerbit', ['penerbit_buku']],
                ['ISBN', ['isbn']],
            ],
            'dates' => ['tanggal_terbit'],
            'link' => 'tautan_buku',
        ],
        'hki' => [
            'label' => 'HKI',
            'columns' => [
                ['Judul', ['judul_hak_cipta']],
                ['Jenis HKI', ['jenis_hki']],
                ['No. Sertifikat / Pendaftaran', ['nomor_sertifikat_hki', 'nomor_pendaftaran_hki']],
                ['Status', ['status_hki']],
            ],
            'dates' => ['tanggal_terbit_sertifikat_hki', 'tanggal_pendaftaran_hki'],
            'link' => 'tautan_hki',
        ],
        'seminar' => [
            'label' => 'Seminar',
            'columns' => [
                ['Seminar / Prosiding', ['nama_seminar', 'nama_prosiding']],
                ['Penyelenggara', ['penyelenggara']],
                ['Lokasi', ['lokasi_seminar']],
            ],
            'dates' => ['tanggal_seminar'],
            'link' => 'tautan_seminar',
        ],
    ];

    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request, string $jenis, string $kategori = 'artikel'): View|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);
        abort_unless(isset(self::KATEGORI[$kategori]), 404);

        $meta = self::KATEGORI[$kategori];
        $filters = $this->validatedFilters($request);

        $query = array_filter(
            $filters + ['kategori' => $kategori, 'per_page' => 10],
            static fn ($value) => $value !== null && $value !== ''
        );

        $viewData = [
            'kategori' => $kategori,
            'tabs' => array_map(static fn (array $item): string => $item['label'], self::KATEGORI),
            'kategoriLabel' => $meta['label'],
            'headers' => array_merge(
                ['Penulis'],
                array_map(static fn (array $column): string => $column[0], $meta['columns']),
                ['Tahun']
            ),
            'filters' => $filters,
            'jenis' => $jenis,
            'jenisLabel' => self::JENIS_KEGIATAN[$jenis],
        ];

        try {
            $response = $this->tridharmaService->getLuaranList(
                request()->routeIs('luaran.tridharma.index') ? $query + ['jenis' => $jenis] : $query
            );
        } catch (ConnectionException) {
            return view('luaran.index', $viewData + $this->emptyState('Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.'));
        }

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            $message = $response->json('message');

            return view('luaran.index', $viewData + $this->emptyState(
                is_string($message) && $message !== '' ? $message : 'Gagal memuat data luaran.'
            ));
        }

        $data = $response->json('data', []);
        $items = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) ? $data : []);

        $rows = array_map(
            fn (array $item): array => $this->toRow($item, $meta),
            array_values(array_filter($items, fn ($item): bool => is_array($item) && $this->hasCategoryContent($item, $kategori)))
        );

        $pagination = [
            'total' => (int) ($data['total'] ?? count($rows)),
            'per_page' => (int) ($data['per_page'] ?? 10),
            'current_page' => (int) ($data['current_page'] ?? 1),
            'last_page' => (int) ($data['last_page'] ?? 1),
            'from' => $data['from'] ?? null,
            'to' => $data['to'] ?? null,
        ];

        return view('luaran.index', $viewData + [
            'rows' => $rows,
            'pagination' => $pagination,
            'apiError' => null,
        ]);
    }

    public function export(Request $request, string $jenis, string $kategori): Response|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);
        abort_unless(isset(self::KATEGORI[$kategori]), 404);

        $filters = $this->validatedFilters($request);
        unset($filters['page']);

        try {
            $response = $this->tridharmaService->exportLuaran(array_filter(
                $filters + ['kategori' => $kategori] + (request()->routeIs('luaran.tridharma.export') ? ['jenis' => $jenis] : []),
                static fn ($value) => $value !== null && $value !== ''
            ));
        } catch (ConnectionException) {
            return $this->exportFailed('Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
        }

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return $this->exportFailed((string) ($response->json('message') ?: 'Gagal mengunduh data Excel.'));
        }

        return response($response->body(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="luaran-'.$kategori.'_'.now()->format('Ymd').'.xlsx"',
        ]);
    }

    public function show(string $jenis, string $kategori, int $id): View|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis], self::KATEGORI[$kategori]), 404);

        try {
            $response = $this->tridharmaService->getLuaran($id);
        } catch (ConnectionException) {
            return redirect()->route('luaran.tridharma.index', [$jenis, $kategori])->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Detail',
                'message' => 'Tidak dapat terhubung ke server Tridharma.',
            ]);
        }

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        abort_if($response->status() === 404, 404);

        if (! $response->successful() || ! is_array($response->json('data'))) {
            return redirect()->route('luaran.tridharma.index', [$jenis, $kategori])->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Detail',
                'message' => (string) ($response->json('message') ?: 'Detail luaran tidak tersedia.'),
            ]);
        }

        return view('luaran.show', [
            'jenis' => $jenis,
            'jenisLabel' => self::JENIS_KEGIATAN[$jenis],
            'kategori' => $kategori,
            'kategoriLabel' => self::KATEGORI[$kategori]['label'],
            'luaran' => $response->json('data'),
        ]);
    }

    public function legacyIndex(Request $request, string $kategori = 'artikel'): View|RedirectResponse
    {
        $kategoriParam = (string) $request->route('kategori', $request->query('kategori', $kategori));

        return $this->index($request, 'penelitian', $kategoriParam);
    }

    public function legacyExport(Request $request, string $kategori): Response|RedirectResponse
    {
        return $this->export($request, 'penelitian', $kategori);
    }

    public function create(Request $request, string $jenis): View|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);

        try {
            $kegiatanResponse = $this->tridharmaService->getKegiatanList([
                'jenis' => $jenis,
                'per_page' => 200,
            ]);
            $jenisResponse = $this->tridharmaService->getJenisLuaranList(['per_page' => 200]);
            $dosenResponse = $this->tridharmaService->getDosenList(['per_page' => 200, 'status' => 'DTPS']);
        } catch (ConnectionException) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Tidak dapat terhubung ke server Tridharma.',
            ]);
        }

        if ($kegiatanResponse->status() === 401 || $jenisResponse->status() === 401 || $dosenResponse->status() === 401) {
            return redirect()->route('login');
        }

        return view('luaran.create', [
            'jenis' => $jenis,
            'jenisLabel' => self::JENIS_KEGIATAN[$jenis],
            'kategori' => $request->query('kategori', 'artikel'),
            'idKegiatan' => $request->query('id_kegiatan'),
            'kegiatan' => $kegiatanResponse->json('data.data', []),
            'jenisLuaran' => $jenisResponse->json('data.data', []),
            'dosen' => $dosenResponse->json('data.data', []),
        ]);
    }

    public function store(Request $request, string $jenis = 'penelitian'): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax() || $request->isJson() || $request->has('jenis_luaran')) {
            return $this->storeJson($request);
        }

        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);

        $validated = $request->validate([
            'id_kegiatan' => ['required', 'integer'],
            'id_dosen' => ['required', 'integer'],
            'id_jenis_luaran' => ['required', 'integer'],
            'judul_buku' => ['nullable', 'string', 'max:255'],
            'nama_jurnal' => ['nullable', 'string', 'max:255'],
            'nama_seminar' => ['nullable', 'string', 'max:255'],
            'nama_prosiding' => ['nullable', 'string', 'max:255'],
            'judul_hak_cipta' => ['nullable', 'string', 'max:255'],
            'nama_produk' => ['nullable', 'string', 'max:255'],
            'tahun_terbit' => ['nullable', 'integer', 'digits:4'],
            'doi' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'tautan_jurnal' => ['nullable', 'url', 'max:2048'],
            'tautan_buku' => ['nullable', 'url', 'max:2048'],
            'tautan_seminar' => ['nullable', 'url', 'max:2048'],
            'tautan_hki' => ['nullable', 'url', 'max:2048'],
            'tautan_produk' => ['nullable', 'url', 'max:2048'],
        ]);

        $response = $this->tridharmaService->createLuaran($validated);

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        if (! $response->successful()) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menyimpan',
                'message' => (string) ($response->json('message') ?: 'Data luaran tidak berhasil disimpan.'),
            ]);
        }

        $kategori = $request->input('kategori', 'artikel');

        return redirect()->route('luaran.tridharma.index', [$jenis, $kategori])->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data luaran berhasil ditambahkan.',
        ]);
    }

    private function storeJson(Request $request): JsonResponse|RedirectResponse
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

        $idJenisLuaran = $this->resolveJenisLuaranId($jenis, $request->input('indeksasi'));
        if ($idJenisLuaran === null) {
            $idJenisLuaran = $this->getJenisLuaranId($jenis);
        }

        $payload = match ($jenis) {
            'Buku' => $this->validateAndBuildBukuPayload($request),
            'Jurnal' => $this->validateAndBuildJurnalPayload($request),
            'Seminar' => $this->validateAndBuildSeminarPayload($request),
            'HKI' => $this->validateAndBuildHkiPayload($request),
            'Produk' => $this->validateAndBuildProdukPayload($request),
        };

        $payload['id_dosen'] = (int) $idDosen;
        $payload['id_jenis_luaran'] = (int) $idJenisLuaran;
        $idKegiatan = $request->validate([
            'id_kegiatan' => ['nullable', 'integer', 'min:1'],
        ])['id_kegiatan'] ?? null;
        if ($idKegiatan !== null) {
            $payload['id_kegiatan'] = (int) $idKegiatan;
        }

        if (! empty($payload['tautan_jurnal'])) {
            $payload['url_luaran'] = $payload['tautan_jurnal'];
        } elseif (! empty($payload['tautan_buku'])) {
            $payload['url_luaran'] = $payload['tautan_buku'];
        } elseif (! empty($payload['tautan_seminar'])) {
            $payload['url_luaran'] = $payload['tautan_seminar'];
        } elseif (! empty($payload['tautan_hki'])) {
            $payload['url_luaran'] = $payload['tautan_hki'];
        } elseif (! empty($payload['tautan_produk'])) {
            $payload['url_luaran'] = $payload['tautan_produk'];
        }

        if (isset($payload['halaman_jurnal']) && is_numeric($payload['halaman_jurnal'])) {
            $payload['halaman_jurnal'] = (int) $payload['halaman_jurnal'];
        }

        unset($payload['jenis_luaran'], $payload['url_publikasi']);

        $response = $this->tridharmaService->post('luaran', $payload);

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
                ], 200);
            }

            return redirect()->route('luaran.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $message,
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $response->status() >= 400 ? $response->status() : 400);
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $message,
        ]);
    }

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

        $idJenisLuaran = $this->resolveJenisLuaranId($jenis, $request->input('indeksasi')) ?? $this->getJenisLuaranId($jenis);

        $payload = match ($jenis) {
            'Buku' => $this->validateAndBuildBukuPayload($request),
            'Jurnal' => $this->validateAndBuildJurnalPayload($request),
            'Seminar' => $this->validateAndBuildSeminarPayload($request),
            'HKI' => $this->validateAndBuildHkiPayload($request),
            'Produk' => $this->validateAndBuildProdukPayload($request),
        };

        $payload['id_dosen'] = (int) $idDosen;
        $payload['id_jenis_luaran'] = (int) $idJenisLuaran;
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
        $role = is_array($user) ? strtolower((string) ($user['role'] ?? '')) : '';

        return $role === 'dosen';
    }

    private function authenticatedDosenId(): ?int
    {
        $user = session('user', []);
        if (! is_array($user)) {
            return null;
        }

        $idDosen = $user['id_dosen'] ?? ($user['dosen_id'] ?? ($user['id'] ?? null));

        return is_numeric($idDosen) ? (int) $idDosen : null;
    }

    private function resolveJenisLuaranId(string $jenis, mixed $index): ?int
    {
        try {
            $response = $this->tridharmaService->get('jenis-luaran');
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('data', []);
        if (is_array($data) && is_array($data['data'] ?? null)) {
            $data = $data['data'];
        }

        if (! is_array($data)) {
            return null;
        }

        $families = match ($jenis) {
            'Buku' => ['buku', 'monograf'],
            'Jurnal' => ['jurnal'],
            'Seminar' => ['prosiding', 'seminar'],
            'HKI' => ['hki'],
            'Produk' => ['produk'],
            default => [],
        };
        $candidates = array_values(array_filter($data, static function (mixed $item) use ($families): bool {
            if (! is_array($item)) {
                return false;
            }

            $name = strtolower((string) ($item['nama_jenis_luaran'] ?? ''));

            foreach ($families as $family) {
                if (str_contains($name, $family)) {
                    return true;
                }
            }

            return false;
        }));

        if ($candidates === []) {
            return null;
        }

        $selectedIndex = strtolower(trim(is_scalar($index) ? (string) $index : ''));
        if ($selectedIndex !== '') {
            foreach ($candidates as $candidate) {
                if (strtolower(trim((string) ($candidate['indeks'] ?? ''))) === $selectedIndex) {
                    $candidates = [$candidate];
                    break;
                }
            }
        }

        $id = $candidates[0]['id_jenis_luaran'] ?? ($candidates[0]['id'] ?? null);

        return is_numeric($id) ? (int) $id : null;
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

    /**
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'tahun' => ['nullable', 'integer', 'between:1900,2100'],
            'id_kegiatan' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function exportFailed(string $message): RedirectResponse
    {
        return back()->with('toast', [
            'type' => 'error',
            'title' => 'Unduh Gagal',
            'message' => $message,
        ]);
    }

    /**
     * Judul utama sebuah luaran, apa pun jenisnya.
     *
     * @param  array<string, mixed>  $luaran
     */
    public static function judul(array $luaran): string
    {
        foreach (['judul_buku', 'nama_jurnal', 'nama_prosiding', 'nama_seminar', 'judul_hak_cipta', 'nama_produk'] as $key) {
            $value = trim((string) ($luaran[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '-';
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array{label: string, columns: array<int, array{0: string, 1: array<int, string>}>, dates: array<int, string>, link: string}  $meta
     * @return array{cells: array<int, string>, link: ?string}
     */
    private function toRow(array $item, array $meta): array
    {
        $cells = [(string) ($item['nama_penulis'] ?? $item['dosen']['nama_dosen'] ?? '-')];

        foreach ($meta['columns'] as [, $keys]) {
            $cells[] = $this->firstFilled($item, $keys);
        }

        $cells[] = $this->tahun($item, $meta['dates']);

        $link = trim((string) ($item[$meta['link']] ?? ''));

        return [
            'id' => $item['id_luaran'] ?? null,
            'cells' => $cells,
            'link' => preg_match('~^https?://~i', $link) === 1 && filter_var($link, FILTER_VALIDATE_URL) ? $link : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $keys
     */
    private function firstFilled(array $item, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($item[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '-';
    }

    /**
     * Hindari menampilkan data yang tidak memiliki field utama kategorinya.
     *
     * @param  array<string, mixed>  $item
     */
    private function hasCategoryContent(array $item, string $kategori): bool
    {
        $fields = match ($kategori) {
            'artikel' => ['nama_jurnal'],
            'buku' => ['judul_buku'],
            'hki' => ['judul_hak_cipta'],
            'seminar' => ['nama_seminar', 'nama_prosiding'],
        };

        foreach ($fields as $field) {
            if (trim((string) ($item[$field] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $dateKeys
     */
    private function tahun(array $item, array $dateKeys): string
    {
        if (! empty($item['tahun_terbit'])) {
            return (string) $item['tahun_terbit'];
        }

        foreach ($dateKeys as $key) {
            if (! empty($item[$key]) && preg_match('/^\d{4}/', (string) $item[$key], $matches)) {
                return $matches[0];
            }
        }

        return '-';
    }

    /**
     * @return array{rows: array<int, mixed>, pagination: array<string, int>, apiError: string}
     */
    private function emptyState(string $message): array
    {
        return [
            'rows' => [],
            'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
            'apiError' => $message,
        ];
    }
}
