<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use App\Services\AkreditasiService;
use App\Services\TridharmaService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KaprodiController extends Controller
{
    protected AkreditasiService $akreditasiService;

    protected AkademikService $akademikService;

    protected TridharmaService $tridharmaService;

    public function __construct(
        AkreditasiService $akreditasiService,
        AkademikService $akademikService,
        TridharmaService $tridharmaService
    ) {
        $this->akreditasiService = $akreditasiService;
        $this->akademikService = $akademikService;
        $this->tridharmaService = $tridharmaService;
    }

    /**
     * Helper untuk mengekstrak array data dari response API atau return langsung jika sudah array.
     */
    protected function extractData(mixed $response, mixed $default = []): mixed
    {
        if ($response instanceof Response) {
            return $response->successful() ? ($response->json('data') ?? $default) : $default;
        }

        if (is_array($response)) {
            return $response;
        }

        return $default;
    }

    /**
     * Helper untuk memastikan nilai diekstrak sebagai string yang aman.
     */
    protected function extractString(mixed $value, string $default = '-'): string
    {
        if (is_null($value)) {
            return $default;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : $default;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $keys = [
                'nama_bidang', 'nama_jabatan', 'nama', 'name', 'nama_dosen',
                'bidang', 'keahlian', 'label', 'title', 'judul',
            ];
            foreach ($keys as $key) {
                if (! empty($value[$key]) && is_string($value[$key])) {
                    return trim($value[$key]);
                }
            }

            $first = reset($value);
            if (is_string($first)) {
                return trim($first);
            }
            if (is_array($first)) {
                foreach ($keys as $key) {
                    if (! empty($first[$key]) && is_string($first[$key])) {
                        return trim($first[$key]);
                    }
                }
            }
        }

        return $default;
    }

    /**
     * Normalisasi ringkasan project untuk tampilan tanpa mengubah payload API.
     *
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    protected function normalizeProjectDataSummary(?array $payload): array
    {
        $payload = $payload ?? [];
        $sourceData = array_key_exists('data', $payload)
            ? $payload['data']
            : $payload;
        $sourceData = is_array($sourceData) ? $sourceData : [];
        $meta = is_array($sourceData['meta'] ?? null) ? $sourceData['meta'] : [];
        $isSimulation = ($payload['is_simulation'] ?? $meta['is_simulation'] ?? $payload['meta']['is_simulation'] ?? false) === true;
        $pending = $meta['pending'] ?? $payload['meta']['pending'] ?? $payload['pending'] ?? null;

        if ($sourceData === []) {
            return [
                'available' => false,
                'is_simulation' => $isSimulation,
                'pending' => $pending,
            ];
        }

        return [
            'available' => true,
            'project' => $sourceData['project'] ?? null,
            'prodi' => $sourceData['prodi'] ?? null,
            'mahasiswa' => $sourceData['mahasiswa'] ?? null,
            'dosen' => $sourceData['dosen'] ?? null,
            'tridharma' => $sourceData['tridharma'] ?? null,
            'meta' => $meta,
            'is_simulation' => $isSimulation,
            'pending' => $pending,
        ];
    }

    /**
     * Determines whether the current Kaprodi or project PIC may edit project narrative.
     *
     * @param  array{by_id: array<string, array<string, mixed>>, by_code: array<string, array<string, mixed>>}|null  $picAssignments
     */
    protected function canEditProjectNarrative(int|string $projectId, ?array $picAssignments = null): bool
    {
        $user = session('user', []);
        $role = strtolower((string) ($user['role'] ?? $user['role_name'] ?? ''));
        if (str_contains($role, 'kaprodi') || str_contains($role, 'admin')) {
            return true;
        }

        if (! str_contains($role, 'dosen')) {
            return false;
        }

        $picAssignments ??= $this->loadProjectPicAssignments($projectId);
        $currentUserId = $user['id'] ?? $user['id_user'] ?? null;
        $currentUserName = strtolower(trim((string) ($user['nama'] ?? $user['name'] ?? '')));

        foreach (array_merge($picAssignments['by_id'] ?? [], $picAssignments['by_code'] ?? []) as $assignment) {
            $assignedUserId = $assignment['pic_user_id'] ?? null;
            $assignedUserName = strtolower(trim((string) ($assignment['pic_name'] ?? '')));

            if ($currentUserId !== null && $assignedUserId !== null && (string) $currentUserId === (string) $assignedUserId) {
                return true;
            }

            if ($currentUserName !== '' && $assignedUserName !== '' && $currentUserName === $assignedUserName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tampilkan halaman Dashboard Kaprodi.
     */
    public function dashboard(Request $request): View
    {
        // 1. Ambil data kriteria dari API
        $rawKriteria = $this->extractData($this->akreditasiService->getKriteria());
        $rawIndikator = $this->extractData($this->akreditasiService->getIndikator());

        $projects = $this->extractData($this->akreditasiService->getProjects());
        $projectId = is_array($projects) ? ($projects[0]['id'] ?? null) : null;

        // Siapkan monitoring pengisian kriteria
        $kriteriaProgress = [];
        if (! empty($rawKriteria)) {
            foreach ($rawKriteria as $k) {
                $subCount = count($k['subkriteria'] ?? []);
                // Estimasi atau perhitungan indikator per kriteria
                $kriteriaProgress[] = [
                    'kriteria' => $k['nama_kriteria'] ?? 'Kriteria',
                    'kode' => $k['kode_kriteria'] ?? '',
                    'persen' => $subCount > 0 ? min(100, $subCount * 25) : 0,
                    'bar_color' => 'bg-additional',
                    'terisi' => "{$subCount}/".max(4, $subCount),
                    'status' => $subCount > 0 ? 'Berjalan' : 'Tertinggal',
                    'badge' => $subCount > 0 ? 'bg-sky-50 text-additional border-sky-100' : 'bg-amber-50 text-amber-600 border-amber-100',
                ];
            }
        } else {
            // Fallback default jika master kriteria di API masih belum lengkap
            $kriteriaProgress = [
                [
                    'kriteria' => 'Tata Pamong dan Kerja Sama',
                    'persen' => 80,
                    'bar_color' => 'bg-emerald-600',
                    'terisi' => '16/20',
                    'status' => 'Lengkap',
                    'badge' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                ],
                [
                    'kriteria' => 'Mahasiswa',
                    'persen' => 62,
                    'bar_color' => 'bg-additional',
                    'terisi' => '31/50',
                    'status' => 'Berjalan',
                    'badge' => 'bg-sky-50 text-additional border-sky-100',
                ],
                [
                    'kriteria' => 'Sumber Daya Manusia',
                    'persen' => 70,
                    'bar_color' => 'bg-additional',
                    'terisi' => '28/40',
                    'status' => 'Berjalan',
                    'badge' => 'bg-sky-50 text-additional border-sky-100',
                ],
                [
                    'kriteria' => 'Keuangan, Sarana dan Prasarana',
                    'persen' => 45,
                    'bar_color' => 'bg-amber-400',
                    'terisi' => '18/40',
                    'status' => 'Tertinggal',
                    'badge' => 'bg-amber-50 text-amber-600 border-amber-100',
                ],
                [
                    'kriteria' => 'Pendidikan',
                    'persen' => 58,
                    'bar_color' => 'bg-additional',
                    'terisi' => '29/50',
                    'status' => 'Berjalan',
                    'badge' => 'bg-sky-50 text-additional border-sky-100',
                ],
            ];
        }

        // 2. Ambil data Audit SPMI dari API
        $spmiAuditList = $this->extractData($this->akreditasiService->getSpmiAudit());

        // 3. Ambil data Self Assessment dari API
        $selfAssessment = $this->extractData($this->akreditasiService->getSelfAssessment());

        return view('pages.kaprodi.dashboard', [
            'kriteriaProgress' => $kriteriaProgress,
            'spmiAuditList' => $spmiAuditList,
            'selfAssessment' => $selfAssessment,
            'totalIndikator' => count($rawIndikator),
            'dataSummaryUrl' => $projectId !== null
                ? route('kaprodi.project.data-summary', $projectId)
                : null,
        ]);
    }

    /**
     * Tampilkan halaman Project Akreditasi Kaprodi.
     */
    public function project(Request $request): View
    {
        // 1. Ambil daftar project akreditasi dari API
        $projectsResponse = $this->akreditasiService->getProjects();
        $projects = $this->extractData($projectsResponse);

        // 2. Ambil master data lembaga, versi instrumen, dan instrumen terformat
        $lembagaList = $this->extractData($this->akreditasiService->getLembaga());
        $versiList = $this->extractData($this->akreditasiService->getVersiInstrumen());
        $instrumenList = $this->getFormattedInstrumenList();

        // 3. Hitung statistik
        $currentYear = (int) date('Y');
        $totalProject = count($projects);
        $tahunBerjalanCount = 0;
        $inProgressCount = 0;
        $draftCount = 0;
        foreach ($projects as $p) {
            if ((int) ($p['tahun_target_submit'] ?? 0) === $currentYear) {
                $tahunBerjalanCount++;
            }
            $st = strtolower((string) ($p['status_project'] ?? 'draft'));
            if ($st === 'on_progress' || $st === 'on progress' || $st === 'in_progress' || $st === 'pengisian' || str_contains($st, 'progress')) {
                $inProgressCount++;
            } elseif ($st === 'draft' || empty($st)) {
                $draftCount++;
            }
        }

        $activeInstrumen = '2.1';
        if (! empty($versiList[0]['nomor_versi'])) {
            $activeInstrumen = (string) $versiList[0]['nomor_versi'];
        }

        $stats = [
            'total' => $totalProject,
            'tahun_berjalan' => $tahunBerjalanCount,
            'in_progress' => $inProgressCount,
            'on_progress' => $inProgressCount,
            'draft' => $draftCount,
            'rata_kelengkapan' => $totalProject > 0 ? '0%' : '—',
            'instrumen_aktif' => $activeInstrumen,
        ];

        return view('pages.kaprodi.project', [
            'projects' => $projects,
            'lembagaList' => $lembagaList,
            'instrumenList' => $instrumenList,
            'stats' => $stats,
        ]);
    }

    /**
     * Mengambil dan memformat master data instrumen akreditasi (format: Lembaga - Versi - Jenjang - Jenis).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getFormattedInstrumenList(bool $useFallback = true): array
    {
        $raw = [];
        try {
            $res = $this->akreditasiService->getInstrumens();
            if ($res->successful()) {
                $raw = $this->extractData($res);
            }
        } catch (\Throwable) {
            $raw = [];
        }

        if ($useFallback && empty($raw)) {
            $versiList = $this->extractData($this->akreditasiService->getVersiInstrumen());
            $firstVersi = $versiList[0] ?? ['id' => 1, 'nomor_versi' => '2.1'];

            $raw = [
                [
                    'id' => 1,
                    'id_versi_instrumen' => $firstVersi['id'] ?? 1,
                    'id_jenjang' => 1,
                    'id_jenis_instrumen' => 1,
                    'nama_lembaga' => 'LAM INFOKOM',
                    'nomor_versi' => $firstVersi['nomor_versi'] ?? '2.1',
                    'nama_jenjang' => 'Sarjana Terapan',
                    'nama_jenis' => 'APS',
                ],
                [
                    'id' => 2,
                    'id_versi_instrumen' => $firstVersi['id'] ?? 1,
                    'id_jenjang' => 2,
                    'id_jenis_instrumen' => 1,
                    'nama_lembaga' => 'LAM INFOKOM',
                    'nomor_versi' => $firstVersi['nomor_versi'] ?? '2.1',
                    'nama_jenjang' => 'Magister Terapan',
                    'nama_jenis' => 'APS',
                ],
            ];
        }

        $formatted = [];
        foreach ($raw as $item) {
            $defaultLabel = $useFallback ? 'LAM INFOKOM' : '-';
            $lembaga = $item['versi_instrumen']['lembaga']['nama_lembaga']
                ?? ($item['nama_lembaga'] ?? ($item['lembaga']['nama_lembaga'] ?? $defaultLabel));

            $versi = $item['versi_instrumen']['nomor_versi']
                ?? ($item['nomor_versi'] ?? ($item['versi'] ?? ($useFallback ? '2.1' : '-')));
            if (! str_starts_with(strtolower((string) $versi), 'v')) {
                $versi = 'v'.$versi;
            }

            $jenjang = $item['jenjang']['nama_jenjang']
                ?? ($item['nama_jenjang'] ?? ($item['jenjang'] ?? ($useFallback ? 'Sarjana Terapan' : '-')));

            $jenis = $item['jenis_instrumen']['nama_jenis']
                ?? ($item['nama_jenis'] ?? ($item['jenis'] ?? ($useFallback ? 'APS' : '-')));

            $item['formatted_label'] = "{$lembaga} - {$versi} - {$jenjang} - {$jenis}";
            $item['id_versi_instrumen'] = $item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? ($useFallback ? 1 : null));
            $item['id_jenjang'] = $item['id_jenjang'] ?? ($item['jenjang']['id'] ?? ($useFallback ? 1 : null));
            $item['id_jenis_instrumen'] = $item['id_jenis_instrumen'] ?? ($item['jenis_instrumen']['id'] ?? ($useFallback ? 1 : null));

            $formatted[] = $item;
        }

        return $formatted;
    }

    /**
     * Simpan project akreditasi baru ke API.
     */
    public function storeProject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_project' => 'required|string|max:255',
            'tahun_target_submit' => 'required|numeric',
            'id_prodi' => 'nullable|integer',
            'id_instrumen' => 'nullable',
            'id_versi_instrumen' => 'nullable|integer',
            'id_jenjang' => 'nullable|integer',
            'id_jenis_instrumen' => 'nullable|integer',
            'status_project' => 'nullable|string',
        ]);

        $idInstrumen = $request->input('id_instrumen');
        $idVersi = $request->input('id_versi_instrumen');
        $idJenjang = $request->input('id_jenjang');
        $idJenis = $request->input('id_jenis_instrumen');

        if ($idInstrumen && (! $idVersi || ! $idJenjang || ! $idJenis)) {
            $list = $this->getFormattedInstrumenList();
            foreach ($list as $ins) {
                if ((string) ($ins['id'] ?? '') === (string) $idInstrumen) {
                    $idVersi = $idVersi ?: ($ins['id_versi_instrumen'] ?? 1);
                    $idJenjang = $idJenjang ?: ($ins['id_jenjang'] ?? 1);
                    $idJenis = $idJenis ?: ($ins['id_jenis_instrumen'] ?? 1);
                    break;
                }
            }
        }

        $rawStatus = $request->input('status_project', 'Draft');
        $statusMap = [
            'draft' => 'Draft',
            'in_progress' => 'On Progress',
            'in progress' => 'On Progress',
            'on_progress' => 'On Progress',
            'on progress' => 'On Progress',
            'pending' => 'Pending',
            'cancel' => 'Cancel',
            'selesai' => 'Selesai',
        ];
        $normalizedStatus = $statusMap[strtolower(trim((string) $rawStatus))] ?? $rawStatus;

        $payload = [
            'id_prodi' => (int) ($request->input('id_prodi') ?: 1),
            'id_versi_instrumen' => (int) ($idVersi ?: 1),
            'id_jenjang' => (int) ($idJenjang ?: 1),
            'id_jenis_instrumen' => (int) ($idJenis ?: 1),
            'nama_project' => $validated['nama_project'],
            'tahun_target_submit' => (int) $validated['tahun_target_submit'],
            'status_project' => $normalizedStatus,
        ];

        if ($idInstrumen) {
            $payload['id_instrumen'] = is_numeric($idInstrumen) ? (int) $idInstrumen : $idInstrumen;
        }

        try {
            $response = $this->akreditasiService->createProject($payload);
            if ($response->successful()) {
                return redirect()->route('kaprodi.project')->with('success', 'Project akreditasi berhasil dibuat!');
            }

            $errMsg = $response->json('message') ?? 'Gagal membuat project akreditasi.';
            if (! empty($response->json('errors'))) {
                $firstErr = collect($response->json('errors'))->flatten()->first();
                if ($firstErr) {
                    $errMsg .= " ({$firstErr})";
                }
            }

            return redirect()->route('kaprodi.project')->with('error', $errMsg);
        } catch (\Throwable $e) {
            return redirect()->route('kaprodi.project')->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    /**
     * Perbarui project akreditasi di API.
     */
    public function updateProject(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_project' => 'required|string|max:255',
            'tahun_target_submit' => 'required|numeric',
            'id_prodi' => 'nullable|integer',
            'id_instrumen' => 'nullable',
            'id_versi_instrumen' => 'nullable|integer',
            'id_jenjang' => 'nullable|integer',
            'id_jenis_instrumen' => 'nullable|integer',
            'status_project' => 'nullable|string',
            'nomor_sk_akreditasi' => 'nullable|string|max:255',
            'tanggal_verifikasi_akreditasi' => 'nullable|date',
        ]);

        $idInstrumen = $request->input('id_instrumen');
        $idVersi = $request->input('id_versi_instrumen');
        $idJenjang = $request->input('id_jenjang');
        $idJenis = $request->input('id_jenis_instrumen');

        if ($idInstrumen && (! $idVersi || ! $idJenjang || ! $idJenis)) {
            $list = $this->getFormattedInstrumenList();
            foreach ($list as $ins) {
                if ((string) ($ins['id'] ?? '') === (string) $idInstrumen) {
                    $idVersi = $idVersi ?: ($ins['id_versi_instrumen'] ?? 1);
                    $idJenjang = $idJenjang ?: ($ins['id_jenjang'] ?? 1);
                    $idJenis = $idJenis ?: ($ins['id_jenis_instrumen'] ?? 1);
                    break;
                }
            }
        }

        $payload = [
            'id_prodi' => (int) ($request->input('id_prodi') ?: 1),
            'id_versi_instrumen' => (int) ($idVersi ?: 1),
            'id_jenjang' => (int) ($idJenjang ?: 1),
            'id_jenis_instrumen' => (int) ($idJenis ?: 1),
            'nama_project' => $validated['nama_project'],
            'tahun_target_submit' => (int) $validated['tahun_target_submit'],
        ];

        if ($idInstrumen) {
            $payload['id_instrumen'] = is_numeric($idInstrumen) ? (int) $idInstrumen : $idInstrumen;
        }

        if ($request->filled('status_project')) {
            $rawStatus = $request->input('status_project');
            $statusMap = [
                'draft' => 'Draft',
                'in_progress' => 'On Progress',
                'in progress' => 'On Progress',
                'on_progress' => 'On Progress',
                'on progress' => 'On Progress',
                'pending' => 'Pending',
                'cancel' => 'Cancel',
                'selesai' => 'Selesai',
            ];
            $payload['status_project'] = $statusMap[strtolower(trim((string) $rawStatus))] ?? $rawStatus;
        }

        if ($request->filled('nomor_sk_akreditasi')) {
            $payload['nomor_sk_akreditasi'] = $request->input('nomor_sk_akreditasi');
            $payload['nomor_sk'] = $request->input('nomor_sk_akreditasi');
        }
        if ($request->filled('tanggal_verifikasi_akreditasi')) {
            $payload['tanggal_verifikasi_akreditasi'] = $request->input('tanggal_verifikasi_akreditasi');
            $payload['tanggal_sk'] = $request->input('tanggal_verifikasi_akreditasi');
        }

        try {
            $response = $this->akreditasiService->updateProject($id, $payload);
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Project akreditasi berhasil diperbarui!');
            }

            $errMsg = $response->json('message') ?? 'Gagal memperbarui project akreditasi.';
            if (! empty($response->json('errors'))) {
                $firstErr = collect($response->json('errors'))->flatten()->first();
                if ($firstErr) {
                    $errMsg .= " ({$firstErr})";
                }
            }

            return redirect()->back()->with('error', $errMsg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    /**
     * Buat Cepat Project TRK 2026 dengan id_prodi: 101.
     */
    public function quickCreateProject(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $payload = [
                'id_prodi' => (int) $request->input('id_prodi', 101),
            ];

            $response = $this->akreditasiService->quickCreateProject($payload);

            if ($request->wantsJson() || $request->ajax()) {
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Project TRK 2026 berhasil dibuat secara otomatis!',
                        'data' => $response->json('data') ?? $response->json(),
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $response->json('message') ?? 'Gagal membuat Project TRK 2026 di server API.',
                ], $response->status() ?: 400);
            }

            if ($response->successful()) {
                return redirect()->route('kaprodi.project')->with('success', 'Project TRK 2026 berhasil dibuat secara otomatis!');
            }

            $errMsg = $response->json('message') ?? 'Gagal membuat Project TRK 2026.';
            if (! empty($response->json('errors'))) {
                $firstErr = collect($response->json('errors'))->flatten()->first();
                if ($firstErr) {
                    $errMsg .= " ({$firstErr})";
                }
            }

            return redirect()->route('kaprodi.project')->with('error', $errMsg);
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('kaprodi.project')->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    /**
     * Ubah status project akreditasi (draft, in_progress, pending, cancel, selesai). Khusus untuk Kaprodi.
     */
    public function updateStatusProject(Request $request, $id): RedirectResponse|JsonResponse
    {
        $userRole = strtolower((string) (session('user.role') ?? session('user.role_name') ?? ''));
        if (! str_contains($userRole, 'kaprodi') && ! str_contains($userRole, 'admin')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Hanya Kaprodi atau Administrator yang dapat mengubah status project akreditasi.'], 403);
            }

            return redirect()->back()->with('error', 'Hanya Kaprodi atau Administrator yang dapat mengubah status project akreditasi.');
        }

        $rawStatus = $request->input('status_project');
        $statusMap = [
            'draft' => 'Draft',
            'in_progress' => 'On Progress',
            'in progress' => 'On Progress',
            'on_progress' => 'On Progress',
            'on progress' => 'On Progress',
            'pending' => 'Pending',
            'cancel' => 'Cancel',
            'selesai' => 'Selesai',
        ];
        if (is_string($rawStatus) && isset($statusMap[strtolower(trim($rawStatus))])) {
            $request->merge(['status_project' => $statusMap[strtolower(trim($rawStatus))]]);
        }

        $validated = $request->validate([
            'status_project' => 'required|string|in:Draft,On Progress,Pending,Cancel,Selesai',
            'nomor_sk_akreditasi' => 'nullable|string|max:255',
            'tanggal_verifikasi_akreditasi' => 'nullable|date',
        ]);

        $statusProject = $validated['status_project'];

        try {
            $currentRes = $this->akreditasiService->getProject($id);
            $currentData = $currentRes->successful() ? ($currentRes->json('data') ?? $currentRes->json() ?? []) : [];
            if (empty($currentData) || empty($currentData['nama_project'])) {
                $allProjects = $this->extractData($this->akreditasiService->getProjects());
                foreach ($allProjects as $p) {
                    if ((string) ($p['id'] ?? '') === (string) $id) {
                        $currentData = $p;
                        break;
                    }
                }
            }

            $payload = [
                'id_prodi' => (int) ($currentData['id_prodi'] ?? 1),
                'id_versi_instrumen' => (int) ($currentData['id_versi_instrumen'] ?? 1),
                'id_jenjang' => (int) ($currentData['id_jenjang'] ?? 1),
                'id_jenis_instrumen' => (int) ($currentData['id_jenis_instrumen'] ?? 1),
                'nama_project' => $currentData['nama_project'] ?? 'Project Akreditasi',
                'tahun_target_submit' => (int) ($currentData['tahun_target_submit'] ?? date('Y')),
                'status_project' => $statusProject,
            ];

            if (! empty($validated['nomor_sk_akreditasi'])) {
                $payload['nomor_sk_akreditasi'] = $validated['nomor_sk_akreditasi'];
                $payload['nomor_sk'] = $validated['nomor_sk_akreditasi'];
            }
            if (! empty($validated['tanggal_verifikasi_akreditasi'])) {
                $payload['tanggal_verifikasi_akreditasi'] = $validated['tanggal_verifikasi_akreditasi'];
                $payload['tanggal_sk'] = $validated['tanggal_verifikasi_akreditasi'];
            }

            $response = $this->akreditasiService->updateProjectStatus($id, $payload);

            $statusLabels = [
                'Draft' => 'Draft',
                'On Progress' => 'On Progress',
                'Pending' => 'Pending',
                'Cancel' => 'Cancel',
                'Selesai' => 'Selesai',
            ];
            $label = $statusLabels[$statusProject] ?? $statusProject;

            if ($request->wantsJson() || $request->ajax()) {
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Status project berhasil diubah menjadi {$label}.",
                        'data' => $payload,
                    ]);
                }

                $apiErrors = $response->json('errors');
                $apiMessage = $response->json('message') ?? 'Gagal memperbarui status project di server.';
                if (! empty($apiErrors)) {
                    $apiMessage .= ' Detail: '.implode(' ', array_map(
                        fn ($msgs) => is_array($msgs) ? implode(' ', $msgs) : $msgs,
                        $apiErrors
                    ));
                }

                return response()->json([
                    'success' => false,
                    'message' => $apiMessage,
                ], $response->status() ?: 400);
            }

            if ($response->successful()) {
                return redirect()->back()->with('success', "Status project berhasil diubah menjadi {$label}.");
            }

            $apiErrors = $response->json('errors');
            $apiMessage = $response->json('message') ?? 'Gagal memperbarui status project.';
            if (! empty($apiErrors)) {
                $apiMessage .= ' Detail: '.implode(' ', array_map(
                    fn ($msgs) => is_array($msgs) ? implode(' ', $msgs) : $msgs,
                    $apiErrors
                ));
            }

            return redirect()->back()->with('error', $apiMessage);
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: '.$e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    /**
     * Pengecekan kesiapan submit (Readiness Check) project akreditasi.
     */
    public function projectReadiness(Request $request, $id): JsonResponse
    {
        try {
            $response = $this->akreditasiService->getProjectReadiness($id);

            if (in_array($response->status(), [401, 403, 404], true)) {
                $message = match ($response->status()) {
                    401 => 'Sesi Anda telah berakhir. Silakan masuk kembali.',
                    403 => 'Anda tidak memiliki akses ke data readiness project ini.',
                    404 => 'Project atau data readiness tidak ditemukan.',
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], $response->status());
            }

            if ($response->successful()) {
                $payload = $response->json();
                $readinessData = $payload['data'] ?? null;
                if (($payload['success'] ?? false) !== true || ! is_array($readinessData)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Respons server tidak memuat data readiness yang valid.',
                    ], 502);
                }

                $readinessData = $this->normalizeProjectReadinessData($readinessData);
                if ($readinessData === null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format data readiness dari server tidak valid.',
                    ], 502);
                }

                $indicators = $this->extractData($this->akreditasiService->getIndikator());
                $picAssignments = $this->loadProjectPicAssignments($id, is_array($indicators) ? $indicators : []);

                return response()->json([
                    'success' => true,
                    'message' => $payload['message'] ?? 'Data kesiapan submit project berhasil dimuat.',
                    'data' => $this->mergeReadinessPicData($readinessData, $picAssignments),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data readiness dari server.',
            ], 502);
        } catch (\Throwable $e) {
            Log::error('Kaprodi readiness request failed', [
                'project_id' => (string) $id,
                'error_type' => get_debug_type($e),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data readiness.',
            ], 500);
        }
    }

    /**
     * Normalize the upstream critical-indicator readiness response for the modal.
     *
     * @param  array<string, mixed>  $readinessData
     * @return array<string, mixed>|null
     */
    protected function normalizeProjectReadinessData(array $readinessData): ?array
    {
        if (isset($readinessData['indicators']) && is_array($readinessData['indicators'])) {
            $readinessData['total_indikator'] ??= count($readinessData['indicators']);
            $readinessData['scope_label'] = 'Butir indikator kritis dari endpoint readiness';
            $readinessData['data_available'] = true;
            $readinessData['indicator_list_is_partial'] = false;

            return $readinessData;
        }

        $unreadyIndicators = $readinessData['indikator_belum_siap'] ?? null;
        $criticalIndicatorCount = $readinessData['total_indikator_kritis'] ?? null;
        if (! is_array($unreadyIndicators) || ! is_numeric($criticalIndicatorCount)) {
            return null;
        }

        $criticalIndicatorCount = (int) $criticalIndicatorCount;
        if ($criticalIndicatorCount < 0 || count($unreadyIndicators) > $criticalIndicatorCount) {
            return null;
        }

        $indicators = [];
        foreach ($unreadyIndicators as $indicator) {
            if (! is_array($indicator) || ! is_array($indicator['belum_terpenuhi'] ?? null)) {
                return null;
            }

            $unmetRequirements = array_values(array_filter(
                $indicator['belum_terpenuhi'],
                fn (mixed $requirement): bool => is_string($requirement),
            ));
            if (count($unmetRequirements) !== count($indicator['belum_terpenuhi'])) {
                return null;
            }
            if (array_diff($unmetRequirements, ['pic', 'link_evidence', 'skor_reviewer']) !== []) {
                return null;
            }

            $hasPic = ! in_array('pic', $unmetRequirements, true);
            $hasEvidence = ! in_array('link_evidence', $unmetRequirements, true);
            $hasAssessment = ! in_array('skor_reviewer', $unmetRequirements, true);
            $missingItems = [];
            if (! $hasPic) {
                $missingItems[] = 'Belum memiliki PIC';
            }
            if (! $hasEvidence) {
                $missingItems[] = 'Belum ada link bukti';
            }
            if (! $hasAssessment) {
                $missingItems[] = 'Belum dinilai oleh reviewer';
            }

            $indicators[] = [
                'id_indikator' => $indicator['id_indikator'] ?? null,
                'kode_indikator' => $indicator['kode_indikator'] ?? null,
                'deskripsi_indikator' => $indicator['deskripsi_indikator'] ?? null,
                'has_pic' => $hasPic,
                'has_evidence' => $hasEvidence,
                'has_assessment' => $hasAssessment,
                'is_ready' => $missingItems === [],
                'missing_items' => $missingItems,
            ];
        }

        $readinessData['indicators'] = $indicators;
        $readinessData['total_indikator'] = $criticalIndicatorCount;
        $readinessData['scope_label'] = 'Butir indikator kritis dari endpoint readiness';
        $readinessData['data_available'] = true;
        $readinessData['indicator_list_is_partial'] = true;

        $unreadyCount = count(array_filter($indicators, fn (array $indicator): bool => ! $indicator['is_ready']));
        $readyCount = $criticalIndicatorCount - $unreadyCount;
        $readinessData['ready_indikator'] = $readyCount;
        $readinessData['unready_indikator'] = $unreadyCount;
        $readinessData['percentage'] = $criticalIndicatorCount > 0
            ? round(($readyCount / $criticalIndicatorCount) * 100)
            : 0;
        $readinessData['missing_pic_count'] = count(array_filter($indicators, fn (array $indicator): bool => ! $indicator['has_pic']));
        $readinessData['missing_evidence_count'] = count(array_filter($indicators, fn (array $indicator): bool => ! $indicator['has_evidence']));
        $readinessData['missing_assessment_count'] = count(array_filter($indicators, fn (array $indicator): bool => ! $indicator['has_assessment']));
        $readinessData['is_ready'] = $criticalIndicatorCount > 0 && $unreadyCount === 0;

        return $readinessData;
    }

    /**
     * Load distinct PIC assignments scoped to one project.
     *
     * @param  array<int, array<string, mixed>>  $indicators
     * @return array{available: bool, by_id: array<string, array<string, mixed>>, by_code: array<string, array<string, mixed>>, count: int}
     */
    protected function loadProjectPicAssignments(int|string $projectId, array $indicators = []): array
    {
        $sessionAssignments = session("plotting_assignments_{$projectId}", []);
        $hasSessionPic = is_array($sessionAssignments) && collect($sessionAssignments)->contains(
            fn (mixed $assignment): bool => is_array($assignment) && ! empty($assignment['pic']),
        );

        if ($indicators === [] && $hasSessionPic) {
            $indicators = $this->extractData($this->akreditasiService->getIndikator());
            $indicators = is_array($indicators) ? $indicators : [];
        }

        $indicatorIdByCode = [];
        $indicatorCodeById = [];
        foreach ($indicators as $indicator) {
            if (! is_array($indicator)) {
                continue;
            }

            $indicatorId = $indicator['id'] ?? $indicator['id_indikator'] ?? null;
            $indicatorCode = strtolower(trim((string) ($indicator['kode_indikator'] ?? $indicator['kode'] ?? '')));
            if ($indicatorId !== null && $indicatorCode !== '') {
                $indicatorIdByCode[$indicatorCode] = (string) $indicatorId;
                $indicatorCodeById[(string) $indicatorId] = $indicatorCode;
            }
        }

        $byId = [];
        $byCode = [];
        $mappingAvailable = false;

        try {
            $response = $this->akreditasiService->getMappingTim(['project_id' => $projectId]);
            $payload = $response->json();
            $mappingData = is_array($payload) ? ($payload['data'] ?? null) : null;
            $mappingAvailable = $response->successful()
                && ($payload['success'] ?? false) === true
                && is_array($mappingData);
            if ($mappingAvailable) {
                $mappings = $mappingData;
                if (is_array($mappings) && isset($mappings['data']) && is_array($mappings['data'])) {
                    $mappings = $mappings['data'];
                } elseif (is_array($mappings) && isset($mappings['mapping_tim']) && is_array($mappings['mapping_tim'])) {
                    $mappings = $mappings['mapping_tim'];
                } elseif (is_array($mappings) && isset($mappings['assignments']) && is_array($mappings['assignments'])) {
                    $mappings = $mappings['assignments'];
                }

                foreach (is_array($mappings) ? $mappings : [] as $mapping) {
                    if (! is_array($mapping) || strtoupper(trim((string) ($mapping['role'] ?? ''))) !== 'PIC') {
                        continue;
                    }

                    $mappingProjectId = $mapping['id_project'] ?? $mapping['project_id'] ?? $mapping['project']['id'] ?? null;
                    if ($mappingProjectId !== null && (string) $mappingProjectId !== (string) $projectId) {
                        continue;
                    }

                    $indicator = is_array($mapping['indikator'] ?? null) ? $mapping['indikator'] : [];
                    $indicatorId = $mapping['id_indikator'] ?? $indicator['id'] ?? null;
                    $indicatorCode = strtolower(trim((string) ($mapping['kode_indikator'] ?? $indicator['kode_indikator'] ?? $indicator['kode'] ?? '')));
                    if ($indicatorId === null && $indicatorCode !== '') {
                        $indicatorId = $indicatorIdByCode[$indicatorCode] ?? null;
                    }

                    $assignment = [
                        'id_indikator' => $indicatorId === null ? null : (string) $indicatorId,
                        'kode_indikator' => $indicatorCode,
                        'pic_name' => $mapping['user']['nama'] ?? $mapping['user_name'] ?? null,
                        'pic_user_id' => $mapping['id_user'] ?? $mapping['user_id'] ?? $mapping['user']['id'] ?? null,
                    ];
                    if ($indicatorId !== null) {
                        $key = (string) $indicatorId;
                        $byId[$key] = $assignment;
                        $indicatorCode = $indicatorCode ?: ($indicatorCodeById[$key] ?? '');
                    }
                    if ($indicatorCode !== '') {
                        $assignment['kode_indikator'] = $indicatorCode;
                        $byCode[$indicatorCode] = $assignment;
                    }
                }
            }
        } catch (\Throwable $e) {
            $mappingAvailable = false;
            Log::warning('Kaprodi PIC mapping request failed', [
                'project_id' => (string) $projectId,
                'error_type' => get_debug_type($e),
            ]);
        }

        foreach (is_array($sessionAssignments) ? $sessionAssignments : [] as $code => $assignment) {
            if (! is_array($assignment) || empty($assignment['pic'])) {
                continue;
            }

            $indicatorCode = strtolower(trim((string) $code));
            $indicatorId = $assignment['id_indikator'] ?? $indicatorIdByCode[$indicatorCode] ?? null;
            $picAssignment = [
                'id_indikator' => $indicatorId === null ? null : (string) $indicatorId,
                'kode_indikator' => $indicatorCode,
                'pic_name' => $assignment['pic'],
                'pic_user_id' => $assignment['pic_user_id'] ?? null,
            ];
            if ($indicatorId !== null) {
                $byId[(string) $indicatorId] = $picAssignment;
            }
            if ($indicatorCode !== '') {
                $byCode[$indicatorCode] = $picAssignment;
            }
        }

        $unresolvedCodeCount = count(array_filter(
            $byCode,
            fn (array $assignment): bool => $assignment['id_indikator'] === null,
        ));

        return [
            'available' => $mappingAvailable || $hasSessionPic,
            'by_id' => $byId,
            'by_code' => $byCode,
            'count' => count($byId) + $unresolvedCodeCount,
        ];
    }

    /**
     * Reconcile readiness PIC state against project-scoped plotting assignments.
     *
     * @param  array<string, mixed>  $backendData
     * @param  array{available: bool, by_id: array<string, array<string, mixed>>, by_code: array<string, array<string, mixed>>, count: int}  $picAssignments
     * @return array<string, mixed>
     */
    protected function mergeReadinessPicData(array $backendData, array $picAssignments): array
    {
        $indicators = $backendData['indicators'] ?? [];
        if (! $picAssignments['available'] || ! is_array($indicators)) {
            $backendData['pic_data_available'] = false;

            return $backendData;
        }

        $normalizedIndicators = [];
        $missingPicCount = 0;
        $missingEvidenceCount = 0;
        $missingAssessmentCount = 0;

        foreach ($indicators as $indicator) {
            if (! is_array($indicator)) {
                continue;
            }

            $indicatorId = (string) ($indicator['id_indikator'] ?? $indicator['id'] ?? $indicator['indikator']['id'] ?? '');
            $indicatorCode = strtolower(trim((string) ($indicator['kode_indikator'] ?? $indicator['indikator']['kode_indikator'] ?? '')));
            $picAssignment = ($indicatorId !== '' ? ($picAssignments['by_id'][$indicatorId] ?? null) : null)
                ?? ($indicatorCode !== '' ? ($picAssignments['by_code'][$indicatorCode] ?? null) : null);
            $hasPic = is_array($picAssignment);

            $indicator['has_pic'] = $hasPic;
            $indicator['pic_name'] = $hasPic ? ($picAssignment['pic_name'] ?? null) : null;
            $missingItems = array_values(array_filter(
                is_array($indicator['missing_items'] ?? null) ? $indicator['missing_items'] : [],
                fn (mixed $item): bool => is_string($item) && $item !== 'Belum memiliki PIC',
            ));

            if (! $hasPic) {
                $missingItems[] = 'Belum memiliki PIC';
                $missingPicCount++;
            }
            if (empty($indicator['has_evidence'])) {
                $missingItems[] = 'Belum ada link bukti';
                $missingEvidenceCount++;
            }
            if (empty($indicator['has_assessment'])) {
                $missingItems[] = 'Belum dinilai oleh reviewer';
                $missingAssessmentCount++;
            }

            $indicator['missing_items'] = array_values(array_unique($missingItems));
            $indicator['is_ready'] = $missingItems === [];
            $normalizedIndicators[] = $indicator;
        }

        $total = max(0, (int) ($backendData['total_indikator'] ?? count($normalizedIndicators)));
        $unreadyCount = count(array_filter($normalizedIndicators, fn (array $indicator): bool => empty($indicator['is_ready'])));
        if (! empty($backendData['indicator_list_is_partial'])) {
            $unreadyCount = min($total, $unreadyCount);
            $readyCount = $total - $unreadyCount;
        } else {
            $readyCount = count(array_filter($normalizedIndicators, fn (array $indicator): bool => ! empty($indicator['is_ready'])));
            $unreadyCount = max(0, $total - $readyCount);
        }

        $backendData['indicators'] = $normalizedIndicators;
        $backendData['total_indikator'] = $total;
        $backendData['ready_indikator'] = $readyCount;
        $backendData['unready_indikator'] = $unreadyCount;
        $backendData['percentage'] = $total > 0 ? round(($readyCount / $total) * 100) : 0;
        $backendData['missing_pic_count'] = $missingPicCount;
        $backendData['missing_evidence_count'] = $missingEvidenceCount;
        $backendData['missing_assessment_count'] = $missingAssessmentCount;
        $backendData['is_ready'] = $unreadyCount === 0 && $total > 0;
        $backendData['pic_data_available'] = true;

        return $backendData;
    }

    /**
     * Simulasi kuantitatif skor SDM Dosen LAM-INFOKOM untuk project akreditasi.
     */
    public function scoringSimulation(Request $request, $id): JsonResponse
    {
        try {
            $response = $this->akreditasiService->getScoringSimulation($id);

            if ($response->successful() && ! empty($response->json('data'))) {
                return response()->json($response->json());
            }

            $fallback = $this->calculateScoringSimulation($id);

            return response()->json([
                'success' => true,
                'message' => 'Simulasi perhitungan skor kuantitatif SDM dosen berhasil dimuat.',
                'data' => $fallback,
            ]);
        } catch (\Throwable $e) {
            $fallback = $this->calculateScoringSimulation($id);

            return response()->json([
                'success' => true,
                'message' => 'Simulasi perhitungan skor kuantitatif SDM dosen berhasil dimuat (fallback).',
                'data' => $fallback,
            ]);
        }
    }

    /**
     * Helper simulasi perhitungan skor kuantitatif SDM dosen LAM-INFOKOM.
     *
     * @return array<string, mixed>
     */
    protected function calculateScoringSimulation(int|string $id): array
    {
        $projectRes = $this->akreditasiService->getProject($id);
        $projectData = $projectRes->successful() ? ($projectRes->json('data') ?? []) : ['id' => $id, 'nama_project' => 'Project Akreditasi'];

        // Cek jumlah dosen homebase dari data akademik atau default ideal
        $dosenCount = 8;
        try {
            $rawDosen = $this->extractData($this->akademikService->getDosen());
            if (! empty($rawDosen) && is_array($rawDosen)) {
                $dosenCount = count($rawDosen);
            }
        } catch (\Throwable) {
            $dosenCount = 8;
        }

        // Ketentuan LAM-INFOKOM: Minimum dosen homebase program studi adalah 5 orang
        $minimumNotMet = $dosenCount < 5;

        // 1. RMD (Rasio Mahasiswa : Dosen Tetap)
        // Standar LAM-INFOKOM: Rasio ideal 1:15 - 1:25 untuk sains & teknologi (skor 4.0)
        $rasioMhsDosen = 18.5;
        $skorRmd = $minimumNotMet ? 1.5 : 3.85;

        // 2. PDS3 (Persentase Dosen Tetap berkualifikasi Doktor S3)
        // Standar LAM-INFOKOM: >= 50% skor 4.0; 30% - 49% skor 3.5; < 30% skor 2.5
        $persenS3 = 41.7;
        $skorPds3 = $minimumNotMet ? 1.0 : 3.50;

        // 3. PJLK (Persentase Jabatan Lektor Kepala & Guru Besar)
        // Standar LAM-INFOKOM: >= 40% skor 4.0; 25% - 39% skor 3.4; < 25% skor 2.5
        $persenLkGb = 33.3;
        $skorPjlk = $minimumNotMet ? 1.0 : 3.40;

        // Total Nilai Akhir (Total NA) bobot gabungan
        $totalNa = round(($skorRmd * 0.35) + ($skorPds3 * 0.35) + ($skorPjlk * 0.30), 2);

        return [
            'project_id' => $id,
            'nama_project' => $projectData['nama_project'] ?? 'Project Akreditasi',
            'is_simulated' => true,
            'minimum_not_met' => $minimumNotMet,
            'jumlah_dosen_homebase' => $dosenCount,
            'total_na' => $totalNa,
            'skor_per_subkriteria' => [
                'rmd' => [
                    'kode' => 'RMD',
                    'nama' => 'Rasio Mahasiswa : Dosen Tetap',
                    'nilai' => '1 : '.$rasioMhsDosen,
                    'rasio_angka' => $rasioMhsDosen,
                    'skor' => $skorRmd,
                    'skala_maks' => 4.0,
                    'status' => $skorRmd >= 3.5 ? 'Sangat Baik' : 'Cukup',
                    'keterangan' => 'Rasio ideal LAM-INFOKOM sains & teknologi (1:15 s.d. 1:25)',
                ],
                'pds3' => [
                    'kode' => 'PDS3',
                    'nama' => 'Persentase Dosen Kualifikasi Doktor (S3)',
                    'nilai' => $persenS3.'%',
                    'persen_angka' => $persenS3,
                    'skor' => $skorPds3,
                    'skala_maks' => 4.0,
                    'status' => $skorPds3 >= 3.5 ? 'Memenuhi Syarat Unggul' : 'Baik',
                    'keterangan' => '5 dari 12 dosen homebase berpendidikan S3',
                ],
                'pjlk' => [
                    'kode' => 'PJLK',
                    'nama' => 'Jabatan Akademik Lektor Kepala & Guru Besar',
                    'nilai' => $persenLkGb.'%',
                    'persen_angka' => $persenLkGb,
                    'skor' => $skorPjlk,
                    'skala_maks' => 4.0,
                    'status' => $skorPjlk >= 3.0 ? 'Memenuhi Syarat' : 'Perlu Peningkatan',
                    'keterangan' => '4 dari 12 dosen menjabat Lektor Kepala atau Guru Besar',
                ],
            ],
        ];
    }

    /**
     * Helper untuk menghitung kesiapan indikator kritis jika endpoint backend /readiness belum aktif.
     *
     * @return array<string, mixed>
     */
    protected function calculateProjectReadiness(int|string $id): array
    {
        $projectRes = $this->akreditasiService->getProject($id);
        $projectData = $projectRes->successful() ? ($projectRes->json('data') ?? []) : ['id' => $id, 'nama_project' => 'Project Akreditasi'];

        $rawIndikator = $this->extractData($this->akreditasiService->getIndikator());
        $rawIndikator = is_array($rawIndikator) ? $rawIndikator : [];
        $picAssignments = $this->loadProjectPicAssignments($id, $rawIndikator);

        $evidenceList = [];
        try {
            $evidenceList = $this->extractData($this->akreditasiService->getEvidenceSpmi(['project_id' => $id]));
        } catch (\Throwable) {
        }

        $assessmentList = [];
        try {
            $assessmentList = $this->extractData($this->akreditasiService->getSelfAssessment(['project_id' => $id]));
        } catch (\Throwable) {
        }

        $evidenceByIndikator = [];
        foreach ($evidenceList as $ev) {
            $indId = $ev['id_indikator'] ?? null;
            if ($indId && ! empty($ev['link_dokumen'] ?? ($ev['file_path'] ?? null))) {
                $evidenceByIndikator[$indId] = $ev['link_dokumen'] ?? $ev['file_path'];
            }
        }

        $scoreByIndikator = [];
        foreach ($assessmentList as $as) {
            $indId = $as['id_indikator'] ?? null;
            if ($indId && isset($as['skor'])) {
                $scoreByIndikator[$indId] = $as['skor'];
            }
        }

        $indicators = [];
        $unreadyCount = 0;
        $missingPicCount = 0;
        $missingEvidenceCount = 0;
        $missingAssessmentCount = 0;

        foreach ($rawIndikator as $item) {
            $indId = $item['id'] ?? $item['id_indikator'] ?? 0;
            $kode = $item['kode_indikator'] ?? 'IND-'.$indId;
            $deskripsi = $item['deskripsi_indikator'] ?? ($item['nama_indikator'] ?? 'Indikator Akreditasi');

            $picAssignment = $picAssignments['by_id'][(string) $indId]
                ?? $picAssignments['by_code'][strtolower(trim((string) $kode))]
                ?? null;
            $hasPic = is_array($picAssignment);
            $hasEvidence = isset($evidenceByIndikator[$indId]);
            $hasAssessment = isset($scoreByIndikator[$indId]);

            $missingItems = [];
            if (! $hasPic) {
                $missingItems[] = 'Belum memiliki PIC';
                $missingPicCount++;
            }
            if (! $hasEvidence) {
                $missingItems[] = 'Belum ada link bukti';
                $missingEvidenceCount++;
            }
            if (! $hasAssessment) {
                $missingItems[] = 'Belum dinilai oleh reviewer';
                $missingAssessmentCount++;
            }

            $isReady = empty($missingItems);
            if (! $isReady) {
                $unreadyCount++;
            }

            $indicators[] = [
                'id_indikator' => $indId,
                'kode_indikator' => $kode,
                'deskripsi_indikator' => $deskripsi,
                'has_pic' => $hasPic,
                'pic_name' => $picAssignment['pic_name'] ?? null,
                'has_evidence' => $hasEvidence,
                'evidence_link' => $evidenceByIndikator[$indId] ?? null,
                'has_assessment' => $hasAssessment,
                'score' => $scoreByIndikator[$indId] ?? null,
                'is_ready' => $isReady,
                'missing_items' => $missingItems,
            ];
        }

        $total = count($indicators);
        $readyCount = $total - $unreadyCount;
        $percentage = $total > 0 ? round(($readyCount / $total) * 100) : 0;
        $isAllReady = $unreadyCount === 0 && $total > 0;

        return [
            'project_id' => $id,
            'nama_project' => $projectData['nama_project'] ?? 'Project Akreditasi',
            'is_ready' => $isAllReady,
            'total_indikator' => $total,
            'ready_indikator' => $readyCount,
            'unready_indikator' => $unreadyCount,
            'percentage' => $percentage,
            'missing_pic_count' => $missingPicCount,
            'missing_evidence_count' => $missingEvidenceCount,
            'missing_assessment_count' => $missingAssessmentCount,
            'indicators' => $indicators,
            'data_available' => $rawIndikator !== [],
            'pic_data_available' => $picAssignments['available'],
            'scope_label' => 'Seluruh indikator data master (fallback, bukan subset kritis)',
        ];
    }

    /**
     * Hapus project akreditasi dari API.
     */
    public function destroyProject($id): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteProject($id);
            if ($response->successful()) {
                return redirect()->route('kaprodi.project')->with('success', 'Project akreditasi berhasil dihapus.');
            }

            return redirect()->route('kaprodi.project')->with('error', $response->json('message') ?? 'Gagal menghapus project.');
        } catch (\Throwable $e) {
            return redirect()->route('kaprodi.project')->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail project akreditasi beserta matriks kriteria & indikator.
     */
    public function projectDetail(Request $request, $id = null): View
    {
        $compositeOverview = [];
        $apiError = null;
        $apiStatus = null;
        $project = [];

        if (! $id) {
            $allProjects = $this->extractData($this->akreditasiService->getProjects());
            $id = $allProjects[0]['id'] ?? null;
        }

        if ($id) {
            try {
                $response = $this->akreditasiService->getProjectCompositeOverview($id);
                if ($response->successful()) {
                    $payload = $response->json();
                    $overviewData = is_array($payload) ? ($payload['data'] ?? null) : null;

                    if (is_array($overviewData) && ($payload['success'] ?? false) === true) {
                        $compositeOverview = $overviewData;
                        $project = is_array($overviewData['profil_project'] ?? null) ? $overviewData['profil_project'] : [];
                    } else {
                        $apiStatus = 502;
                        $apiError = 'Server mengirim data ringkasan kosong atau dengan format yang tidak valid.';
                    }
                } else {
                    $apiStatus = $response->status();
                    $apiError = $this->projectOverviewErrorMessage($apiStatus);
                }
            } catch (\Throwable $e) {
                $apiStatus = 0;
                $apiError = 'Tidak dapat terhubung ke server. Periksa koneksi jaringan lalu coba kembali.';
            }

        } else {
            $apiStatus = 404;
            $apiError = 'Project akreditasi belum tersedia.';
        }

        $instrumenList = $apiError === null ? $this->getFormattedInstrumenList(false) : [];
        $picSummary = null;
        $picAssignments = null;

        if ($apiError === null && is_array($compositeOverview['progress'] ?? null)) {
            $picAssignments = $this->loadProjectPicAssignments($id);
            $totalIndicators = $compositeOverview['progress']['total_indikator'] ?? null;
            if ($picAssignments['available']) {
                $assignedIndicators = $picAssignments['count'];
                if (is_numeric($totalIndicators)) {
                    $assignedIndicators = min((int) $totalIndicators, $assignedIndicators);
                }
                $picSummary = [
                    'assigned' => $assignedIndicators,
                    'unassigned' => is_numeric($totalIndicators)
                        ? max(0, (int) $totalIndicators - $assignedIndicators)
                        : null,
                ];
            }
        }
        $canEditNarrative = $id !== null
            && $apiError === null
            && $this->canEditProjectNarrative($id, $picAssignments);

        return view('pages.kaprodi.project-detail', [
            'project' => $project,
            'projectId' => $id,
            'compositeOverview' => $compositeOverview,
            'apiError' => $apiError,
            'apiStatus' => $apiStatus,
            'picSummary' => $picSummary,
            'canEditNarrative' => $canEditNarrative,
            'instrumenList' => $instrumenList,
            'dataSummaryUrl' => $id !== null
                ? route('kaprodi.project.data-summary', $id)
                : null,
        ]);
    }

    protected function projectOverviewErrorMessage(int $status): string
    {
        return match ($status) {
            401 => 'Sesi Anda telah berakhir. Silakan masuk kembali.',
            403 => 'Anda tidak memiliki akses untuk melihat project ini.',
            404 => 'Project akreditasi tidak ditemukan.',
            408, 504 => 'Server terlalu lama merespons. Silakan coba kembali.',
            default => 'Ringkasan project tidak dapat dimuat (HTTP '.$status.'). Silakan coba kembali.',
        };
    }

    public function compositeOverview(Request $request, int|string $id): JsonResponse
    {
        try {
            $response = $this->akreditasiService->getProjectCompositeOverview($id);
            $payload = $response->json();

            if (! $response->successful()) {
                return response()->json([
                    'message' => match ($response->status()) {
                        401 => 'Sesi Anda telah berakhir.',
                        403 => 'Anda tidak memiliki akses.',
                        404 => 'Project tidak ditemukan.',
                        408, 504 => 'Server terlalu lama merespons.',
                        default => 'Ringkasan project gagal dimuat.',
                    },
                ], $response->status());
            }

            return response()->json(is_array($payload) ? $payload : ['data' => []]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Tidak dapat terhubung ke server.',
            ], 503);
        }
    }

    public function projectDataSummary(int|string $projectId): JsonResponse
    {
        try {
            $response = $this->akreditasiService->getProjectDataSummary($projectId);
            $payload = $response->json();

            if (! $response->successful()) {
                return response()->json([
                    'message' => is_array($payload)
                        ? ($payload['message'] ?? 'Ringkasan data project tidak dapat dimuat.')
                        : 'Ringkasan data project tidak dapat dimuat.',
                ], $response->status());
            }

            if (! is_array($payload)) {
                return response()->json(['message' => 'Server mengirim format ringkasan data yang tidak valid.'], 502);
            }

            return response()->json([
                'success' => true,
                'data' => $this->normalizeProjectDataSummary($payload),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Project data summary request failed.', [
                'project_id' => $projectId,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Tidak dapat terhubung ke server untuk memuat ringkasan data.',
            ], 503);
        }
    }

    public function narasiTemplates(Request $request): JsonResponse
    {
        try {
            $response = $this->akreditasiService->getNarasiTemplates($request->query());
            $payload = $response->json();

            if (! $response->successful()) {
                return response()->json(['message' => 'Template narasi tidak dapat dimuat.'], $response->status());
            }

            return response()->json(is_array($payload) ? $payload : ['data' => []]);
        } catch (\Throwable) {
            return response()->json(['message' => 'Tidak dapat terhubung ke server.'], 503);
        }
    }

    public function storeNarasiTemplate(Request $request): JsonResponse
    {
        try {
            $response = $this->akreditasiService->createNarasiTemplate($request->except(['_token', '_method']));
            $payload = $response->json();

            if (! $response->successful()) {
                return response()->json([
                    'message' => is_array($payload) ? ($payload['message'] ?? 'Template narasi gagal dibuat.') : 'Template narasi gagal dibuat.',
                ], $response->status());
            }

            return response()->json(is_array($payload) ? $payload : ['message' => 'Template narasi berhasil dibuat.']);
        } catch (\Throwable) {
            return response()->json(['message' => 'Tidak dapat terhubung ke server.'], 503);
        }
    }

    public function updateNarasiTemplate(Request $request, int|string $id): JsonResponse
    {
        try {
            $response = $this->akreditasiService->updateNarasiTemplate($id, $request->except(['_token', '_method']));
            $payload = $response->json();

            if (! $response->successful()) {
                return response()->json([
                    'message' => is_array($payload) ? ($payload['message'] ?? 'Template narasi gagal diperbarui.') : 'Template narasi gagal diperbarui.',
                ], $response->status());
            }

            return response()->json(is_array($payload) ? $payload : ['message' => 'Template narasi berhasil diperbarui.']);
        } catch (\Throwable) {
            return response()->json(['message' => 'Tidak dapat terhubung ke server.'], 503);
        }
    }

    public function subkriteriaNarrativeTemplate(int|string $projectId, int|string $subkriteriaId): JsonResponse
    {
        try {
            $response = $this->akreditasiService->getSubkriteriaNarrativeTemplate($projectId, $subkriteriaId);
            $payload = $response->json();

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'Template narasi tidak dapat dimuat.',
                ], $response->status());
            }

            return response()->json(is_array($payload) ? $payload : ['data' => []]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Tidak dapat terhubung ke server.'], 503);
        }
    }

    public function updateSubkriteriaNarrative(Request $request, int|string $projectId, int|string $subkriteriaId): JsonResponse
    {
        if (! $this->canEditProjectNarrative($projectId)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah narasi project ini.'], 403);
        }

        $request->validate([
            'draf_narasi' => ['required', 'string'],
        ]);
        $payload = $request->except(['_token', '_method']);

        try {
            $response = $this->akreditasiService->updateSubkriteriaNarrative($projectId, $subkriteriaId, $payload);
            $body = $response->json();

            if (! $response->successful()) {
                return response()->json([
                    'message' => is_array($body) ? ($body['message'] ?? 'Narasi gagal disimpan.') : 'Narasi gagal disimpan.',
                ], $response->status());
            }

            return response()->json(is_array($body) ? $body : ['message' => 'Narasi berhasil disimpan.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Tidak dapat terhubung ke server.'], 503);
        }
    }

    /**
     * Tampilkan halaman Plotting Tim Akreditasi.
     */
    public function plotting(Request $request): View
    {
        $projects = $this->extractData($this->akreditasiService->getProjects());
        $rawKriteria = $this->extractData($this->akreditasiService->getKriteria());
        $rawIndikator = $this->extractData($this->akreditasiService->getIndikator());

        $projectId = (string) $request->query('project_id', $projects[0]['id'] ?? '1');

        $mappingTim = [];
        try {
            $mappingTim = $this->extractData($this->akreditasiService->getMappingTim(['project_id' => $projectId]));
        } catch (\Throwable $e) {
            $mappingTim = [];
        }
        if (is_array($mappingTim) && isset($mappingTim['data']) && is_array($mappingTim['data'])) {
            $mappingTim = $mappingTim['data'];
        }
        $mappingTim = is_array($mappingTim) ? array_values(array_filter(
            $mappingTim,
            fn (mixed $mapping): bool => is_array($mapping)
                && (($mapping['id_project'] ?? $mapping['project_id'] ?? $mapping['project']['id'] ?? null) === null
                    || (string) ($mapping['id_project'] ?? $mapping['project_id'] ?? $mapping['project']['id']) === $projectId),
        )) : [];

        // Ambil data dosen dari API Kelompok 2 Tridharma, fallback ke GET /external/dosen, lalu API Akademik
        $dosenList = [];
        $dosenSources = [
            fn () => $this->tridharmaService->getDosenList(['per_page' => 100]),
            fn () => $this->akreditasiService->getExternalDosen(['per_page' => 100]),
            fn () => $this->akademikService->getDosenList(['per_page' => 100]),
        ];

        foreach ($dosenSources as $fetchDosen) {
            try {
                $res = $fetchDosen();
                $records = $this->extractDosenRecordsFromResponse($res);
                $normalized = $this->normalizePlottingDosenList($records);
                if (! empty($normalized)) {
                    $dosenList = $normalized;
                    break;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        if (empty($dosenList)) {
            $dosenList = [
                ['id' => 1, 'nama' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.', 'nidn' => '0026047903', 'bidang_keahlian' => 'Artificial Intelligence & Machine Learning', 'jabatan' => 'Guru Besar'],
                ['id' => 2, 'nama' => 'KUWAT SANTOSO M.Kom.', 'nidn' => '0419078404', 'bidang_keahlian' => 'Rekayasa Perangkat Lunak & Sistem Terdistribusi', 'jabatan' => 'Lektor'],
                ['id' => 3, 'nama' => 'Dr. LILIEK TRIYONO S.T., M.Kom.', 'nidn' => '0620048402', 'bidang_keahlian' => 'Jaringan Komputer & Cyber Security', 'jabatan' => 'Lektor Kepala'],
                ['id' => 4, 'nama' => 'MARDIYONO S.Kom., M.Sc.', 'nidn' => '0011037401', 'bidang_keahlian' => 'Data Science & Big Data Analytics', 'jabatan' => 'Lektor'],
                ['id' => 5, 'nama' => 'SARNO S.T., M.T.', 'nidn' => '0028118705', 'bidang_keahlian' => 'Keamanan Siber & Kriptografi Terapan', 'jabatan' => 'Asisten Ahli'],
                ['id' => 6, 'nama' => 'SLAMET HANDOKO S.T., M.Kom.', 'nidn' => '0031129001', 'bidang_keahlian' => 'Tata Kelola TI & Manajemen Proyek', 'jabatan' => 'Asisten Ahli'],
                ['id' => 7, 'nama' => 'TRISNO CAHYO S.Pd., M.Cs.', 'nidn' => '0019048902', 'bidang_keahlian' => 'Web & Mobile Engineering', 'jabatan' => 'Asisten Ahli'],
                ['id' => 8, 'nama' => 'SUROSO S.Kom., M.Kom.', 'nidn' => '0022068603', 'bidang_keahlian' => 'Sistem Informasi Enterprise & Basis Data', 'jabatan' => 'Lektor'],
            ];
        }

        $apiAssignments = [];
        foreach ($mappingTim as $mapping) {
            $code = $mapping['indikator']['kode_indikator'] ?? null;
            $role = strtolower((string) ($mapping['role'] ?? ''));
            $userName = $mapping['user']['nama'] ?? null;
            if ($code && in_array($role, ['pj', 'pic'], true) && $userName) {
                $apiAssignments[$code] ??= ['pj' => null, 'pic' => null];
                $apiAssignments[$code][$role] = $userName;
            }
        }

        // ── Fitur Kaprodi Mandiri: Kaprodi dapat menugaskan dirinya sendiri sebagai PIC ──
        // Tambahkan akun Kaprodi yang sedang login ke dalam daftar dosen jika belum ada
        $currentUser = session('user', []);
        $kaprodiName = $currentUser['nama'] ?? ($currentUser['name'] ?? null);
        $kaprodiNidn = $currentUser['nidn'] ?? ($currentUser['nip'] ?? null);
        $kaprodiRole = strtolower((string) ($currentUser['role'] ?? ($currentUser['role_name'] ?? '')));

        if ($kaprodiName && str_contains($kaprodiRole, 'kaprodi')) {
            $alreadyInList = false;
            foreach ($dosenList as &$d) {
                $existingName = strtolower(trim((string) ($d['nama'] ?? '')));
                $cleanKaprodi = strtolower(trim($kaprodiName));
                if ($existingName === $cleanKaprodi
                    || str_contains($existingName, $cleanKaprodi)
                    || str_contains($cleanKaprodi, $existingName)) {
                    $alreadyInList = true;
                    $d['jabatan'] = 'Kaprodi TRK';
                    break;
                }
            }
            unset($d);

            if (! $alreadyInList) {
                array_unshift($dosenList, [
                    'id' => $currentUser['id'] ?? 0,
                    'nama' => $kaprodiName.' (Kaprodi)',
                    'nidn' => $kaprodiNidn ?? '-',
                    'bidang_keahlian' => 'Kaprodi — Butir Strategis',
                    'jabatan' => 'Ketua Program Studi',
                ]);
            }
        }

        // Ambil nama-nama dosen aktual untuk default assignment dinamis
        $dosenNames = array_values(array_filter(array_map(fn ($d) => $d['nama'] ?? null, $dosenList)));
        $d0 = $dosenNames[0] ?? 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.';
        $d1 = $dosenNames[1] ?? ($dosenNames[0] ?? 'KUWAT SANTOSO M.Kom.');
        $d2 = $dosenNames[2] ?? ($dosenNames[0] ?? 'Dr. LILIEK TRIYONO S.T., M.Kom.');
        $d3 = $dosenNames[3] ?? ($dosenNames[1] ?? 'MARDIYONO S.Kom., M.Sc.');
        $d4 = $dosenNames[4] ?? ($dosenNames[2] ?? 'SARNO S.T., M.T.');
        $d5 = $dosenNames[5] ?? ($dosenNames[3] ?? 'SLAMET HANDOKO S.T., M.Kom.');
        $d6 = $dosenNames[6] ?? ($dosenNames[0] ?? 'TRISNO CAHYO S.Pd., M.Cs.');
        $d7 = $dosenNames[7] ?? ($dosenNames[1] ?? 'SUROSO S.Kom., M.Kom.');

        // Mapping default awal menggunakan dosen aktual Tridharma (tanpa C.2/K2 & C.5/K5 karena dikelola Institusi/UPPS)
        $defaultAssignments = [
            'K1.1' => ['pj' => $d0, 'pic' => $d1],
            'K3.1' => ['pj' => $d1, 'pic' => $d4],
            'K3.2' => ['pj' => $d1, 'pic' => $d1],
            'K3.3' => ['pj' => null, 'pic' => null],
            'K3.4' => ['pj' => $d0, 'pic' => $d0],
            'K4.1' => ['pj' => $d2, 'pic' => $d3],
            'K4.2' => ['pj' => $d2, 'pic' => $d5],
            'K6.1' => ['pj' => $d3, 'pic' => $d4],
            'K7.1' => ['pj' => $d2, 'pic' => $d5],
            'K8.1' => ['pj' => $d1, 'pic' => $d6],
            'K9.1' => ['pj' => $d0, 'pic' => $d7],
        ];

        // Jika mapping dari API ada, sinkronkan
        if (! empty($mappingTim) && is_array($mappingTim)) {
            foreach ($mappingTim as $m) {
                $code = $m['indikator']['kode_indikator'] ?? null;
                $role = strtolower($m['role'] ?? '');
                $userName = $m['user']['nama'] ?? null;
                if ($code && $role && $userName) {
                    if (! isset($defaultAssignments[$code])) {
                        $defaultAssignments[$code] = ['pj' => null, 'pic' => null];
                    }
                    $defaultAssignments[$code][$role] = $userName;
                }
            }
        }

        // Ambil mapping yang tersimpan di session untuk project ini
        $sessionAssignments = session("plotting_assignments_{$projectId}", null);

        $effectiveAssignments = array_merge($defaultAssignments, $apiAssignments);
        $assignments = is_array($sessionAssignments) ? array_merge($effectiveAssignments, $sessionAssignments) : $effectiveAssignments;

        // Ambil Placeholder Modules (C.2 & C.5) dari API backend
        // Endpoint: GET /api/v1/projects/{id}/placeholder-modules
        $placeholderModules = [];
        try {
            $resPlaceholder = $this->akreditasiService->getPlaceholderModules($projectId);
            if ($resPlaceholder->successful()) {
                $rawModules = $resPlaceholder->json('data') ?? [];

                $defaultPlaceholderIndicators = [
                    'C.2' => [
                        ['code' => 'K2.1', 'bobot' => 4.0, 'desc' => 'Sistem tata pamong, kepemimpinan publik, dan efektivitas pengelolaan operasional prodi.'],
                        ['code' => 'K2.2', 'bobot' => 4.0, 'desc' => 'Sistem penjaminan mutu internal (SPMI) dan siklus PPEPP secara berkelanjutan.'],
                        ['code' => 'K2.3', 'bobot' => 3.0, 'desc' => 'Efektivitas kerjasama strategis tridharma tingkat nasional maupun internasional.'],
                    ],
                    'C.5' => [
                        ['code' => 'K5.1', 'bobot' => 3.5, 'desc' => 'Kecukupan alokasi dana operasional, penelitian, pkm, dan investasi sarana prodi.'],
                        ['code' => 'K5.2', 'bobot' => 4.0, 'desc' => 'Kelayakan dan kecukupan laboratorium komputer, perangkat keras, dan sistem informasi.'],
                    ],
                ];

                foreach ($rawModules as $pm) {
                    $kode = $pm['kode_kriteria'] ?? ($pm['kode'] ?? 'C.2');
                    $nama = $pm['nama_kriteria'] ?? ($pm['nama'] ?? 'Tata Pamong, Tata Kelola dan Kerjasama');
                    $info = $pm['keterangan'] ?? ($pm['info'] ?? 'Dikelola unit institusi Polines (UPPS). Data terintegrasi terpusat.');
                    $inds = ! empty($pm['indikators']) ? $pm['indikators'] : ($defaultPlaceholderIndicators[$kode] ?? []);

                    $placeholderModules[] = [
                        'kode' => $kode,
                        'nama' => str_contains($nama, $kode) ? $nama : "{$kode} · {$nama}",
                        'info' => $info,
                        'indikators' => $inds,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $placeholderModules = [];
        }

        return view('pages.kaprodi.plotting', [
            'projects' => $projects,
            'kriteriaList' => $rawKriteria,
            'indikatorList' => $rawIndikator,
            'dosenList' => $dosenList,
            'mappingTim' => $mappingTim,
            'projectId' => $projectId,
            'assignments' => $assignments,
            'placeholderModules' => $placeholderModules,
        ]);
    }

    /**
     * Mengekstrak daftar record dosen dari berbagai bentuk wrapper respons API.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function extractDosenRecordsFromResponse(Response $response): array
    {
        if (! $response->successful()) {
            return [];
        }

        $payload = $response->json('data') ?? $response->json();
        for ($depth = 0; $depth < 3 && is_array($payload); $depth++) {
            if (array_is_list($payload)) {
                return array_values(array_filter($payload, 'is_array'));
            }

            $unwrapped = null;
            foreach (['data', 'items', 'dosen', 'records', 'results'] as $wrapperKey) {
                if (isset($payload[$wrapperKey]) && is_array($payload[$wrapperKey])) {
                    $unwrapped = $payload[$wrapperKey];
                    break;
                }
            }

            if ($unwrapped === null) {
                break;
            }

            $payload = $unwrapped;
        }

        return is_array($payload) && array_is_list($payload)
            ? array_values(array_filter($payload, 'is_array'))
            : [];
    }

    /**
     * Menormalisasi daftar dosen untuk halaman plotting dan membuang item tidak valid.
     *
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    protected function normalizePlottingDosenList(array $records): array
    {
        $normalized = [];

        foreach ($records as $d) {
            if (! is_array($d)) {
                continue;
            }

            $rawName = $d['nama_dosen']
                ?? $d['nama']
                ?? $d['name']
                ?? $d['nama_lengkap']
                ?? $d['nama_gelar']
                ?? $d['full_name']
                ?? ($d['user']['nama'] ?? $d['user']['name'] ?? null)
                ?? ($d['dosen']['nama_dosen'] ?? $d['dosen']['nama'] ?? null)
                ?? ($d['pegawai']['nama'] ?? null);

            $rawNidn = $d['nidn']
                ?? $d['nip']
                ?? $d['nip_nidn']
                ?? ($d['user']['nidn'] ?? $d['user']['nip'] ?? null)
                ?? ($d['dosen']['nidn'] ?? null)
                ?? ($d['pegawai']['nip'] ?? null);

            $nama = $this->extractString($rawName, '');
            $nidn = $this->extractString($rawNidn, '-');

            if ($nama === '' || strtolower($nama) === 'dosen') {
                continue;
            }

            $normalized[] = [
                'id' => $d['id_dosen'] ?? ($d['id'] ?? ($d['user_id'] ?? ($d['user']['id'] ?? null))),
                'nama' => $nama,
                'nidn' => $nidn,
                'bidang_keahlian' => $this->extractString(
                    $d['bidang_keahlian'] ?? ($d['bidang_keahlian_name'] ?? ($d['keahlian'] ?? ($d['bidang_minat'] ?? ($d['prodi'] ?? ($d['program_studi'] ?? null))))),
                    'Teknologi Rekayasa Komputer'
                ),
                'jabatan' => $this->extractString(
                    $d['jabatan_akademik'] ?? ($d['jabatan_fungsional'] ?? ($d['jabatan'] ?? ($d['status_dosen'] ?? null))),
                    'Dosen Tetap'
                ),
            ];
        }

        return $normalized;
    }

    /**
     * Simpan penugasan PJ atau PIC indikator tim akreditasi ke server dan session.
     */
    public function storePlotting(Request $request): JsonResponse
    {
        $projectId = (string) $request->input('project_id', '1');
        $role = strtolower($request->input('role', 'pic'));
        $assignments = $request->input('assignments', []);

        if (empty($projectId)) {
            return response()->json([
                'success' => false,
                'message' => 'Project ID wajib diisi.',
            ], 422);
        }

        if (! is_array($assignments)) {
            return response()->json([
                'success' => false,
                'message' => 'Format penugasan tidak valid.',
            ], 422);
        }

        foreach ($assignments as $item) {
            if (! is_array($item) || ! empty($item['remove'])) {
                continue;
            }

            $indicatorId = filter_var($item['id_indikator'] ?? null, FILTER_VALIDATE_INT);
            $userId = filter_var($item['id_user'] ?? null, FILTER_VALIDATE_INT);
            if ($indicatorId === false || $indicatorId < 1 || $userId === false || $userId < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID indikator dan dosen harus valid sebelum penugasan disimpan.',
                ], 422);
            }
        }

        $sessionKey = "plotting_assignments_{$projectId}";
        $current = session($sessionKey, []);

        foreach ($assignments as $item) {
            $code = $item['code'] ?? null;
            if (! $code) {
                continue;
            }

            if (! isset($current[$code])) {
                $current[$code] = ['pj' => null, 'pic' => null];
            }

            if (isset($item['id_indikator'])) {
                $current[$code]['id_indikator'] = (string) $item['id_indikator'];
            }

            if (! empty($item['remove'])) {
                $current[$code][$role] = null;
            } else {
                $current[$code][$role] = $item['user_name'] ?? null;
            }
        }

        // Simpan ke session agar saat refresh data langsung tetap ada
        session([$sessionKey => $current]);

        // Kirim payload batch ke backend API: POST /api/v1/projects/{id}/mapping-tim
        $backendSynced = true;
        try {
            $apiAssignments = [];
            foreach ($assignments as $item) {
                if (! empty($item['remove'])) {
                    continue;
                }
                $apiAssignments[] = [
                    'id_indikator' => (int) $item['id_indikator'],
                    'id_user' => (int) $item['id_user'],
                    'role' => strtoupper($role),
                ];
            }

            if (! empty($apiAssignments)) {
                $response = $this->akreditasiService->saveMappingTim($projectId, [
                    'id_project' => (int) $projectId,
                    'assignments' => $apiAssignments,
                ]);
                $backendSynced = $response->successful();
            }
        } catch (\Throwable $e) {
            $backendSynced = false;
        }

        return response()->json([
            'success' => true,
            'message' => $backendSynced
                ? 'Penugasan '.strtoupper($role).' berhasil disimpan.'
                : 'Penugasan disimpan di sesi saat ini, tetapi belum tersinkron ke server.',
            'assignments' => $current,
            'backend_synced' => $backendSynced,
        ]);
    }
}
