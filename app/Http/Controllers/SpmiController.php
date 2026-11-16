<?php

namespace App\Http\Controllers;

use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
                    'id' => $id ?: ($stored[$kategori]['id'] ?? null),
                    'deskripsi' => $deskripsi,
                    'link_dokumen' => $linkDokumen,
                    'status' => ($deskripsi !== '' || $linkDokumen !== '') ? $status : 'belum_terisi',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        // Tangkap juga input langsung berbasis nama kolom backend jika ada
        if ($request->hasAny([
            'link_bukti_evaluasi',
            'deskripsi_bukti_evaluasi',
            'link_bukti_pengendalian',
            'deskripsi_bukti_pengendalian',
            'link_bukti_dampak',
            'deskripsi_bukti_dampak',
            'link_bukti_dampak_tridharma',
            'deskripsi_bukti_dampak_tridharma',
            'link_dokumen_implementasi',
            'deskripsi_dokumen_implementasi',
        ])) {
            $linkEval = $request->input('link_bukti_evaluasi', $stored['bukti_evaluasi']['link_dokumen'] ?? '');
            $deskEval = $request->input('deskripsi_bukti_evaluasi', $stored['bukti_evaluasi']['deskripsi'] ?? '');
            $stored['bukti_evaluasi'] = [
                'id' => $stored['bukti_evaluasi']['id'] ?? null,
                'deskripsi' => trim((string) $deskEval),
                'link_dokumen' => trim((string) $linkEval),
                'status' => (trim((string) $deskEval) !== '' || trim((string) $linkEval) !== '') ? $status : 'belum_terisi',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $linkPeng = $request->input('link_bukti_pengendalian', $stored['bukti_pengendalian']['link_dokumen'] ?? '');
            $deskPeng = $request->input('deskripsi_bukti_pengendalian', $stored['bukti_pengendalian']['deskripsi'] ?? '');
            $stored['bukti_pengendalian'] = [
                'id' => $stored['bukti_pengendalian']['id'] ?? null,
                'deskripsi' => trim((string) $deskPeng),
                'link_dokumen' => trim((string) $linkPeng),
                'status' => (trim((string) $deskPeng) !== '' || trim((string) $linkPeng) !== '') ? $status : 'belum_terisi',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $linkDampak = $request->input('link_bukti_dampak', $request->input('link_bukti_dampak_tridharma', $stored['bukti_dampak_tridharma']['link_dokumen'] ?? ''));
            $deskDampak = $request->input('deskripsi_bukti_dampak', $request->input('deskripsi_bukti_dampak_tridharma', $stored['bukti_dampak_tridharma']['deskripsi'] ?? ''));
            $stored['bukti_dampak_tridharma'] = [
                'id' => $stored['bukti_dampak_tridharma']['id'] ?? null,
                'deskripsi' => trim((string) $deskDampak),
                'link_dokumen' => trim((string) $linkDampak),
                'status' => (trim((string) $deskDampak) !== '' || trim((string) $linkDampak) !== '') ? $status : 'belum_terisi',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $linkImpl = $request->input('link_dokumen_implementasi', $stored['dokumen_implementasi']['link_dokumen'] ?? '');
            $deskImpl = $request->input('deskripsi_dokumen_implementasi', $stored['dokumen_implementasi']['deskripsi'] ?? '');
            $stored['dokumen_implementasi'] = [
                'id' => $stored['dokumen_implementasi']['id'] ?? null,
                'deskripsi' => trim((string) $deskImpl),
                'link_dokumen' => trim((string) $linkImpl),
                'status' => (trim((string) $deskImpl) !== '' || trim((string) $linkImpl) !== '') ? $status : 'belum_terisi',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        // Dukung input single kategori (seperti dari test legacy)
        $singleKat = $request->input('kategori');
        if ($singleKat && isset($stored[$singleKat])) {
            $deskripsi = trim((string) $request->input('deskripsi', ''));
            $link = trim((string) $request->input('link_dokumen', ''));
            $stored[$singleKat] = [
                'id' => (int) ($request->input('id') ?: ($stored[$singleKat]['id'] ?? null)),
                'deskripsi' => $deskripsi,
                'link_dokumen' => $link,
                'status' => ($deskripsi !== '' || $link !== '') ? $status : 'belum_terisi',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        // Siapkan payload 100% presisi sesuai skema StoreEvidenceSpmiRequest backend
        $apiPayload = [
            'project_id' => $projectId,
            'link_bukti_evaluasi' => ! empty($stored['bukti_evaluasi']['link_dokumen']) ? $stored['bukti_evaluasi']['link_dokumen'] : null,
            'deskripsi_bukti_evaluasi' => ! empty($stored['bukti_evaluasi']['deskripsi']) ? $stored['bukti_evaluasi']['deskripsi'] : null,
            'link_bukti_pengendalian' => ! empty($stored['bukti_pengendalian']['link_dokumen']) ? $stored['bukti_pengendalian']['link_dokumen'] : null,
            'deskripsi_bukti_pengendalian' => ! empty($stored['bukti_pengendalian']['deskripsi']) ? $stored['bukti_pengendalian']['deskripsi'] : null,
            'link_bukti_dampak' => ! empty($stored['bukti_dampak_tridharma']['link_dokumen']) ? $stored['bukti_dampak_tridharma']['link_dokumen'] : null,
            'deskripsi_bukti_dampak' => ! empty($stored['bukti_dampak_tridharma']['deskripsi']) ? $stored['bukti_dampak_tridharma']['deskripsi'] : null,
            'link_dokumen_implementasi' => ! empty($stored['dokumen_implementasi']['link_dokumen']) ? $stored['dokumen_implementasi']['link_dokumen'] : null,
            'deskripsi_dokumen_implementasi' => ! empty($stored['dokumen_implementasi']['deskripsi']) ? $stored['dokumen_implementasi']['deskripsi'] : null,
        ];

        // Simpan ke Cache persisten lokal
        Cache::forever("spmi_evidence_project_{$projectId}", $stored);

        // Sinkronisasi ke backend API
        $successMessage = 'Data Evidence SPMI berhasil disimpan.';
        try {
            $response = $this->akreditasiService->saveEvidenceSpmi($apiPayload);
            if ($response->successful()) {
                $saved = $response->json('data');
                if (is_array($saved) && ! empty($saved['id'])) {
                    $stored['id'] = $saved['id'];
                    $stored['bukti_evaluasi']['id'] = $saved['id'];
                    $stored['bukti_pengendalian']['id'] = $saved['id'];
                    $stored['bukti_dampak_tridharma']['id'] = $saved['id'];
                    $stored['dokumen_implementasi']['id'] = $saved['id'];
                    Cache::forever("spmi_evidence_project_{$projectId}", $stored);
                }
                $successMessage = $response->json('message') ?? 'Data Evidence SPMI berhasil disimpan ke API.';
            } else {
                Log::warning('Respon API Evidence SPMI non-200', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::info('Koneksi API Evidence SPMI offline, fallback ke cache lokal: '.$e->getMessage());
        }

        return redirect()->route('spmi.evidence', [
            'project_id' => $projectId,
        ])->with('success', $successMessage);
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

        // Backend menggunakan updateOrCreate pada /evidence-spmi
        $apiPayload = [
            'project_id' => $projectId,
            'link_bukti_evaluasi' => ! empty($stored['bukti_evaluasi']['link_dokumen']) ? $stored['bukti_evaluasi']['link_dokumen'] : null,
            'deskripsi_bukti_evaluasi' => ! empty($stored['bukti_evaluasi']['deskripsi']) ? $stored['bukti_evaluasi']['deskripsi'] : null,
            'link_bukti_pengendalian' => ! empty($stored['bukti_pengendalian']['link_dokumen']) ? $stored['bukti_pengendalian']['link_dokumen'] : null,
            'deskripsi_bukti_pengendalian' => ! empty($stored['bukti_pengendalian']['deskripsi']) ? $stored['bukti_pengendalian']['deskripsi'] : null,
            'link_bukti_dampak' => ! empty($stored['bukti_dampak_tridharma']['link_dokumen']) ? $stored['bukti_dampak_tridharma']['link_dokumen'] : null,
            'deskripsi_bukti_dampak' => ! empty($stored['bukti_dampak_tridharma']['deskripsi']) ? $stored['bukti_dampak_tridharma']['deskripsi'] : null,
            'link_dokumen_implementasi' => ! empty($stored['dokumen_implementasi']['link_dokumen']) ? $stored['dokumen_implementasi']['link_dokumen'] : null,
            'deskripsi_dokumen_implementasi' => ! empty($stored['dokumen_implementasi']['deskripsi']) ? $stored['dokumen_implementasi']['deskripsi'] : null,
        ];

        try {
            $this->akreditasiService->saveEvidenceSpmi($apiPayload);
        } catch (\Throwable $e) {
            Log::info('Koneksi API Evidence SPMI offline pada update: '.$e->getMessage());
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
     * @return array<string, mixed>
     */
    protected function loadEvidenceForProject(int $projectId): array
    {
        $cached = Cache::get("spmi_evidence_project_{$projectId}");

        try {
            $response = $this->akreditasiService->getEvidenceSpmi(['project_id' => $projectId]);
            if ($response->successful()) {
                $data = $response->json('data');

                // 1. Jika data null dari API (project ada di sistem, tapi belum ada isian bukti di database)
                if ($data === null) {
                    $empty = $this->getEmptyEvidenceStructure($projectId);
                    Cache::forever("spmi_evidence_project_{$projectId}", $empty);

                    return $empty;
                }

                // 2. Jika API mengembalikan data flat dari /evidence-spmi
                if (is_array($data) && (isset($data['project_id']) || array_key_exists('link_bukti_evaluasi', $data))) {
                    $p1Link = $data['link_bukti_evaluasi'] ?? '';
                    $p1Desk = $data['deskripsi_bukti_evaluasi'] ?? '';
                    $p2Link = $data['link_bukti_pengendalian'] ?? '';
                    $p2Desk = $data['deskripsi_bukti_pengendalian'] ?? '';
                    $p3Link = $data['link_bukti_dampak'] ?? ($data['link_bukti_dampak_tridharma'] ?? '');
                    $p3Desk = $data['deskripsi_bukti_dampak'] ?? ($data['deskripsi_bukti_dampak_tridharma'] ?? '');
                    $p4Link = $data['link_dokumen_implementasi'] ?? '';
                    $p4Desk = $data['deskripsi_dokumen_implementasi'] ?? '';

                    $synced = [
                        'id' => $data['id'] ?? null,
                        'project_id' => $projectId,
                        'bukti_evaluasi' => [
                            'id' => $data['id'] ?? null,
                            'link_dokumen' => $p1Link,
                            'deskripsi' => $p1Desk,
                            'status' => (! empty($p1Link) || ! empty($p1Desk)) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $data['updated_at'] ?? null,
                        ],
                        'bukti_pengendalian' => [
                            'id' => $data['id'] ?? null,
                            'link_dokumen' => $p2Link,
                            'deskripsi' => $p2Desk,
                            'status' => (! empty($p2Link) || ! empty($p2Desk)) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $data['updated_at'] ?? null,
                        ],
                        'bukti_dampak_tridharma' => [
                            'id' => $data['id'] ?? null,
                            'link_dokumen' => $p3Link,
                            'deskripsi' => $p3Desk,
                            'status' => (! empty($p3Link) || ! empty($p3Desk)) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $data['updated_at'] ?? null,
                        ],
                        'dokumen_implementasi' => [
                            'id' => $data['id'] ?? null,
                            'link_dokumen' => $p4Link,
                            'deskripsi' => $p4Desk,
                            'status' => (! empty($p4Link) || ! empty($p4Desk)) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $data['updated_at'] ?? null,
                        ],
                    ];

                    Cache::forever("spmi_evidence_project_{$projectId}", $synced);

                    return $synced;
                }

                // 3. Jika API mengembalikan data bertingkat dari /projects/{id}/evidence-overview
                $evidenceObj = $data['evidence'] ?? (isset($data['pilar_1_evaluasi']) ? $data : null);
                if (is_array($evidenceObj)) {
                    $p1 = $evidenceObj['pilar_1_evaluasi'] ?? [];
                    $p2 = $evidenceObj['pilar_2_pengendalian'] ?? [];
                    $p3 = $evidenceObj['pilar_3_dampak'] ?? [];
                    $p4 = $evidenceObj['pilar_4_implementasi'] ?? [];

                    $synced = [
                        'id' => $evidenceObj['id'] ?? null,
                        'project_id' => $projectId,
                        'bukti_evaluasi' => [
                            'id' => $evidenceObj['id'] ?? null,
                            'link_dokumen' => $p1['link'] ?? '',
                            'deskripsi' => $p1['deskripsi'] ?? '',
                            'status' => (! empty($p1['link']) || ! empty($p1['deskripsi'])) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $evidenceObj['updated_at'] ?? null,
                        ],
                        'bukti_pengendalian' => [
                            'id' => $evidenceObj['id'] ?? null,
                            'link_dokumen' => $p2['link'] ?? '',
                            'deskripsi' => $p2['deskripsi'] ?? '',
                            'status' => (! empty($p2['link']) || ! empty($p2['deskripsi'])) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $evidenceObj['updated_at'] ?? null,
                        ],
                        'bukti_dampak_tridharma' => [
                            'id' => $evidenceObj['id'] ?? null,
                            'link_dokumen' => $p3['link'] ?? '',
                            'deskripsi' => $p3['deskripsi'] ?? '',
                            'status' => (! empty($p3['link']) || ! empty($p3['deskripsi'])) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $evidenceObj['updated_at'] ?? null,
                        ],
                        'dokumen_implementasi' => [
                            'id' => $evidenceObj['id'] ?? null,
                            'link_dokumen' => $p4['link'] ?? '',
                            'deskripsi' => $p4['deskripsi'] ?? '',
                            'status' => (! empty($p4['link']) || ! empty($p4['deskripsi'])) ? 'terverifikasi' : 'belum_terisi',
                            'updated_at' => $evidenceObj['updated_at'] ?? null,
                        ],
                    ];

                    Cache::forever("spmi_evidence_project_{$projectId}", $synced);

                    return $synced;
                }
            }
        } catch (\Throwable $e) {
            Log::info('Koneksi API getEvidenceSpmi gagal, gunakan cache: '.$e->getMessage());
        }

        if ($cached === null) {
            $cached = $this->getDefaultEvidenceStructure($projectId);
            Cache::forever("spmi_evidence_project_{$projectId}", $cached);
        }

        return $cached;
    }

    /**
     * Struktur kosong untuk evidence baru.
     *
     * @return array<string, mixed>
     */
    protected function getEmptyEvidenceStructure(int $projectId): array
    {
        return [
            'id' => null,
            'project_id' => $projectId,
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
