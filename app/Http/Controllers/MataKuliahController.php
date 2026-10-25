<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10; // Requirement: menampilkan 10 data pada setiap halamannya
        $search = trim((string) $request->query('search', ''));
        $kurikulumFilter = $request->query('kurikulum_id');

        $response = $this->akademikService->getMataKuliahList([
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

        // Extract list of items
        $mataKuliahList = [];
        if (is_array($rawData)) {
            if (isset($rawData['data']) && is_array($rawData['data'])) {
                $mataKuliahList = $rawData['data'];
            } else {
                $mataKuliahList = $rawData;
            }
        }

        // Merge custom session items if any (for smooth local resilience)
        $customMataKuliah = session('custom_mata_kuliah_list', []);
        if (is_array($customMataKuliah) && ! empty($customMataKuliah)) {
            $deletedIds = session('deleted_mata_kuliah_ids', []);
            $mataKuliahList = array_values(array_filter($mataKuliahList, fn ($item) => ! in_array($item['id'] ?? null, $deletedIds)));

            foreach ($customMataKuliah as $customItem) {
                $foundIdx = null;
                foreach ($mataKuliahList as $idx => $existingItem) {
                    if ((string) ($existingItem['id'] ?? '') === (string) ($customItem['id'] ?? '')) {
                        $foundIdx = $idx;
                        break;
                    }
                }
                if ($foundIdx !== null) {
                    $mataKuliahList[$foundIdx] = array_merge($mataKuliahList[$foundIdx], $customItem);
                } else {
                    array_unshift($mataKuliahList, $customItem);
                }
            }
        } else {
            $deletedIds = session('deleted_mata_kuliah_ids', []);
            if (! empty($deletedIds)) {
                $mataKuliahList = array_values(array_filter($mataKuliahList, fn ($item) => ! in_array($item['id'] ?? null, $deletedIds)));
            }
        }

        // Fetch Kurikulum list to map names & populate select dropdowns
        $kurikulumList = [];
        try {
            $kResponse = $this->akademikService->getKurikulumList();
            if ($kResponse->successful()) {
                $kRaw = $kResponse->json('data') ?? [];
                $kurikulumList = isset($kRaw['data']) && is_array($kRaw['data']) ? $kRaw['data'] : (is_array($kRaw) ? $kRaw : []);
            }
        } catch (\Throwable $e) {
            // Silently fall through
        }

        // Default / Session kurikulum fallback if empty
        if (empty($kurikulumList)) {
            $kurikulumList = session('custom_kurikulum_list', [
                ['id' => 1, 'nama_kurikulum' => 'Kurikulum TRK 2024'],
                ['id' => 2, 'nama_kurikulum' => 'Kurikulum D3 Teknik Informatika 2023'],
                ['id' => 3, 'nama_kurikulum' => 'Kurikulum Merdeka 2024'],
            ]);
        }

        $kurikulumMap = collect($kurikulumList)->keyBy('id');

        // Populate kurikulum relation on each mata kuliah item if missing
        foreach ($mataKuliahList as &$mk) {
            $kId = $mk['id_kurikulum'] ?? $mk['kurikulum_id'] ?? null;
            if (empty($mk['kurikulum']) && $kId && isset($kurikulumMap[$kId])) {
                $mk['kurikulum'] = $kurikulumMap[$kId];
            }
        }
        unset($mk);

        $rawTotalCount = count($mataKuliahList);

        // Filter by Search keyword (Nama or Kode)
        if ($search !== '') {
            $keyword = mb_strtolower($search);
            $mataKuliahList = array_values(array_filter($mataKuliahList, function ($item) use ($keyword) {
                $nama = mb_strtolower($item['nama_mata_kuliah'] ?? $item['nama'] ?? '');
                $kode = mb_strtolower($item['kode_mata_kuliah'] ?? $item['kode'] ?? '');

                return str_contains($nama, $keyword) || str_contains($kode, $keyword);
            }));
        }

        // Filter by Kurikulum ID
        if (! empty($kurikulumFilter)) {
            $mataKuliahList = array_values(array_filter($mataKuliahList, function ($item) use ($kurikulumFilter) {
                $kId = (string) ($item['id_kurikulum'] ?? $item['kurikulum_id'] ?? '');

                return $kId === (string) $kurikulumFilter;
            }));
        }

        // Setup Template Pagination (10 data per halaman)
        $total = count($mataKuliahList);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($page, $lastPage));
        $offset = ($currentPage - 1) * $perPage;
        $pagedList = array_slice($mataKuliahList, $offset, $perPage);

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        return view('mata-kuliah.index', [
            'mataKuliahList' => $pagedList,
            'pagination' => $pagination,
            'kurikulumList' => $kurikulumList,
            'kurikulumMap' => $kurikulumMap,
            'rawTotalCount' => $rawTotalCount,
            'search' => $search,
            'selectedKurikulum' => $kurikulumFilter,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('mata-kuliah.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_mata_kuliah' => ['required', 'string', 'max:255'],
            'kode_mata_kuliah' => ['required', 'string', 'max:50'],
            'id_kurikulum' => ['required'],
            'sks' => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'nama_mata_kuliah.required' => 'Nama mata kuliah wajib diisi.',
            'nama_mata_kuliah.string' => 'Nama mata kuliah harus berupa teks.',
            'kode_mata_kuliah.required' => 'Kode mata kuliah wajib diisi.',
            'id_kurikulum.required' => 'Kurikulum wajib dipilih.',
            'sks.required' => 'SKS wajib diisi.',
            'sks.integer' => 'SKS harus berupa angka.',
            'sks.min' => 'SKS minimal 1.',
            'sks.max' => 'SKS maksimal 20.',
        ]);

        $payload = [
            'id_kurikulum' => (int) $validated['id_kurikulum'],
            'kode_mata_kuliah' => trim($validated['kode_mata_kuliah']),
            'nama_mata_kuliah' => trim($validated['nama_mata_kuliah']),
            'sks' => (int) $validated['sks'],
        ];

        $response = $this->akademikService->createMataKuliah($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $createdId = $body['data']['id'] ?? (int) (microtime(true) * 1000);
            $custom = session('custom_mata_kuliah_list', []);
            $custom[] = array_merge($payload, [
                'id' => $createdId,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
            session(['custom_mata_kuliah_list' => $custom]);

            return back()->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => $body['message'] ?? 'Data mata kuliah berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan data mata kuliah.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $errorMessage,
        ]);
    }

    public function show(int|string $id): View|JsonResponse|RedirectResponse
    {
        if (request()->wantsJson() && ! request()->header('X-Inertia')) {
            $response = $this->akademikService->getMataKuliahById($id);
            $data = $response->json('data');

            if (! $data) {
                $custom = session('custom_mata_kuliah_list', []);
                foreach ($custom as $item) {
                    if ((string) ($item['id'] ?? '') === (string) $id) {
                        $data = $item;
                        break;
                    }
                }
            }

            if ($data) {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data mata kuliah tidak ditemukan.',
            ], 404);
        }

        // 1. Fetch Mata Kuliah Detail
        $response = $this->akademikService->getMataKuliahById($id);
        $mataKuliah = $response->json('data');

        if (! $mataKuliah) {
            $custom = session('custom_mata_kuliah_list', []);
            foreach ($custom as $item) {
                if ((string) ($item['id'] ?? '') === (string) $id) {
                    $mataKuliah = $item;
                    break;
                }
            }
        }

        if (! $mataKuliah) {
            $allMkRes = $this->akademikService->getMataKuliahList();
            $allData = $allMkRes->json('data') ?? [];
            $list = isset($allData['data']) && is_array($allData['data']) ? $allData['data'] : (is_array($allData) ? $allData : []);
            foreach ($list as $item) {
                if ((string) ($item['id'] ?? '') === (string) $id) {
                    $mataKuliah = $item;
                    break;
                }
            }
        }

        if (! $mataKuliah) {
            return redirect()->route('mata-kuliah.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data mata kuliah yang diminta tidak ditemukan.',
            ]);
        }

        // 2. Fetch Kurikulum & Prodi info
        $kurikulumList = [];
        try {
            $kResponse = $this->akademikService->getKurikulumList();
            if ($kResponse->successful()) {
                $kRaw = $kResponse->json('data') ?? [];
                $kurikulumList = isset($kRaw['data']) && is_array($kRaw['data']) ? $kRaw['data'] : (is_array($kRaw) ? $kRaw : []);
            }
        } catch (\Throwable $e) {
        }

        if (empty($kurikulumList)) {
            $kurikulumList = session('custom_kurikulum_list', [
                ['id' => 1, 'nama_kurikulum' => 'Kurikulum TRK 2024', 'id_prodi' => 1],
                ['id' => 2, 'nama_kurikulum' => 'Kurikulum D3 Teknik Informatika 2023', 'id_prodi' => 2],
                ['id' => 3, 'nama_kurikulum' => 'Kurikulum Merdeka 2024', 'id_prodi' => 1],
            ]);
        }

        $kId = $mataKuliah['id_kurikulum'] ?? $mataKuliah['kurikulum_id'] ?? null;
        $kurikulumMap = collect($kurikulumList)->keyBy('id');
        $currentKurikulum = $kId && isset($kurikulumMap[$kId]) ? $kurikulumMap[$kId] : null;
        $kurikulumNama = $currentKurikulum['nama_kurikulum'] ?? $currentKurikulum['nama'] ?? ($kId ? 'Kurikulum #'.$kId : 'Kurikulum TRK 2024');

        $responses = [
            'prodi' => $this->akademikService->getProdiList(),
            'kelas' => $this->akademikService->getKelasList(['per_page' => 500]),
            'angkatan_kelas' => $this->akademikService->getAngkatanKelasList(['per_page' => 500]),
            'angkatan' => $this->akademikService->getAngkatanList(['per_page' => 500]),
            'tahun_akademik' => $this->akademikService->getTahunAkademikList(['per_page' => 500]),
            'enrollment_kelas' => $this->akademikService->getEnrollmentKelasList(['per_page' => 500]),
            'enrollment_mata_kuliah' => $this->akademikService->getEnrollmentMataKuliahList(['per_page' => 500]),
            'mata_kuliah_angkatan_kelas' => $this->akademikService->getMataKuliahAngkatanKelasList(['per_page' => 500]),
            'nilai_mahasiswa' => $this->akademikService->getNilaiMahasiswaList(['per_page' => 500]),
        ];
        $kelasDataError = collect($responses)->contains(fn (Response $response) => ! $response->successful());

        $prodiList = $this->responseList($responses['prodi']);
        $masterKelas = collect($this->responseList($responses['kelas']))->keyBy('id');
        $angkatanKelasMap = collect($this->responseList($responses['angkatan_kelas']))->keyBy('id');
        $angkatanMap = collect($this->responseList($responses['angkatan']))->keyBy('id');
        $tahunAkademikMap = collect($this->responseList($responses['tahun_akademik']))->keyBy('id');
        $enrollmentKelas = $this->responseList($responses['enrollment_kelas']);
        $enrollmentMataKuliah = $this->responseList($responses['enrollment_mata_kuliah']);
        $mataKuliahAngkatanKelas = $this->responseList($responses['mata_kuliah_angkatan_kelas']);
        $nilaiMahasiswa = $this->responseList($responses['nilai_mahasiswa']);

        $prodiMap = collect($prodiList)->keyBy('id');
        $prodiId = $currentKurikulum['id_prodi'] ?? null;
        $prodiNama = $prodiMap->get($prodiId)['nama_program_studi']
            ?? $prodiMap->get($prodiId)['nama_prodi']
            ?? $prodiMap->get($prodiId)['nama']
            ?? '-';

        $courseEnrollments = collect($enrollmentMataKuliah)
            ->filter(fn (array $item) => (string) ($item['mata_kuliah_id'] ?? '') === (string) $id)
            ->keyBy(fn (array $item) => (string) ($item['id'] ?? ''));
        $courseEnrollmentIds = $courseEnrollments->keys()->map(fn ($enrollmentId) => (string) $enrollmentId)->all();
        $allClassOfferings = collect($mataKuliahAngkatanKelas);
        $courseOfferings = $allClassOfferings->filter(
            fn (array $item) => in_array((string) ($item['enrollment_mata_kuliah_id'] ?? ''), $courseEnrollmentIds, true)
        );
        $offeringsByClassId = $allClassOfferings->groupBy(fn (array $item) => (string) ($item['angkatan_kelas_id'] ?? ''));
        $enrollmentKelasByClassId = collect($enrollmentKelas)->groupBy(fn (array $item) => (string) ($item['angkatan_kelas_id'] ?? ''));
        $kelasList = [];

        foreach ($courseOfferings as $offering) {
            $classId = (string) ($offering['angkatan_kelas_id'] ?? '');
            $angkatanKelas = $angkatanKelasMap->get($classId);

            if (! is_array($angkatanKelas) || isset($kelasList[$classId])) {
                continue;
            }

            $courseEnrollment = $courseEnrollments->get((string) ($offering['enrollment_mata_kuliah_id'] ?? ''));
            $masterKelasItem = $masterKelas->get((string) ($angkatanKelas['kelas_id'] ?? ''));
            $angkatan = $angkatanKelas['angkatan'] ?? $angkatanMap->get((string) ($angkatanKelas['angkatan_id'] ?? ''));
            $prodi = $angkatanKelas['prodi'] ?? $prodiMap->get((string) ($angkatanKelas['prodi_id'] ?? ''));
            $tahunAkademik = $angkatanKelas['tahun_akademik']
                ?? $angkatanKelas['tahunAkademik']
                ?? $tahunAkademikMap->get((string) ($angkatanKelas['tahun_akademik_id'] ?? ''));
            $classOfferingIds = $offeringsByClassId->get($classId, collect())
                ->pluck('id')
                ->map(fn ($offeringId) => (string) $offeringId)
                ->all();
            $courseOfferingIds = $courseOfferings
                ->filter(fn (array $courseOffering) => (string) ($courseOffering['angkatan_kelas_id'] ?? '') === $classId)
                ->pluck('id')
                ->map(fn ($offeringId) => (string) $offeringId)
                ->all();
            $gradePoints = collect($nilaiMahasiswa)
                ->filter(fn (array $grade) => in_array((string) ($grade['mata_kuliah_angkatan_kelas_id'] ?? ''), $classOfferingIds, true))
                ->pluck('bobot_nilai')
                ->filter(fn ($gradePoint) => is_numeric($gradePoint))
                ->map(fn ($gradePoint) => (float) $gradePoint);
            $averageIpk = $gradePoints->isNotEmpty() ? number_format($gradePoints->avg(), 2) : '-';
            $classGrades = collect($nilaiMahasiswa)
                ->filter(fn (array $grade) => in_array((string) ($grade['mata_kuliah_angkatan_kelas_id'] ?? ''), $courseOfferingIds, true));
            $gradeLinks = $classGrades->pluck('link_nilai')
                ->filter(fn ($link) => is_string($link) && trim($link) !== '')
                ->map(fn (string $link) => trim($link))
                ->unique()
                ->values();
            $evidenceLinks = $classGrades->pluck('link_evidence')
                ->filter(fn ($link) => is_string($link) && trim($link) !== '')
                ->map(fn (string $link) => trim($link))
                ->unique()
                ->values();

            $kelasList[$classId] = [
                'id' => $classId,
                'nama_kelas' => $masterKelasItem['kelas'] ?? $masterKelasItem['nama_kelas'] ?? $angkatanKelas['kelas']['kelas'] ?? '-',
                'kurikulum' => $kurikulumNama,
                'angkatan' => is_array($angkatan) ? ($angkatan['angkatan'] ?? $angkatan['tahun'] ?? '-') : '-',
                'jumlah_mahasiswa' => $enrollmentKelasByClassId->get($classId, collect())->count(),
                'prodi' => is_array($prodi) ? ($prodi['nama_program_studi'] ?? $prodi['nama_prodi'] ?? $prodi['nama'] ?? '-') : '-',
                'tahun_akademik' => is_array($tahunAkademik) ? ($tahunAkademik['tahun'] ?? $tahunAkademik['nama'] ?? '-') : '-',
                'rata_rata_ipk' => $averageIpk,
                'jumlah_mata_kuliah' => $offeringsByClassId->get($classId, collect())->pluck('id')->unique()->count(),
                'enrollment_mata_kuliah_id' => $courseEnrollment['id'] ?? null,
                'evidence' => $courseEnrollment['evidence'] ?? '',
                'link_nilai' => $gradeLinks->count() === 1 ? $gradeLinks->first() : '',
                'link_evidence' => $evidenceLinks->count() === 1 ? $evidenceLinks->first() : '',
                'has_multiple_grade_links' => $gradeLinks->count() > 1,
                'has_multiple_evidence_links' => $evidenceLinks->count() > 1,
            ];
        }
        $kelasList = array_values($kelasList);

        return view('mata-kuliah.show', [
            'mataKuliah' => $mataKuliah,
            'kurikulumNama' => $kurikulumNama,
            'prodiNama' => $prodiNama,
            'kelasList' => $kelasList,
            'kurikulumList' => $kurikulumList,
            'kelasDataError' => $kelasDataError,
        ]);
    }

    public function updateNilaiLink(Request $request, int|string $mataKuliahId, int|string $angkatanKelasId): RedirectResponse
    {
        return $this->updateNilaiMahasiswaLink(
            $request,
            $mataKuliahId,
            $angkatanKelasId,
            'link_nilai',
            'Link nilai',
            fn (int|string $id, array $payload) => $this->akademikService->updateNilaiMahasiswaLinkNilai($id, $payload)
        );
    }

    public function updateEvidenceLink(Request $request, int|string $mataKuliahId, int|string $angkatanKelasId): RedirectResponse
    {
        return $this->updateNilaiMahasiswaLink(
            $request,
            $mataKuliahId,
            $angkatanKelasId,
            'link_evidence',
            'Link evidence',
            fn (int|string $id, array $payload) => $this->akademikService->updateNilaiMahasiswaLinkEvidence($id, $payload)
        );
    }

    private function updateNilaiMahasiswaLink(
        Request $request,
        int|string $mataKuliahId,
        int|string $angkatanKelasId,
        string $field,
        string $label,
        callable $persist
    ): RedirectResponse {
        $validated = $request->validate([
            $field => ['required', 'url', 'max:2048'],
        ]);
        $offeringsResponse = $this->akademikService->getMataKuliahAngkatanKelasList(['per_page' => 500]);
        $enrollmentsResponse = $this->akademikService->getEnrollmentMataKuliahList(['per_page' => 500]);
        $gradesResponse = $this->akademikService->getNilaiMahasiswaList(['per_page' => 500]);
        foreach ([$offeringsResponse, $enrollmentsResponse, $gradesResponse] as $response) {
            if ($response->status() === 401) {
                return redirect()->route('login')->with('toast', [
                    'type' => 'warning',
                    'title' => 'Sesi Berakhir',
                    'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
                ]);
            }
            if (! $response->successful()) {
                return back()->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal Memverifikasi Data Nilai',
                    'message' => $this->extractErrorMessage($response, 'Relasi nilai, kelas, dan mata kuliah gagal diverifikasi.'),
                ]);
            }
        }

        $courseEnrollmentMap = collect($this->responseList($enrollmentsResponse))
            ->keyBy(fn (array $item) => (string) ($item['id'] ?? ''));
        $offeringIds = collect($this->responseList($offeringsResponse))
            ->filter(function (array $offering) use ($angkatanKelasId, $mataKuliahId, $courseEnrollmentMap): bool {
                if ((string) ($offering['angkatan_kelas_id'] ?? '') !== (string) $angkatanKelasId) {
                    return false;
                }

                $courseEnrollment = $courseEnrollmentMap->get((string) ($offering['enrollment_mata_kuliah_id'] ?? ''));

                return is_array($courseEnrollment)
                    && (string) ($courseEnrollment['mata_kuliah_id'] ?? '') === (string) $mataKuliahId;
            })
            ->pluck('id')
            ->map(fn ($offeringId) => (string) $offeringId)
            ->all();

        if ($offeringIds === []) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Sesuai',
                'message' => 'Tidak ada penawaran mata kuliah pada kelas yang dipilih.',
            ]);
        }

        $gradeRecords = collect($this->responseList($gradesResponse))
            ->filter(fn (array $grade) => in_array(
                (string) ($grade['mata_kuliah_angkatan_kelas_id'] ?? ''),
                $offeringIds,
                true
            ))
            ->filter(fn (array $grade) => ! empty($grade['id']))
            ->values();

        if ($gradeRecords->isEmpty()) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Nilai Tidak Ditemukan',
                'message' => 'Tidak ada record nilai mahasiswa pada kelas dan mata kuliah ini.',
            ]);
        }

        $failedUpdates = [];
        foreach ($gradeRecords as $grade) {
            $payload = [
                'link_nilai' => $field === 'link_nilai'
                    ? trim($validated[$field])
                    : (string) ($grade['link_nilai'] ?? ''),
                'link_evidence' => $field === 'link_evidence'
                    ? trim($validated[$field])
                    : (string) ($grade['link_evidence'] ?? ''),
                'mata_kuliah_angkatan_kelas_id' => (int) ($grade['mata_kuliah_angkatan_kelas_id'] ?? 0),
            ];
            $response = $persist((string) $grade['id'], $payload);
            $body = $response->json();

            if (! $response->successful() || ! ($body['success'] ?? true)) {
                $failedUpdates[] = $this->extractErrorMessage($response, "{$label} gagal disimpan untuk satu mahasiswa.");
            }
        }

        if ($failedUpdates === []) {
            return redirect()->route('mata-kuliah.show', $mataKuliahId)->with('toast', [
                'type' => 'success',
                'title' => "{$label} Disimpan",
                'message' => "{$label} berhasil disimpan untuk {$gradeRecords->count()} mahasiswa.",
            ]);
        }

        return back()->with('toast', [
            'type' => 'warning',
            'title' => "{$label} Tersimpan Sebagian",
            'message' => sprintf(
                '%s berhasil disimpan untuk %d dari %d mahasiswa. Error pertama: %s',
                $label,
                $gradeRecords->count() - count($failedUpdates),
                $gradeRecords->count(),
                $failedUpdates[0]
            ),
        ]);
    }

    public function updateEvidence(Request $request, int|string $enrollmentId): RedirectResponse
    {
        $validated = $request->validate([
            'evidence' => ['required', 'string', 'url', 'max:2048'],
        ]);

        $enrollmentResponse = $this->akademikService->getEnrollmentMataKuliahById($enrollmentId);
        $enrollment = $enrollmentResponse->json('data');

        if (! $enrollmentResponse->successful() || ! is_array($enrollment)) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Enrollment',
                'message' => $this->extractErrorMessage($enrollmentResponse, 'Data enrollment mata kuliah tidak dapat ditemukan.'),
            ]);
        }

        if (empty($enrollment['tahun_akademik_id']) || empty($enrollment['mata_kuliah_id'])) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Data Enrollment Tidak Lengkap',
                'message' => 'Link evidence tidak dapat disimpan karena data enrollment tidak memiliki tahun akademik atau mata kuliah.',
            ]);
        }

        $payload = [
            'tahun_akademik_id' => $enrollment['tahun_akademik_id'],
            'mata_kuliah_id' => $enrollment['mata_kuliah_id'],
            'evidence' => trim($validated['evidence']),
        ];

        foreach (['dosen_id', 'mata_kuliah_angkatan_kelas_id'] as $optionalField) {
            if (array_key_exists($optionalField, $enrollment)) {
                $payload[$optionalField] = $enrollment[$optionalField];
            }
        }

        $response = $this->akademikService->updateEnrollmentMataKuliah($enrollmentId, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return back()->with('toast', [
                'type' => 'success',
                'title' => 'Evidence Disimpan',
                'message' => $body['message'] ?? 'Link evidence mata kuliah berhasil disimpan.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menyimpan Evidence',
            'message' => $this->extractErrorMessage($response, 'Link evidence mata kuliah gagal disimpan.'),
        ]);
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('mata-kuliah.show', $id);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_mata_kuliah' => ['required', 'string', 'max:255'],
            'kode_mata_kuliah' => ['required', 'string', 'max:50'],
            'id_kurikulum' => ['required'],
            'sks' => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'nama_mata_kuliah.required' => 'Nama mata kuliah wajib diisi.',
            'nama_mata_kuliah.string' => 'Nama mata kuliah harus berupa teks.',
            'kode_mata_kuliah.required' => 'Kode mata kuliah wajib diisi.',
            'id_kurikulum.required' => 'Kurikulum wajib dipilih.',
            'sks.required' => 'SKS wajib diisi.',
            'sks.integer' => 'SKS harus berupa angka.',
            'sks.min' => 'SKS minimal 1.',
            'sks.max' => 'SKS maksimal 20.',
        ]);

        $payload = [
            'id_kurikulum' => (int) $validated['id_kurikulum'],
            'kode_mata_kuliah' => trim($validated['kode_mata_kuliah']),
            'nama_mata_kuliah' => trim($validated['nama_mata_kuliah']),
            'sks' => (int) $validated['sks'],
        ];

        $response = $this->akademikService->updateMataKuliah($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_mata_kuliah_list', []);
            $found = false;
            foreach ($custom as &$c) {
                if ((string) ($c['id'] ?? '') === (string) $id) {
                    $c = array_merge($c, $payload, ['updated_at' => now()->toDateTimeString()]);
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $custom[] = array_merge($payload, [
                    'id' => $id,
                    'updated_at' => now()->toDateTimeString(),
                ]);
            }
            session(['custom_mata_kuliah_list' => $custom]);

            return back()->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Data mata kuliah berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui data mata kuliah.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteMataKuliah($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $previousUrl = url()->previous();
            $custom = session('custom_mata_kuliah_list', []);
            $custom = array_values(array_filter($custom, fn ($item) => (string) ($item['id'] ?? '') !== (string) $id));
            session(['custom_mata_kuliah_list' => $custom]);

            $deleted = session('deleted_mata_kuliah_ids', []);
            $deleted[] = $id;
            session(['deleted_mata_kuliah_ids' => $deleted]);

            $toast = [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Data mata kuliah berhasil dihapus.',
            ];

            // Jika penghapusan dilakukan dari show page detail (/mata-kuliah/{id}), kembali ke index
            if (str_contains($previousUrl, "/mata-kuliah/{$id}")) {
                return redirect()->route('mata-kuliah.index')->with('toast', $toast);
            }

            return back()->with('toast', $toast);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus data mata kuliah.');

        return back()->with('toast', [
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

    private function responseList(Response $response): array
    {
        if (! $response->successful()) {
            return [];
        }

        $items = $response->json('data') ?? [];
        if (is_array($items) && isset($items['data']) && is_array($items['data'])) {
            $items = $items['data'];
        }

        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }
}
