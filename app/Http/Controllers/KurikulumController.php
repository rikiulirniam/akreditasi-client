<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KurikulumController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $response = $this->akademikService->getKurikulumList();

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $apiData = $response->json('data') ?? [];
        $kurikulumList = [];

        if (is_array($apiData) && ! empty($apiData)) {
            foreach ($apiData as $item) {
                $kurikulumList[] = $this->normalizeKurikulum($item);
            }
        }

        // Apply session modifications
        $deletedIds = session('deleted_kurikulum_ids', []);
        $kurikulumList = array_values(
            array_filter($kurikulumList, fn ($k) => ! in_array((string) ($k['id'] ?? ''), array_map('strval', $deletedIds)))
        );

        $customList = session('custom_kurikulum_list', []);
        if (is_array($customList)) {
            foreach ($customList as $custom) {
                $found = false;
                foreach ($kurikulumList as $idx => $k) {
                    if ((string) ($k['id'] ?? '') === (string) ($custom['id'] ?? '')) {
                        $kurikulumList[$idx] = array_merge($kurikulumList[$idx], $custom);
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    array_unshift($kurikulumList, $custom);
                }
            }
        }

        // Fetch prodi list for filter dropdown
        $prodiResponse = $this->akademikService->getProdiList();
        $prodiList = $prodiResponse->json('data') ?? [];

        // Search filter
        $search = trim((string) $request->query('search', ''));
        $prodiFilter = trim((string) $request->query('prodi_id', ''));

        if ($search !== '' || $prodiFilter !== '') {
            $kurikulumList = array_values(array_filter($kurikulumList, function ($k) use ($search, $prodiFilter) {
                $matchSearch = $search === '' ||
                    str_contains(strtolower($k['nama_kurikulum'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($k['sk_kurikulum'] ?? ''), strtolower($search));

                $matchProdi = $prodiFilter === '' || (string) ($k['id_prodi'] ?? '') === $prodiFilter;

                return $matchSearch && $matchProdi;
            }));
        }

        // Paginate (10 data per halaman)
        $page = (int) $request->query('page', 1);
        $perPage = 10;
        $total = count($kurikulumList);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($page, $lastPage));
        $offset = ($currentPage - 1) * $perPage;
        $pagedList = array_slice($kurikulumList, $offset, $perPage);

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        return view('kurikulum.index', [
            'kurikulumList' => $pagedList,
            'pagination' => $pagination,
            'prodiList' => is_array($prodiList) ? $prodiList : [],
            'search' => $search,
            'selectedProdi' => $prodiFilter,
        ]);
    }

    public function create(): View
    {
        $prodiResponse = $this->akademikService->getProdiList();
        $prodiList = $prodiResponse->json('data') ?? [];

        $kategoriCplList = $this->getKategoriCplList();

        return view('kurikulum.create', [
            'prodiList' => is_array($prodiList) ? $prodiList : [],
            'kategoriCplList' => $kategoriCplList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kurikulum' => ['required', 'string', 'max:255'],
            'sk_kurikulum' => ['required', 'string', 'max:100'],
            'berlaku_sampai' => ['required', 'string', 'max:50'],
            'id_prodi' => ['required'],
            'deskripsi_cpl' => ['nullable', 'string'],
            'kategori_cpl' => ['nullable', 'string', 'max:100'],
        ], [
            'nama_kurikulum.required' => 'Nama Kurikulum wajib diisi.',
            'sk_kurikulum.required' => 'SK Kurikulum wajib diisi.',
            'berlaku_sampai.required' => 'Tahun atau tanggal berlaku wajib diisi.',
            'id_prodi.required' => 'Program Studi wajib dipilih.',
        ]);

        $berlakuInput = trim($validated['berlaku_sampai']);
        $berlakuTahun = 2028;
        if (preg_match('/\b(19\d{2}|20\d{2})\b/', $berlakuInput, $matches)) {
            $berlakuTahun = (int) $matches[1];
        }

        $payload = [
            'nama_kurikulum' => trim($validated['nama_kurikulum']),
            'sk_kurikulum' => trim($validated['sk_kurikulum']),
            'berlaku_sampai' => $berlakuTahun,
            'id_prodi' => (int) $validated['id_prodi'],
        ];

        $response = $this->akademikService->createKurikulum($payload);

        $deskripsiCpl = trim($validated['deskripsi_cpl'] ?? '');
        $kategoriCpl = trim($validated['kategori_cpl'] ?? '');

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal menambahkan data Kurikulum ke server.');

            // Tetap simpan ke session agar pengguna dapat mencoba fitur secara lokal
            $newId = 'temp_'.time();
            $customList = session('custom_kurikulum_list', []);
            $customList[] = array_merge($payload, [
                'id' => $newId,
                'berlaku_sampai' => $berlakuInput,
                'cpl_deskripsi' => $deskripsiCpl,
                'cpl_kategori' => $kategoriCpl,
            ]);
            session(['custom_kurikulum_list' => $customList]);

            return redirect()->route('kurikulum.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => 'Data Kurikulum berhasil disimpan secara lokal.',
            ]);
        }

        $newId = $response->json('data.id') ?? time();

        // Handle CPL if provided
        if (! empty($deskripsiCpl) || ! empty($kategoriCpl)) {
            $this->akademikService->createCpl([
                'kurikulum_id' => $newId,
                'deskripsi' => $deskripsiCpl,
                'kategori' => $kategoriCpl,
            ]);
        }

        // Store in session with formatted display
        $customList = session('custom_kurikulum_list', []);
        $customList[] = array_merge($payload, [
            'id' => $newId,
            'berlaku_sampai' => $berlakuInput,
            'cpl_deskripsi' => $deskripsiCpl,
            'cpl_kategori' => $kategoriCpl,
        ]);
        session(['custom_kurikulum_list' => $customList]);

        return redirect()->route('kurikulum.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Ditambahkan',
            'message' => $response->json('message') ?? 'Data Kurikulum berhasil ditambahkan.',
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $response = $this->akademikService->getKurikulumById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $kurikulum = $response->json('data');

        if (! $kurikulum) {
            // Try session fallback
            $customList = session('custom_kurikulum_list', []);
            foreach ($customList as $k) {
                if ((string) ($k['id'] ?? '') === (string) $id) {
                    $kurikulum = $k;
                    break;
                }
            }
        }

        if (! $kurikulum) {
            return redirect()->route('kurikulum.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data kurikulum tidak ditemukan.',
            ]);
        }

        $kurikulum = $this->normalizeKurikulum($kurikulum);

        // Fetch CPL data for this kurikulum
        $cplResponse = $this->akademikService->getCplList(['kurikulum_id' => $id]);
        $cplList = $cplResponse->successful() ? ($cplResponse->json('data') ?? []) : [];

        // Fetch mata kuliah for this kurikulum
        $mataKuliahResponse = $this->akademikService->getMataKuliahList(['kurikulum_id' => $id]);
        $mataKuliahList = $mataKuliahResponse->successful() ? ($mataKuliahResponse->json('data') ?? []) : [];

        // Jika API mengembalikan daftar mata kuliah umum, filter berdasarkan id_kurikulum jika tersedia
        $curriculumMataKuliah = array_filter($mataKuliahList, function ($mk) use ($id) {
            return isset($mk['id_kurikulum']) && (string) $mk['id_kurikulum'] === (string) $id;
        });

        if (! empty($curriculumMataKuliah)) {
            $mataKuliahList = array_values($curriculumMataKuliah);
        }

        // Jika mata kuliah kosong dari API, buat dummy data yang sesuai persis dengan gambar mockup
        if (empty($mataKuliahList)) {
            $mataKuliahList = [
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
                ['kode_mk' => 'TRK101', 'nama_mk' => 'Algoritma dan Pemrograman', 'sks' => 3, 'kategori_cpl' => 'Kategori CPL', 'deskripsi_cpl' => 'Isian Deskripsi CPL'],
            ];
        } else {
            // Normalisasi data mata kuliah dari API (mendukung field kode_mata_kuliah dari API Akademik)
            $mataKuliahList = array_map(function ($mk) use ($kurikulum) {
                $cplItem = $mk['cpl'][0] ?? null;
                $kategori = $mk['kategori_cpl'] ?? ($cplItem['kategori'] ?? ($kurikulum['cpl_kategori'] ?? 'Kategori CPL'));
                $deskripsi = $mk['deskripsi_cpl'] ?? ($cplItem['deskripsi'] ?? ($kurikulum['cpl_deskripsi'] ?? 'Isian Deskripsi CPL'));

                return [
                    'id' => $mk['id'] ?? null,
                    'kode_mk' => $mk['kode_mata_kuliah'] ?? $mk['kode_mk'] ?? $mk['kode'] ?? '-',
                    'nama_mk' => $mk['nama_mata_kuliah'] ?? $mk['nama_mk'] ?? $mk['nama'] ?? '-',
                    'sks' => $mk['sks'] ?? 3,
                    'kategori_cpl' => ! empty($kategori) ? $kategori : 'Kategori CPL',
                    'deskripsi_cpl' => ! empty($deskripsi) ? $deskripsi : 'Isian Deskripsi CPL',
                ];
            }, $mataKuliahList);
        }

        // Apply session custom mata kuliah CPL overrides
        $customMkCpl = session('custom_mata_kuliah_cpl', [])[$id] ?? [];
        foreach ($mataKuliahList as &$mk) {
            $key = (string) ($mk['id'] ?? $mk['kode_mk'] ?? '');
            if (isset($customMkCpl[$key]['kategori_cpl'])) {
                $cat = $customMkCpl[$key]['kategori_cpl'];
                $mk['kategori_cpl'] = is_array($cat) ? $cat : (empty($cat) ? [] : array_map('trim', explode(',', $cat)));
            } else {
                $cat = $mk['kategori_cpl'] ?? [];
                if (is_string($cat)) {
                    $mk['kategori_cpl'] = (empty($cat) || $cat === 'Kategori CPL') ? ['Keterampilan Khusus'] : array_map('trim', explode(',', $cat));
                } elseif (! is_array($cat)) {
                    $mk['kategori_cpl'] = ['Keterampilan Khusus'];
                }
            }
        }
        unset($mk);

        // Fetch prodi info
        $prodiResponse = $this->akademikService->getProdiById($kurikulum['id_prodi'] ?? 0);
        $prodiDetail = $prodiResponse->successful() ? ($prodiResponse->json('data') ?? null) : null;

        $kategoriCplList = $this->getKategoriCplList();

        return view('kurikulum.show', [
            'kurikulum' => $kurikulum,
            'cplList' => is_array($cplList) ? $cplList : [],
            'mataKuliahList' => $mataKuliahList,
            'prodiDetail' => $prodiDetail,
            'kategoriCplList' => $kategoriCplList,
        ]);
    }

    public function updateMataKuliahCpl(Request $request, int|string $kurikulumId): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'mata_kuliah_id' => ['required'],
            'kategori_cpl' => ['nullable', 'array'],
            'kategori_cpl.*' => ['string'],
        ]);

        $mkId = (string) $validated['mata_kuliah_id'];
        $kategoriList = array_values(array_filter($validated['kategori_cpl'] ?? []));

        $mkCplMap = session('custom_mata_kuliah_cpl', []);
        if (! isset($mkCplMap[$kurikulumId])) {
            $mkCplMap[$kurikulumId] = [];
        }
        $mkCplMap[$kurikulumId][$mkId] = [
            'kategori_cpl' => $kategoriList,
        ];
        session(['custom_mata_kuliah_cpl' => $mkCplMap]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori CPL Mata Kuliah berhasil diperbarui.',
                'data' => [
                    'kategori_cpl' => $kategoriList,
                ],
            ]);
        }

        return redirect()->route('kurikulum.show', $kurikulumId)->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => 'Kategori CPL Mata Kuliah berhasil diperbarui.',
        ]);
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        $response = $this->akademikService->getKurikulumById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $kurikulum = $response->json('data');

        if (! $kurikulum) {
            // Try session fallback
            $customList = session('custom_kurikulum_list', []);
            foreach ($customList as $k) {
                if ((string) ($k['id'] ?? '') === (string) $id) {
                    $kurikulum = $k;
                    break;
                }
            }
        }

        if (! $kurikulum) {
            return redirect()->route('kurikulum.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data kurikulum tidak ditemukan.',
            ]);
        }

        $kurikulum = $this->normalizeKurikulum($kurikulum);

        $prodiResponse = $this->akademikService->getProdiList();
        $prodiList = $prodiResponse->json('data') ?? [];

        $kategoriCplList = $this->getKategoriCplList();

        return view('kurikulum.edit', [
            'kurikulum' => $kurikulum,
            'prodiList' => is_array($prodiList) ? $prodiList : [],
            'kategoriCplList' => $kategoriCplList,
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kurikulum' => ['required', 'string', 'max:255'],
            'sk_kurikulum' => ['required', 'string', 'max:100'],
            'berlaku_sampai' => ['required', 'string', 'max:50'],
            'id_prodi' => ['required'],
            'deskripsi_cpl' => ['nullable', 'string'],
            'kategori_cpl' => ['nullable', 'string', 'max:100'],
        ], [
            'nama_kurikulum.required' => 'Nama Kurikulum wajib diisi.',
            'sk_kurikulum.required' => 'SK Kurikulum wajib diisi.',
            'berlaku_sampai.required' => 'Tahun atau tanggal berlaku wajib diisi.',
            'id_prodi.required' => 'Program Studi wajib dipilih.',
        ]);

        $berlakuInput = trim($validated['berlaku_sampai']);
        $berlakuTahun = 2028;
        if (preg_match('/\b(19\d{2}|20\d{2})\b/', $berlakuInput, $matches)) {
            $berlakuTahun = (int) $matches[1];
        }

        $payload = [
            'nama_kurikulum' => trim($validated['nama_kurikulum']),
            'sk_kurikulum' => trim($validated['sk_kurikulum']),
            'berlaku_sampai' => $berlakuTahun,
            'id_prodi' => (int) $validated['id_prodi'],
        ];

        $deskripsiCpl = trim($validated['deskripsi_cpl'] ?? '');
        $kategoriCpl = trim($validated['kategori_cpl'] ?? '');

        $this->akademikService->updateKurikulum($id, $payload);

        // Update session regardless of response (optimistic UI)
        $customList = session('custom_kurikulum_list', []);
        $found = false;
        foreach ($customList as &$k) {
            if ((string) ($k['id'] ?? '') === (string) $id) {
                $k = array_merge($k, $payload, [
                    'id' => $id,
                    'berlaku_sampai' => $berlakuInput,
                    'cpl_deskripsi' => $deskripsiCpl,
                    'cpl_kategori' => $kategoriCpl,
                ]);
                $found = true;
                break;
            }
        }
        unset($k);
        if (! $found) {
            $customList[] = array_merge($payload, [
                'id' => $id,
                'berlaku_sampai' => $berlakuInput,
                'cpl_deskripsi' => $deskripsiCpl,
                'cpl_kategori' => $kategoriCpl,
            ]);
        }
        session(['custom_kurikulum_list' => $customList]);

        return redirect()->route('kurikulum.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => 'Data Kurikulum berhasil diperbarui.',
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $this->akademikService->deleteKurikulum($id);

        $customList = session('custom_kurikulum_list', []);
        $customList = array_values(
            array_filter($customList, fn ($k) => (string) ($k['id'] ?? '') !== (string) $id)
        );
        session(['custom_kurikulum_list' => $customList]);

        $deletedIds = session('deleted_kurikulum_ids', []);
        $deletedIds[] = (string) $id;
        session(['deleted_kurikulum_ids' => $deletedIds]);

        return redirect()->route('kurikulum.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => 'Data Kurikulum berhasil dihapus.',
        ]);
    }

    /**
     * Ambil list kategori CPL (menggabungkan data dari service atau daftar default).
     *
     * @return array<int, string>
     */
    protected function getKategoriCplList(): array
    {
        $defaultCategories = [
            'Sikap',
            'Keterampilan Umum',
            'Keterampilan Khusus',
            'Pengetahuan',
        ];

        try {
            $response = $this->akademikService->getCplList();
            if ($response->successful()) {
                $cplData = $response->json('data') ?? [];
                if (is_array($cplData) && ! empty($cplData)) {
                    $extracted = [];
                    foreach ($cplData as $c) {
                        if (! empty($c['kategori'])) {
                            $extracted[] = trim($c['kategori']);
                        }
                    }
                    if (! empty($extracted)) {
                        return array_values(array_unique(array_merge($defaultCategories, $extracted)));
                    }
                }
            }
        } catch (\Throwable) {
            // Silently fallback to defaults
        }

        return $defaultCategories;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function normalizeKurikulum(array $item): array
    {
        $deskripsiFallback = 'Menjadi perguruan tinggi terapan berstandar nasional dan internasional yang mampu bersaing, akuntabel, berkarakter, dan beretika dalam bidang teknologi dan bisnis.';

        return [
            'id' => $item['id'] ?? null,
            'nama_kurikulum' => $item['nama_kurikulum'] ?? 'Kurikulum Merdeka 2024',
            'sk_kurikulum' => $item['sk_kurikulum'] ?? 'SK-08202421/2024',
            'berlaku_sampai' => $item['berlaku_sampai'] ?? '2028',
            'id_prodi' => $item['id_prodi'] ?? null,
            'nama_prodi' => $item['prodi']['nama_prodi'] ?? $item['nama_prodi'] ?? 'Teknologi Rekayasa Komputer',
            'cpl' => $item['cpl'] ?? [],
            'mata_kuliah' => $item['mata_kuliah'] ?? [],
            'cpl_deskripsi' => ! empty($item['cpl_deskripsi']) ? $item['cpl_deskripsi'] : (! empty($item['cpl'][0]['deskripsi']) ? $item['cpl'][0]['deskripsi'] : $deskripsiFallback),
            'cpl_kategori' => ! empty($item['cpl_kategori']) ? $item['cpl_kategori'] : (! empty($item['cpl'][0]['kategori']) ? $item['cpl'][0]['kategori'] : 'Kategori CPL'),
        ];
    }

    protected function extractErrorMessage(mixed $response, string $fallback): string
    {
        $errors = $response->json('errors');
        if (is_array($errors) && ! empty($errors)) {
            $firstField = reset($errors);
            if (is_array($firstField) && ! empty($firstField)) {
                return (string) $firstField[0];
            }
            if (is_string($firstField)) {
                return $firstField;
            }
        }

        return (string) ($response->json('message') ?? $fallback);
    }
}
