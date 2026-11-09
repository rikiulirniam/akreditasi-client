<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AngkatanController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));

        $response = $this->akademikService->getAngkatanList([
            'page' => $page,
            'per_page' => $perPage,
        ]);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawData = $response->json('data') ?? [];
        $apiPagination = $response->json('pagination');

        // Extract list of items
        $angkatanList = [];
        if (is_array($rawData)) {
            if (isset($rawData['data']) && is_array($rawData['data'])) {
                $angkatanList = $rawData['data'];
                if (! $apiPagination) {
                    $apiPagination = [
                        'total' => $rawData['total'] ?? count($angkatanList),
                        'per_page' => $rawData['per_page'] ?? $perPage,
                        'current_page' => $rawData['current_page'] ?? $page,
                        'last_page' => $rawData['last_page'] ?? 1,
                    ];
                }
            } else {
                $angkatanList = $rawData;
            }
        }

        // Merge custom session items if any
        $customAngkatan = session('custom_angkatan_list', []);
        if (is_array($customAngkatan) && ! empty($customAngkatan)) {
            $deletedIds = session('deleted_angkatan_ids', []);
            $angkatanList = array_values(array_filter($angkatanList, fn ($item) => ! in_array($item['id'] ?? null, $deletedIds)));

            foreach ($customAngkatan as $customItem) {
                $foundIdx = null;
                foreach ($angkatanList as $idx => $existingItem) {
                    if ((string) ($existingItem['id'] ?? '') === (string) ($customItem['id'] ?? '')) {
                        $foundIdx = $idx;
                        break;
                    }
                }
                if ($foundIdx !== null) {
                    $angkatanList[$foundIdx] = array_merge($angkatanList[$foundIdx], $customItem);
                } else {
                    array_unshift($angkatanList, $customItem);
                }
            }
        }

        // Fetch angkatan-kelas to count classes assigned to each angkatan
        $akResponse = $this->akademikService->getAngkatanKelasList();
        $allAk = $akResponse->json('data') ?? [];
        if (isset($allAk['data']) && is_array($allAk['data'])) {
            $allAk = $allAk['data'];
        }
        $customAk = session('custom_angkatan_kelas_list', []);
        $deletedAk = session('deleted_angkatan_kelas_ids', []);
        if (is_array($allAk)) {
            $allAk = array_values(array_filter($allAk, fn ($item) => ! in_array($item['id'] ?? null, $deletedAk)));
            if (is_array($customAk)) {
                foreach ($customAk as $cak) {
                    $allAk[] = $cak;
                }
            }
        }

        $kelasCountByAngkatan = [];
        if (is_array($allAk)) {
            foreach ($allAk as $ak) {
                $aid = (string) ($ak['angkatan_id'] ?? '');
                if ($aid !== '') {
                    $kelasCountByAngkatan[$aid] = ($kelasCountByAngkatan[$aid] ?? 0) + 1;
                }
            }
        }

        // Setup pagination data
        if ($apiPagination && is_array($apiPagination)) {
            $pagination = [
                'total' => (int) ($apiPagination['total'] ?? count($angkatanList)),
                'per_page' => (int) ($apiPagination['per_page'] ?? $perPage),
                'current_page' => (int) ($apiPagination['current_page'] ?? $page),
                'last_page' => (int) ($apiPagination['last_page'] ?? 1),
                'from' => $apiPagination['from'] ?? (($page - 1) * $perPage + 1),
                'to' => $apiPagination['to'] ?? min($page * $perPage, (int) ($apiPagination['total'] ?? count($angkatanList))),
            ];
        } else {
            $total = count($angkatanList);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = max(1, min($page, $lastPage));
            $offset = ($currentPage - 1) * $perPage;
            $pagedList = array_slice($angkatanList, $offset, $perPage);

            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ];
            $angkatanList = $pagedList;
        }

        return view('angkatan.index', [
            'angkatanList' => $angkatanList,
            'pagination' => $pagination,
            'kelasCountByAngkatan' => $kelasCountByAngkatan,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('angkatan.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'angkatan' => ['required', 'integer', 'min:1900', 'max:2100'],
        ], [
            'angkatan.required' => 'Tahun angkatan wajib diisi.',
            'angkatan.integer' => 'Tahun angkatan harus berupa angka tahun yang valid.',
            'angkatan.min' => 'Tahun angkatan minimal tahun 1900.',
            'angkatan.max' => 'Tahun angkatan maksimal tahun 2100.',
        ]);

        $tahun = (int) $validated['angkatan'];
        $payload = [
            'angkatan' => $tahun,
            'tahun' => (string) $tahun,
        ];

        $response = $this->akademikService->createAngkatan($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $createdId = $body['data']['id'] ?? time();
            $custom = session('custom_angkatan_list', []);
            $custom[] = [
                'id' => $createdId,
                'angkatan' => $tahun,
                'tahun' => (string) $tahun,
            ];
            session(['custom_angkatan_list' => $custom]);

            return redirect()->route('angkatan.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => $body['message'] ?? 'Data angkatan berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan data angkatan.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $errorMessage,
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        // 1. Fetch Angkatan detail
        $angkatanResponse = $this->akademikService->getAngkatanById($id);
        $angkatan = $angkatanResponse->json('data');

        if (! $angkatan) {
            $custom = session('custom_angkatan_list', []);
            foreach ($custom as $c) {
                if ((string) ($c['id'] ?? '') === (string) $id) {
                    $angkatan = $c;
                    break;
                }
            }
        }

        if (! $angkatan) {
            $angkatan = [
                'id' => $id,
                'angkatan' => $id,
                'tahun' => (string) $id,
            ];
        }

        // 2. Fetch angkatan-kelas for this angkatan
        $akResponse = $this->akademikService->getAngkatanKelasList();
        $allAk = $akResponse->json('data') ?? [];
        if (isset($allAk['data']) && is_array($allAk['data'])) {
            $allAk = $allAk['data'];
        }

        $customAk = session('custom_angkatan_kelas_list', []);
        $deletedAk = session('deleted_angkatan_kelas_ids', []);
        if (is_array($allAk)) {
            $allAk = array_values(array_filter($allAk, fn ($item) => ! in_array($item['id'] ?? null, $deletedAk)));
            if (is_array($customAk)) {
                foreach ($customAk as $cak) {
                    $allAk[] = $cak;
                }
            }
        }

        $angkatanKelasList = [];
        if (is_array($allAk)) {
            $angkatanKelasList = array_values(array_filter($allAk, fn ($item) => (string) ($item['angkatan_id'] ?? '') === (string) $id));
        }

        // 3. Fetch Master Lists for Modal Dropdowns (Prodi, Kelas, Tahun Akademik)
        $prodiRaw = $this->akademikService->getProdiList()->json('data') ?? [];
        $prodiList = isset($prodiRaw['data']) ? $prodiRaw['data'] : $prodiRaw;

        $kelasRaw = $this->akademikService->getKelasList(['per_page' => 100])->json('data') ?? [];
        $kelasList = isset($kelasRaw['data']) ? $kelasRaw['data'] : $kelasRaw;

        $taRaw = $this->akademikService->getTahunAkademikList(['per_page' => 100])->json('data') ?? [];
        $tahunAkademikList = isset($taRaw['data']) ? $taRaw['data'] : $taRaw;

        // Populate fallback relations if item came from local session
        $prodiMap = collect($prodiList)->keyBy('id');
        $kelasMap = collect($kelasList)->keyBy('id');
        $taMap = collect($tahunAkademikList)->keyBy('id');

        foreach ($angkatanKelasList as &$ak) {
            if (empty($ak['kelas']) && isset($ak['kelas_id']) && isset($kelasMap[$ak['kelas_id']])) {
                $ak['kelas'] = $kelasMap[$ak['kelas_id']];
            }
            if (empty($ak['prodi']) && isset($ak['prodi_id']) && isset($prodiMap[$ak['prodi_id']])) {
                $ak['prodi'] = $prodiMap[$ak['prodi_id']];
            }
            if (empty($ak['tahun_akademik']) && empty($ak['tahunAkademik']) && isset($ak['tahun_akademik_id']) && isset($taMap[$ak['tahun_akademik_id']])) {
                $ak['tahun_akademik'] = $taMap[$ak['tahun_akademik_id']];
            }
        }
        unset($ak);

        return view('angkatan.show', [
            'angkatan' => $angkatan,
            'angkatanKelasList' => $angkatanKelasList,
            'prodiList' => $prodiList,
            'kelasList' => $kelasList,
            'tahunAkademikList' => $tahunAkademikList,
        ]);
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('angkatan.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'angkatan' => ['required', 'integer', 'min:1900', 'max:2100'],
        ], [
            'angkatan.required' => 'Tahun angkatan wajib diisi.',
            'angkatan.integer' => 'Tahun angkatan harus berupa angka tahun yang valid.',
            'angkatan.min' => 'Tahun angkatan minimal tahun 1900.',
            'angkatan.max' => 'Tahun angkatan maksimal tahun 2100.',
        ]);

        $tahun = (int) $validated['angkatan'];
        $payload = [
            'angkatan' => $tahun,
            'tahun' => (string) $tahun,
        ];

        $response = $this->akademikService->updateAngkatan($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_angkatan_list', []);
            $found = false;
            foreach ($custom as &$c) {
                if ((string) $c['id'] === (string) $id) {
                    $c['angkatan'] = $tahun;
                    $c['tahun'] = (string) $tahun;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $custom[] = [
                    'id' => $id,
                    'angkatan' => $tahun,
                    'tahun' => (string) $tahun,
                ];
            }
            session(['custom_angkatan_list' => $custom]);

            return redirect()->route('angkatan.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Data angkatan berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui data angkatan.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteAngkatan($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_angkatan_list', []);
            $custom = array_values(array_filter($custom, fn ($item) => (string) ($item['id'] ?? '') !== (string) $id));
            session(['custom_angkatan_list' => $custom]);

            $deleted = session('deleted_angkatan_ids', []);
            $deleted[] = $id;
            session(['deleted_angkatan_ids' => $deleted]);

            return redirect()->route('angkatan.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Data angkatan berhasil dihapus.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus data angkatan.');

        return redirect()->route('angkatan.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $errorMessage,
        ]);
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
