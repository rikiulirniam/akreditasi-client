<?php

namespace App\Http\Controllers;

use App\Services\ReviewerService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewerController extends Controller
{
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

        if (is_array($indicators) && collect($indicators)->contains(fn (mixed $item): bool => is_array($item) && ($item['_needs_api_detail'] ?? false))) {
            try {
                $indicatorResponse = $this->reviewerService->getIndicators();
            } catch (ConnectionException) {
                $indicatorResponse = null;
                $detailError = 'Tidak dapat memuat detail indikator dari server akreditasi.';
            }

            if ($indicatorResponse?->successful()) {
                $apiIndicators = $this->responseList($indicatorResponse->json());

                if ($apiIndicators !== null) {
                    $assignedIndicatorIds = array_fill_keys(array_map(
                        fn (array $item): string => (string) $item['id'],
                        array_filter($indicators, fn (mixed $item): bool => is_array($item) && isset($item['id'])),
                    ), true);
                    $indicators = array_values(array_filter(
                        array_map(fn (array $item): array => $this->normalizeIndicator($item), $apiIndicators),
                        fn (array $item): bool => isset($assignedIndicatorIds[(string) $item['id']]),
                    ));
                    $project['indicators'] = $indicators;
                } else {
                    $indicators = [];
                    $detailError = 'Format data indikator dari server akreditasi tidak sesuai.';
                }
            } elseif ($indicatorResponse !== null) {
                $indicators = [];
                $detailError = $this->apiErrorMessage($indicatorResponse);
            } else {
                $indicators = [];
            }
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
        $savedAssessment = session("reviewer_assessments.{$projectId}.{$indicator['id']}", []);
        $savedAssessments = array_reduce($indicators, function (array $assessments, array $item) use ($projectId): array {
            $saved = session("reviewer_assessments.{$projectId}.{$item['id']}", []);

            if (is_array($saved)) {
                $assessments[(string) $item['id']] = $saved;
            }

            return $assessments;
        }, []);

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
        $indicatorExists = $project !== null && collect($project['indicators'] ?? [])
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
            'evidence_status' => ['nullable', 'array'],
            'evidence_status.*' => ['required', 'in:Disetujui,Pending,Perlu revisi'],
            'evidence_action' => ['nullable', 'string', 'regex:/^\d+:(Disetujui|Perlu revisi)$/'],
        ]);

        $evidenceStatuses = $validated['evidence_status'] ?? [];
        if (isset($validated['evidence_action'])) {
            [$evidenceIndex, $evidenceStatus] = explode(':', $validated['evidence_action'], 2);
            $evidenceStatuses[$evidenceIndex] = $evidenceStatus;
        }

        $request->session()->put("reviewer_assessments.{$projectId}.{$indicatorId}", [
            'score' => (int) $validated['score'],
            'notes' => $validated['notes'] ?? '',
            'evidence_status' => $evidenceStatuses,
        ]);

        return redirect()->route('reviewer.assessment-detail', [
            'projectId' => $projectId,
            'indikator' => $indicatorId,
        ])->with('toast', [
            'type' => 'success',
            'title' => 'Penilaian tersimpan',
            'message' => 'Skor dan catatan indikator berhasil disimpan untuk sesi ini.',
        ]);
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
            return [$this->prepareProjects($projects), null, true];
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
            return [$this->prepareProjects($projects), null, true];
        }

        return [[], 'File data dummy reviewer tidak ditemukan.', false];
    }

    /**
     * @param  array<int, array<string, mixed>>  $projects
     * @return array<int, array<string, mixed>>
     */
    protected function prepareProjects(array $projects): array
    {
        return array_map(function (array $project): array {
            $project = $this->normalizeProject($project);
            $project['_reviewer_progress'] = $this->progress($project);
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

        foreach (['nama_prodi', 'nama_jenjang', 'nama_jenis_instrumen', 'nama_instrumen', 'nama', 'name'] as $key) {
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

        if (str_contains($status, 'menunggu') || str_contains($status, 'pending') || str_contains($status, 'belum')) {
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
        $target = $project['target_submit'] ?? $project['tahun'] ?? $project['tahun_akademik'] ?? '';

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
