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
            'apiError' => $error,
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
        return array_map(function (array $project): array {
            $project['_reviewer_progress'] = $this->progress($project);
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

        if (str_contains($status, 'menunggu') || str_contains($status, 'pending') || str_contains($status, 'belum')) {
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
