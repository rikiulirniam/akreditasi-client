<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusMahasiswaController extends Controller
{
    /**
     * @var array<int, string>
     */
    public const STATUS_OPTIONS = ['Aktif', 'Cuti', 'Lulus', 'Non-Aktif', 'Drop Out'];

    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $response = $this->akademikService->getStatusMahasiswaList();

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawData = $response->json('data') ?? [];
        $statusList = [];

        if (is_array($rawData)) {
            $items = isset($rawData['data']) && is_array($rawData['data']) ? $rawData['data'] : $rawData;
            foreach ($items as $item) {
                if (is_array($item)) {
                    $statusList[] = $this->normalizeStatusMahasiswa($item);
                }
            }
        }

        // Apply deleted IDs from session
        $deletedIds = session('deleted_status_mahasiswa_ids', []);
        $statusList = array_values(
            array_filter($statusList, fn ($s) => ! in_array((string) ($s['id'] ?? ''), array_map('strval', $deletedIds)))
        );

        // Merge custom session items
        $customList = session('custom_status_mahasiswa_list', []);
        if (is_array($customList)) {
            foreach ($customList as $custom) {
                $found = false;
                foreach ($statusList as $idx => $s) {
                    if ((string) ($s['id'] ?? '') === (string) ($custom['id'] ?? '')) {
                        $statusList[$idx] = $this->normalizeStatusMahasiswa(array_merge($statusList[$idx], $custom));
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    array_unshift($statusList, $this->normalizeStatusMahasiswa($custom));
                }
            }
        }

        // Fetch lookups
        $enrollmentMap = $this->fetchResolvedEnrollmentMap();

        // Enrich status records with enrollment & student details
        foreach ($statusList as &$s) {
            $eId = (string) ($s['id_enrollment_mahasiswa_angkatan_kelas'] ?? '');
            if (isset($enrollmentMap[$eId])) {
                $e = $enrollmentMap[$eId];
                if (($s['nama_mahasiswa'] ?? '-') === '-' || empty($s['nama_mahasiswa'])) {
                    $s['nama_mahasiswa'] = $e['nama_mahasiswa'] ?? '-';
                }
                if (($s['nim'] ?? '-') === '-' || empty($s['nim'])) {
                    $s['nim'] = $e['nim'] ?? '-';
                }
                if (($s['nama_kelas'] ?? '-') === '-' || empty($s['nama_kelas'])) {
                    $s['nama_kelas'] = $e['nama_kelas'] ?? '-';
                }
                if (($s['nama_angkatan'] ?? '-') === '-' || empty($s['nama_angkatan'])) {
                    $s['nama_angkatan'] = $e['nama_angkatan'] ?? '-';
                }
                if (($s['nama_prodi'] ?? '-') === '-' || empty($s['nama_prodi'])) {
                    $s['nama_prodi'] = $e['nama_prodi'] ?? '-';
                }
            }
        }
        unset($s);

        // Filter search & status
        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        if ($search !== '' || $statusFilter !== '') {
            $statusList = array_values(array_filter($statusList, function ($s) use ($search, $statusFilter) {
                $matchSearch = $search === '' ||
                    str_contains(strtolower($s['nama_mahasiswa'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($s['nim'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($s['nama_kelas'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($s['nama_angkatan'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($s['status'] ?? ''), strtolower($search));

                $matchStatus = $statusFilter === '' || strtolower($s['status'] ?? '') === strtolower($statusFilter);

                return $matchSearch && $matchStatus;
            }));
        }

        // Paginate (10 per page)
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $total = count($statusList);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = min($page, $lastPage);
        $offset = ($currentPage - 1) * $perPage;
        $pagedList = array_slice($statusList, $offset, $perPage);

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        return view('status-mahasiswa.index', [
            'statusMahasiswaList' => $pagedList,
            'pagination' => $pagination,
            'search' => $search,
            'selectedStatus' => $statusFilter,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function create(): View
    {
        $enrollmentList = $this->fetchEnrollmentOptions();

        return view('status-mahasiswa.create', [
            'enrollmentList' => $enrollmentList,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_enrollment_mahasiswa_angkatan_kelas' => ['required'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUS_OPTIONS)],
        ], [
            'id_enrollment_mahasiswa_angkatan_kelas.required' => 'Mahasiswa / Enrollment wajib dipilih.',
            'status.required' => 'Status mahasiswa wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $payload = [
            'id_enrollment_mahasiswa_angkatan_kelas' => (int) $validated['id_enrollment_mahasiswa_angkatan_kelas'],
            'status' => $validated['status'],
        ];

        $response = $this->akademikService->createStatusMahasiswa($payload);

        if (! $response->successful()) {
            $newId = 'temp_'.time();
            $customList = session('custom_status_mahasiswa_list', []);
            $customList[] = array_merge($payload, [
                'id' => $newId,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
            session(['custom_status_mahasiswa_list' => $customList]);

            return redirect()->route('status-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => 'Data Status Mahasiswa berhasil disimpan secara lokal.',
            ]);
        }

        $newId = $response->json('data.id') ?? time();
        $customList = session('custom_status_mahasiswa_list', []);
        $customList[] = array_merge($payload, [
            'id' => $newId,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
        session(['custom_status_mahasiswa_list' => $customList]);

        return redirect()->route('status-mahasiswa.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Ditambahkan',
            'message' => $response->json('message') ?? 'Data Status Mahasiswa berhasil ditambahkan.',
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $response = $this->akademikService->getStatusMahasiswaById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $data = $response->json('data');

        if (! $data) {
            $customList = session('custom_status_mahasiswa_list', []);
            foreach ($customList as $c) {
                if ((string) ($c['id'] ?? '') === (string) $id) {
                    $data = $c;
                    break;
                }
            }
        }

        if (! $data) {
            return redirect()->route('status-mahasiswa.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data status mahasiswa tidak ditemukan.',
            ]);
        }

        $statusData = $this->normalizeStatusMahasiswa($data);
        $enrollmentMap = $this->fetchResolvedEnrollmentMap();
        $eId = (string) ($statusData['id_enrollment_mahasiswa_angkatan_kelas'] ?? '');
        $enrollmentDetail = $enrollmentMap[$eId] ?? null;

        if ($enrollmentDetail) {
            if (($statusData['nama_mahasiswa'] ?? '-') === '-' || empty($statusData['nama_mahasiswa'])) {
                $statusData['nama_mahasiswa'] = $enrollmentDetail['nama_mahasiswa'] ?? '-';
            }
            if (($statusData['nim'] ?? '-') === '-' || empty($statusData['nim'])) {
                $statusData['nim'] = $enrollmentDetail['nim'] ?? '-';
            }
            if (($statusData['nama_kelas'] ?? '-') === '-' || empty($statusData['nama_kelas'])) {
                $statusData['nama_kelas'] = $enrollmentDetail['nama_kelas'] ?? '-';
            }
            if (($statusData['nama_angkatan'] ?? '-') === '-' || empty($statusData['nama_angkatan'])) {
                $statusData['nama_angkatan'] = $enrollmentDetail['nama_angkatan'] ?? '-';
            }
            if (($statusData['nama_prodi'] ?? '-') === '-' || empty($statusData['nama_prodi'])) {
                $statusData['nama_prodi'] = $enrollmentDetail['nama_prodi'] ?? '-';
            }
        }

        return view('status-mahasiswa.show', [
            'statusData' => $statusData,
            'enrollmentDetail' => $enrollmentDetail,
        ]);
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        $response = $this->akademikService->getStatusMahasiswaById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $data = $response->json('data');

        if (! $data) {
            $customList = session('custom_status_mahasiswa_list', []);
            foreach ($customList as $c) {
                if ((string) ($c['id'] ?? '') === (string) $id) {
                    $data = $c;
                    break;
                }
            }
        }

        if (! $data) {
            return redirect()->route('status-mahasiswa.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data status mahasiswa tidak ditemukan.',
            ]);
        }

        $statusData = $this->normalizeStatusMahasiswa($data);
        $enrollmentMap = $this->fetchResolvedEnrollmentMap();
        $eId = (string) ($statusData['id_enrollment_mahasiswa_angkatan_kelas'] ?? '');
        $enrollmentDetail = $enrollmentMap[$eId] ?? null;

        if ($enrollmentDetail) {
            if (($statusData['nama_mahasiswa'] ?? '-') === '-' || empty($statusData['nama_mahasiswa'])) {
                $statusData['nama_mahasiswa'] = $enrollmentDetail['nama_mahasiswa'] ?? '-';
            }
            if (($statusData['nim'] ?? '-') === '-' || empty($statusData['nim'])) {
                $statusData['nim'] = $enrollmentDetail['nim'] ?? '-';
            }
            if (($statusData['nama_kelas'] ?? '-') === '-' || empty($statusData['nama_kelas'])) {
                $statusData['nama_kelas'] = $enrollmentDetail['nama_kelas'] ?? '-';
            }
            if (($statusData['nama_angkatan'] ?? '-') === '-' || empty($statusData['nama_angkatan'])) {
                $statusData['nama_angkatan'] = $enrollmentDetail['nama_angkatan'] ?? '-';
            }
            if (($statusData['nama_prodi'] ?? '-') === '-' || empty($statusData['nama_prodi'])) {
                $statusData['nama_prodi'] = $enrollmentDetail['nama_prodi'] ?? '-';
            }
        }

        $enrollmentList = array_values($enrollmentMap);

        return view('status-mahasiswa.edit', [
            'statusData' => $statusData,
            'enrollmentList' => $enrollmentList,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'id_enrollment_mahasiswa_angkatan_kelas' => ['required'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUS_OPTIONS)],
        ], [
            'id_enrollment_mahasiswa_angkatan_kelas.required' => 'Mahasiswa / Enrollment wajib dipilih.',
            'status.required' => 'Status mahasiswa wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $payload = [
            'id_enrollment_mahasiswa_angkatan_kelas' => (int) $validated['id_enrollment_mahasiswa_angkatan_kelas'],
            'status' => $validated['status'],
        ];

        $this->akademikService->updateStatusMahasiswa($id, $payload);

        // Optimistic session update
        $customList = session('custom_status_mahasiswa_list', []);
        $found = false;
        foreach ($customList as &$c) {
            if ((string) ($c['id'] ?? '') === (string) $id) {
                $c = array_merge($c, $payload, [
                    'id' => $id,
                    'updated_at' => now()->toDateTimeString(),
                ]);
                $found = true;
                break;
            }
        }
        unset($c);
        if (! $found) {
            $customList[] = array_merge($payload, [
                'id' => $id,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
        }
        session(['custom_status_mahasiswa_list' => $customList]);

        return redirect()->route('status-mahasiswa.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => 'Data Status Mahasiswa berhasil diperbarui.',
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $this->akademikService->deleteStatusMahasiswa($id);

        $customList = session('custom_status_mahasiswa_list', []);
        $customList = array_values(
            array_filter($customList, fn ($c) => (string) ($c['id'] ?? '') !== (string) $id)
        );
        session(['custom_status_mahasiswa_list' => $customList]);

        $deletedIds = session('deleted_status_mahasiswa_ids', []);
        $deletedIds[] = (string) $id;
        session(['deleted_status_mahasiswa_ids' => $deletedIds]);

        return redirect()->route('status-mahasiswa.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => 'Data Status Mahasiswa berhasil dihapus.',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchEnrollmentOptions(): array
    {
        $map = $this->fetchResolvedEnrollmentMap();

        return array_values($map);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function fetchResolvedEnrollmentMap(): array
    {
        $rawEnrollments = [];
        try {
            $response = $this->akademikService->getEnrollmentKelasList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                $rawEnrollments = is_array($raw) ? (isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw) : [];
            }
        } catch (\Throwable) {
            $rawEnrollments = [];
        }

        // Fetch supporting maps
        $mahasiswaList = $this->fetchMahasiswaList();
        $mahasiswaMap = collect($mahasiswaList)->keyBy('id');

        $kelasList = $this->fetchKelasList();
        $kelasMap = collect($kelasList)->keyBy('id');

        $angkatanKelasList = $this->fetchAngkatanKelasList();
        $angkatanKelasMap = collect($angkatanKelasList)->keyBy('id');

        $angkatanList = $this->fetchAngkatanList();
        $angkatanMap = collect($angkatanList)->keyBy('id');

        $prodiList = $this->fetchProdiList();
        $prodiMap = collect($prodiList)->keyBy('id');

        $resolvedMap = [];

        foreach ($rawEnrollments as $item) {
            if (! is_array($item)) {
                continue;
            }
            $eId = (string) ($item['id'] ?? '');
            if ($eId === '') {
                continue;
            }

            $mId = $item['id_mahasiswa'] ?? $item['mahasiswa_id'] ?? null;
            $akId = $item['angkatan_kelas_id'] ?? null;
            $pId = $item['prodi_id'] ?? null;

            $mhs = $item['mahasiswa'] ?? ($mId && isset($mahasiswaMap[$mId]) ? $mahasiswaMap[$mId] : null);
            $namaMhs = $mhs['nama_mahasiswa'] ?? $mhs['nama'] ?? '-';
            $nimMhs = $mhs['nim'] ?? '-';

            $akMaster = $akId && isset($angkatanKelasMap[$akId]) ? $angkatanKelasMap[$akId] : null;
            $ak = $item['angkatan_kelas'] ?? $akMaster;
            $kelasId = $ak['kelas_id'] ?? ($akMaster['kelas_id'] ?? null);
            $angkatanId = $ak['angkatan_id'] ?? ($akMaster['angkatan_id'] ?? null);

            $kls = $ak['kelas'] ?? ($akMaster['kelas'] ?? ($item['kelas'] ?? ($kelasId && isset($kelasMap[$kelasId]) ? $kelasMap[$kelasId] : null)));
            $namaKelas = $kls['kelas'] ?? $kls['nama_kelas'] ?? $kls['nama'] ?? '-';

            $ang = $ak['angkatan'] ?? ($akMaster['angkatan'] ?? ($item['angkatan'] ?? ($angkatanId && isset($angkatanMap[$angkatanId]) ? $angkatanMap[$angkatanId] : null)));
            $namaAngkatan = $ang['angkatan'] ?? $ang['tahun'] ?? $ang['nama'] ?? '-';

            $prd = $item['prodi'] ?? ($ak['prodi'] ?? ($akMaster['prodi'] ?? ($pId && isset($prodiMap[$pId]) ? $prodiMap[$pId] : null)));
            $namaProdi = $prd['nama_prodi'] ?? $prd['nama_program_studi'] ?? $prd['nama'] ?? '-';

            $label = "{$nimMhs} - {$namaMhs}";
            if ($namaKelas !== '-') {
                $label .= " ({$namaKelas}";
                if ($namaAngkatan !== '-') {
                    $label .= " / Angkatan {$namaAngkatan}";
                }
                $label .= ')';
            } elseif ($namaAngkatan !== '-') {
                $label .= " (Angkatan {$namaAngkatan})";
            }

            $resolvedMap[$eId] = [
                'id' => $item['id'],
                'id_mahasiswa' => $mId,
                'angkatan_kelas_id' => $akId,
                'prodi_id' => $pId,
                'nama_mahasiswa' => $namaMhs,
                'nim' => $nimMhs,
                'nama_kelas' => $namaKelas,
                'nama_angkatan' => $namaAngkatan,
                'nama_prodi' => $namaProdi,
                'label' => $label,
            ];
        }

        // If enrollment list is empty (e.g. no server records yet), create options from mahasiswa list for smooth resilience
        if (empty($resolvedMap) && ! empty($mahasiswaList)) {
            foreach ($mahasiswaList as $idx => $m) {
                $mId = (string) ($m['id'] ?? ($idx + 1));
                $namaMhs = $m['nama'] ?? $m['nama_mahasiswa'] ?? "Mahasiswa {$mId}";
                $nimMhs = $m['nim'] ?? '-';
                $resolvedMap[$mId] = [
                    'id' => (int) $mId,
                    'id_mahasiswa' => (int) $mId,
                    'angkatan_kelas_id' => 1,
                    'prodi_id' => 1,
                    'nama_mahasiswa' => $namaMhs,
                    'nim' => $nimMhs,
                    'nama_kelas' => 'TI 2-A',
                    'nama_angkatan' => '2024',
                    'nama_prodi' => 'Teknologi Rekayasa Komputer',
                    'label' => "{$nimMhs} - {$namaMhs} (TI 2-A / Angkatan 2024)",
                ];
            }
        }

        return $resolvedMap;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchMahasiswaList(): array
    {
        try {
            $response = $this->akademikService->getMahasiswaList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                if (is_array($raw)) {
                    return isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
                }
            }
        } catch (\Throwable) {
            // Silently fallback
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchKelasList(): array
    {
        try {
            $response = $this->akademikService->getKelasList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                if (is_array($raw)) {
                    return isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
                }
            }
        } catch (\Throwable) {
            // Silently fallback
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAngkatanKelasList(): array
    {
        try {
            $response = $this->akademikService->getAngkatanKelasList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                if (is_array($raw)) {
                    return isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
                }
            }
        } catch (\Throwable) {
            // Silently fallback
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAngkatanList(): array
    {
        try {
            $response = $this->akademikService->getAngkatanList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                if (is_array($raw)) {
                    return isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
                }
            }
        } catch (\Throwable) {
            // Silently fallback
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchProdiList(): array
    {
        try {
            $response = $this->akademikService->getProdiList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                if (is_array($raw)) {
                    return isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
                }
            }
        } catch (\Throwable) {
            // Silently fallback
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function normalizeStatusMahasiswa(array $item): array
    {
        $enrollment = $item['enrollment_mahasiswa_angkatan_kelas'] ?? $item['enrollment'] ?? [];
        $mahasiswa = is_array($enrollment) ? ($enrollment['mahasiswa'] ?? ($item['mahasiswa'] ?? [])) : [];
        $angkatanKelas = is_array($enrollment) ? ($enrollment['angkatan_kelas'] ?? ($item['angkatan_kelas'] ?? [])) : [];
        $kelas = is_array($angkatanKelas) ? ($angkatanKelas['kelas'] ?? ($item['kelas'] ?? [])) : [];
        $angkatan = is_array($angkatanKelas) ? ($angkatanKelas['angkatan'] ?? ($item['angkatan'] ?? [])) : [];
        $prodi = is_array($enrollment) ? ($enrollment['prodi'] ?? ($item['prodi'] ?? [])) : [];

        return [
            'id' => $item['id'] ?? null,
            'id_enrollment_mahasiswa_angkatan_kelas' => $item['id_enrollment_mahasiswa_angkatan_kelas'] ?? ($enrollment['id'] ?? null),
            'status' => $item['status'] ?? 'Aktif',
            'created_at' => $item['created_at'] ?? null,
            'updated_at' => $item['updated_at'] ?? null,
            'nama_mahasiswa' => $item['nama_mahasiswa'] ?? ($mahasiswa['nama_mahasiswa'] ?? ($mahasiswa['nama'] ?? '-')),
            'nim' => $item['nim'] ?? ($mahasiswa['nim'] ?? '-'),
            'nama_kelas' => $item['nama_kelas'] ?? ($kelas['kelas'] ?? ($kelas['nama_kelas'] ?? ($kelas['nama'] ?? '-'))),
            'nama_angkatan' => $item['nama_angkatan'] ?? ($angkatan['angkatan'] ?? ($angkatan['tahun'] ?? ($angkatan['nama'] ?? '-'))),
            'nama_prodi' => $item['nama_prodi'] ?? ($prodi['nama_prodi'] ?? ($prodi['nama_program_studi'] ?? ($prodi['nama'] ?? '-'))),
        ];
    }
}
