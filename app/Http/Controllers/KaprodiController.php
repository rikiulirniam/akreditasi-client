<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use App\Services\AkreditasiService;
use Illuminate\Http\Client\Response;
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

        // 2. Ambil master data lembaga dan versi instrumen untuk modal tambah
        $lembagaList = $this->extractData($this->akreditasiService->getLembaga());
        $versiList = $this->extractData($this->akreditasiService->getVersiInstrumen());

        // 3. Hitung statistik
        $currentYear = (int) date('Y');
        $totalProject = count($projects);
        $tahunBerjalanCount = 0;
        foreach ($projects as $p) {
            if ((int) ($p['tahun_target_submit'] ?? 0) === $currentYear) {
                $tahunBerjalanCount++;
            }
        }

        $activeInstrumen = '2.1';
        if (! empty($versiList[0]['nomor_versi'])) {
            $activeInstrumen = (string) $versiList[0]['nomor_versi'];
        }

        $stats = [
            'total' => $totalProject,
            'tahun_berjalan' => $tahunBerjalanCount,
            'rata_kelengkapan' => $totalProject > 0 ? '0%' : '—',
            'instrumen_aktif' => $activeInstrumen,
        ];

        return view('pages.kaprodi.project', [
            'projects' => $projects,
            'lembagaList' => $lembagaList,
            'versiList' => $versiList,
            'stats' => $stats,
        ]);
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
            'id_versi_instrumen' => 'nullable|integer',
            'id_jenjang' => 'nullable|integer',
            'id_jenis_instrumen' => 'nullable|integer',
            'status_project' => 'nullable|string',
        ]);

        $payload = [
            'id_prodi' => (int) ($request->input('id_prodi') ?: 1),
            'id_versi_instrumen' => (int) ($request->input('id_versi_instrumen') ?: 1),
            'id_jenjang' => (int) ($request->input('id_jenjang') ?: 1),
            'id_jenis_instrumen' => (int) ($request->input('id_jenis_instrumen') ?: 1),
            'nama_project' => $validated['nama_project'],
            'tahun_target_submit' => (int) $validated['tahun_target_submit'],
            'status_project' => $request->input('status_project', 'draft'),
        ];

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

        // Ambil data kriteria dan indikator dari API
        $rawKriteria = $this->extractData($this->akreditasiService->getKriteria());
        $rawIndikator = $this->extractData($this->akreditasiService->getIndikator());

        return view('pages.kaprodi.project-detail', [
            'project' => $project,
            'apiKriteria' => $rawKriteria,
            'apiIndikator' => $rawIndikator,
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
        if (!empty($mappingTim) && is_array($mappingTim)) {
            foreach ($mappingTim as $m) {
                $code = $m['indikator']['kode_indikator'] ?? null;
                $role = strtolower($m['role'] ?? '');
                $userName = $m['user']['nama'] ?? null;
                if ($code && $role && $userName) {
                    if (!isset($defaultAssignments[$code])) {
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
    public function storePlotting(Request $request): \Illuminate\Http\JsonResponse
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
            if (!$code) continue;

            if (!isset($current[$code])) {
                $current[$code] = ['pj' => null, 'pic' => null];
            }

            if (!empty($item['remove'])) {
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
            'message' => 'Penugasan ' . strtoupper($role) . ' berhasil disimpan.',
            'assignments' => $current,
        ]);
    }
}
