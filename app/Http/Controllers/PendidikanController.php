<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PendidikanController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'jenjang' => ['nullable', 'string', 'max:50'],
            'id_dosen' => ['nullable', 'integer', 'min:1'],
            'id_tendik' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $page = (int) ($filters['page'] ?? 1);
        $perPage = 10;
        $search = trim((string) ($filters['search'] ?? ''));
        $selectedJenjang = trim((string) ($filters['jenjang'] ?? ''));
        $idDosen = ! empty($filters['id_dosen']) ? (int) $filters['id_dosen'] : null;
        $idTendik = ! empty($filters['id_tendik']) ? (int) $filters['id_tendik'] : null;

        $isDosen = $this->isAuthenticatedDosen();
        $authDosen = $isDosen ? $this->getAuthenticatedDosenData() : null;

        // If logged in as Dosen, restrict context directly to their own id_dosen
        if ($isDosen && $authDosen) {
            $idDosen = $authDosen['id_dosen'];
        }

        // Fetch Dosen & Tendik lists for selection in Modal (only if not a Dosen)
        $dosenOptions = ! $isDosen ? $this->fetchDosenOptions() : [];
        $tendikOptions = ! $isDosen ? $this->fetchTendikOptions() : [];

        // If user is logged in as Dosen, they should ONLY see their own education data
        if ($isDosen && $authDosen) {
            $allItems = [];
            $dosenPendidikanId = $authDosen['id_pendidikan'] ?? null;
            $dosenPendidikanObj = $authDosen['pendidikan'] ?? null;

            if ($dosenPendidikanObj && is_array($dosenPendidikanObj) && ! empty($dosenPendidikanObj['jenjang'] ?? $dosenPendidikanObj['universitas'] ?? null)) {
                $allItems[] = $dosenPendidikanObj;
            } elseif ($dosenPendidikanId) {
                try {
                    $pendResp = $this->tridharmaService->getPendidikanById($dosenPendidikanId);
                    if ($pendResp->successful() && is_array($pendResp->json('data'))) {
                        $allItems[] = $pendResp->json('data');
                    }
                } catch (\Throwable) {
                }
            }

            // Apply search filter if specified
            if ($search !== '') {
                $allItems = array_values(array_filter($allItems, function (array $item) use ($search) {
                    $univ = (string) ($item['universitas'] ?? '');
                    $prodi = (string) ($item['program_studi'] ?? '');
                    $jenjang = (string) ($item['jenjang'] ?? '');

                    return stripos($univ, $search) !== false
                        || stripos($prodi, $search) !== false
                        || stripos($jenjang, $search) !== false;
                }));
            }

            // Apply jenjang filter if specified
            if ($selectedJenjang !== '') {
                $allItems = array_values(array_filter($allItems, fn (array $item) => $this->matchesJenjang($item['jenjang'] ?? '', $selectedJenjang)));
            }

            $total = count($allItems);
            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => 1,
                'last_page' => 1,
                'from' => $total > 0 ? 1 : 0,
                'to' => $total,
            ];

            return view('pendidikan.index', [
                'pendidikanList' => $allItems,
                'pagination' => $pagination,
                'filters' => $filters,
                'jenjangOptions' => $this->defaultJenjangOptions(),
                'dosenOptions' => [],
                'tendikOptions' => [],
                'selectedDosen' => [
                    'id' => $authDosen['id_dosen'],
                    'nama' => $authDosen['nama_dosen'],
                ],
                'selectedTendik' => null,
                'isDosen' => true,
                'authDosen' => $authDosen,
                'apiError' => null,
            ]);
        }

        // If viewing education for a specific Tendik (from Tendik Detail page or filter)
        if ($idTendik !== null) {
            $selectedTendik = null;
            $tendikApiRecord = null;
            foreach ($tendikOptions as $tendik) {
                if ($tendik['id'] === $idTendik) {
                    $selectedTendik = $tendik;
                    break;
                }
            }

            try {
                $singleTendikResp = $this->tridharmaService->getTendik($idTendik);
                if ($singleTendikResp->successful()) {
                    $tendikApiRecord = $singleTendikResp->json('data', []);
                    if ($selectedTendik === null) {
                        $selectedTendik = [
                            'id' => (int) ($tendikApiRecord['id_tendik'] ?? $idTendik),
                            'nama' => (string) ($tendikApiRecord['nama_tendik'] ?? $tendikApiRecord['nama'] ?? 'Tenaga Kependidikan #'.$idTendik),
                        ];
                    }
                }
            } catch (\Throwable) {
            }

            if ($selectedTendik === null) {
                $selectedTendik = ['id' => $idTendik, 'nama' => 'Tenaga Kependidikan #'.$idTendik];
            }

            $allItems = $this->tridharmaService->getTendikPendidikanList($idTendik, $tendikApiRecord);

            // Apply search filter if specified
            if ($search !== '') {
                $allItems = array_values(array_filter($allItems, function (array $item) use ($search) {
                    $univ = (string) ($item['universitas'] ?? '');
                    $prodi = (string) ($item['program_studi'] ?? '');
                    $jenjang = (string) ($item['jenjang'] ?? '');

                    return stripos($univ, $search) !== false
                        || stripos($prodi, $search) !== false
                        || stripos($jenjang, $search) !== false;
                }));
            }

            // Apply jenjang filter if specified
            if ($selectedJenjang !== '') {
                $allItems = array_values(array_filter($allItems, fn (array $item) => $this->matchesJenjang($item['jenjang'] ?? '', $selectedJenjang)));
            }

            $total = count($allItems);
            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => 1,
                'last_page' => 1,
                'from' => $total > 0 ? 1 : 0,
                'to' => $total,
            ];

            return view('pendidikan.index', [
                'pendidikanList' => $allItems,
                'pagination' => $pagination,
                'filters' => $filters,
                'jenjangOptions' => $this->defaultJenjangOptions(),
                'dosenOptions' => $dosenOptions,
                'tendikOptions' => $tendikOptions,
                'selectedDosen' => null,
                'selectedTendik' => $selectedTendik,
                'isDosen' => false,
                'authDosen' => null,
                'apiError' => null,
            ]);
        }

        // NON-DOSEN (e.g. Admin / Kaprodi): normal global list from API
        $apiParams = [
            'page' => $page,
            'per_page' => $perPage,
        ];

        if ($search !== '') {
            $apiParams['search'] = $search;
        }

        try {
            $response = $this->tridharmaService->getPendidikanList($apiParams);
        } catch (ConnectionException) {
            return view('pendidikan.index', [
                'pendidikanList' => [],
                'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'last_page' => 1],
                'filters' => $filters,
                'jenjangOptions' => $this->defaultJenjangOptions(),
                'dosenOptions' => [],
                'tendikOptions' => [],
                'selectedDosen' => null,
                'selectedTendik' => null,
                'isDosen' => false,
                'authDosen' => null,
                'apiError' => 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            return view('pendidikan.index', [
                'pendidikanList' => [],
                'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'last_page' => 1],
                'filters' => $filters,
                'jenjangOptions' => $this->defaultJenjangOptions(),
                'dosenOptions' => [],
                'tendikOptions' => [],
                'selectedDosen' => null,
                'selectedTendik' => null,
                'isDosen' => false,
                'authDosen' => null,
                'apiError' => $this->responseMessage($response, 'Gagal memuat data pendidikan.'),
            ]);
        }

        $rawResponse = $response->json();
        $responseData = $rawResponse['data'] ?? [];

        // Support both paginated structure ($responseData['data']) and flat array ($responseData)
        $allItems = [];
        if (isset($responseData['data']) && is_array($responseData['data'])) {
            $allItems = $responseData['data'];
            $total = (int) ($responseData['total'] ?? count($allItems));
            $perPage = (int) ($responseData['per_page'] ?? $perPage);
            $lastPage = (int) ($responseData['last_page'] ?? max(1, (int) ceil($total / $perPage)));
            $currentPage = (int) ($responseData['current_page'] ?? $page);
        } elseif (is_array($responseData)) {
            $rawPagination = $rawResponse['pagination'] ?? [];
            if (! empty($rawPagination) && is_array($rawPagination)) {
                $allItems = $responseData;
                $total = (int) ($rawPagination['total'] ?? count($allItems));
                $perPage = (int) ($rawPagination['per_page'] ?? $perPage);
                $lastPage = (int) ($rawPagination['last_page'] ?? max(1, (int) ceil($total / $perPage)));
                $currentPage = (int) ($rawPagination['current_page'] ?? $page);
            } else {
                $allItems = $responseData;
                $total = count($allItems);
                $lastPage = max(1, (int) ceil($total / $perPage));
                $currentPage = max(1, min($page, $lastPage));
            }
        } else {
            $total = 0;
            $lastPage = 1;
            $currentPage = 1;
        }

        // If jenjang filter is specified, filter items using normalized matching
        if ($selectedJenjang !== '') {
            $filtered = array_values(array_filter($allItems, fn (array $item) => $this->matchesJenjang($item['jenjang'] ?? '', $selectedJenjang)));

            $allItems = $filtered;
            $total = count($allItems);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = max(1, min($page, $lastPage));
            $offset = ($currentPage - 1) * $perPage;
            $allItems = array_slice($allItems, $offset, $perPage);
        }

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0,
            'to' => $total > 0 ? min($currentPage * $perPage, $total) : 0,
        ];

        $selectedDosen = null;
        if ($idDosen !== null) {
            foreach ($dosenOptions as $dosen) {
                if ($dosen['id'] === $idDosen) {
                    $selectedDosen = $dosen;
                    break;
                }
            }
            if ($selectedDosen === null) {
                try {
                    $singleDosenResp = $this->tridharmaService->getDosenById($idDosen);
                    if ($singleDosenResp->successful()) {
                        $dData = $singleDosenResp->json('data', []);
                        $selectedDosen = [
                            'id' => (int) ($dData['id_dosen'] ?? $idDosen),
                            'nama' => (string) ($dData['nama_dosen'] ?? 'Dosen #'.$idDosen),
                        ];
                    }
                } catch (\Throwable) {
                    $selectedDosen = ['id' => $idDosen, 'nama' => 'Dosen #'.$idDosen];
                }
            }
        }

        $selectedTendik = null;
        if ($idTendik !== null) {
            foreach ($tendikOptions as $tendik) {
                if ($tendik['id'] === $idTendik) {
                    $selectedTendik = $tendik;
                    break;
                }
            }
            if ($selectedTendik === null) {
                try {
                    $singleTendikResp = $this->tridharmaService->getTendik($idTendik);
                    if ($singleTendikResp->successful()) {
                        $tData = $singleTendikResp->json('data', []);
                        $selectedTendik = [
                            'id' => (int) ($tData['id_tendik'] ?? $idTendik),
                            'nama' => (string) ($tData['nama_tendik'] ?? 'Tenaga Kependidikan #'.$idTendik),
                        ];
                    }
                } catch (\Throwable) {
                    $selectedTendik = ['id' => $idTendik, 'nama' => 'Tenaga Kependidikan #'.$idTendik];
                }
            }
        }

        return view('pendidikan.index', [
            'pendidikanList' => $allItems,
            'pagination' => $pagination,
            'filters' => $filters,
            'jenjangOptions' => $this->defaultJenjangOptions(),
            'dosenOptions' => $dosenOptions,
            'tendikOptions' => $tendikOptions,
            'selectedDosen' => $selectedDosen,
            'selectedTendik' => $selectedTendik,
            'isDosen' => false,
            'authDosen' => null,
            'apiError' => null,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $isDosen = $this->isAuthenticatedDosen();
        $authDosen = $isDosen ? $this->getAuthenticatedDosenData() : null;

        $validated = $request->validate([
            'universitas' => ['required', 'string', 'max:255'],
            'program_studi' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'string', 'max:50'],
            'tahun_lulus' => ['required', 'integer', 'between:1900,2100'],
            'target_type' => ['nullable', 'in:none,dosen,tendik'],
            'id_dosen' => ['nullable', 'integer', 'min:1'],
            'id_tendik' => ['nullable', 'integer', 'min:1'],
        ], [
            'universitas.required' => 'Nama universitas wajib diisi.',
            'program_studi.required' => 'Program studi wajib diisi.',
            'jenjang.required' => 'Jenjang pendidikan wajib dipilih.',
            'tahun_lulus.required' => 'Tahun lulus wajib diisi.',
            'tahun_lulus.between' => 'Tahun lulus harus di antara 1900 dan 2100.',
        ]);

        $normalizedJenjang = self::normalizeJenjang($validated['jenjang']);

        $payload = [
            'universitas' => trim($validated['universitas']),
            'program_studi' => trim($validated['program_studi']),
            'jenjang' => $normalizedJenjang,
            'tahun_lulus' => (int) $validated['tahun_lulus'],
        ];

        try {
            $response = $this->tridharmaService->createPendidikan($payload);
        } catch (ConnectionException) {
            return $this->errorResponse($request, 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            $message = $this->responseMessage($response, 'Gagal menambahkan data pendidikan.');

            return $this->errorResponse($request, $message, $response->json('errors'));
        }

        $createdData = $response->json('data', []);
        $newPendidikanId = $createdData['id_pendidikan'] ?? $createdData['id'] ?? null;

        // If user is Dosen, automatically link newly created pendidikan to this Dosen
        if ($isDosen && $authDosen && $newPendidikanId) {
            try {
                $this->tridharmaService->updateDosen($authDosen['id_dosen'], ['id_pendidikan' => (int) $newPendidikanId]);
            } catch (\Throwable) {
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pendidikan Anda berhasil ditambahkan.',
                    'data' => $createdData,
                ]);
            }

            return redirect()->route('pendidikan.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data pendidikan Anda berhasil ditambahkan.',
            ]);
        }

        // NON-DOSEN linkage
        $targetType = $validated['target_type'] ?? null;
        $idDosen = ! empty($validated['id_dosen']) ? (int) $validated['id_dosen'] : null;
        $idTendik = ! empty($validated['id_tendik']) ? (int) $validated['id_tendik'] : null;

        if ($newPendidikanId) {
            if (($targetType === 'dosen' || ($targetType === null && $idDosen !== null)) && $idDosen) {
                try {
                    $this->tridharmaService->updateDosen($idDosen, ['id_pendidikan' => (int) $newPendidikanId]);
                } catch (\Throwable) {
                }
            } elseif (($targetType === 'tendik' || ($targetType === null && $idTendik !== null)) && $idTendik) {
                // First ensure existing qualifications from API are loaded and preserved in cache
                $this->tridharmaService->getTendikPendidikanList($idTendik);

                $cacheKey = "tendik_kualifikasi_{$idTendik}";
                $customKey = "tendik_custom_pendidikan_{$idTendik}";
                $deletedKey = "tendik_deleted_pendidikan_{$idTendik}";

                $cachedIds = Cache::get($cacheKey, []);
                if (! is_array($cachedIds)) {
                    $cachedIds = [];
                }
                if (! in_array((int) $newPendidikanId, $cachedIds, true)) {
                    $cachedIds[] = (int) $newPendidikanId;
                }
                Cache::put($cacheKey, array_values(array_unique($cachedIds)), now()->addYears(5));

                $customItems = Cache::get($customKey, []);
                if (! is_array($customItems)) {
                    $customItems = [];
                }
                $customItems[$newPendidikanId] = [
                    'id' => (int) $newPendidikanId,
                    'id_pendidikan' => (int) $newPendidikanId,
                    'universitas' => trim($validated['universitas']),
                    'institusi' => trim($validated['universitas']),
                    'program_studi' => trim($validated['program_studi']),
                    'jenjang' => $normalizedJenjang,
                    'tahun_lulus' => (string) $validated['tahun_lulus'],
                    'gelar' => TridharmaService::defaultGelarForJenjang($normalizedJenjang),
                ];
                Cache::put($customKey, $customItems, now()->addYears(5));

                // Remove from deleted list if present
                $deletedItems = Cache::get($deletedKey, []);
                if (! is_array($deletedItems)) {
                    $deletedItems = [];
                }
                $sig = md5(strtolower(trim($validated['universitas']).'|'.trim($validated['program_studi']).'|'.$normalizedJenjang.'|'.$validated['tahun_lulus']));
                $deletedItems = array_values(array_filter($deletedItems, fn ($d) => (string) $d !== (string) $newPendidikanId && (string) $d !== $sig));
                Cache::put($deletedKey, $deletedItems, now()->addYears(5));

                Cache::put("pendidikan_tendik_{$newPendidikanId}", $idTendik, now()->addYears(5));

                try {
                    $this->tridharmaService->updateTendik($idTendik, ['id_pendidikan' => (int) $newPendidikanId]);
                } catch (\Throwable) {
                }
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pendidikan berhasil ditambahkan.',
                'data' => $createdData,
            ]);
        }

        return redirect()->route('pendidikan.index', request()->only(['id_dosen', 'id_tendik']))->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data pendidikan berhasil ditambahkan.',
        ]);
    }

    public function show(int $id): JsonResponse|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getPendidikanById($id);
            if ($response->successful()) {
                $p = $response->json('data', []);
                if (is_array($p)) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'id' => $id,
                            'id_pendidikan' => $id,
                            'universitas' => (string) ($p['universitas'] ?? $p['institusi'] ?? ''),
                            'program_studi' => (string) ($p['program_studi'] ?? ''),
                            'jenjang' => (string) ($p['jenjang'] ?? ''),
                            'tahun_lulus' => (int) ($p['tahun_lulus'] ?? 0),
                        ],
                    ]);
                }
            }
        } catch (\Throwable) {
        }

        $isDosen = $this->isAuthenticatedDosen();
        $authDosen = $isDosen ? $this->getAuthenticatedDosenData() : null;

        if ($isDosen && $authDosen) {
            $myPendidikanId = $authDosen['id_pendidikan'] ?? null;
            if ($myPendidikanId !== null && (int) $myPendidikanId !== $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk melihat data pendidikan ini.',
                ], 403);
            }
        }

        try {
            $response = $this->tridharmaService->getPendidikanById($id);
        } catch (ConnectionException) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Koneksi API gagal.'], 503);
            }

            return redirect()->route('pendidikan.index')->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Tidak dapat terhubung ke server Tridharma.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }

            return redirect()->route('pendidikan.index')->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Data pendidikan tidak ditemukan.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json('data', []),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'universitas' => ['required', 'string', 'max:255'],
            'program_studi' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'string', 'max:50'],
            'tahun_lulus' => ['required', 'integer', 'between:1900,2100'],
            'target_type' => ['nullable', 'in:none,dosen,tendik'],
            'id_dosen' => ['nullable', 'integer', 'min:1'],
            'id_tendik' => ['nullable', 'integer', 'min:1'],
        ], [
            'universitas.required' => 'Nama universitas wajib diisi.',
            'program_studi.required' => 'Program studi wajib diisi.',
            'jenjang.required' => 'Jenjang pendidikan wajib dipilih.',
            'tahun_lulus.required' => 'Tahun lulus wajib diisi.',
            'tahun_lulus.between' => 'Tahun lulus harus di antara 1900 dan 2100.',
        ]);

        $normalizedJenjang = self::normalizeJenjang($validated['jenjang']);

        $isDosen = $this->isAuthenticatedDosen();
        $authDosen = $isDosen ? $this->getAuthenticatedDosenData() : null;

        // Restriction: Dosen can ONLY edit their own pendidikan
        if ($isDosen && $authDosen) {
            $myPendidikanId = $authDosen['id_pendidikan'] ?? null;
            if ($myPendidikanId !== null && (int) $myPendidikanId !== $id) {
                return $this->errorResponse($request, 'Anda hanya dapat mengubah data pendidikan milik Anda sendiri.', null, 403);
            }
        }

        $payload = [
            'universitas' => trim($validated['universitas']),
            'program_studi' => trim($validated['program_studi']),
            'jenjang' => $normalizedJenjang,
            'tahun_lulus' => (int) $validated['tahun_lulus'],
        ];

        try {
            $response = $this->tridharmaService->updatePendidikan($id, $payload);
        } catch (ConnectionException) {
            return $this->errorResponse($request, 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            $message = $this->responseMessage($response, 'Gagal memperbarui data pendidikan.');

            return $this->errorResponse($request, $message, $response->json('errors'));
        }

        // If user is Dosen, ensure their record stays linked
        if ($isDosen && $authDosen) {
            try {
                $this->tridharmaService->updateDosen($authDosen['id_dosen'], ['id_pendidikan' => $id]);
            } catch (\Throwable) {
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pendidikan Anda berhasil diperbarui.',
                    'data' => $response->json('data', []),
                ]);
            }

            return redirect()->route('pendidikan.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data pendidikan Anda berhasil diperbarui.',
            ]);
        }

        // NON-DOSEN linkage
        $targetType = $validated['target_type'] ?? null;
        $idDosen = ! empty($validated['id_dosen']) ? (int) $validated['id_dosen'] : null;
        $idTendik = ! empty($validated['id_tendik']) ? (int) $validated['id_tendik'] : null;

        if (($targetType === 'dosen' || ($targetType === null && $idDosen !== null)) && $idDosen) {
            try {
                $this->tridharmaService->updateDosen($idDosen, ['id_pendidikan' => $id]);
            } catch (\Throwable) {
            }
        } elseif (($targetType === 'tendik' || ($targetType === null && $idTendik !== null)) && $idTendik) {
            try {
                $this->tridharmaService->updateTendikPendidikan($idTendik, $id, [
                    'universitas' => trim($validated['universitas']),
                    'program_studi' => trim($validated['program_studi']),
                    'jenjang' => $normalizedJenjang,
                    'tahun_lulus' => (int) $validated['tahun_lulus'],
                    'gelar' => TridharmaService::defaultGelarForJenjang($normalizedJenjang),
                ]);
            } catch (\Throwable) {
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pendidikan berhasil diperbarui.',
                'data' => $response->json('data', []),
            ]);
        }

        return redirect()->route('pendidikan.index', request()->only(['id_dosen', 'id_tendik']))->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data pendidikan berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $idTendik = $request->input('id_tendik');
        if (! $idTendik) {
            $idTendik = Cache::get("pendidikan_tendik_{$id}");
        }
        if (! $idTendik) {
            $allTendiks = Cache::get('all_cached_tendik_ids', []);
            foreach ($allTendiks as $tId) {
                $cIds = Cache::get("tendik_kualifikasi_{$tId}", []);
                if (in_array((int) $id, array_map('intval', (array) $cIds), true)) {
                    $idTendik = $tId;
                    break;
                }
            }
        }

        if ($idTendik) {
            $this->tridharmaService->deleteTendikPendidikan((int) $idTendik, $id);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pendidikan berhasil dihapus.',
                ]);
            }

            return redirect()->route('pendidikan.index', ['id_tendik' => $idTendik])->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => 'Data pendidikan berhasil dihapus.',
            ]);
        }

        $isDosen = $this->isAuthenticatedDosen();
        $authDosen = $isDosen ? $this->getAuthenticatedDosenData() : null;

        // Restriction: Dosen can ONLY delete their own pendidikan
        if ($isDosen && $authDosen) {
            $myPendidikanId = $authDosen['id_pendidikan'] ?? null;
            if ($myPendidikanId !== null && (int) $myPendidikanId !== $id) {
                return $this->errorResponse($request, 'Anda hanya dapat menghapus data pendidikan milik Anda sendiri.', null, 403);
            }
        }

        try {
            $response = $this->tridharmaService->deletePendidikan($id);
        } catch (ConnectionException) {
            return $this->errorResponse($request, 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            $message = $this->responseMessage($response, 'Gagal menghapus data pendidikan.');

            return $this->errorResponse($request, $message);
        }

        // If user is Dosen, unlink their profile
        if ($isDosen && $authDosen) {
            try {
                $this->tridharmaService->updateDosen($authDosen['id_dosen'], ['id_pendidikan' => null]);
            } catch (\Throwable) {
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pendidikan berhasil dihapus.',
            ]);
        }

        return redirect()->route('pendidikan.index', request()->only(['id_dosen', 'id_tendik']))->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => 'Data pendidikan berhasil dihapus.',
        ]);
    }

    public static function defaultGelarForJenjang(string $jenjang): string
    {
        $j = strtoupper(trim($jenjang));
        if ($j === 'S3') {
            return 'Dr';
        }
        if ($j === 'S2') {
            return 'M.Kom';
        }
        if (str_contains($j, 'S1') || str_contains($j, 'D4')) {
            return 'S.Kom';
        }
        if (str_contains($j, 'D3')) {
            return 'A.Md';
        }

        return 'S.Kom';
    }

    /**
     * Check if currently authenticated user has the 'dosen' role
     */
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

    /**
     * Get authenticated Dosen details from API or session
     *
     * @return array{id_dosen: int, nama_dosen: string, id_pendidikan: ?int, pendidikan: ?array<string, mixed>}|null
     */
    private function getAuthenticatedDosenData(): ?array
    {
        if (! $this->isAuthenticatedDosen()) {
            return null;
        }

        $user = session('user', []);
        if (! is_array($user)) {
            return null;
        }

        $sessionDosenId = $user['id_dosen'] ?? null;
        $userEmail = strtolower(trim((string) ($user['email'] ?? '')));
        $userName = trim((string) ($user['nama'] ?? ($user['name'] ?? '')));
        $userId = $user['id'] ?? null;

        try {
            if ($sessionDosenId) {
                $resp = $this->tridharmaService->getDosenById($sessionDosenId);
                if ($resp->successful() && is_array($resp->json('data'))) {
                    $d = $resp->json('data');

                    return [
                        'id_dosen' => (int) ($d['id_dosen'] ?? $sessionDosenId),
                        'nama_dosen' => (string) ($d['nama_dosen'] ?? $userName),
                        'id_pendidikan' => isset($d['id_pendidikan']) && is_numeric($d['id_pendidikan']) ? (int) $d['id_pendidikan'] : null,
                        'pendidikan' => is_array($d['pendidikan'] ?? null) ? $d['pendidikan'] : null,
                    ];
                }
            }

            // Match from Dosen list by email or ID
            $listResp = $this->tridharmaService->getDosenList(['per_page' => 100]);
            if ($listResp->successful()) {
                $payload = $listResp->json();
                $data = $payload['data'] ?? [];
                $records = is_array($data) && is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) ? $data : []);

                foreach ($records as $d) {
                    if (! is_array($d)) {
                        continue;
                    }
                    $dEmail = strtolower(trim((string) ($d['email'] ?? '')));
                    $dId = $d['id_dosen'] ?? null;

                    if (($userEmail !== '' && $dEmail === $userEmail) || ($userId !== null && (string) $dId === (string) $userId)) {
                        return [
                            'id_dosen' => (int) $dId,
                            'nama_dosen' => (string) ($d['nama_dosen'] ?? $userName),
                            'id_pendidikan' => isset($d['id_pendidikan']) && is_numeric($d['id_pendidikan']) ? (int) $d['id_pendidikan'] : null,
                            'pendidikan' => is_array($d['pendidikan'] ?? null) ? $d['pendidikan'] : null,
                        ];
                    }
                }
            }
        } catch (\Throwable) {
        }

        if (is_numeric($sessionDosenId) || is_numeric($userId)) {
            $fallbackId = (int) ($sessionDosenId ?? $userId);

            return [
                'id_dosen' => $fallbackId,
                'nama_dosen' => $userName ?: 'Dosen #'.$fallbackId,
                'id_pendidikan' => null,
                'pendidikan' => null,
            ];
        }

        return null;
    }

    /**
     * Normalize Jenjang string without duplicates
     */
    public static function normalizeJenjang(?string $jenjang): string
    {
        $raw = trim((string) $jenjang);
        if ($raw === '') {
            return '-';
        }

        $upper = strtoupper($raw);

        if (str_contains($upper, 'S3') || str_contains($upper, 'DOKTOR')) {
            return 'S3';
        }

        if (str_contains($upper, 'S2') || str_contains($upper, 'MAGISTER')) {
            return 'S2';
        }

        if (str_contains($upper, 'S1') || str_contains($upper, 'D4') || str_contains($upper, 'SARJANA')) {
            return 'D4/S1';
        }

        if (str_contains($upper, 'D3') || str_contains($upper, 'DIPLOMA 3') || str_contains($upper, 'D-3')) {
            return 'D3';
        }

        return $raw;
    }

    /**
     * Check if an item's jenjang matches the selected filter
     */
    private function matchesJenjang(string $itemJenjang, string $filterJenjang): bool
    {
        $normalizedItem = self::normalizeJenjang($itemJenjang);
        $normalizedFilter = self::normalizeJenjang($filterJenjang);

        return strcasecmp($normalizedItem, $normalizedFilter) === 0;
    }

    /**
     * Clean non-duplicate Jenjang options list
     *
     * @return array<int, string>
     */
    private function defaultJenjangOptions(): array
    {
        return [
            'D3',
            'D4/S1',
            'S2',
            'S3',
        ];
    }

    /**
     * Fetch Dosen options for select dropdown
     *
     * @return array<int, array{id: int, nama: string}>
     */
    private function fetchDosenOptions(): array
    {
        try {
            $response = $this->tridharmaService->getDosenList(['per_page' => 100]);
            if ($response->successful()) {
                $payload = $response->json();
                $data = $payload['data'] ?? [];
                $records = is_array($data) && is_array($data['data'] ?? null)
                    ? $data['data']
                    : (is_array($data) ? $data : []);

                $options = [];
                foreach ($records as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $id = $item['id_dosen'] ?? $item['id'] ?? null;
                    $nama = $item['nama_dosen'] ?? $item['nama'] ?? null;
                    if (is_numeric($id) && is_string($nama) && trim($nama) !== '') {
                        $options[] = [
                            'id' => (int) $id,
                            'nama' => trim($nama),
                        ];
                    }
                }

                return $options;
            }
        } catch (\Throwable) {
        }

        return [];
    }

    /**
     * Fetch Tendik options for select dropdown
     *
     * @return array<int, array{id: int, nama: string}>
     */
    private function fetchTendikOptions(): array
    {
        try {
            $response = $this->tridharmaService->getTendikList(['per_page' => 100]);
            if ($response->successful()) {
                $payload = $response->json();
                $data = $payload['data'] ?? [];
                $records = is_array($data) && is_array($data['data'] ?? null)
                    ? $data['data']
                    : (is_array($data) ? $data : []);

                $options = [];
                foreach ($records as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $id = $item['id_tendik'] ?? $item['id'] ?? null;
                    $nama = $item['nama_tendik'] ?? $item['nama'] ?? null;
                    if (is_numeric($id) && is_string($nama) && trim($nama) !== '') {
                        $options[] = [
                            'id' => (int) $id,
                            'nama' => trim($nama),
                        ];
                    }
                }

                return $options;
            }
        } catch (\Throwable) {
        }

        return [];
    }

    private function responseMessage(Response $response, string $fallback): string
    {
        $message = $response->json('message');

        return is_string($message) && $message !== '' ? $message : $fallback;
    }

    private function errorResponse(Request $request, string $message, mixed $errors = null, int $statusCode = 422): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], $statusCode);
        }

        return back()->withInput()->withErrors($errors ?: [])->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $message,
        ]);
    }

    private function redirectToLogin(): RedirectResponse
    {
        return redirect()->route('login')->with('toast', [
            'type' => 'warning',
            'title' => 'Sesi Berakhir',
            'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
        ]);
    }
}
