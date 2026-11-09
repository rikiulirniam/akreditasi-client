<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GapAnalysisController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'status' => ['nullable', 'in:all,gap_only,target_met'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $selectedYear = (int) ($validated['tahun'] ?? now()->year);
        $statusFilter = $validated['status'] ?? 'all';
        $search = trim($validated['search'] ?? '');
        $apiError = null;
        $rawIndicators = [];
        $apiSummary = [];

        try {
            $response = $this->akademikService->getAssessmentPendidikanGap(['tahun' => $selectedYear]);

            if ($response->status() === 401) {
                return redirect()->route('login')->with('toast', [
                    'type' => 'warning',
                    'title' => 'Sesi Berakhir',
                    'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
                ]);
            }

            if (! $response->successful()) {
                $apiError = (string) ($response->json('message') ?? 'Data gap analysis tidak dapat dimuat dari API.');
            } else {
                $data = $response->json('data') ?? [];
                if (! is_array($data)) {
                    $data = [];
                }

                $apiSummary = is_array($data['summary'] ?? null) ? $data['summary'] : [];
                $items = $data['indicators'] ?? $data['items'] ?? $data['data'] ?? $data;
                if (is_array($items)) {
                    $rawIndicators = array_values(array_filter($items, 'is_array'));
                }
            }
        } catch (ConnectionException $exception) {
            Log::warning('Tidak dapat terhubung ke API gap analysis pendidikan.', [
                'message' => $exception->getMessage(),
            ]);
            $apiError = 'Server gap analysis sedang tidak dapat dihubungi. Silakan coba kembali.';
        }

        $indicators = array_map(fn (array $item): array => $this->normalizeIndicator($item), $rawIndicators);
        $filteredItems = array_values(array_filter($indicators, function (array $item) use ($statusFilter, $search): bool {
            $hasGap = $item['gap_numeric'] !== null
                ? $item['gap_numeric'] < 0
                : $item['status'] === 'Di Bawah Target';

            if ($statusFilter === 'gap_only' && ! $hasGap) {
                return false;
            }
            if ($statusFilter === 'target_met' && $hasGap) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            $needle = mb_strtolower($search);

            return str_contains(mb_strtolower($item['kode']), $needle)
                || str_contains(mb_strtolower($item['nama']), $needle)
                || str_contains(mb_strtolower($item['rekomendasi']), $needle);
        }));

        $actualScores = array_values(array_filter(array_column($indicators, 'aktual_value'), 'is_numeric'));
        $targetScores = array_values(array_filter(array_column($indicators, 'target_value'), 'is_numeric'));
        $gaps = array_values(array_filter(array_column($indicators, 'gap_numeric'), 'is_numeric'));
        $belowTargetCount = count(array_filter(
            $indicators,
            fn (array $item): bool => $item['status'] === 'Di Bawah Target'
        ));
        $metTargetCount = count(array_filter(
            $indicators,
            fn (array $item): bool => $item['status'] === 'Memenuhi Target'
        ));
        $readiness = count($targetScores) > 0
            ? round(array_sum($actualScores) / max(array_sum($targetScores), 0.00001) * 100, 1)
            : null;

        $stats = [
            'total_indikator' => count($indicators),
            'tercapai_count' => $metTargetCount,
            'gap_count' => $belowTargetCount,
            'skor_aktual_avg' => $this->numericAssessmentValue($apiSummary['skor_aktual_avg'] ?? null)
                ?? (count($actualScores) > 0 ? round(array_sum($actualScores) / count($actualScores), 2) : null),
            'skor_target_avg' => $this->numericAssessmentValue($apiSummary['skor_target_avg'] ?? null)
                ?? (count($targetScores) > 0 ? round(array_sum($targetScores) / count($targetScores), 2) : null),
            'skor_gap_avg' => $this->numericAssessmentValue($apiSummary['skor_gap_avg'] ?? null)
                ?? (count($gaps) > 0 ? round(array_sum($gaps) / count($gaps), 2) : null),
            'persentase_kesiapan' => $this->numericAssessmentValue($apiSummary['persentase_kesiapan'] ?? null) ?? $readiness,
        ];

        return view('pages.gap-analysis.index', [
            'indicators' => $filteredItems,
            'stats' => $stats,
            'selectedYear' => $selectedYear,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'apiError' => $apiError,
        ]);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeIndicator(array $item): array
    {
        $actual = $this->numericAssessmentValue($item['aktual_value'] ?? $item['skor_aktual'] ?? $item['aktual'] ?? null);
        $target = $this->numericAssessmentValue($item['target_value'] ?? $item['skor_target'] ?? $item['target'] ?? null);
        $gap = $this->numericAssessmentValue($item['gap_numeric'] ?? $item['gap'] ?? null);

        if ($gap === null && $actual !== null && $target !== null) {
            $gap = $actual - $target;
        }

        $status = trim((string) ($item['status'] ?? ''));
        if (in_array(mb_strtolower($status), ['memenuhi target', 'tercapai'], true)) {
            $status = 'Memenuhi Target';
        } elseif (in_array(mb_strtolower($status), ['di bawah target', 'belum tercapai'], true)) {
            $status = 'Di Bawah Target';
        } elseif ($gap !== null) {
            $status = $gap >= 0 ? 'Memenuhi Target' : 'Di Bawah Target';
        } else {
            $status = 'Data tidak tersedia';
        }

        return [
            'id' => $item['id'] ?? null,
            'kode' => (string) ($item['kode'] ?? $item['kode_indikator'] ?? $item['kode_kriteria'] ?? '-'),
            'kriteria' => (string) ($item['kriteria'] ?? $item['nama_kriteria'] ?? 'Kriteria Pendidikan'),
            'nama' => (string) ($item['nama'] ?? $item['indikator'] ?? $item['nama_indikator'] ?? '-'),
            'standar_deskripsi' => (string) ($item['standar_deskripsi'] ?? $item['standar'] ?? ''),
            'target_label' => (string) ($item['target_label'] ?? $item['target'] ?? '-'),
            'target_value' => $target,
            'aktual_label' => (string) ($item['aktual_label'] ?? $item['aktual_value'] ?? $item['skor_aktual'] ?? '-'),
            'aktual_value' => $actual,
            'gap_label' => (string) ($item['gap_label'] ?? $item['gap'] ?? '-'),
            'gap_numeric' => $gap,
            'status' => $status,
            'kategori_gap' => (string) ($item['kategori_gap'] ?? ''),
            'rekomendasi' => (string) ($item['rekomendasi'] ?? ''),
            'pic' => (string) ($item['pic'] ?? ''),
        ];
    }

    private function numericAssessmentValue(mixed $value): ?float
    {
        if (! is_numeric($value) && is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
