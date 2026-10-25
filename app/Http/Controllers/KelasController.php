<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));

        $response = $this->akademikService->getKelasList([
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
        $kelasList = [];
        if (is_array($rawData)) {
            // Check if data is nested under data key
            if (isset($rawData['data']) && is_array($rawData['data'])) {
                $kelasList = $rawData['data'];
                if (! $apiPagination) {
                    $apiPagination = [
                        'total' => $rawData['total'] ?? count($kelasList),
                        'per_page' => $rawData['per_page'] ?? $perPage,
                        'current_page' => $rawData['current_page'] ?? $page,
                        'last_page' => $rawData['last_page'] ?? 1,
                    ];
                }
            } else {
                $kelasList = $rawData;
            }
        }

        // Merge custom session items if any (for smooth local resilience)
        $customKelas = session('custom_kelas_list', []);
        if (is_array($customKelas) && ! empty($customKelas)) {
            $deletedIds = session('deleted_kelas_ids', []);
            $kelasList = array_values(array_filter($kelasList, fn ($item) => ! in_array($item['id'] ?? null, $deletedIds)));

            foreach ($customKelas as $customItem) {
                $foundIdx = null;
                foreach ($kelasList as $idx => $existingItem) {
                    if ((string) ($existingItem['id'] ?? '') === (string) ($customItem['id'] ?? '')) {
                        $foundIdx = $idx;
                        break;
                    }
                }
                if ($foundIdx !== null) {
                    $kelasList[$foundIdx] = array_merge($kelasList[$foundIdx], $customItem);
                } else {
                    array_unshift($kelasList, $customItem);
                }
            }
        }

        // Setup pagination data
        if ($apiPagination && is_array($apiPagination)) {
            $pagination = [
                'total' => (int) ($apiPagination['total'] ?? count($kelasList)),
                'per_page' => (int) ($apiPagination['per_page'] ?? $perPage),
                'current_page' => (int) ($apiPagination['current_page'] ?? $page),
                'last_page' => (int) ($apiPagination['last_page'] ?? 1),
                'from' => $apiPagination['from'] ?? (($page - 1) * $perPage + 1),
                'to' => $apiPagination['to'] ?? min($page * $perPage, (int) ($apiPagination['total'] ?? count($kelasList))),
            ];
        } else {
            $total = count($kelasList);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = max(1, min($page, $lastPage));
            $offset = ($currentPage - 1) * $perPage;
            $pagedList = array_slice($kelasList, $offset, $perPage);

            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ];
            $kelasList = $pagedList;
        }

        return view('kelas.index', [
            'kelasList' => $kelasList,
            'pagination' => $pagination,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('kelas.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas' => ['required', 'string', 'max:50'],
        ], [
            'kelas.required' => 'Nama kelas wajib diisi.',
            'kelas.string' => 'Nama kelas harus berupa teks.',
            'kelas.max' => 'Nama kelas maksimal 50 karakter.',
        ]);

        $namaKelas = trim($validated['kelas']);
        $payload = [
            'kelas' => $namaKelas,
            'nama_kelas' => $namaKelas,
        ];

        $response = $this->akademikService->createKelas($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $createdId = $body['data']['id'] ?? time();
            $custom = session('custom_kelas_list', []);
            $custom[] = [
                'id' => $createdId,
                'kelas' => $namaKelas,
                'nama_kelas' => $namaKelas,
            ];
            session(['custom_kelas_list' => $custom]);

            return redirect()->route('kelas.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => $body['message'] ?? 'Data kelas berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan data kelas.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $errorMessage,
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $response = $this->akademikService->getKelasById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawData = $response->json('data') ?? [];
        if (! $response->successful() || empty($rawData)) {
            // Check custom session items as fallback
            $custom = session('custom_kelas_list', []);
            foreach ($custom as $item) {
                if ((string) ($item['id'] ?? '') === (string) $id) {
                    $rawData = $item;
                    break;
                }
            }
        }

        if (empty($rawData)) {
            return redirect()->route('kelas.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data kelas tidak ditemukan atau gagal diambil dari server.',
            ]);
        }

        $mhsResponse = $this->akademikService->getMahasiswaByKelasId($id);
        $mahasiswaList = $mhsResponse->successful() ? ($mhsResponse->json('data') ?? []) : [];

        $mkResponse = $this->akademikService->getMataKuliahByKelasId($id);
        $allMataKuliah = $mkResponse->successful() ? ($mkResponse->json('data') ?? []) : [];

        // Tentukan ID tahun akademik aktif (dari detail kelas atau tahun_akademik yang is_active)
        $activeTahunAkademikId = $rawData['tahun_akademik_id'] ?? ($rawData['tahun_akademik']['id'] ?? null);

        // Filter hanya mata kuliah pada tahun akademik yang sedang aktif
        $activeMataKuliah = array_values(array_filter($allMataKuliah, function ($mk) use ($activeTahunAkademikId) {
            $isTaActive = $mk['tahun_akademik']['is_active'] ?? ($mk['is_active'] ?? false);
            if ($isTaActive === true || $isTaActive === 1 || $isTaActive === '1') {
                return true;
            }

            if ($activeTahunAkademikId && isset($mk['tahun_akademik_id'])) {
                return (string) $mk['tahun_akademik_id'] === (string) $activeTahunAkademikId;
            }

            return false;
        }));

        // Gunakan mata kuliah aktif jika ada, atau fallback ke seluruh mata kuliah jika belum ada flag aktif
        $mataKuliahList = ! empty($activeMataKuliah) ? $activeMataKuliah : $allMataKuliah;

        // Tentukan nama tahun akademik aktif
        $tahunAkademikAktif = $rawData['tahun_akademik']['nama_tahun_akademik']
            ?? ($rawData['tahun_akademik']['tahun'] ?? null);

        if (! $tahunAkademikAktif && ! empty($activeMataKuliah[0]['tahun_akademik']['nama_tahun_akademik'])) {
            $tahunAkademikAktif = $activeMataKuliah[0]['tahun_akademik']['nama_tahun_akademik'];
        }

        return view('kelas.show', [
            'kelas' => $rawData,
            'mahasiswaList' => $mahasiswaList,
            'mataKuliahList' => $mataKuliahList,
            'tahunAkademikAktif' => $tahunAkademikAktif,
        ]);
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('kelas.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'kelas' => ['required', 'string', 'max:50'],
        ], [
            'kelas.required' => 'Nama kelas wajib diisi.',
            'kelas.string' => 'Nama kelas harus berupa teks.',
            'kelas.max' => 'Nama kelas maksimal 50 karakter.',
        ]);

        $namaKelas = trim($validated['kelas']);
        $payload = [
            'kelas' => $namaKelas,
            'nama_kelas' => $namaKelas,
        ];

        $response = $this->akademikService->updateKelas($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_kelas_list', []);
            $found = false;
            foreach ($custom as &$c) {
                if ((string) $c['id'] === (string) $id) {
                    $c['kelas'] = $namaKelas;
                    $c['nama_kelas'] = $namaKelas;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $custom[] = [
                    'id' => $id,
                    'kelas' => $namaKelas,
                    'nama_kelas' => $namaKelas,
                ];
            }
            session(['custom_kelas_list' => $custom]);

            return redirect()->route('kelas.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Data kelas berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui data kelas.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteKelas($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_kelas_list', []);
            $custom = array_values(array_filter($custom, fn ($item) => (string) ($item['id'] ?? '') !== (string) $id));
            session(['custom_kelas_list' => $custom]);

            $deleted = session('deleted_kelas_ids', []);
            $deleted[] = $id;
            session(['deleted_kelas_ids' => $deleted]);

            return redirect()->route('kelas.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Data kelas berhasil dihapus.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus data kelas.');

        return redirect()->route('kelas.index')->with('toast', [
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
