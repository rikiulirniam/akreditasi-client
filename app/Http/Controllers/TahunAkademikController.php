<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahunAkademikController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));

        $response = $this->akademikService->getTahunAkademikList([
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
        $tahunAkademikList = [];
        if (is_array($rawData)) {
            // Check if data is nested under data key
            if (isset($rawData['data']) && is_array($rawData['data'])) {
                $tahunAkademikList = $rawData['data'];
                if (! $apiPagination) {
                    $apiPagination = [
                        'total' => $rawData['total'] ?? count($tahunAkademikList),
                        'per_page' => $rawData['per_page'] ?? $perPage,
                        'current_page' => $rawData['current_page'] ?? $page,
                        'last_page' => $rawData['last_page'] ?? 1,
                    ];
                }
            } else {
                $tahunAkademikList = $rawData;
            }
        }

        // Merge custom session items if any (for smooth local resilience)
        $customList = session('custom_tahun_akademik_list', []);
        if (is_array($customList) && ! empty($customList)) {
            $deletedIds = session('deleted_tahun_akademik_ids', []);
            $tahunAkademikList = array_values(array_filter($tahunAkademikList, fn ($item) => ! in_array($item['id'] ?? null, $deletedIds)));

            foreach ($customList as $customItem) {
                $foundIdx = null;
                foreach ($tahunAkademikList as $idx => $existingItem) {
                    if ((string) ($existingItem['id'] ?? '') === (string) ($customItem['id'] ?? '')) {
                        $foundIdx = $idx;
                        break;
                    }
                }
                if ($foundIdx !== null) {
                    $tahunAkademikList[$foundIdx] = array_merge($tahunAkademikList[$foundIdx], $customItem);
                } else {
                    array_unshift($tahunAkademikList, $customItem);
                }
            }
        }

        // Setup pagination data
        if ($apiPagination && is_array($apiPagination)) {
            $pagination = [
                'total' => (int) ($apiPagination['total'] ?? count($tahunAkademikList)),
                'per_page' => (int) ($apiPagination['per_page'] ?? $perPage),
                'current_page' => (int) ($apiPagination['current_page'] ?? $page),
                'last_page' => (int) ($apiPagination['last_page'] ?? 1),
                'from' => $apiPagination['from'] ?? (($page - 1) * $perPage + 1),
                'to' => $apiPagination['to'] ?? min($page * $perPage, (int) ($apiPagination['total'] ?? count($tahunAkademikList))),
            ];
        } else {
            $total = count($tahunAkademikList);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = max(1, min($page, $lastPage));
            $offset = ($currentPage - 1) * $perPage;
            $pagedList = array_slice($tahunAkademikList, $offset, $perPage);

            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ];
            $tahunAkademikList = $pagedList;
        }

        return view('tahun-akademik.index', [
            'tahunAkademikList' => $tahunAkademikList,
            'pagination' => $pagination,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('tahun-akademik.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'tahun.required' => 'Tahun akademik wajib diisi.',
            'tahun.max' => 'Tahun akademik maksimal 20 karakter.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester harus berupa Ganjil atau Genap.',
        ]);

        $tahun = trim($validated['tahun']);
        $semester = $validated['semester'];
        $isActive = $request->boolean('is_active');

        $payload = [
            'tahun' => $tahun,
            'semester' => $semester,
            'is_active' => $isActive,
        ];

        $response = $this->akademikService->createTahunAkademik($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $createdId = $body['data']['id'] ?? time();
            $custom = session('custom_tahun_akademik_list', []);
            $custom[] = array_merge($payload, ['id' => $createdId]);
            session(['custom_tahun_akademik_list' => $custom]);

            return redirect()->route('tahun-akademik.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => $body['message'] ?? 'Tahun akademik berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan tahun akademik.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $errorMessage,
        ]);
    }

    public function show(int|string $id): RedirectResponse
    {
        return redirect()->route('tahun-akademik.index');
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('tahun-akademik.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'tahun.required' => 'Tahun akademik wajib diisi.',
            'tahun.max' => 'Tahun akademik maksimal 20 karakter.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester harus berupa Ganjil atau Genap.',
        ]);

        $tahun = trim($validated['tahun']);
        $semester = $validated['semester'];
        $isActive = $request->boolean('is_active');

        $payload = [
            'tahun' => $tahun,
            'semester' => $semester,
            'is_active' => $isActive,
        ];

        $response = $this->akademikService->updateTahunAkademik($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_tahun_akademik_list', []);
            $found = false;
            foreach ($custom as &$c) {
                if ((string) $c['id'] === (string) $id) {
                    $c['tahun'] = $tahun;
                    $c['semester'] = $semester;
                    $c['is_active'] = $isActive;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $custom[] = array_merge($payload, ['id' => $id]);
            }
            session(['custom_tahun_akademik_list' => $custom]);

            return redirect()->route('tahun-akademik.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Tahun akademik berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui tahun akademik.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteTahunAkademik($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_tahun_akademik_list', []);
            $custom = array_values(array_filter($custom, fn ($item) => (string) ($item['id'] ?? '') !== (string) $id));
            session(['custom_tahun_akademik_list' => $custom]);

            $deleted = session('deleted_tahun_akademik_ids', []);
            $deleted[] = $id;
            session(['deleted_tahun_akademik_ids' => $deleted]);

            return redirect()->route('tahun-akademik.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Tahun akademik berhasil dihapus.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus tahun akademik.');

        return redirect()->route('tahun-akademik.index')->with('toast', [
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

    /**
     * Flow Halaman 2: Daftar Kelas berdasarkan Tahun Akademik
     */
    public function daftarKelas(Request $request, int|string $id): View|RedirectResponse
    {
        // 1. Ambil detail Tahun Akademik
        $tahunResponse = $this->akademikService->getTahunAkademikById($id);

        if ($tahunResponse->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $tahunAkademik = $tahunResponse->json('data') ?? ['id' => $id, 'tahun' => '2025/2026'];

        // 2. Ambil daftar kelas dari API
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));

        $kelasResponse = $this->akademikService->getKelasList([
            'tahun_akademik_id' => $id,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $kelasList = $kelasResponse->json('data') ?? [];
        $apiPagination = $kelasResponse->json('pagination');

        $pagination = [
            'total' => (int) ($apiPagination['total'] ?? count($kelasList)),
            'per_page' => (int) ($apiPagination['per_page'] ?? $perPage),
            'current_page' => (int) ($apiPagination['current_page'] ?? $page),
            'last_page' => (int) ($apiPagination['last_page'] ?? 1),
        ];

        return view('tahun-akademik.kelas', [
            'tahunAkademik' => $tahunAkademik,
            'kelasList' => $kelasList,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Flow Halaman 3: Detail Kelas (Daftar Mahasiswa & Daftar Mata Kuliah)
     */
    public function detailKelas(int|string $id, int|string $kelasId): View|RedirectResponse
    {
        // 1. Ambil detail Tahun Akademik & Kelas
        $tahunResponse = $this->akademikService->getTahunAkademikById($id);

        if ($tahunResponse->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $tahunAkademik = $tahunResponse->json('data') ?? ['id' => $id, 'tahun' => '2025/2026'];

        $kelasResponse = $this->akademikService->getKelasById($kelasId);
        $kelas = $kelasResponse->json('data') ?? ['id' => $kelasId, 'nama' => 'TI 2-A'];

        // 2. Ambil Daftar Mahasiswa
        $mahasiswaResponse = $this->akademikService->getMahasiswaList([
            'kelas_id' => $kelasId,
        ]);
        $mahasiswaList = $mahasiswaResponse->json('data') ?? [];

        // 3. Ambil Daftar Mata Kuliah
        $matakuliahResponse = $this->akademikService->get("kelas/{$kelasId}/mata-kuliah");
        $matakuliahList = $matakuliahResponse->json('data') ?? [];

        return view('tahun-akademik.detail', [
            'tahunAkademik' => $tahunAkademik,
            'kelas' => $kelas,
            'mahasiswaList' => $mahasiswaList,
            'matakuliahList' => $matakuliahList,
        ]);
    }
}
