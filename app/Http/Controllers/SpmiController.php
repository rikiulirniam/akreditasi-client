<?php

namespace App\Http\Controllers;

use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SpmiController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService,
    ) {}

    /**
     * Display the Evidence SPMI page per Project Akreditasi.
     */
    public function evidenceSpmi(Request $request): View
    {
        // 1. Muat daftar project akreditasi dari API / Fallback
        $projects = $this->loadProjectsFromApi();

        $defaultProjectId = $projects[0]['id'] ?? 1;
        $selectedProjectId = (int) $request->query('project_id', $defaultProjectId);

        // Cari data project yang sedang aktif dipilih
        $activeProject = null;
        foreach ($projects as $p) {
            if ((int) ($p['id'] ?? 0) === $selectedProjectId) {
                $activeProject = $p;
                break;
            }
        }
        if (! $activeProject && ! empty($projects)) {
            $activeProject = $projects[0];
            $selectedProjectId = (int) ($activeProject['id'] ?? 1);
        }

        // 2. Load data evidence SPMI untuk project yang dipilih (dengan sinkronisasi API & cache persisten)
        $evidenceData = $this->loadEvidenceForProject($selectedProjectId);

        // 3. Hitung statistik ringkasan kelengkapan 4 pilar evidence
        $summary = $this->calculateEvidenceSummary($evidenceData);

        // 4. Definisi 4 kategori dokumen bukti evidence
        $kategoriEvidence = $this->getKategoriEvidence();

        return view('pages.kaprodi.evidence-spmi', [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
            'activeProject' => $activeProject,
            'evidenceData' => $evidenceData,
            'summary' => $summary,
            'kategoriEvidence' => $kategoriEvidence,
        ]);
    }

    /**
     * Store evidence SPMI submission to Akreditasi API & local persistent cache.
     */
    public function storeEvidence(Request $request): RedirectResponse
    {
        $projectId = (int) $request->input('project_id', $request->input('standar_id', 1));
        $status = 'terverifikasi';

        // Muat data tersimpan saat ini untuk project ini
        $stored = $this->loadEvidenceForProject($projectId);

        $evidences = $request->input('evidences');

        if (is_array($evidences)) {
            // Format bertingkat (array categories)
            foreach ($evidences as $kategori => $item) {
                $deskripsi = trim((string) ($item['deskripsi'] ?? ''));
                $linkDokumen = trim((string) ($item['link_dokumen'] ?? ''));
                $id = ! empty($item['id']) ? (int) $item['id'] : null;

                $stored[$kategori] = [
                    'id' => $id ?: (int) ($projectId * 100 + (abs(crc32($kategori)) % 90) + 10),
                    'deskripsi' => $deskripsi,
                    'link_dokumen' => $linkDokumen,
                    'status' => ($deskripsi !== '' || $linkDokumen !== '') ? $status : 'belum_terisi',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        } else {
            // Format form langsung (flat fields)
            $mappings = [
                'bukti_evaluasi' => [
                    'deskripsi' => $request->input('deskripsi_bukti_evaluasi', $request->input('deskripsi')),
                    'link' => $request->input('link_bukti_evaluasi', $request->input('link_dokumen')),
                ],
                'bukti_pengendalian' => [
                    'deskripsi' => $request->input('deskripsi_bukti_pengendalian'),
                    'link' => $request->input('link_bukti_pengendalian'),
                ],
                'bukti_dampak_tridharma' => [
                    'deskripsi' => $request->input('deskripsi_bukti_dampak', $request->input('deskripsi_bukti_dampak_tridharma')),
                    'link' => $request->input('link_bukti_dampak', $request->input('link_bukti_dampak_tridharma')),
                ],
                'dokumen_implementasi' => [
                    'deskripsi' => $request->input('deskripsi_dokumen_implementasi'),
                    'link' => $request->input('link_dokumen_implementasi'),
                ],
            ];

            // Jika ada input single kategori (seperti dari test lama)
            $singleKat = $request->input('kategori');
            if ($singleKat && isset($stored[$singleKat])) {
                $deskripsi = trim((string) $request->input('deskripsi', ''));
                $link = trim((string) $request->input('link_dokumen', ''));
                $stored[$singleKat] = [
                    'id' => (int) ($request->input('id') ?: ($projectId * 100 + 1)),
                    'deskripsi' => $deskripsi,
                    'link_dokumen' => $link,
                    'status' => ($deskripsi !== '' || $link !== '') ? $status : 'belum_terisi',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            } else {
                foreach ($mappings as $katKey => $vals) {
                    $deskripsi = trim((string) ($vals['deskripsi'] ?? ''));
                    $link = trim((string) ($vals['link'] ?? ''));

                    // Hanya update jika field dikirimkan di request
                    if ($request->hasAny([
                        "deskripsi_{$katKey}",
                        "link_{$katKey}",
                        'link_bukti_evaluasi',
                        'deskripsi_bukti_evaluasi',
                    ])) {
                        $stored[$katKey] = [
                            'id' => $stored[$katKey]['id'] ?? (int) ($projectId * 100 + 1),
                            'deskripsi' => $deskripsi,
                            'link_dokumen' => $link,
                            'status' => ($deskripsi !== '' || $link !== '') ? $status : 'belum_terisi',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }
        }

        // Simpan ke Cache persisten lokal
        Cache::forever("spmi_evidence_project_{$projectId}", $stored);

        // Siapkan payload untuk API backend
        $apiPayload = [
            'project_id' => $projectId,
            'link_bukti_evaluasi' => $stored['bukti_evaluasi']['link_dokumen'] ?? null,
            'deskripsi_bukti_evaluasi' => $stored['bukti_evaluasi']['deskripsi'] ?? null,
            'link_bukti_pengendalian' => $stored['bukti_pengendalian']['link_dokumen'] ?? null,
            'deskripsi_bukti_pengendalian' => $stored['bukti_pengendalian']['deskripsi'] ?? null,
            'link_bukti_dampak' => $stored['bukti_dampak_tridharma']['link_dokumen'] ?? null,
            'deskripsi_bukti_dampak' => $stored['bukti_dampak_tridharma']['deskripsi'] ?? null,
            'link_dokumen_implementasi' => $stored['dokumen_implementasi']['link_dokumen'] ?? null,
            'deskripsi_dokumen_implementasi' => $stored['dokumen_implementasi']['deskripsi'] ?? null,
        ];

        // Sinkronisasi ke backend API
        try {
            $this->akreditasiService->saveEvidenceSpmi($apiPayload);
        } catch (\Throwable $e) {
            // Abaikan kegagalan jaringan, data sudah tersimpan di persistent cache
        }

        return redirect()->route('spmi.evidence', [
            'project_id' => $projectId,
        ])->with('success', 'Data Evidence SPMI berhasil disimpan.');
    }

    /**
     * Update existing evidence SPMI.
     */
    public function updateEvidence(Request $request, int|string $id): RedirectResponse
    {
        $projectId = (int) $request->input('project_id', $request->input('standar_id', 1));
        $kategori = (string) $request->input('kategori', 'bukti_evaluasi');
        $deskripsi = trim((string) $request->input('deskripsi', ''));
        $linkDokumen = trim((string) $request->input('link_dokumen', ''));

        $stored = $this->loadEvidenceForProject($projectId);
        $stored[$kategori] = [
            'id' => (int) $id,
            'deskripsi' => $deskripsi,
            'link_dokumen' => $linkDokumen,
            'status' => ($deskripsi !== '' || $linkDokumen !== '') ? 'terverifikasi' : 'belum_terisi',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        Cache::forever("spmi_evidence_project_{$projectId}", $stored);

        try {
            $this->akreditasiService->updateEvidenceSpmi($id, [
                'project_id' => $projectId,
                'deskripsi' => $deskripsi,
                'link_dokumen' => $linkDokumen,
            ]);
        } catch (\Throwable $e) {
            // Fallback jika API belum tersedia
        }

        return redirect()->route('spmi.evidence', [
            'project_id' => $projectId,
        ])->with('success', 'Data Evidence SPMI berhasil diperbarui.');
    }

    /**
     * Memuat daftar project akreditasi dari API atau fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadProjectsFromApi(): array
    {
        try {
            $response = $this->akreditasiService->getProjects();
            if ($response->successful()) {
                $apiData = $response->json('data') ?? [];
                if (is_array($apiData) && ! empty($apiData)) {
                    $mapped = [];
                    foreach ($apiData as $item) {
                        $mapped[] = [
                            'id' => (int) ($item['id'] ?? 1),
                            'nama_project' => $item['nama_project'] ?? 'Project Akreditasi',
                            'tahun_target_submit' => (int) ($item['tahun_target_submit'] ?? date('Y')),
                            'status_project' => ucfirst((string) ($item['status_project'] ?? 'In Progress')),
                            'nama_lembaga' => $item['nama_lembaga'] ?? ($item['lembaga']['nama_lembaga'] ?? 'LAM INFOKOM'),
                            'nama_prodi' => $item['nama_prodi'] ?? 'D4 Teknologi Rekayasa Komputer',
                        ];
                    }

                    return $mapped;
                }
            }
        } catch (\Throwable $e) {
            // Abaikan error koneksi
        }

        return $this->getDefaultProjects();
    }

    /**
     * Memuat data evidence untuk project tertentu (dari Cache lokal & disinkronkan dengan API).
     *
     * @return array<string, array<string, mixed>>
     */
    protected function loadEvidenceForProject(int $projectId): array
    {
        $cached = Cache::get("spmi_evidence_project_{$projectId}");

        if ($cached === null) {
            $cached = $this->getDefaultEvidenceStructure($projectId);
            Cache::forever("spmi_evidence_project_{$projectId}", $cached);
        }

        try {
            $response = $this->akreditasiService->getEvidenceSpmi(['project_id' => $projectId]);
            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                if (is_array($data) && ! empty($data)) {
                    // Cek jika API mengembalikan flat object per project
                    if (isset($data['project_id']) || isset($data['link_bukti_evaluasi']) || isset($data['link_bukti_pengendalian'])) {
                        $cached['bukti_evaluasi']['link_dokumen'] = $data['link_bukti_evaluasi'] ?? $cached['bukti_evaluasi']['link_dokumen'];
                        $cached['bukti_evaluasi']['deskripsi'] = $data['deskripsi_bukti_evaluasi'] ?? $cached['bukti_evaluasi']['deskripsi'];
                        $cached['bukti_evaluasi']['status'] = (! empty($cached['bukti_evaluasi']['link_dokumen']) || ! empty($cached['bukti_evaluasi']['deskripsi'])) ? 'terverifikasi' : 'belum_terisi';

                        $cached['bukti_pengendalian']['link_dokumen'] = $data['link_bukti_pengendalian'] ?? $cached['bukti_pengendalian']['link_dokumen'];
                        $cached['bukti_pengendalian']['deskripsi'] = $data['deskripsi_bukti_pengendalian'] ?? $cached['bukti_pengendalian']['deskripsi'];
                        $cached['bukti_pengendalian']['status'] = (! empty($cached['bukti_pengendalian']['link_dokumen']) || ! empty($cached['bukti_pengendalian']['deskripsi'])) ? 'terverifikasi' : 'belum_terisi';

                        $cached['bukti_dampak_tridharma']['link_dokumen'] = $data['link_bukti_dampak'] ?? ($data['link_bukti_dampak_tridharma'] ?? $cached['bukti_dampak_tridharma']['link_dokumen']);
                        $cached['bukti_dampak_tridharma']['deskripsi'] = $data['deskripsi_bukti_dampak'] ?? ($data['deskripsi_bukti_dampak_tridharma'] ?? $cached['bukti_dampak_tridharma']['deskripsi']);
                        $cached['bukti_dampak_tridharma']['status'] = (! empty($cached['bukti_dampak_tridharma']['link_dokumen']) || ! empty($cached['bukti_dampak_tridharma']['deskripsi'])) ? 'terverifikasi' : 'belum_terisi';

                        $cached['dokumen_implementasi']['link_dokumen'] = $data['link_dokumen_implementasi'] ?? $cached['dokumen_implementasi']['link_dokumen'];
                        $cached['dokumen_implementasi']['deskripsi'] = $data['deskripsi_dokumen_implementasi'] ?? $cached['dokumen_implementasi']['deskripsi'];
                        $cached['dokumen_implementasi']['status'] = (! empty($cached['dokumen_implementasi']['link_dokumen']) || ! empty($cached['dokumen_implementasi']['deskripsi'])) ? 'terverifikasi' : 'belum_terisi';

                        Cache::forever("spmi_evidence_project_{$projectId}", $cached);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan kesalahan koneksi
        }

        return $cached;
    }

    /**
     * Menghitung ringkasan statistik evidence untuk project aktif.
     *
     * @param  array<string, array<string, mixed>>  $evidenceData
     * @return array<string, int>
     */
    protected function calculateEvidenceSummary(array $evidenceData): array
    {
        $totalSlots = 4; // 4 pilar kategori evidence per project
        $terisi = 0;

        foreach ($evidenceData as $evidence) {
            $hasContent = ! empty($evidence['deskripsi']) || ! empty($evidence['link_dokumen']);
            if ($hasContent) {
                $terisi++;
            }
        }

        $belumTerisi = max(0, $totalSlots - $terisi);

        return [
            'total_evidence' => $totalSlots,
            'terisi' => $terisi,
            'terverifikasi' => $terisi,
            'belum_terisi' => $belumTerisi,
            'persen' => (int) round(($terisi / $totalSlots) * 100),
        ];
    }

    /**
     * Definisi 4 kategori evidence SPMI.
     *
     * @return array<int, array<string, string>>
     */
    protected function getKategoriEvidence(): array
    {
        return [
            [
                'key' => 'bukti_evaluasi',
                'label' => 'Bukti Evaluasi',
                'deskripsi' => 'Dokumen evaluasi capaian standar mutu (hasil audit mutu internal, analisis gap, evaluasi diri).',
                'field_link' => 'link_bukti_evaluasi',
                'field_deskripsi' => 'deskripsi_bukti_evaluasi',
                'icon' => 'clipboard-check',
            ],
            [
                'key' => 'bukti_pengendalian',
                'label' => 'Bukti Pengendalian & Peningkatan',
                'deskripsi' => 'Dokumen tindakan korektif, preventif, dan peningkatan mutu berkelanjutan (RTL & RTM).',
                'field_link' => 'link_bukti_pengendalian',
                'field_deskripsi' => 'deskripsi_bukti_pengendalian',
                'icon' => 'adjustments',
            ],
            [
                'key' => 'bukti_dampak_tridharma',
                'label' => 'Bukti Dampak Tridharma',
                'deskripsi' => 'Bukti luaran Tridharma (prestasi mahasiswa/dosen, publikasi, luaran riset, kepuasan mitra).',
                'field_link' => 'link_bukti_dampak',
                'field_deskripsi' => 'deskripsi_bukti_dampak',
                'icon' => 'academic-cap',
            ],
            [
                'key' => 'dokumen_implementasi',
                'label' => 'Dokumen Implementasi Harian',
                'deskripsi' => 'Dokumen operasional harian prodi (SOP, instrumen, logbook, rubrik penilaian, notulen).',
                'field_link' => 'link_dokumen_implementasi',
                'field_deskripsi' => 'deskripsi_dokumen_implementasi',
                'icon' => 'document-text',
            ],
        ];
    }

    /**
     * Default struktur data evidence untuk project baru.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getDefaultEvidenceStructure(int $projectId): array
    {
        // Berikan data demo bawaan jika project default 1
        if ($projectId === 1) {
            return [
                'bukti_evaluasi' => [
                    'id' => 101,
                    'deskripsi' => 'Laporan Hasil Audit Mutu Internal (AMI) dan analisis gap kurikulum semester berjalan.',
                    'link_dokumen' => 'https://drive.google.com/file/d/ami-evaluasi-trk-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-09-15',
                ],
                'bukti_pengendalian' => [
                    'id' => 102,
                    'deskripsi' => 'Dokumen Rencana Tindak Lanjut (RTL) dan notulensi Rapat Tinjauan Manajemen (RTM).',
                    'link_dokumen' => 'https://drive.google.com/file/d/rtl-rtm-trk-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-09-20',
                ],
                'bukti_dampak_tridharma' => [
                    'id' => 103,
                    'deskripsi' => '',
                    'link_dokumen' => '',
                    'status' => 'belum_terisi',
                    'updated_at' => null,
                ],
                'dokumen_implementasi' => [
                    'id' => 104,
                    'deskripsi' => '',
                    'link_dokumen' => '',
                    'status' => 'belum_terisi',
                    'updated_at' => null,
                ],
            ];
        }

        return [
            'bukti_evaluasi' => [
                'id' => null,
                'deskripsi' => '',
                'link_dokumen' => '',
                'status' => 'belum_terisi',
                'updated_at' => null,
            ],
            'bukti_pengendalian' => [
                'id' => null,
                'deskripsi' => '',
                'link_dokumen' => '',
                'status' => 'belum_terisi',
                'updated_at' => null,
            ],
            'bukti_dampak_tridharma' => [
                'id' => null,
                'deskripsi' => '',
                'link_dokumen' => '',
                'status' => 'belum_terisi',
                'updated_at' => null,
            ],
            'dokumen_implementasi' => [
                'id' => null,
                'deskripsi' => '',
                'link_dokumen' => '',
                'status' => 'belum_terisi',
                'updated_at' => null,
            ],
        ];
    }

    /**
     * Default projects list untuk fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getDefaultProjects(): array
    {
        return [
            [
                'id' => 1,
                'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2026',
                'tahun_target_submit' => 2026,
                'status_project' => 'In Progress',
                'nama_lembaga' => 'LAM INFOKOM',
                'nama_prodi' => 'D4 Teknologi Rekayasa Komputer',
            ],
            [
                'id' => 2,
                'nama_project' => 'Akreditasi BAN-PT D4 TRK 2025',
                'tahun_target_submit' => 2025,
                'status_project' => 'Selesai',
                'nama_lembaga' => 'BAN-PT',
                'nama_prodi' => 'D4 Teknologi Rekayasa Komputer',
            ],
        ];
    }
}
