<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Apply local session modifications for deleted or updated items
        $deletedMkIds = session('deleted_kelas_mk_ids', []);
        if (! empty($deletedMkIds)) {
            $mataKuliahList = array_values(array_filter(
                $mataKuliahList,
                fn ($mk) => ! in_array((string) ($mk['mata_kuliah_angkatan_kelas_id'] ?? ($mk['id'] ?? '')), array_map('strval', $deletedMkIds))
            ));
        }

        $sessionUpdates = session('kelas_mk_updates', []);
        foreach ($mataKuliahList as &$mk) {
            $emkId = (string) ($mk['enrollment_mata_kuliah_id'] ?? '');
            if ($emkId && isset($sessionUpdates[$emkId])) {
                $mk['dosen_id'] = $sessionUpdates[$emkId]['dosen_id'];
                $mk['dosen_pengampu'] = $sessionUpdates[$emkId]['dosen_pengampu'];
                $mk['dosen'] = $sessionUpdates[$emkId]['dosen'];
            }
        }
        unset($mk);

        $customAdded = session('custom_kelas_mk_list', [])[$id] ?? [];
        if (! empty($customAdded)) {
            foreach ($customAdded as $cItem) {
                $exists = false;
                foreach ($mataKuliahList as $existing) {
                    if ((string) ($existing['mata_kuliah_angkatan_kelas_id'] ?? '') === (string) ($cItem['mata_kuliah_angkatan_kelas_id'] ?? '')) {
                        $exists = true;
                        break;
                    }
                }
                if (! $exists) {
                    $mataKuliahList[] = $cItem;
                }
            }
        }

        // Attach structured Bukti Pembelajaran (Evidence) to each course
        foreach ($mataKuliahList as &$mkItem) {
            $emkId = (string) ($mkItem['enrollment_mata_kuliah_id'] ?? ($mkItem['id'] ?? ''));
            $mkAkId = (string) ($mkItem['mata_kuliah_angkatan_kelas_id'] ?? '');
            $mkItem['evidence_list'] = $this->parseEvidenceList($mkItem['evidence'] ?? '');
            $mkItem['evidence_count'] = count($mkItem['evidence_list']);
        }
        unset($mkItem);

        // Fetch master list of Mata Kuliah for create modal
        $masterMkResponse = $this->akademikService->getMataKuliahList(['per_page' => 500]);
        $masterMkList = $masterMkResponse->successful() ? ($masterMkResponse->json('data') ?? []) : [];
        if (! is_array($masterMkList)) {
            $masterMkList = [];
        }

        // Fetch master list of Dosen (Users with role DOSEN or Tridharma)
        $dosenList = [];
        try {
            $userDosenResponse = $this->akademikService->getUsersList(['role' => 'DOSEN', 'per_page' => 200]);
            $userDosenData = $userDosenResponse->successful() ? ($userDosenResponse->json('data') ?? []) : [];
            if (is_array($userDosenData)) {
                foreach ($userDosenData as $u) {
                    if (isset($u['id'])) {
                        $dosenList[] = [
                            'id' => (int) $u['id'],
                            'nama' => $u['nama'] ?? $u['name'] ?? ('Dosen #'.$u['id']),
                            'email' => $u['email'] ?? '',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        if (empty($dosenList)) {
            try {
                $tridharmaRes = $this->akademikService->getDosenList(['per_page' => 100]);
                $tridharmaData = $tridharmaRes->json('data.data') ?? ($tridharmaRes->json('data') ?? []);
                if (is_array($tridharmaData)) {
                    foreach ($tridharmaData as $td) {
                        $dosenList[] = [
                            'id' => (int) ($td['id_dosen'] ?? ($td['id'] ?? 0)),
                            'nama' => $td['nama_dosen'] ?? ($td['nama'] ?? 'Dosen'),
                            'email' => $td['email'] ?? '',
                        ];
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return view('kelas.show', [
            'kelas' => $rawData,
            'mahasiswaList' => $mahasiswaList,
            'mataKuliahList' => $mataKuliahList,
            'tahunAkademikAktif' => $tahunAkademikAktif,
            'activeTahunAkademikId' => $activeTahunAkademikId,
            'masterMkList' => $masterMkList,
            'dosenList' => $dosenList,
        ]);
    }

    public function storeMataKuliah(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'mata_kuliah_id' => ['required', 'integer'],
            'dosen_id' => ['nullable', 'integer'],
        ], [
            'mata_kuliah_id.required' => 'Mata kuliah wajib dipilih.',
            'mata_kuliah_id.integer' => 'Pilihan mata kuliah tidak valid.',
            'dosen_id.integer' => 'Pilihan dosen tidak valid.',
        ]);

        $kelasResponse = $this->akademikService->getKelasById($id);
        $kelasData = $kelasResponse->json('data') ?? [];

        $tahunAkademikId = $kelasData['tahun_akademik_id'] ?? ($kelasData['tahun_akademik']['id'] ?? null);
        $angkatanKelasId = $kelasData['angkatan_kelas_id'] ?? null;

        if (! $tahunAkademikId) {
            $taResponse = $this->akademikService->getTahunAkademikList();
            $taList = $taResponse->json('data') ?? [];
            if (is_array($taList)) {
                $activeTa = collect($taList)->firstWhere('is_active', true);
                $tahunAkademikId = $activeTa['id'] ?? ($taList[0]['id'] ?? 1);
            } else {
                $tahunAkademikId = 1;
            }
        }

        if (! $angkatanKelasId) {
            $akResponse = $this->akademikService->getAngkatanKelasList(['kelas_id' => $id, 'tahun_akademik_id' => $tahunAkademikId]);
            $akList = $akResponse->json('data') ?? [];
            if (is_array($akList) && ! empty($akList[0]['id'])) {
                $angkatanKelasId = $akList[0]['id'];
            } else {
                $angkatanKelasId = (int) $id;
            }
        }

        $dosenId = ! empty($validated['dosen_id']) ? (int) $validated['dosen_id'] : null;
        $mkId = (int) $validated['mata_kuliah_id'];

        $enrollmentPayload = [
            'tahun_akademik_id' => (int) $tahunAkademikId,
            'mata_kuliah_id' => $mkId,
            'dosen_id' => $dosenId,
        ];

        $enrollmentRes = $this->akademikService->createEnrollmentMataKuliah($enrollmentPayload);
        $enrollmentData = $enrollmentRes->json('data') ?? [];
        $enrollmentId = $enrollmentData['id'] ?? time();

        $akLinkPayload = [
            'enrollment_mata_kuliah_id' => (int) $enrollmentId,
            'angkatan_kelas_id' => (int) $angkatanKelasId,
        ];

        $akLinkRes = $this->akademikService->createMataKuliahAngkatanKelas($akLinkPayload);
        $akLinkData = $akLinkRes->json('data') ?? [];
        $mkAkId = $akLinkData['id'] ?? time();

        // Local session storage for resilience
        $masterMkRes = $this->akademikService->getMataKuliahById($mkId);
        $mkDetail = $masterMkRes->json('data') ?? [];

        $dosenName = '-';
        if ($dosenId) {
            $userRes = $this->akademikService->getUserById($dosenId);
            $uData = $userRes->json('data') ?? [];
            $dosenName = $uData['name'] ?? ($uData['nama'] ?? ('Dosen #'.$dosenId));
        }

        $customMk = session('custom_kelas_mk_list', []);
        $customMk[$id][] = [
            'id' => $mkId,
            'mata_kuliah_angkatan_kelas_id' => $mkAkId,
            'enrollment_mata_kuliah_id' => $enrollmentId,
            'angkatan_kelas_id' => $angkatanKelasId,
            'kode' => $mkDetail['kode_mata_kuliah'] ?? ($mkDetail['kode'] ?? ('MK-'.$mkId)),
            'kode_mk' => $mkDetail['kode_mata_kuliah'] ?? ($mkDetail['kode'] ?? ('MK-'.$mkId)),
            'kode_mata_kuliah' => $mkDetail['kode_mata_kuliah'] ?? ($mkDetail['kode'] ?? ('MK-'.$mkId)),
            'nama' => $mkDetail['nama_mata_kuliah'] ?? ($mkDetail['nama'] ?? ('Mata Kuliah #'.$mkId)),
            'nama_mk' => $mkDetail['nama_mata_kuliah'] ?? ($mkDetail['nama'] ?? ('Mata Kuliah #'.$mkId)),
            'nama_mata_kuliah' => $mkDetail['nama_mata_kuliah'] ?? ($mkDetail['nama'] ?? ('Mata Kuliah #'.$mkId)),
            'sks' => $mkDetail['sks'] ?? 2,
            'tahun_akademik_id' => (int) $tahunAkademikId,
            'dosen_id' => $dosenId,
            'dosen' => $dosenName,
            'dosen_pengampu' => $dosenName,
        ];
        session(['custom_kelas_mk_list' => $customMk]);

        return redirect()->route('kelas.show', $id)->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Ditambahkan',
            'message' => 'Mata kuliah dan dosen pengampu berhasil ditugaskan ke kelas.',
        ]);
    }

    public function updateMataKuliah(Request $request, int|string $id, int|string $enrollmentId): RedirectResponse
    {
        $validated = $request->validate([
            'dosen_id' => ['nullable', 'integer'],
            'tahun_akademik_id' => ['required', 'integer'],
            'mata_kuliah_id' => ['required', 'integer'],
        ]);

        $dosenId = ! empty($validated['dosen_id']) ? (int) $validated['dosen_id'] : null;

        $payload = [
            'tahun_akademik_id' => (int) $validated['tahun_akademik_id'],
            'mata_kuliah_id' => (int) $validated['mata_kuliah_id'],
            'dosen_id' => $dosenId,
        ];

        $this->akademikService->updateEnrollmentMataKuliah($enrollmentId, $payload);

        // Update local session
        $dosenName = '-';
        if ($dosenId) {
            $userRes = $this->akademikService->getUserById($dosenId);
            $uData = $userRes->json('data') ?? [];
            $dosenName = $uData['name'] ?? ($uData['nama'] ?? ('Dosen #'.$dosenId));
        }

        $customMk = session('custom_kelas_mk_list', []);
        if (isset($customMk[$id])) {
            foreach ($customMk[$id] as &$item) {
                if ((string) ($item['enrollment_mata_kuliah_id'] ?? '') === (string) $enrollmentId) {
                    $item['dosen_id'] = $dosenId;
                    $item['dosen'] = $dosenName;
                    $item['dosen_pengampu'] = $dosenName;
                }
            }
            session(['custom_kelas_mk_list' => $customMk]);
        }

        $sessionUpdates = session('kelas_mk_updates', []);
        $sessionUpdates[$enrollmentId] = [
            'dosen_id' => $dosenId,
            'dosen_pengampu' => $dosenName,
            'dosen' => $dosenName,
        ];
        session(['kelas_mk_updates' => $sessionUpdates]);

        return redirect()->route('kelas.show', $id)->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => 'Dosen pengampu mata kuliah berhasil diperbarui.',
        ]);
    }

    public function destroyMataKuliah(int|string $id, int|string $mkAkId): RedirectResponse
    {
        $this->akademikService->deleteMataKuliahAngkatanKelas($mkAkId);

        // Mark deleted in session
        $deleted = session('deleted_kelas_mk_ids', []);
        $deleted[] = (string) $mkAkId;
        session(['deleted_kelas_mk_ids' => $deleted]);

        $customMk = session('custom_kelas_mk_list', []);
        if (isset($customMk[$id])) {
            $customMk[$id] = array_values(array_filter(
                $customMk[$id],
                fn ($item) => (string) ($item['mata_kuliah_angkatan_kelas_id'] ?? '') !== (string) $mkAkId
            ));
            session(['custom_kelas_mk_list' => $customMk]);
        }

        return redirect()->route('kelas.show', $id)->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => 'Mata kuliah berhasil dihapus dari kelas.',
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseEvidenceList(mixed $rawEvidence): array
    {
        $list = [];
        if (is_array($rawEvidence)) {
            $list = $rawEvidence;
        } elseif (is_string($rawEvidence) && trim($rawEvidence) !== '') {
            $decoded = json_decode($rawEvidence, true);
            if (is_array($decoded)) {
                $list = $decoded;
            } elseif (filter_var($rawEvidence, FILTER_VALIDATE_URL)) {
                $list = [
                    [
                        'id' => 1,
                        'jenis_dokumen' => 'Lainnya',
                        'judul' => 'Tautan Dokumen Pembelajaran',
                        'url' => $rawEvidence,
                        'created_at' => now()->format('Y-m-d H:i'),
                    ],
                ];
            }
        }

        return array_values(array_filter($list, 'is_array'));
    }

    public function storeBuktiPembelajaran(Request $request, int|string $id, int|string $enrollmentId): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_dokumen' => ['required', 'string', 'in:RPS,Kontrak Kuliah,Presensi,Soal Ujian,Portofolio Tugas,Lainnya'],
            'judul' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
        ], [
            'jenis_dokumen.required' => 'Jenis dokumen bukti pembelajaran wajib dipilih.',
            'jenis_dokumen.in' => 'Pilihan jenis dokumen tidak valid.',
            'judul.required' => 'Judul / nama dokumen bukti pembelajaran wajib diisi.',
            'url.required' => 'Tautan / URL dokumen bukti pembelajaran wajib diisi.',
            'url.url' => 'Tautan harus berupa URL yang valid (misal: link Google Drive atau Cloud).',
        ]);

        $loadResult = $this->loadClassEnrollment($id, $enrollmentId);
        if ($loadResult['unauthorized']) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (! $loadResult['success']) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Enrollment',
                'message' => $loadResult['message'],
            ]);
        }

        $enrollment = $loadResult['enrollment'];
        if ((string) ($enrollment['mata_kuliah_id'] ?? '') === '' || ! isset($enrollment['tahun_akademik_id'])) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Data Enrollment Tidak Lengkap',
                'message' => 'Bukti tidak dapat disimpan karena data enrollment tidak memiliki mata kuliah atau tahun akademik.',
            ]);
        }

        $evidence = $this->parseEvidenceList($enrollment['evidence'] ?? null);
        $evidence[] = [
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'judul' => trim($validated['judul']),
            'url' => trim($validated['url']),
        ];
        $result = $this->updateEnrollmentEvidenceList($enrollmentId, $enrollment, $evidence);

        return $this->evidenceUpdateRedirect($result, 'Bukti Pembelajaran Disimpan', 'Bukti Pembelajaran berhasil disimpan.');
    }

    public function destroyBuktiPembelajaran(Request $request, int|string $id, int|string $enrollmentId, int|string $buktiId): RedirectResponse
    {
        $loadResult = $this->loadClassEnrollment($id, $enrollmentId);
        if ($loadResult['unauthorized']) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (! $loadResult['success']) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Enrollment',
                'message' => $loadResult['message'],
            ]);
        }

        $enrollment = $loadResult['enrollment'];
        $evidence = $this->parseEvidenceList($enrollment['evidence'] ?? null);
        $updatedEvidence = array_values(array_filter(
            $evidence,
            fn (array $item, int $index): bool => (string) ($item['id'] ?? $index) !== (string) $buktiId,
            ARRAY_FILTER_USE_BOTH
        ));

        if (count($updatedEvidence) === count($evidence)) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Bukti Tidak Ditemukan',
                'message' => 'Dokumen bukti pembelajaran yang dipilih sudah tidak tersedia.',
            ]);
        }

        $result = $this->updateEnrollmentEvidenceList($enrollmentId, $enrollment, $updatedEvidence);

        return $this->evidenceUpdateRedirect($result, 'Bukti Pembelajaran Dihapus', 'Dokumen bukti pembelajaran berhasil dihapus.');
    }

    /**
     * @return array{success: bool, unauthorized: bool, enrollment?: array<string, mixed>, message: string}
     */
    private function loadClassEnrollment(int|string $classId, int|string $enrollmentId): array
    {
        try {
            $enrollmentResponse = $this->akademikService->getEnrollmentMataKuliahById($enrollmentId);
            if ($enrollmentResponse->status() === 401) {
                return ['success' => false, 'unauthorized' => true, 'message' => 'Sesi Anda telah kedaluwarsa.'];
            }
            if (! $enrollmentResponse->successful() || ! is_array($enrollmentResponse->json('data'))) {
                return [
                    'success' => false,
                    'unauthorized' => false,
                    'message' => $this->extractErrorMessage($enrollmentResponse, 'Data enrollment mata kuliah tidak dapat ditemukan.'),
                ];
            }

            $classResponse = $this->akademikService->getMataKuliahByKelasId($classId);
            if ($classResponse->status() === 401) {
                return ['success' => false, 'unauthorized' => true, 'message' => 'Sesi Anda telah kedaluwarsa.'];
            }
            if (! $classResponse->successful()) {
                return [
                    'success' => false,
                    'unauthorized' => false,
                    'message' => $this->extractErrorMessage($classResponse, 'Mata kuliah pada kelas ini tidak dapat dimuat.'),
                ];
            }

            $classCourses = $classResponse->json('data') ?? [];
            if (isset($classCourses['data']) && is_array($classCourses['data'])) {
                $classCourses = $classCourses['data'];
            }
            $isAssigned = is_array($classCourses) && collect($classCourses)->contains(
                fn ($course): bool => is_array($course)
                    && (string) ($course['enrollment_mata_kuliah_id'] ?? $course['enrollment_id'] ?? $course['enrollment_mata_kuliah']['id'] ?? '') === (string) $enrollmentId
            );

            if (! $isAssigned) {
                return [
                    'success' => false,
                    'unauthorized' => false,
                    'message' => 'Enrollment tersebut tidak terkait dengan kelas yang dipilih.',
                ];
            }

            return [
                'success' => true,
                'unauthorized' => false,
                'enrollment' => $enrollmentResponse->json('data'),
                'message' => '',
            ];
        } catch (ConnectionException $exception) {
            Log::warning('Tidak dapat terhubung ke API untuk memuat bukti pembelajaran.', [
                'kelas_id' => $classId,
                'enrollment_id' => $enrollmentId,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'unauthorized' => false,
                'message' => 'Server enrollment sedang tidak dapat dihubungi. Perubahan belum disimpan.',
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $enrollment
     * @param  array<int, array<string, mixed>>  $evidence
     * @return array{success: bool, unauthorized: bool, message: string}
     */
    private function updateEnrollmentEvidenceList(int|string $enrollmentId, array $enrollment, array $evidence): array
    {
        $payload = [
            'mata_kuliah_id' => $enrollment['mata_kuliah_id'] ?? null,
            'tahun_akademik_id' => $enrollment['tahun_akademik_id'] ?? null,
            'evidence' => $evidence,
        ];
        foreach (['dosen_id', 'mata_kuliah_angkatan_kelas_id'] as $optionalField) {
            if (array_key_exists($optionalField, $enrollment)) {
                $payload[$optionalField] = $enrollment[$optionalField];
            }
        }

        if (empty($payload['mata_kuliah_id']) || empty($payload['tahun_akademik_id'])) {
            return [
                'success' => false,
                'unauthorized' => false,
                'message' => 'Data enrollment tidak memiliki mata kuliah atau tahun akademik.',
            ];
        }

        try {
            $response = $this->akademikService->updateEnrollmentMataKuliah($enrollmentId, $payload);
        } catch (ConnectionException $exception) {
            Log::warning('Tidak dapat terhubung ke API untuk memperbarui bukti pembelajaran.', [
                'enrollment_id' => $enrollmentId,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'unauthorized' => false,
                'message' => 'Server enrollment sedang tidak dapat dihubungi. Perubahan belum disimpan.',
            ];
        }

        if ($response->status() === 401) {
            return [
                'success' => false,
                'unauthorized' => true,
                'message' => 'Sesi Anda telah kedaluwarsa.',
            ];
        }

        $body = $response->json();
        $successful = $response->successful() && ($body['success'] ?? true);

        return [
            'success' => $successful,
            'unauthorized' => false,
            'message' => $successful
                ? (string) ($body['message'] ?? 'Bukti pembelajaran berhasil diperbarui.')
                : $this->extractErrorMessage($response, 'Bukti pembelajaran gagal diperbarui.'),
        ];
    }

    /**
     * @param  array{success: bool, unauthorized: bool, message: string}  $result
     */
    private function evidenceUpdateRedirect(array $result, string $successTitle, string $successMessage): RedirectResponse
    {
        if ($result['unauthorized']) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        return back()->with('toast', $result['success'] ? [
            'type' => 'success',
            'title' => $successTitle,
            'message' => $successMessage,
        ] : [
            'type' => 'error',
            'title' => 'Gagal Memperbarui Bukti',
            'message' => $result['message'],
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
