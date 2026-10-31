<?php

namespace App\Http\Controllers;

use App\Services\ReviewerService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ReviewerController extends Controller
{
    /**
     * Cache indikator dan rubrik untuk satu request lifecycle.
     */
    protected ?array $cachedIndikators = null;

    protected ?array $cachedRubriks = null;

    public function __construct(
        protected ReviewerService $reviewerService,
    ) {}

    public function dashboard(): View
    {
        [$projects, $error, $isDemo] = $this->projects();

        $completedProjects = array_filter($projects, fn (array $project): bool => $this->isCompleted($project));
        $inProgressProjects = array_filter($projects, fn (array $project): bool => $this->isInProgress($project));
        $averageProgress = count($projects) > 0
            ? (int) round(array_sum(array_column($projects, '_reviewer_progress')) / count($projects))
            : 0;

        return view('pages.reviewer.dashboard', [
            'user' => session('user', []),
            'projects' => array_slice($projects, 0, 5),
            'projectCount' => count($projects),
            'inProgressCount' => count($inProgressProjects),
            'completedCount' => count($completedProjects),
            'averageProgress' => $averageProgress,
            'apiError' => $error,
            'isDemo' => $isDemo,
        ]);
    }

    public function assessments(Request $request): View
    {
        [$projects, $error, $isDemo] = $this->projects();
        $statusFilter = (string) $request->query('status', '');
        $yearFilter = (string) $request->query('tahun', '');
        $statuses = array_values(array_unique(array_column($projects, '_reviewer_status_key')));
        $years = array_values(array_filter(array_unique(array_column($projects, '_reviewer_year'))));
        rsort($years);
        $filteredProjects = array_values(array_filter($projects, function (array $project) use ($statusFilter, $yearFilter): bool {
            return ($statusFilter === '' || $project['_reviewer_status_key'] === $statusFilter)
                && ($yearFilter === '' || $project['_reviewer_year'] === $yearFilter);
        }));

        return view('pages.reviewer.assessments', [
            'user' => session('user', []),
            'projects' => $filteredProjects,
            'totalProjects' => count($projects),
            'statuses' => $statuses,
            'years' => $years,
            'statusFilter' => $statusFilter,
            'yearFilter' => $yearFilter,
            'apiError' => $error,
            'isDemo' => $isDemo,
        ]);
    }

    public function assessmentDetail(Request $request, string $projectId): View|RedirectResponse
    {
        [$projects, $error, $isDemo] = $this->projects();
        $project = $this->findProject($projects, $projectId);

        if ($project === null) {
            return redirect()->route('reviewer.assessments')->with('toast', [
                'type' => 'error',
                'title' => 'Project tidak ditemukan',
                'message' => $error ?? 'Project penilaian tidak tersedia.',
            ]);
        }

        $indicators = $project['indicators'] ?? [];

        // Jika data project dari API tidak menyertakan indicators langsung, ambil dari master indikator
        if (empty($indicators)) {
            $indicators = $this->loadIndicatorsFromApi($projectId);
            $project['indicators'] = $indicators;
        }

        if (! is_array($indicators)
            || ! array_is_list($indicators)
            || $indicators === []
            || count(array_filter($indicators, 'is_array')) !== count($indicators)) {
            return view('pages.reviewer.assessment-detail', [
                'user' => session('user', []),
                'project' => $project,
                'indicators' => [],
                'indicator' => null,
                'indicatorIndex' => 0,
                'savedAssessment' => [],
                'savedAssessments' => [],
                'isDemo' => $isDemo,
                'apiError' => $error ?? 'Detail indikator penilaian belum tersedia dari server.',
            ]);
        }

        $indicatorId = (string) $request->query('indikator', $indicators[0]['id'] ?? '');
        $indicatorIndex = array_find_key($indicators, fn (mixed $item): bool => is_array($item) && (string) ($item['id'] ?? '') === $indicatorId);

        if (! is_int($indicatorIndex)) {
            $indicatorIndex = 0;
        }

        $indicator = $indicators[$indicatorIndex];

        // Muat penilaian tersimpan dari API dan gabungkan dengan session terkini
        $savedAssessments = $this->loadSavedAssessments($projectId, $indicators);
        $savedAssessment = $savedAssessments[(string) $indicator['id']] ?? [];

        return view('pages.reviewer.assessment-detail', [
            'user' => session('user', []),
            'project' => $project,
            'indicators' => $indicators,
            'indicator' => $indicator,
            'indicatorIndex' => $indicatorIndex,
            'savedAssessment' => is_array($savedAssessment) ? $savedAssessment : [],
            'savedAssessments' => $savedAssessments,
            'isDemo' => $isDemo,
            'apiError' => $error,
        ]);
    }

    public function saveAssessment(Request $request, string $projectId, string $indicatorId): RedirectResponse
    {
        [$projects, $error] = $this->projects();
        $project = $this->findProject($projects, $projectId);

        $indicators = $project['indicators'] ?? [];
        if (empty($indicators)) {
            $indicators = $this->loadIndicatorsFromApi($projectId);
            $project['indicators'] = $indicators;
        }

        $indicatorExists = $project !== null && collect($indicators)
            ->contains(fn (mixed $indicator): bool => is_array($indicator) && (string) ($indicator['id'] ?? '') === $indicatorId);

        if (! $indicatorExists) {
            return redirect()->route('reviewer.assessments')->with('toast', [
                'type' => 'error',
                'title' => 'Indikator tidak ditemukan',
                'message' => $error ?? 'Indikator penilaian tidak tersedia.',
            ]);
        }

        $validated = $request->validate([
            'score' => ['required', 'integer', 'between:0,4'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rubrik_id' => ['nullable', 'integer'],
            'rubrik_ids' => ['nullable', 'array'],
            'evidence_status' => ['nullable', 'array'],
            'evidence_status.*' => ['required', 'in:Disetujui,Pending,Perlu revisi'],
            'evidence_action' => ['nullable', 'string', 'regex:/^\d+:(Disetujui|Perlu revisi)$/'],
        ]);

        $evidenceStatuses = $validated['evidence_status'] ?? [];
        if (isset($validated['evidence_action'])) {
            [$evidenceIndex, $evidenceStatus] = explode(':', $validated['evidence_action'], 2);
            $evidenceStatuses[$evidenceIndex] = $evidenceStatus;
        }

        // Tentukan ID rubrik jika ada
        $score = (int) $validated['score'];
        $rubrikId = $validated['rubrik_id'] ?? ($validated['rubrik_ids'][$score] ?? null);

        if (! $rubrikId && ! empty($indicators)) {
            $currentIndicator = collect($indicators)->first(fn ($item) => (string) ($item['id'] ?? '') === $indicatorId);
            $rubriks = $currentIndicator['rubriks'] ?? [];
            foreach ($rubriks as $r) {
                if ((int) ($r['skor'] ?? 0) === $score) {
                    $rubrikId = $r['id'] ?? null;
                    break;
                }
            }
        }

        // Simpan ke session untuk akses instan dan offline UX
        $request->session()->put("reviewer_assessments.{$projectId}.{$indicatorId}", [
            'score' => $score,
            'rubrik_id' => $rubrikId,
            'notes' => $validated['notes'] ?? '',
            'evidence_status' => $evidenceStatuses,
        ]);

        // Kirim penyimpanan penilaian ke API akreditasi
        try {
            $user = $request->session()->get('user', []);
            $reviewerId = (int) ($user['id'] ?? 1);
            $this->reviewerService->saveSelfAssessment([
                'id_project' => (int) $projectId,
                'id_indikator' => (int) $indicatorId,
                'id_rubrik' => (int) ($rubrikId ?? 1),
                'id_reviewer' => $reviewerId,
                'catatan_reviewer' => (string) ($validated['notes'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Reviewer save assessment to API: '.$e->getMessage());
        }

        return redirect()->route('reviewer.assessment-detail', [
            'projectId' => $projectId,
            'indikator' => $indicatorId,
        ])->with('toast', [
            'type' => 'success',
            'title' => 'Penilaian tersimpan',
            'message' => 'Skor dan catatan indikator berhasil disimpan.',
        ]);
    }

    /**
     * Memuat daftar indikator dari API master data.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadIndicatorsFromApi(string|int $projectId): array
    {
        if ($this->cachedIndikators !== null) {
            return $this->cachedIndikators;
        }

        try {
            $resIndikator = $this->reviewerService->getIndikators();
            $rawIndikators = $resIndikator->successful() ? ($resIndikator->json('data') ?? []) : [];

            $resRubrik = $this->reviewerService->getRubriks();
            $rawRubriks = $resRubrik->successful() ? ($resRubrik->json('data') ?? []) : [];

            $rubriksByIndikator = [];
            foreach ($rawRubriks as $rubrik) {
                $indId = $rubrik['id_indikator'] ?? null;
                if ($indId !== null) {
                    $rubriksByIndikator[$indId][] = $rubrik;
                }
            }

            $indicators = [];
            foreach ($rawIndikators as $ind) {
                $kriteriaNama = $ind['subkriteria']['kriteria']['nama_kriteria'] ?? null;
                $kriteriaKode = $ind['subkriteria']['kriteria']['kode_kriteria'] ?? null;
                $criterion = $kriteriaKode ? "Kriteria {$kriteriaKode}" : ($kriteriaNama ?? 'Kriteria');

                $rubriks = $rubriksByIndikator[$ind['id']] ?? [];
                usort($rubriks, fn ($a, $b) => ($b['skor'] ?? 0) <=> ($a['skor'] ?? 0));

                $indicators[] = [
                    'id' => (string) $ind['id'],
                    'criterion' => $criterion,
                    'criterion_title' => $kriteriaNama ?? $criterion,
                    'code' => $ind['kode_indikator'] ?? ('IND-'.$ind['id']),
                    'title' => $ind['deskripsi_indikator'] ?? 'Indikator Penilaian',
                    'parameter' => $ind['parameter_penilaian'] ?? 'Parameter penilaian standar.',
                    'narrative' => 'Program studi telah menyusun instrumen dan dokumentasi pelaksanaan kegiatan penjaminan mutu sesuai standar kriteria yang ditetapkan.',
                    'evidence' => [
                        ['type' => 'Dokumen kebijakan', 'name' => 'Dokumen SPMI & Regulasi Terkait', 'date' => date('d M Y'), 'status' => 'Disetujui'],
                        ['type' => 'Laporan kinerja', 'name' => 'Laporan Hasil Evaluasi dan Tindak Lanjut', 'date' => date('d M Y'), 'status' => 'Pending'],
                    ],
                    'rubriks' => $rubriks,
                    'weight' => 2.5,
                ];
            }

            $this->cachedIndikators = $indicators;

            return $indicators;
        } catch (\Throwable $e) {
            Log::warning('Gagal memuat indikator dari API: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Menggabungkan penilaian dari API dan session.
     *
     * @param  array<int, array<string, mixed>>  $indicators
     * @return array<string, array<string, mixed>>
     */
    protected function loadSavedAssessments(string|int $projectId, array $indicators): array
    {
        $savedAssessments = [];

        // 1. Ambil dari API self-assessment
        try {
            $res = $this->reviewerService->getSelfAssessments(['id_project' => $projectId]);
            if ($res->successful()) {
                $apiAssessments = $res->json('data') ?? [];
                foreach ($apiAssessments as $s) {
                    $indId = (string) ($s['id_indikator'] ?? '');
                    if ($indId !== '') {
                        $savedAssessments[$indId] = [
                            'score' => (int) ($s['rubrik']['skor'] ?? 4),
                            'notes' => $s['catatan_reviewer'] ?? '',
                            'rubrik_id' => $s['id_rubrik'] ?? null,
                            'evidence_status' => session("reviewer_assessments.{$projectId}.{$indId}.evidence_status", []),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal memuat self assessment dari API: '.$e->getMessage());
        }

        // 2. Gabungkan dengan session untuk indikator
        foreach ($indicators as $item) {
            $itemId = (string) ($item['id'] ?? '');
            $sessionSaved = session("reviewer_assessments.{$projectId}.{$itemId}", []);
            if (is_array($sessionSaved) && ! empty($sessionSaved)) {
                $savedAssessments[$itemId] = array_merge($savedAssessments[$itemId] ?? [], $sessionSaved);
            }
        }

        return $savedAssessments;
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: ?string, 2: bool}
     */
    protected function projects(): array
    {
        try {
            $response = $this->reviewerService->getProjects();
        } catch (ConnectionException) {
            return $this->projectsFromFallback('Tidak dapat terhubung ke server akreditasi. Silakan coba lagi nanti.');
        }

        if (! $response->successful()) {
            return $this->projectsFromFallback($this->apiErrorMessage($response));
        }

        $body = $response->json();
        $data = $body['data'] ?? $body;
        $projects = is_array($data) && isset($data['projects'])
            ? $data['projects']
            : $data;

        if (! is_array($projects) || ! array_is_list($projects)) {
            return [[], 'Format data project dari server akreditasi tidak sesuai.', false];
        }

        if (count(array_filter($projects, 'is_array')) !== count($projects)) {
            return [[], 'Format data project dari server akreditasi tidak sesuai.', false];
        }

        return [$this->prepareProjects($projects), null, false];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: ?string, 2: bool}
     */
    protected function projectsFromFallback(string $error): array
    {
        $projects = $this->reviewerService->getDummyProjects();

        if ($projects !== null) {
            return [$this->prepareProjects($projects), null, true];
        }

        return [[], $error, false];
    }

    /**
     * @param  array<int, array<string, mixed>>  $projects
     * @return array<int, array<string, mixed>>
     */
    protected function prepareProjects(array $projects): array
    {
        $prodiMap = $this->reviewerService->getProdiMap();

        // Ambil data self assessments untuk perhitungan progres akurat jika belum ada
        $assessmentsByProject = [];
        try {
            $selfRes = $this->reviewerService->getSelfAssessments();
            if ($selfRes->successful()) {
                $allSelf = $selfRes->json('data') ?? [];
                if (is_array($allSelf)) {
                    foreach ($allSelf as $s) {
                        $pId = $s['id_project'] ?? null;
                        if ($pId !== null) {
                            $assessmentsByProject[$pId][] = $s;
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // fallback gracefully
        }

        return array_map(function (array $project) use ($prodiMap, $assessmentsByProject): array {
            $id = $project['id'] ?? $project['project_id'] ?? null;

            // Normalisasi nama program studi
            if (empty($project['program_studi'])) {
                if (! empty($project['id_prodi']) && isset($prodiMap[$project['id_prodi']])) {
                    $project['program_studi'] = $prodiMap[$project['id_prodi']];
                } elseif (! empty($project['prodi']['nama'])) {
                    $project['program_studi'] = $project['prodi']['nama'];
                } elseif (! empty($project['nama_prodi'])) {
                    $project['program_studi'] = $project['nama_prodi'];
                } elseif (preg_match('/(?:D3|D4|S1|S2)\s+([A-Za-z\s]+?)(?:\s+\d{4}|$)/', (string) ($project['nama_project'] ?? ''), $match)) {
                    $project['program_studi'] = trim($match[1]);
                } else {
                    $project['program_studi'] = 'Program Studi';
                }
            }

            // Normalisasi jenjang
            if (is_array($project['jenjang'] ?? null)) {
                $project['jenjang'] = $project['jenjang']['nama_jenjang'] ?? 'Program Diploma';
            }

            // Normalisasi jenis instrumen
            if (is_array($project['jenis_instrumen'] ?? null)) {
                $project['jenis_instrumen'] = $project['jenis_instrumen']['nama_jenis']
                    ?? ($project['versi_instrumen']['nomor_versi'] ?? 'LAM-INFOKOM 2.1');
            }

            // Normalisasi target submit
            if (empty($project['target_submit']) && ! empty($project['tahun_target_submit'])) {
                $project['target_submit'] = (string) $project['tahun_target_submit'];
            }

            // Hitung progres dinamis jika project tidak membawa completed_indicators / progress
            if (! isset($project['progress_percentage']) && ! isset($project['completed_indicators'])) {
                $apiCount = count($assessmentsByProject[$id] ?? []);
                $sessionAssessments = session("reviewer_assessments.{$id}", []);
                $sessionCount = is_array($sessionAssessments) ? count($sessionAssessments) : 0;
                $completed = max($apiCount, $sessionCount);
                $total = count($project['indicators'] ?? []) ?: 31;

                $project['completed_indicators'] = $completed;
                $project['total_indicators'] = $total;
                $project['_reviewer_progress'] = $total > 0 ? min(100, round(($completed / $total) * 100, 1)) : 0;

                if ($project['_reviewer_progress'] >= 100) {
                    $project['status'] = 'Selesai';
                } elseif ($completed > 0) {
                    $project['status'] = 'Sedang dinilai';
                } else {
                    $project['status'] = $project['status_project'] ?? 'Menunggu';
                }
            } else {
                $project['_reviewer_progress'] = $this->progress($project);
            }

            $project['_reviewer_status_key'] = $this->statusKey($project);
            $project['_reviewer_year'] = $this->year($project);

            return $project;
        }, $projects);
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function statusKey(array $project): string
    {
        $status = strtolower(trim((string) ($project['status'] ?? $project['status_project'] ?? '')));

        if (str_contains($status, 'selesai') || str_contains($status, 'complete')) {
            return 'selesai';
        }

        if (str_contains($status, 'menunggu') || str_contains($status, 'pending') || str_contains($status, 'belum') || str_contains($status, 'draft')) {
            return 'menunggu';
        }

        if (str_contains($status, 'dinilai') || str_contains($status, 'progress') || str_contains($status, 'proses')) {
            return 'sedang_dinilai';
        }

        return str_replace(' ', '_', $status);
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function year(array $project): string
    {
        $target = $project['target_submit'] ?? $project['tahun'] ?? $project['tahun_akademik'] ?? ($project['tahun_target_submit'] ?? '');

        if (is_array($target)) {
            $target = $target['tahun'] ?? $target['nama'] ?? '';
        }

        return preg_match('/20\d{2}/', (string) $target, $matches) === 1 ? $matches[0] : '';
    }

    /**
     * @param  array<int, array<string, mixed>>  $projects
     * @return array<string, mixed>|null
     */
    protected function findProject(array $projects, string $projectId): ?array
    {
        foreach ($projects as $project) {
            $id = $project['id'] ?? $project['project_id'] ?? null;

            if ($id !== null && (string) $id === $projectId) {
                return $project;
            }
        }

        return null;
    }

    protected function apiErrorMessage(Response $response): string
    {
        return (string) ($response->json('message') ?? 'Data project gagal dimuat dari server akreditasi.');
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function isCompleted(array $project): bool
    {
        $status = strtolower((string) ($project['status'] ?? $project['status_project'] ?? ''));

        return str_contains($status, 'selesai') || str_contains($status, 'complete');
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function isInProgress(array $project): bool
    {
        $status = strtolower((string) ($project['status'] ?? $project['status_project'] ?? ''));

        return str_contains($status, 'dinilai') || str_contains($status, 'progress');
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function progress(array $project): float
    {
        $value = $project['progress_percentage'] ?? $project['progress'] ?? $project['persentase'] ?? null;

        if (! is_numeric($value)) {
            $completedIndicators = $project['completed_indicators'] ?? $project['indikator_selesai'] ?? null;
            $totalIndicators = $project['total_indicators'] ?? $project['total_indikator'] ?? null;

            if (is_numeric($completedIndicators) && is_numeric($totalIndicators) && (float) $totalIndicators > 0) {
                $value = ((float) $completedIndicators / (float) $totalIndicators) * 100;
            } else {
                $value = 0;
            }
        }

        return is_numeric($value) ? min(100, max(0, (float) $value)) : 0;
    }
}
