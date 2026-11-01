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

    public function dashboard(Request $request): View
    {
        if ($request->has('demo')) {
            $request->session()->put('reviewer_demo_mode', $request->boolean('demo'));
        }

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
        $detailError = $error;
        $assignedIndicatorIds = array_fill_keys(array_map(
            fn (array $indicator): string => (string) $indicator['id'],
            array_filter($indicators, fn (mixed $indicator): bool => is_array($indicator) && isset($indicator['id'])),
        ), true);
        $needsApiDetails = collect($indicators)->contains(
            fn (mixed $indicator): bool => is_array($indicator) && ($indicator['_needs_api_detail'] ?? false),
        );

        if ($needsApiDetails) {
            $apiIndicators = $this->loadIndicatorsFromApi($projectId);
            $indicators = array_values(array_filter(
                $apiIndicators,
                fn (array $indicator): bool => isset($assignedIndicatorIds[(string) ($indicator['id'] ?? '')]),
            ));

            if ($indicators === []) {
                $detailError = 'Detail indikator yang ditugaskan tidak ditemukan pada master indikator.';
            }

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
                'apiError' => $detailError ?? 'Detail indikator penilaian belum tersedia dari server.',
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
            'apiError' => $detailError,
        ]);
    }

    public function saveAssessment(Request $request, string $projectId, string $indicatorId): RedirectResponse
    {
        [$projects, $error] = $this->projects();
        $project = $this->findProject($projects, $projectId);

        if ($project === null) {
            return redirect()->route('reviewer.assessments')->with('toast', [
                'type' => 'error',
                'title' => 'Project tidak ditemukan',
                'message' => $error ?? 'Project penilaian tidak tersedia.',
            ]);
        }

        $indicators = $project['indicators'] ?? [];
        $needsApiDetails = collect($indicators)->contains(
            fn (mixed $indicator): bool => is_array($indicator) && ($indicator['_needs_api_detail'] ?? false),
        );

        if ($needsApiDetails) {
            $assignedIndicatorIds = array_fill_keys(array_map(
                fn (array $indicator): string => (string) $indicator['id'],
                array_filter($indicators, fn (mixed $indicator): bool => is_array($indicator) && isset($indicator['id'])),
            ), true);
            $indicators = array_values(array_filter(
                $this->loadIndicatorsFromApi($projectId),
                fn (array $indicator): bool => isset($assignedIndicatorIds[(string) ($indicator['id'] ?? '')]),
            ));
            $project['indicators'] = $indicators;
        }

        $indicatorExists = collect($indicators)
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

        if (! (bool) $request->session()->get('reviewer_demo_mode', false)) {
            try {
                $user = $request->session()->get('user', []);
                $reviewerId = is_array($user) ? ($user['id'] ?? $user['id_user'] ?? $user['user_id'] ?? null) : null;
                $assessment = [
                    'id_project' => (int) $projectId,
                    'id_indikator' => (int) $indicatorId,
                    'id_rubrik' => $rubrikId !== null ? (int) $rubrikId : null,
                    'catatan_reviewer' => (string) ($validated['notes'] ?? ''),
                ];

                if ($reviewerId !== null) {
                    $assessment['reviewer_id'] = (int) $reviewerId;
                }

                $response = $this->reviewerService->saveSelfAssessment($assessment);

                if (! $response->successful()) {
                    Log::warning('Reviewer save assessment API returned an unsuccessful response.', [
                        'status' => $response->status(),
                        'project_id' => $projectId,
                        'indicator_id' => $indicatorId,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Reviewer save assessment to API: '.$e->getMessage());
            }
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
        } catch (ConnectionException $e) {
            Log::warning('Gagal memuat indikator dari API: '.$e->getMessage());

            return [];
        }

        if (! $resIndikator->successful()) {
            Log::warning('API indikator reviewer mengembalikan respons gagal.', [
                'status' => $resIndikator->status(),
            ]);

            return [];
        }

        $rawIndikators = $resIndikator->json('data') ?? [];
        if (! is_array($rawIndikators)) {
            Log::warning('Format daftar indikator dari API reviewer tidak sesuai.');

            return [];
        }

        $rawRubriks = [];
        try {
            $resRubrik = $this->reviewerService->getRubriks();
            if ($resRubrik->successful()) {
                $rawRubriks = $resRubrik->json('data') ?? [];
            } else {
                Log::warning('API rubrik reviewer mengembalikan respons gagal.', [
                    'status' => $resRubrik->status(),
                ]);
            }
        } catch (ConnectionException $e) {
            Log::warning('Gagal memuat rubrik reviewer dari API: '.$e->getMessage());
        }

        $rubriksByIndikator = [];
        foreach (is_array($rawRubriks) ? $rawRubriks : [] as $rubrik) {
            if (! is_array($rubrik)) {
                continue;
            }

            $indId = $rubrik['id_indikator'] ?? null;
            if ($indId !== null) {
                $rubriksByIndikator[$indId][] = $rubrik;
            }
        }

        $indicators = [];
        foreach ($rawIndikators as $ind) {
            if (! is_array($ind) || ! isset($ind['id'])) {
                continue;
            }

            $kriteriaNama = $ind['subkriteria']['kriteria']['nama_kriteria'] ?? null;
            $kriteriaKode = $ind['subkriteria']['kriteria']['kode_kriteria'] ?? null;
            $criterion = $kriteriaKode ? "Kriteria {$kriteriaKode}" : ($kriteriaNama ?? 'Kriteria');

            $rubriks = $rubriksByIndikator[$ind['id']] ?? [];
            usort($rubriks, fn (array $a, array $b): int => ($b['skor'] ?? 0) <=> ($a['skor'] ?? 0));

            $indicators[] = [
                'id' => (string) $ind['id'],
                'criterion' => $criterion,
                'criterion_title' => $kriteriaNama ?? $criterion,
                'code' => $ind['kode_indikator'] ?? ('IND-'.$ind['id']),
                'title' => $ind['deskripsi_indikator'] ?? $ind['nama_indikator'] ?? 'Indikator Penilaian',
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
        if (session('reviewer_demo_mode', false)) {
            return $this->projectsFromDemoMode();
        }

        try {
            $response = $this->reviewerService->getProjects();
        } catch (ConnectionException) {
            return $this->projectsFromFallback('Tidak dapat terhubung ke server akreditasi. Silakan coba lagi nanti.');
        }

        if (! $response->successful()) {
            return $this->projectsFromFallback($this->apiErrorMessage($response));
        }

        $projects = $this->responseList($response->json());

        if ($projects === null) {
            return [[], 'Format data project dari server akreditasi tidak sesuai.', false];
        }

        $user = session('user', []);
        $reviewerId = is_array($user) ? ($user['id'] ?? $user['id_user'] ?? $user['user_id'] ?? null) : null;

        if ($reviewerId === null || $reviewerId === '') {
            return [[], 'ID reviewer tidak ditemukan pada sesi login.', false];
        }

        try {
            $mappingResponse = $this->reviewerService->getTeamAssignments();
        } catch (ConnectionException) {
            return $this->projectsFromFallback('Tidak dapat memuat penugasan reviewer dari server akreditasi.');
        }

        if (! $mappingResponse->successful()) {
            return $this->projectsFromFallback($this->apiErrorMessage($mappingResponse));
        }

        $assignments = $this->responseList($mappingResponse->json());

        if ($assignments === null) {
            return [[], 'Format data penugasan reviewer dari server akreditasi tidak sesuai.', false];
        }

        $assignmentsByProject = [];
        foreach ($assignments as $assignment) {
            $assignmentReviewerId = $this->assignmentReviewerId($assignment);

            if (! is_scalar($assignmentReviewerId) || (string) $assignmentReviewerId !== (string) $reviewerId) {
                continue;
            }

            $projectId = $this->assignmentProjectId($assignment);

            if ($projectId === null) {
                continue;
            }

            $assignmentsByProject[(string) $projectId][] = $assignment;
        }

        $assignedProjects = array_values(array_filter($projects, function (array $project) use ($assignmentsByProject): bool {
            $projectId = $project['id'] ?? $project['id_project'] ?? $project['project_id'] ?? null;

            return $projectId !== null && isset($assignmentsByProject[(string) $projectId]);
        }));

        foreach ($assignedProjects as &$project) {
            $projectId = (string) ($project['id'] ?? $project['id_project'] ?? $project['project_id']);
            $project['indicators'] = $this->indicatorsFromAssignments($assignmentsByProject[$projectId]);
            $project['total_indicators'] = $project['total_indicators'] ?? count($project['indicators']);
            $project = $this->normalizeProject($project);
        }
        unset($project);

        return [$this->prepareProjects($assignedProjects), null, false];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: ?string, 2: bool}
     */
    protected function projectsFromFallback(string $error): array
    {
        $projects = $this->reviewerService->getDummyProjects();

        if ($projects !== null) {
            return [$this->prepareProjects($projects, true), null, true];
        }

        return [[], $error, false];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: ?string, 2: bool}
     */
    protected function projectsFromDemoMode(): array
    {
        $projects = $this->reviewerService->getDummyProjects();

        if ($projects !== null) {
            return [$this->prepareProjects($projects, true), null, true];
        }

        return [[], 'File data dummy reviewer tidak ditemukan.', false];
    }

    /**
     * @param  array<int, array<string, mixed>>  $projects
     * @return array<int, array<string, mixed>>
     */
    protected function prepareProjects(array $projects, bool $isDemo = false): array
    {
        $needsProdiMap = ! $isDemo && collect($projects)->contains(
            fn (array $project): bool => empty($project['program_studi'])
                && empty($project['prodi'])
                && empty($project['nama_prodi'])
                && isset($project['id_prodi']),
        );
        $prodiMap = $needsProdiMap ? $this->reviewerService->getProdiMap() : [];

        $assessmentsByProject = [];
        $needsProgressFromAssessments = ! $isDemo && collect($projects)->contains(
            fn (array $project): bool => ! isset($project['progress_percentage'])
                && ! isset($project['completed_indicators']),
        );
        if ($needsProgressFromAssessments) {
            try {
                $selfRes = $this->reviewerService->getSelfAssessments();
                if ($selfRes->successful()) {
                    $allSelf = $selfRes->json('data') ?? [];
                    if (is_array($allSelf)) {
                        foreach ($allSelf as $assessment) {
                            if (! is_array($assessment)) {
                                continue;
                            }

                            $projectId = $assessment['id_project'] ?? null;
                            if ($projectId !== null) {
                                $assessmentsByProject[(string) $projectId][] = $assessment;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal memuat self assessment untuk progres reviewer: '.$e->getMessage());
            }
        }

        return array_map(function (array $project) use ($prodiMap, $assessmentsByProject): array {
            $id = $project['id'] ?? $project['id_project'] ?? $project['project_id'] ?? null;

            if (empty($project['program_studi']) && ! empty($project['id_prodi']) && isset($prodiMap[$project['id_prodi']])) {
                $project['program_studi'] = $prodiMap[$project['id_prodi']];
            }

            if (! isset($project['progress_percentage']) && ! isset($project['completed_indicators'])) {
                $apiAssessmentIds = array_filter(array_map(
                    fn (array $assessment): ?string => isset($assessment['id_indikator'])
                        ? (string) $assessment['id_indikator']
                        : null,
                    $assessmentsByProject[(string) $id] ?? [],
                ));
                $sessionAssessments = session("reviewer_assessments.{$id}", []);
                $sessionAssessmentIds = is_array($sessionAssessments) ? array_keys($sessionAssessments) : [];
                $completed = count(array_unique(array_merge($apiAssessmentIds, $sessionAssessmentIds)));
                if ($completed === 0) {
                    $completed = count($assessmentsByProject[(string) $id] ?? []);
                }
                $total = (int) ($project['total_indicators'] ?? 0);
                if ($total < 1) {
                    $total = count($project['indicators'] ?? []) ?: 31;
                }

                $project['completed_indicators'] = $completed;
                $project['total_indicators'] = $total;
                $project['_reviewer_progress'] = $total > 0 ? min(100, round(($completed / $total) * 100, 1)) : 0;

                if ($project['_reviewer_progress'] >= 100) {
                    $project['status'] = 'Selesai';
                } elseif ($completed > 0) {
                    $project['status'] = 'Sedang dinilai';
                }
            }

            $project = $this->normalizeProject($project);
            $project['_reviewer_progress'] = $project['_reviewer_progress'] ?? $this->progress($project);
            $project['_reviewer_status_key'] = $this->statusKey($project);
            $project['_reviewer_year'] = $this->year($project);

            return $project;
        }, $projects);
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    protected function responseList(mixed $body): ?array
    {
        if (! is_array($body)) {
            return null;
        }

        $data = $body['data'] ?? $body;
        if (is_array($data) && ! array_is_list($data)) {
            $data = $data['projects'] ?? $data['mapping_tim'] ?? null;
        }

        if (! is_array($data) || ! array_is_list($data) || count(array_filter($data, 'is_array')) !== count($data)) {
            return null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $assignment
     */
    protected function assignmentReviewerId(array $assignment): mixed
    {
        return $assignment['id_user']
            ?? $assignment['reviewer_id']
            ?? $assignment['id_reviewer']
            ?? $assignment['user_id']
            ?? $assignment['user']['id']
            ?? $assignment['reviewer']['id']
            ?? null;
    }

    /**
     * @param  array<string, mixed>  $assignment
     */
    protected function assignmentProjectId(array $assignment): mixed
    {
        return $assignment['id_project']
            ?? $assignment['project_id']
            ?? $assignment['project']['id']
            ?? null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $assignments
     * @return array<int, array<string, mixed>>
     */
    protected function indicatorsFromAssignments(array $assignments): array
    {
        $indicators = [];

        foreach ($assignments as $assignment) {
            $indicator = $assignment['indikator'] ?? $assignment['indicator'] ?? null;
            if (! is_array($indicator)) {
                $indicator = ['_needs_api_detail' => true];
            }

            $indicator['id'] = $indicator['id'] ?? $assignment['id_indikator'] ?? null;
            $indicator = $this->normalizeIndicator($indicator);

            if ($indicator['id'] !== null) {
                $indicators[(string) $indicator['id']] = $indicator;
            }
        }

        return array_values($indicators);
    }

    /**
     * @param  array<string, mixed>  $project
     * @return array<string, mixed>
     */
    protected function normalizeProject(array $project): array
    {
        $program = $project['program_studi'] ?? $project['prodi'] ?? null;
        $degree = $project['jenjang'] ?? null;
        $instrument = $project['jenis_instrumen'] ?? $project['instrumen'] ?? null;
        $instrumentVersion = $project['versi_instrumen'] ?? null;
        $instrumentDetail = is_array($instrumentVersion) ? ($instrumentVersion['instrumen'] ?? null) : null;

        $project['program_studi'] = $this->nestedName($program)
            ?? $project['nama_prodi']
            ?? $project['nama_program_studi']
            ?? $this->programNameFromProject($project)
            ?? 'Program studi';
        $project['jenjang'] = $this->nestedName($degree)
            ?? (is_array($program) ? $this->nestedName($program['jenjang'] ?? null) : null)
            ?? '—';
        $project['jenis_instrumen'] = $this->nestedName($instrument)
            ?? (is_array($instrumentDetail) ? $this->nestedName($instrumentDetail) : null)
            ?? (is_array($instrumentVersion) ? $this->nestedName($instrumentVersion) : null)
            ?? 'Instrumen akreditasi';
        $project['target_submit'] = $project['tahun_target_submit']
            ?? $project['target_submit']
            ?? $project['tahun']
            ?? '—';
        $project['status'] = $this->statusLabel((string) ($project['status_project'] ?? $project['status'] ?? ''));
        $project['indicators'] = array_values(array_map(
            fn (array $indicator): array => $this->normalizeIndicator($indicator),
            array_filter($project['indicators'] ?? [], 'is_array'),
        ));

        return $project;
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function programNameFromProject(array $project): ?string
    {
        if (preg_match('/(?:D3|D4|S1|S2)\s+([A-Za-z\s]+?)(?:\s+\d{4}|$)/', (string) ($project['nama_project'] ?? ''), $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $indicator
     * @return array<string, mixed>
     */
    protected function normalizeIndicator(array $indicator): array
    {
        $subcriterion = $indicator['subkriteria'] ?? $indicator['sub_criteria'] ?? null;
        $criterion = is_array($subcriterion) ? ($subcriterion['kriteria'] ?? $subcriterion['criterion'] ?? null) : null;
        $evidence = $indicator['evidence'] ?? $indicator['bukti_pendukung'] ?? [];

        $indicator['id'] = $indicator['id'] ?? $indicator['id_indikator'] ?? null;
        $indicator['code'] = $indicator['kode_indikator'] ?? $indicator['code'] ?? $indicator['kode'] ?? 'Indikator';
        $indicator['title'] = $indicator['nama_indikator'] ?? $indicator['title'] ?? $indicator['nama'] ?? 'Indikator penilaian';
        $indicator['criterion'] = $indicator['criterion']
            ?? (is_array($criterion) ? ($criterion['nama_kriteria'] ?? $criterion['nama'] ?? $criterion['name'] ?? null) : null)
            ?? $indicator['nama_kriteria']
            ?? 'Kriteria';
        $indicator['criterion_title'] = $indicator['criterion_title']
            ?? (is_array($criterion) ? ($criterion['nama_kriteria'] ?? $criterion['nama'] ?? $criterion['name'] ?? null) : null)
            ?? $indicator['nama_kriteria']
            ?? $indicator['criterion'];
        $indicator['parameter'] = $indicator['parameter_penilaian']
            ?? $indicator['parameter']
            ?? $indicator['deskripsi']
            ?? 'Parameter penilaian belum tersedia.';
        $indicator['narrative'] = $indicator['narasi_prodi'] ?? $indicator['narrative'] ?? null;
        $indicator['evidence'] = is_array($evidence)
            ? array_values(array_map(function (array $item): array {
                $item['type'] = $item['jenis_bukti'] ?? $item['type'] ?? $item['tipe'] ?? 'Bukti pendukung';
                $item['name'] = $item['nama_bukti'] ?? $item['name'] ?? $item['nama'] ?? 'Dokumen bukti';
                $item['date'] = $item['tanggal'] ?? $item['date'] ?? '—';
                $item['status'] = $item['status_review'] ?? $item['status'] ?? 'Pending';

                return $item;
            }, array_filter($evidence, 'is_array')))
            : [];

        return $indicator;
    }

    protected function nestedName(mixed $value): ?string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        if (! is_array($value)) {
            return null;
        }

        foreach (['nama_prodi', 'nama_jenjang', 'nama_jenis_instrumen', 'nama_jenis', 'nama_instrumen', 'nama_versi', 'nomor_versi', 'nama', 'name'] as $key) {
            if (isset($value[$key]) && (is_string($value[$key]) || is_numeric($value[$key]))) {
                return (string) $value[$key];
            }
        }

        return null;
    }

    protected function statusLabel(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'review', 'sedang_dinilai', 'sedang dinilai', 'in_review', 'in progress', 'in_progress' => 'Sedang dinilai',
            'submit', 'submitted', 'selesai', 'complete', 'completed' => 'Selesai',
            'draft', 'menunggu', 'pending', 'waiting' => 'Menunggu',
            default => $status !== '' ? $status : 'Menunggu',
        };
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function statusKey(array $project): string
    {
        $status = strtolower(trim((string) ($project['status'] ?? $project['status_project'] ?? '')));

        if (str_contains($status, 'selesai') || str_contains($status, 'complete') || $status === 'submit' || $status === 'submitted') {
            return 'selesai';
        }

        if (str_contains($status, 'menunggu') || str_contains($status, 'pending') || str_contains($status, 'belum') || str_contains($status, 'draft')) {
            return 'menunggu';
        }

        if (str_contains($status, 'dinilai') || str_contains($status, 'progress') || str_contains($status, 'proses') || $status === 'review' || $status === 'in_review') {
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

        return str_contains($status, 'selesai') || str_contains($status, 'complete') || $status === 'submit' || $status === 'submitted';
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function isInProgress(array $project): bool
    {
        $status = strtolower((string) ($project['status'] ?? $project['status_project'] ?? ''));

        return str_contains($status, 'dinilai') || str_contains($status, 'progress') || $status === 'review' || $status === 'in_review';
    }

    /**
     * @param  array<string, mixed>  $project
     */
    protected function progress(array $project): float
    {
        $value = $project['_reviewer_progress'] ?? $project['progress_percentage'] ?? $project['progress'] ?? $project['persentase'] ?? null;

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
