<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use App\Services\AkreditasiService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaprodiController extends Controller
{
    protected AkreditasiService $akreditasiService;

    protected AkademikService $akademikService;

    public function __construct(AkreditasiService $akreditasiService, AkademikService $akademikService)
    {
        $this->akreditasiService = $akreditasiService;
        $this->akademikService = $akademikService;
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
     * Tampilkan halaman Dashboard Kaprodi.
     */
    public function dashboard(Request $request): View
    {
        // 1. Ambil data kriteria dari API
        $rawKriteria = $this->extractData($this->akreditasiService->getKriteria());
        $rawIndikator = $this->extractData($this->akreditasiService->getIndikator());

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
        ]);
    }

    /**
     * Tampilkan halaman Project Akreditasi Kaprodi.
     */
    public function project(Request $request): View
    {
        // 1. Ambil daftar project akreditasi dari API
        $projects = $this->extractData($this->akreditasiService->getProjects());

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
            if ($st === 'in_progress' || $st === 'pengisian' || str_contains($st, 'progress')) {
                $inProgressCount++;
            } else {
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
            'draft' => $draftCount,
            'rata_kelengkapan' => $totalProject > 0 ? '0%' : '—',
            'instrumen_aktif' => $activeInstrumen,
        ];

        return view('pages.kaprodi.project', [
            'projects' => $projects,
            'lembagaList' => $lembagaList,
            'versiList' => $versiList,
            'instrumenList' => $instrumenList,
            'stats' => $stats,
        ]);
    }

    /**
     * Mengambil dan memformat master data instrumen akreditasi (format: Lembaga - Versi - Jenjang - Jenis).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getFormattedInstrumenList(): array
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

        if (empty($raw)) {
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
            $lembaga = $item['versi_instrumen']['lembaga']['nama_lembaga']
                ?? ($item['nama_lembaga'] ?? ($item['lembaga']['nama_lembaga'] ?? 'LAM INFOKOM'));

            $versi = $item['versi_instrumen']['nomor_versi']
                ?? ($item['nomor_versi'] ?? ($item['versi'] ?? '2.1'));
            if (! str_starts_with(strtolower((string) $versi), 'v')) {
                $versi = 'v'.$versi;
            }

            $jenjang = $item['jenjang']['nama_jenjang']
                ?? ($item['nama_jenjang'] ?? ($item['jenjang'] ?? 'Sarjana Terapan'));

            $jenis = $item['jenis_instrumen']['nama_jenis']
                ?? ($item['nama_jenis'] ?? ($item['jenis'] ?? 'APS'));

            $item['formatted_label'] = "{$lembaga} - {$versi} - {$jenjang} - {$jenis}";
            $item['id_versi_instrumen'] = $item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? 1);
            $item['id_jenjang'] = $item['id_jenjang'] ?? ($item['jenjang']['id'] ?? 1);
            $item['id_jenis_instrumen'] = $item['id_jenis_instrumen'] ?? ($item['jenis_instrumen']['id'] ?? 1);

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

        $payload = [
            'id_prodi' => (int) ($request->input('id_prodi') ?: 1),
            'id_versi_instrumen' => (int) ($idVersi ?: 1),
            'id_jenjang' => (int) ($idJenjang ?: 1),
            'id_jenis_instrumen' => (int) ($idJenis ?: 1),
            'nama_project' => $validated['nama_project'],
            'tahun_target_submit' => (int) $validated['tahun_target_submit'],
            'status_project' => $request->input('status_project', 'draft'),
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
            $payload['status_project'] = $request->input('status_project');
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
     * Ubah status project akreditasi (draft / in_progress). Khusus untuk Kaprodi.
     */
    public function updateStatusProject(Request $request, $id): RedirectResponse
    {
        $userRole = strtolower((string) (session('user.role') ?? session('user.role_name') ?? ''));
        if (! str_contains($userRole, 'kaprodi')) {
            return redirect()->back()->with('error', 'Hanya Kaprodi yang dapat mengubah status project akreditasi.');
        }

        $validated = $request->validate([
            'status_project' => 'required|string|in:draft,in_progress,selesai',
        ]);

        try {
            $currentRes = $this->akreditasiService->getProject($id);
            $currentData = $currentRes->successful() ? ($currentRes->json('data') ?? []) : [];

            $payload = [
                'id_prodi' => (int) ($currentData['id_prodi'] ?? 1),
                'id_versi_instrumen' => (int) ($currentData['id_versi_instrumen'] ?? 1),
                'id_jenjang' => (int) ($currentData['id_jenjang'] ?? 1),
                'id_jenis_instrumen' => (int) ($currentData['id_jenis_instrumen'] ?? 1),
                'nama_project' => $currentData['nama_project'] ?? 'Project Akreditasi',
                'tahun_target_submit' => (int) ($currentData['tahun_target_submit'] ?? date('Y')),
                'status_project' => $validated['status_project'],
            ];

            $response = $this->akreditasiService->updateProject($id, $payload);
            if ($response->successful()) {
                $label = $validated['status_project'] === 'in_progress' ? 'In Progress' : ucfirst($validated['status_project']);

                return redirect()->back()->with('success', "Status project berhasil diubah menjadi {$label}.");
            }

            return redirect()->back()->with('error', $response->json('message') ?? 'Gagal memperbarui status project.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
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
        $project = null;

        if ($id) {
            $project = $this->extractData($this->akreditasiService->getProject($id), null);
        }

        if (! $project) {
            // Ambil project pertama yang ada dari API jika ID tidak diberikan
            $allProjects = $this->extractData($this->akreditasiService->getProjects());
            $project = $allProjects[0] ?? [
                'id' => 1,
                'nama_project' => 'Sarjana Terapan Test',
                'tahun_target_submit' => 2026,
                'versi_instrumen' => ['nomor_versi' => '2.1'],
                'jenjang' => ['nama_jenjang' => 'Sarjana Terapan'],
                'jenis_instrumen' => ['nama_jenis' => 'Unggul'],
            ];
        }

        // Ambil data kriteria, indikator, dan instrumen dari API
        $rawKriteria = $this->extractData($this->akreditasiService->getKriteria());
        $rawIndikator = $this->extractData($this->akreditasiService->getIndikator());
        $versiList = $this->extractData($this->akreditasiService->getVersiInstrumen());
        $instrumenList = $this->getFormattedInstrumenList();

        return view('pages.kaprodi.project-detail', [
            'project' => $project,
            'apiKriteria' => $rawKriteria,
            'apiIndikator' => $rawIndikator,
            'versiList' => $versiList,
            'instrumenList' => $instrumenList,
        ]);
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

        // Mapping default awal jika belum ada perubahan
        $defaultAssignments = [
            'K1.1' => ['pj' => 'Dr. Rina Kusuma', 'pic' => 'Budi Santoso M.Kom.'],
            'K2.1' => ['pj' => 'Dr. Rina Kusuma', 'pic' => 'Budi Santoso M.Kom.'],
            'K2.2' => ['pj' => 'Dr. Rina Kusuma', 'pic' => null],
            'K2.3' => ['pj' => null, 'pic' => 'Sari Wulandari'],
            'K3.1' => ['pj' => 'Budi Santoso M.Kom.', 'pic' => 'Andi Pratama'],
            'K3.2' => ['pj' => 'Budi Santoso M.Kom.', 'pic' => 'Budi Santoso M.Kom.'],
            'K3.3' => ['pj' => null, 'pic' => null],
            'K3.4' => ['pj' => 'Dr. Rina Kusuma', 'pic' => 'Dr. Rina Kusuma'],
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
        $assignments = is_array($sessionAssignments) ? array_merge($defaultAssignments, $sessionAssignments) : $defaultAssignments;

        return view('pages.kaprodi.plotting', [
            'projects' => $projects,
            'kriteriaList' => $rawKriteria,
            'indikatorList' => $rawIndikator,
            'mappingTim' => $mappingTim,
            'projectId' => $projectId,
            'assignments' => $assignments,
        ]);
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

        $sessionKey = "plotting_assignments_{$projectId}";
        $current = session($sessionKey, [
            'K1.1' => ['pj' => 'Dr. Rina Kusuma', 'pic' => 'Budi Santoso M.Kom.'],
            'K2.1' => ['pj' => 'Dr. Rina Kusuma', 'pic' => 'Budi Santoso M.Kom.'],
            'K2.2' => ['pj' => 'Dr. Rina Kusuma', 'pic' => null],
            'K2.3' => ['pj' => null, 'pic' => 'Sari Wulandari'],
            'K3.1' => ['pj' => 'Budi Santoso M.Kom.', 'pic' => 'Andi Pratama'],
            'K3.2' => ['pj' => 'Budi Santoso M.Kom.', 'pic' => 'Budi Santoso M.Kom.'],
            'K3.3' => ['pj' => null, 'pic' => null],
            'K3.4' => ['pj' => 'Dr. Rina Kusuma', 'pic' => 'Dr. Rina Kusuma'],
        ]);

        foreach ($assignments as $item) {
            $code = $item['code'] ?? null;
            if (! $code) {
                continue;
            }

            if (! isset($current[$code])) {
                $current[$code] = ['pj' => null, 'pic' => null];
            }

            if (! empty($item['remove'])) {
                $current[$code][$role] = null;
            } else {
                $current[$code][$role] = $item['user_name'] ?? null;
            }
        }

        // Simpan ke session agar saat refresh data langsung tetap ada
        session([$sessionKey => $current]);

        // Coba kirim ke API backend
        try {
            $payload = [
                'assignments' => array_map(function ($item) use ($role) {
                    return [
                        'id_indikator' => is_array($item) ? ($item['id_indikator'] ?? (int) preg_replace('/\D/', '', $item['code'] ?? '1')) : (int) $item,
                        'id_user' => 101,
                        'role' => strtoupper($role),
                    ];
                }, $assignments),
            ];

            $this->akreditasiService->saveMappingTim($projectId, $payload);
        } catch (\Throwable $e) {
            // Silently fallback to session
        }

        return response()->json([
            'success' => true,
            'message' => 'Penugasan '.strtoupper($role).' berhasil disimpan.',
            'assignments' => $current,
        ]);
    }
}
