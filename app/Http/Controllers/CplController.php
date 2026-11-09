<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CplController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $response = $this->akademikService->getCplList();

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $apiData = $response->json('data') ?? [];
        $cplList = is_array($apiData) ? $apiData : [];

        // Apply local session modifications for resilience
        $deletedIds = session('deleted_cpl_ids', []);
        $cplList = array_values(
            array_filter($cplList, fn ($item) => ! in_array((string) ($item['id'] ?? ''), array_map('strval', $deletedIds)))
        );

        $customList = session('custom_cpl_list', []);
        if (is_array($customList)) {
            foreach ($customList as $custom) {
                $found = false;
                foreach ($cplList as $idx => $existing) {
                    if ((string) ($existing['id'] ?? '') === (string) ($custom['id'] ?? '')) {
                        $cplList[$idx] = array_merge($cplList[$idx], $custom);
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    array_unshift($cplList, $custom);
                }
            }
        }

        // Fetch list of kurikulum for filters and form dropdowns
        $kurikulumResponse = $this->akademikService->getKurikulumList();
        $kurikulumList = $kurikulumResponse->json('data') ?? [];
        if (! is_array($kurikulumList)) {
            $kurikulumList = [];
        }

        // Statistics counts by category
        $stats = [
            'total' => count($cplList),
            'sikap' => count(array_filter($cplList, fn ($c) => strtolower(trim((string) ($c['kategori'] ?? ''))) === 'sikap')),
            'pengetahuan' => count(array_filter($cplList, fn ($c) => strtolower(trim((string) ($c['kategori'] ?? ''))) === 'pengetahuan')),
            'keterampilan_umum' => count(array_filter($cplList, fn ($c) => strtolower(trim((string) ($c['kategori'] ?? ''))) === 'keterampilan umum')),
            'keterampilan_khusus' => count(array_filter($cplList, fn ($c) => strtolower(trim((string) ($c['kategori'] ?? ''))) === 'keterampilan khusus')),
        ];

        // Search & Filters
        $search = trim((string) $request->query('search', ''));
        $kategoriFilter = trim((string) $request->query('kategori', ''));
        $kurikulumFilter = trim((string) $request->query('kurikulum_id', ''));

        if ($search !== '' || $kategoriFilter !== '' || $kurikulumFilter !== '') {
            $cplList = array_values(array_filter($cplList, function ($item) use ($search, $kategoriFilter, $kurikulumFilter) {
                $matchSearch = $search === '' ||
                    str_contains(strtolower($item['kode_cpl'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($item['deskripsi_cpl'] ?? ''), strtolower($search));

                $matchKategori = $kategoriFilter === '' ||
                    strtolower(trim((string) ($item['kategori'] ?? ''))) === strtolower($kategoriFilter);

                $matchKurikulum = $kurikulumFilter === '' ||
                    (string) ($item['id_kurikulum'] ?? ($item['kurikulum']['id'] ?? '')) === $kurikulumFilter;

                return $matchSearch && $matchKategori && $matchKurikulum;
            }));
        }

        // Pagination
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));
        $total = count($cplList);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = min($page, $lastPage);
        $offset = ($currentPage - 1) * $perPage;
        $pagedList = array_slice($cplList, $offset, $perPage);

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        return view('cpl.index', [
            'cplList' => $pagedList,
            'pagination' => $pagination,
            'kurikulumList' => $kurikulumList,
            'stats' => $stats,
            'search' => $search,
            'selectedKategori' => $kategoriFilter,
            'selectedKurikulum' => $kurikulumFilter,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('cpl.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_kurikulum' => ['required', 'integer'],
            'kode_cpl' => ['required', 'string', 'max:50'],
            'deskripsi_cpl' => ['required', 'string'],
            'kategori' => ['required', 'string', 'in:Sikap,Pengetahuan,Keterampilan Umum,Keterampilan Khusus'],
        ], [
            'id_kurikulum.required' => 'Kurikulum wajib dipilih.',
            'id_kurikulum.integer' => 'Pilihan kurikulum tidak valid.',
            'kode_cpl.required' => 'Kode CPL wajib diisi.',
            'kode_cpl.max' => 'Kode CPL maksimal 50 karakter.',
            'deskripsi_cpl.required' => 'Deskripsi CPL wajib diisi.',
            'kategori.required' => 'Kategori CPL wajib dipilih.',
            'kategori.in' => 'Kategori CPL harus berupa Sikap, Pengetahuan, Keterampilan Umum, atau Keterampilan Khusus.',
        ]);

        $payload = [
            'id_kurikulum' => (int) $validated['id_kurikulum'],
            'kode_cpl' => trim($validated['kode_cpl']),
            'deskripsi_cpl' => trim($validated['deskripsi_cpl']),
            'kategori' => trim($validated['kategori']),
        ];

        $response = $this->akademikService->createCpl($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $createdData = $body['data'] ?? [];
            $createdId = $createdData['id'] ?? time();

            // Find selected kurikulum name for session resilience
            $kurikulumResponse = $this->akademikService->getKurikulumList();
            $kList = $kurikulumResponse->json('data') ?? [];
            $matchedKurikulum = collect($kList)->firstWhere('id', (int) $validated['id_kurikulum']);

            $custom = session('custom_cpl_list', []);
            $custom[] = array_merge($payload, [
                'id' => $createdId,
                'kurikulum' => $matchedKurikulum ?? ['id' => $payload['id_kurikulum'], 'nama_kurikulum' => 'Kurikulum Terpilih'],
            ]);
            session(['custom_cpl_list' => $custom]);

            return redirect()->route('cpl.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => $body['message'] ?? 'Data CPL berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan data CPL.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $errorMessage,
        ]);
    }

    public function show(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $response = $this->akademikService->getCplById($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($response->json(), $response->status());
        }

        return redirect()->route('cpl.index');
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('cpl.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'id_kurikulum' => ['required', 'integer'],
            'kode_cpl' => ['required', 'string', 'max:50'],
            'deskripsi_cpl' => ['required', 'string'],
            'kategori' => ['required', 'string', 'in:Sikap,Pengetahuan,Keterampilan Umum,Keterampilan Khusus'],
        ], [
            'id_kurikulum.required' => 'Kurikulum wajib dipilih.',
            'id_kurikulum.integer' => 'Pilihan kurikulum tidak valid.',
            'kode_cpl.required' => 'Kode CPL wajib diisi.',
            'kode_cpl.max' => 'Kode CPL maksimal 50 karakter.',
            'deskripsi_cpl.required' => 'Deskripsi CPL wajib diisi.',
            'kategori.required' => 'Kategori CPL wajib dipilih.',
            'kategori.in' => 'Kategori CPL harus berupa Sikap, Pengetahuan, Keterampilan Umum, atau Keterampilan Khusus.',
        ]);

        $payload = [
            'id_kurikulum' => (int) $validated['id_kurikulum'],
            'kode_cpl' => trim($validated['kode_cpl']),
            'deskripsi_cpl' => trim($validated['deskripsi_cpl']),
            'kategori' => trim($validated['kategori']),
        ];

        $response = $this->akademikService->updateCpl($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            // Find matched kurikulum
            $kurikulumResponse = $this->akademikService->getKurikulumList();
            $kList = $kurikulumResponse->json('data') ?? [];
            $matchedKurikulum = collect($kList)->firstWhere('id', (int) $validated['id_kurikulum']);

            $custom = session('custom_cpl_list', []);
            $found = false;
            foreach ($custom as &$c) {
                if ((string) ($c['id'] ?? '') === (string) $id) {
                    $c = array_merge($c, $payload, [
                        'kurikulum' => $matchedKurikulum ?? ($c['kurikulum'] ?? []),
                    ]);
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $custom[] = array_merge($payload, [
                    'id' => $id,
                    'kurikulum' => $matchedKurikulum ?? ['id' => $payload['id_kurikulum']],
                ]);
            }
            session(['custom_cpl_list' => $custom]);

            return redirect()->route('cpl.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Data CPL berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui data CPL.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteCpl($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_cpl_list', []);
            $custom = array_values(array_filter($custom, fn ($item) => (string) ($item['id'] ?? '') !== (string) $id));
            session(['custom_cpl_list' => $custom]);

            $deleted = session('deleted_cpl_ids', []);
            $deleted[] = $id;
            session(['deleted_cpl_ids' => $deleted]);

            return redirect()->route('cpl.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Data CPL berhasil dihapus.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus data CPL.');

        return redirect()->route('cpl.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $errorMessage,
        ]);
    }

    /**
     * Display the Evaluasi Ketercapaian CPL interface.
     */
    public function evaluasi(Request $request): View|RedirectResponse
    {
        $kurikulumResponse = $this->akademikService->getKurikulumList();
        $kurikulumList = $kurikulumResponse->json('data') ?? [];
        if (isset($kurikulumList['data']) && is_array($kurikulumList['data'])) {
            $kurikulumList = $kurikulumList['data'];
        }
        $kurikulumList = is_array($kurikulumList) ? array_values(array_filter($kurikulumList, 'is_array')) : [];

        $defaultKurikulumId = $kurikulumList[0]['id'] ?? '';
        $selectedKurikulum = (string) $request->query('id_kurikulum', $defaultKurikulumId);

        $angkatanResponse = $this->akademikService->getAngkatanList();
        $angkatanList = $angkatanResponse->json('data') ?? [];
        if (isset($angkatanList['data']) && is_array($angkatanList['data'])) {
            $angkatanList = $angkatanList['data'];
        }
        $angkatanList = is_array($angkatanList) ? array_values(array_filter($angkatanList, 'is_array')) : [];
        $defaultAngkatan = $angkatanList[0]['angkatan'] ?? ($angkatanList[0]['tahun'] ?? '');
        $selectedAngkatan = (string) $request->query('angkatan', $defaultAngkatan);

        $search = trim((string) $request->query('search', ''));
        $kategoriFilter = trim((string) $request->query('kategori', ''));
        $apiError = null;
        $items = [];

        if ($selectedKurikulum === '' || $selectedAngkatan === '') {
            $apiError = 'Pilih kurikulum dan angkatan yang tersedia untuk memuat hasil evaluasi.';
        } else {
            try {
                $response = $this->akademikService->getAssessmentCpl([
                    'id_kurikulum' => $selectedKurikulum,
                    'angkatan' => $selectedAngkatan,
                ]);

                if ($response->status() === 401) {
                    return redirect()->route('login')->with('toast', [
                        'type' => 'warning',
                        'title' => 'Sesi Berakhir',
                        'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
                    ]);
                }

                if (! $response->successful()) {
                    $apiError = (string) ($response->json('message') ?? 'Hasil evaluasi CPL tidak dapat dimuat dari API.');
                } else {
                    $data = $response->json('data') ?? [];
                    $rawItems = $data['items'] ?? $data['cpl'] ?? $data['results'] ?? $data;

                    if (is_array($rawItems)) {
                        foreach ($rawItems as $item) {
                            if (! is_array($item)) {
                                continue;
                            }

                            $score = $item['skor_aktual']
                                ?? $item['persentase_ketercapaian']
                                ?? $item['persentase']
                                ?? $item['skor']
                                ?? null;
                            $target = $item['target'] ?? $item['ambang_batas'] ?? 70;
                            $scoreValue = $this->numericAssessmentValue($score);
                            $targetValue = $this->numericAssessmentValue($target) ?? 70.0;
                            $status = trim((string) ($item['status'] ?? ''));

                            if (strcasecmp($status, 'tercapai') === 0) {
                                $status = 'Tercapai';
                            } elseif (strcasecmp($status, 'belum tercapai') === 0) {
                                $status = 'Belum Tercapai';
                            } elseif ($scoreValue !== null) {
                                $status = $scoreValue >= $targetValue ? 'Tercapai' : 'Belum Tercapai';
                            } else {
                                $status = 'Data tidak tersedia';
                            }

                            $items[] = [
                                'id' => $item['id'] ?? null,
                                'kode_cpl' => $item['kode_cpl'] ?? $item['kode'] ?? '-',
                                'kategori' => $item['kategori'] ?? '-',
                                'deskripsi_cpl' => $item['deskripsi_cpl'] ?? $item['deskripsi'] ?? '-',
                                'skor_aktual' => $scoreValue,
                                'target' => $targetValue,
                                'status' => $status,
                                'mata_kuliah_pendukung' => $item['mata_kuliah_pendukung'] ?? $item['mata_kuliah'] ?? [],
                                'rekomendasi' => $item['rekomendasi'] ?? null,
                            ];
                        }
                    }
                }
            } catch (ConnectionException $exception) {
                Log::warning('Tidak dapat terhubung ke API evaluasi CPL.', [
                    'message' => $exception->getMessage(),
                ]);
                $apiError = 'Server evaluasi CPL sedang tidak dapat dihubungi. Silakan coba kembali.';
            }
        }

        $filteredItems = array_values(array_filter($items, function ($item) use ($search, $kategoriFilter) {
            if ($kategoriFilter !== '' && strtolower((string) $item['kategori']) !== strtolower($kategoriFilter)) {
                return false;
            }

            if ($search !== '') {
                $matchCode = str_contains(strtolower((string) $item['kode_cpl']), strtolower($search));
                $matchDesc = str_contains(strtolower((string) $item['deskripsi_cpl']), strtolower($search));

                return $matchCode || $matchDesc;
            }

            return true;
        }));

        $scores = array_values(array_filter(array_column($items, 'skor_aktual'), 'is_numeric'));
        $achievedCount = count(array_filter($items, fn ($item) => $item['status'] === 'Tercapai'));
        $stats = [
            'total_cpl' => count($items),
            'tercapai_count' => $achievedCount,
            'belum_tercapai_count' => count(array_filter($items, fn ($item) => $item['status'] === 'Belum Tercapai')),
            'rata_rata_skor' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null,
            'persentase_tercapai' => count($items) > 0 ? round(($achievedCount / count($items)) * 100, 1) : null,
        ];

        return view('cpl.evaluasi', [
            'evaluasiList' => $filteredItems,
            'kurikulumList' => $kurikulumList,
            'angkatanList' => $angkatanList,
            'selectedKurikulum' => $selectedKurikulum,
            'selectedAngkatan' => $selectedAngkatan,
            'search' => $search,
            'selectedKategori' => $kategoriFilter,
            'stats' => $stats,
            'apiError' => $apiError,
        ]);
    }

    private function numericAssessmentValue(mixed $value): ?float
    {
        if (! is_numeric($value) && is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        return is_numeric($value) ? (float) $value : null;
    }

    protected function extractErrorMessage($response, string $fallback): string
    {
        $body = $response->json();
        if (is_array($body) && ! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                return (string) $firstError;
            }
        }

        return (string) ($body['message'] ?? $fallback);
    }
}
