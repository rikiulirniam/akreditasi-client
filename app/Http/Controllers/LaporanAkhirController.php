<?php

namespace App\Http\Controllers;

use App\Services\AkreditasiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanAkhirController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService,
    ) {}

    /**
     * Display the Laporan Akhir page.
     */
    public function index(Request $request): View
    {
        $selectedYear = (int) $request->query('tahun', 2026);
        $project = 'Akreditasi LAM INFOKOM D4 TRK 2027';
        $statusProject = 'Dinilai';

        // Muat project akreditasi dari Akreditasi API jika ada
        try {
            $projectRes = $this->akreditasiService->getProjects();
            if ($projectRes->successful()) {
                $projects = $projectRes->json('data');
                if (is_array($projects) && ! empty($projects)) {
                    $activeProj = $projects[0];
                    $project = $activeProj['nama_project'] ?? $project;
                    $statusProject = ucfirst($activeProj['status_project'] ?? $statusProject);
                }
            }
        } catch (\Throwable $e) {
            // Fallback ke default
        }

        // Muat master kriteria dari API
        $rekapKriteria = $this->loadRekapKriteriaFromApi();

        $summaryKesiapan = [
            'skor' => '3,08',
            'skor_max' => '4,00',
            'status' => 'Mendekati target',
            'kelengkapan_borang' => 61,
            'indikator_tercapai' => 14,
            'di_bawah_target' => 6,
            'temuan_terbuka' => 5,
        ];

        $gapAnalysis = [
            ['kode' => 'K3.1', 'indikator' => 'Seleksi mahasiswa baru', 'skor' => '2,50'],
            ['kode' => 'K4.2', 'indikator' => 'Rasio dosen-mahasiswa', 'skor' => '2,40'],
            ['kode' => 'K7.1', 'indikator' => 'Roadmap penelitian', 'skor' => '2,60'],
            ['kode' => 'K7.3', 'indikator' => 'Publikasi dosen', 'skor' => '2,50'],
            ['kode' => 'K4.4', 'indikator' => 'Kualifikasi akademik dosen', 'skor' => '2,80'],
            ['kode' => 'K8.2', 'indikator' => 'Keterlibatan mahasiswa PkM', 'skor' => '2,90'],
        ];

        return view('pages.kaprodi.laporan-akhir', [
            'project' => $project,
            'statusProject' => $statusProject,
            'selectedYear' => $selectedYear,
            'summaryKesiapan' => $summaryKesiapan,
            'rekapKriteria' => $rekapKriteria,
            'gapAnalysis' => $gapAnalysis,
        ]);
    }

    /**
     * Memuat daftar kriteria akreditasi dari API atau fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadRekapKriteriaFromApi(): array
    {
        $defaultKriteria = [
            ['kode' => 'K1', 'nama' => 'Visi, Misi, Tujuan, Strategi', 'skor' => '3,40', 'target' => '3,25', 'tercapai' => true],
            ['kode' => 'K2', 'nama' => 'Tata Pamong dan Kerja Sama', 'skor' => '3,20', 'target' => '3,25', 'tercapai' => false],
            ['kode' => 'K3', 'nama' => 'Mahasiswa', 'skor' => '2,80', 'target' => '3,25', 'tercapai' => false],
            ['kode' => 'K4', 'nama' => 'Sumber Daya Manusia', 'skor' => '2,90', 'target' => '3,25', 'tercapai' => false],
            ['kode' => 'K5', 'nama' => 'Keuangan dan Sarpras', 'skor' => '3,10', 'target' => '3,25', 'tercapai' => false],
            ['kode' => 'K6', 'nama' => 'Pendidikan', 'skor' => '3,30', 'target' => '3,25', 'tercapai' => true],
            ['kode' => 'K7', 'nama' => 'Penelitian', 'skor' => '2,70', 'target' => '3,25', 'tercapai' => false],
            ['kode' => 'K8', 'nama' => 'Pengabdian kepada Masyarakat', 'skor' => '3,00', 'target' => '3,25', 'tercapai' => false],
            ['kode' => 'K9', 'nama' => 'Luaran dan Capaian', 'skor' => '3,32', 'target' => '3,25', 'tercapai' => true],
        ];

        try {
            $res = $this->akreditasiService->getKriteria();
            if ($res->successful()) {
                $apiKriteria = $res->json('data');
                if (is_array($apiKriteria) && ! empty($apiKriteria)) {
                    $mapped = [];
                    foreach ($apiKriteria as $i => $item) {
                        $mapped[] = [
                            'kode' => $item['kode_kriteria'] ?? ('K'.($i + 1)),
                            'nama' => $item['nama_kriteria'] ?? 'Kriteria Akreditasi',
                            'skor' => '3,40',
                            'target' => '3,25',
                            'tercapai' => true,
                        ];
                    }

                    $existingKodes = array_column($mapped, 'kode');
                    foreach ($defaultKriteria as $def) {
                        if (! in_array($def['kode'], $existingKodes, true)) {
                            $mapped[] = $def;
                        }
                    }

                    return $mapped;
                }
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return $defaultKriteria;
    }
}
