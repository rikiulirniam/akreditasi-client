<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(): View
    {
        $projects = [];
        $lembagas = [];
        $versis = [];
        $indikators = [];
        $kriterias = [];
        $standars = [];

        try {
            $projects = $this->akreditasiService->getProjects()->json('data') ?? [];
            $lembagas = $this->akreditasiService->getLembagaAkreditasi()->json('data') ?? [];
            $versis = $this->akreditasiService->getVersiInstrumen()->json('data') ?? [];
            $indikators = $this->akreditasiService->getIndikators()->json('data') ?? [];
            $kriterias = $this->akreditasiService->getKriteria()->json('data') ?? [];
            $standars = $this->akreditasiService->getStandarMutu()->json('data') ?? [];
        } catch (\Throwable) {
            // Biarkan default array kosong jika API gagal diakses
        }

        $activeProjects = array_filter($projects, function (array $project): bool {
            $status = strtolower((string) ($project['status_project'] ?? $project['status'] ?? ''));

            return ! in_array($status, ['selesai', 'complete', 'ditolak']);
        });

        $activeVersi = array_filter($versis, function (array $versi): bool {
            return ! empty($versi['status_aktif']);
        });

        $stats = [
            [
                'label' => 'Lembaga Akreditasi',
                'value' => count($lembagas),
                'note' => count($versis).' versi instrumen',
                'iconBg' => 'bg-sky-100',
                'iconColor' => 'text-sky-800',
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'dot' => 'bg-sky-600',
            ],
            [
                'label' => 'Total Kriteria',
                'value' => count($kriterias),
                'note' => count($indikators).' indikator penilaian',
                'iconBg' => 'bg-emerald-100',
                'iconColor' => 'text-emerald-800',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.6L19 9v10a2 2 0 0 1-2 2Z',
                'dot' => 'bg-emerald-600',
            ],
            [
                'label' => 'Project Akreditasi',
                'value' => count($projects),
                'note' => count($activeProjects).' project aktif',
                'iconBg' => 'bg-rose-100',
                'iconColor' => 'text-rose-800',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'dot' => 'bg-rose-500',
            ],
            [
                'label' => 'Standar SPMI',
                'value' => count($standars),
                'note' => count($activeVersi).' instrumen aktif',
                'iconBg' => 'bg-amber-100',
                'iconColor' => 'text-amber-800',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'dot' => 'bg-amber-500',
            ],
        ];

        // Format prioritas pengisian dari indikator API riil
        $prioritas = [];
        foreach (array_slice($indikators, 0, 5) as $ind) {
            $prioritas[] = [
                'kode' => $ind['kode_indikator'] ?? 'IND',
                'judul' => $ind['deskripsi_indikator'] ?? $ind['nama_indikator'] ?? '-',
                'subkriteria' => $ind['subkriteria']['nama_subkriteria'] ?? '-',
                'deadline' => 'Tahap Berjalan',
                'status' => 'Tersedia',
            ];
        }

        $totalIndikator = count($indikators);
        $indikatorTerisi = 0;
        $persentase = $totalIndikator > 0
            ? (int) round(($indikatorTerisi / $totalIndikator) * 100)
            : 0;

        return view('pages.superadmin.dashboard', [
            'stats' => $stats,
            'prioritas' => $prioritas,
            'totalIndikator' => $totalIndikator,
            'indikatorTerisi' => $indikatorTerisi,
            'persentase' => $persentase,
        ]);
    }
}
