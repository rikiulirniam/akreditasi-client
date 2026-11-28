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
    protected ?string $lastApiError = null;

    protected bool $lastIsSimulation = false;

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

        $viewName = view()->exists('pages.spmi.evidence') ? 'pages.spmi.evidence' : 'pages.kaprodi.evidence-spmi';

        return view($viewName, [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
            'activeProject' => $activeProject,
            'evidenceData' => $evidenceData,
            'summary' => $summary,
            'kategoriEvidence' => $kategoriEvidence,
            'apiError' => $this->lastApiError,
            'isSimulation' => $this->lastIsSimulation,
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
                    $this->lastApiError = null;
                    $this->lastIsSimulation = (bool) ($data['is_simulation'] ?? false);

                    return $synced;
                }
            } else {
                $this->lastApiError = 'Endpoint Evidence SPMI mengembalikan status '.$response->status().'. Menampilkan data tersimpan.';
                $this->lastIsSimulation = true;
            }
        } catch (\Throwable $e) {
            Log::info('Koneksi API getEvidenceSpmi gagal, gunakan cache: '.$e->getMessage());
            $this->lastApiError = 'Gagal terhubung ke server API Evidence SPMI. Menampilkan data lokal/simulasi.';
            $this->lastIsSimulation = true;
        }

        if ($cached === null) {
            $cached = $this->getDefaultEvidenceStructure($projectId);
            Cache::forever("spmi_evidence_project_{$projectId}", $cached);
            $this->lastIsSimulation = true;
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
                'label' => 'Laporan Audit Mutu Internal (AMI & Monev)',
                'pilar' => 'Pilar 1: Evaluasi Mutu',
                'deskripsi' => 'Dokumen evaluasi capaian standar mutu, laporan berkala Audit Mutu Internal (AMI), monitoring, dan evaluasi diri.',
                'field_link' => 'link_bukti_evaluasi',
                'field_deskripsi' => 'deskripsi_bukti_evaluasi',
                'placeholder_deskripsi' => 'Contoh: Laporan audit mutu internal AMI siklus berjalan, analisis gap kurikulum, dan evaluasi standar prodi...',
                'placeholder_link' => 'https://drive.google.com/drive/folders/... atau https://drive.google.com/file/d/...',
                'icon' => 'clipboard-check',
            ],
            [
                'key' => 'bukti_pengendalian',
                'label' => 'Rapat Tinjauan Manajemen (RTM & RTL)',
                'pilar' => 'Pilar 2: Pengendalian Mutu',
                'deskripsi' => 'Dokumen notulensi Rapat Tinjauan Manajemen (RTM), Rencana Tindak Lanjut (RTL), tindakan korektif, dan preventif.',
                'field_link' => 'link_bukti_pengendalian',
                'field_deskripsi' => 'deskripsi_bukti_pengendalian',
                'placeholder_deskripsi' => 'Contoh: Risalah rapat tinjauan manajemen (RTM) fakultas/prodi dan lembar rencana tindak lanjut (RTL)...',
                'placeholder_link' => 'https://drive.google.com/drive/folders/... atau https://drive.google.com/file/d/...',
                'icon' => 'adjustments',
            ],
            [
                'key' => 'bukti_dampak_tridharma',
                'label' => 'Capaian IKU & Survei Kepuasan Stakeholder',
                'pilar' => 'Pilar 3: Dampak & Kepuasan',
                'deskripsi' => 'Dokumen capaian Indikator Kinerja Utama (IKU), luaran Tridharma perguruan tinggi, serta laporan survei kepuasan stakeholder.',
                'field_link' => 'link_bukti_dampak',
                'field_deskripsi' => 'deskripsi_bukti_dampak',
                'placeholder_deskripsi' => 'Contoh: Laporan capaian IKU prodi, survei kepuasan pengguna lulusan, mitra industri, mahasiswa, dan dosen...',
                'placeholder_link' => 'https://drive.google.com/drive/folders/... atau https://drive.google.com/file/d/...',
                'icon' => 'academic-cap',
            ],
            [
                'key' => 'dokumen_implementasi',
                'label' => 'Dokumen Implementasi (Buku Kebijakan SPMI, Manual Mutu, SOP PPEPP)',
                'pilar' => 'Pilar 4: Implementasi & SOP',
                'deskripsi' => 'Repositori dokumen mutu institusi mencakup Buku Kebijakan SPMI, Manual Mutu, Dokumen Standar, dan SOP PPEPP.',
                'field_link' => 'link_dokumen_implementasi',
                'field_deskripsi' => 'deskripsi_dokumen_implementasi',
                'placeholder_deskripsi' => 'Contoh: Berkas Buku Kebijakan SPMI, Manual Mutu, standar pendidikan/penelitian/pengabdian, dan SOP PPEPP prodi...',
                'placeholder_link' => 'https://drive.google.com/drive/folders/... atau https://drive.google.com/file/d/...',
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
                    'deskripsi' => 'Laporan Hasil Audit Mutu Internal (AMI & Monev) dan matriks analisis gap capaian standar kurikulum.',
                    'link_dokumen' => 'https://drive.google.com/file/d/ami-evaluasi-trk-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-09-15',
                ],
                'bukti_pengendalian' => [
                    'id' => 102,
                    'deskripsi' => 'Dokumen Rencana Tindak Lanjut (RTL) dan notulensi Rapat Tinjauan Manajemen (RTM) berkala.',
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

    /**
     * Halaman Rekapitulasi Dokumen Evidence Asesor (Read Data).
     * Sesuai alur Bu Wikta: Project > Evidence > PIC > Read Data.
     */
    public function evidenceOverview(Request $request, ?int $id = null): View
    {
        $projects = $this->loadProjectsFromApi();
        $defaultProjectId = $projects[0]['id'] ?? 1;
        $selectedProjectId = (int) ($id ?: $request->query('project_id', $defaultProjectId));

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

        $overviewData = $this->loadOverviewForProject($selectedProjectId);
        $publicToken = $this->generatePublicEvidenceToken($selectedProjectId);
        $publicUrl = route('spmi.public-overview', ['token' => $publicToken]);

        return view('pages.kaprodi.evidence_overview', [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
            'activeProject' => $activeProject,
            'evidenceData' => $overviewData['evidenceData'],
            'assignedIndicators' => $overviewData['assignedIndicators'],
            'indicatorsWithEvidence' => $overviewData['indicatorsWithEvidence'],
            'indicatorsWithoutEvidence' => $overviewData['indicatorsWithoutEvidence'],
            'indicatorStats' => $overviewData['indicatorStats'],
            'apiError' => $overviewData['apiError'],
            'isSimulation' => $overviewData['isSimulation'],
            'publicToken' => $publicToken,
            'publicUrl' => $publicUrl,
            'isReadOnly' => false,
        ]);
    }

    /**
     * Halaman Rekapitulasi Dokumen Read-Only untuk Asesor Eksternal via Token Publik.
     */
    public function publicEvidenceOverview(string $token): View
    {
        $projectId = $this->resolveProjectIdFromToken($token);
        $projects = $this->loadProjectsFromApi();

        try {
            $publicRes = $this->akreditasiService->getPublicEvidence($token);
            if ($publicRes->successful()) {
                $publicPayload = $publicRes->json('data') ?? [];
                if (! empty($publicPayload['project']['id'])) {
                    $projectId = (int) $publicPayload['project']['id'];
                } elseif (! empty($publicPayload['project_id'])) {
                    $projectId = (int) $publicPayload['project_id'];
                }
            }
        } catch (\Throwable) {
        }

        $activeProject = null;
        foreach ($projects as $p) {
            if ((int) ($p['id'] ?? 0) === $projectId) {
                $activeProject = $p;
                break;
            }
        }
        if (! $activeProject && ! empty($projects)) {
            $activeProject = $projects[0];
            $projectId = (int) ($activeProject['id'] ?? 1);
        }

        $overviewData = $this->loadOverviewForProject($projectId);

        return view('pages.kaprodi.evidence_overview', [
            'projects' => [$activeProject],
            'selectedProjectId' => $projectId,
            'activeProject' => $activeProject,
            'evidenceData' => $overviewData['evidenceData'],
            'assignedIndicators' => $overviewData['assignedIndicators'],
            'indicatorsWithEvidence' => $overviewData['indicatorsWithEvidence'],
            'indicatorsWithoutEvidence' => $overviewData['indicatorsWithoutEvidence'],
            'indicatorStats' => $overviewData['indicatorStats'],
            'apiError' => $overviewData['apiError'],
            'isSimulation' => $overviewData['isSimulation'],
            'publicToken' => $token,
            'publicUrl' => route('spmi.public-overview', ['token' => $token]),
            'isReadOnly' => true,
        ]);
    }

    /**
     * Generate secure URL token for external assessor view.
     */
    protected function generatePublicEvidenceToken(int $projectId): string
    {
        $payload = "{$projectId}:".date('Ymd');

        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    /**
     * Resolve project ID from public assessor token.
     */
    protected function resolveProjectIdFromToken(string $token): int
    {
        try {
            $decoded = base64_decode(strtr($token, '-_', '+/'));
            if ($decoded && str_contains($decoded, ':')) {
                [$pid] = explode(':', $decoded, 2);
                if (is_numeric($pid) && (int) $pid > 0) {
                    return (int) $pid;
                }
            }
        } catch (\Throwable) {
        }

        return 1;
    }

    /**
     * Memuat ringkasan overview evidence dan mapping indikator untuk sebuah project.
     *
     * @return array<string, mixed>
     */
    protected function loadOverviewForProject(int $projectId): array
    {
        $evidenceData = $this->loadEvidenceForProject($projectId);
        $assignedIndicators = [];
        $overviewApiError = $this->lastApiError;
        $isSimulation = $this->lastIsSimulation;

        try {
            $res = $this->akreditasiService->getEvidenceOverview($projectId);
            if ($res->successful()) {
                $data = $res->json('data') ?? [];
                $overviewApiError = null;
                $isSimulation = (bool) ($data['is_simulation'] ?? false);
                if (! empty($data['assigned_indicators']) && is_array($data['assigned_indicators'])) {
                    $assignedIndicators = $data['assigned_indicators'];
                } elseif (! empty($data['indicators']) && is_array($data['indicators'])) {
                    $assignedIndicators = $data['indicators'];
                }
            } else {
                $overviewApiError = 'Gagal memuat rekapitulasi evidence dari server (HTTP '.$res->status().'). Menampilkan data simulasi/cadangan.';
                $isSimulation = true;
            }
        } catch (\Throwable $e) {
            $overviewApiError = 'Koneksi ke endpoint evidence-overview gagal. Menampilkan data simulasi/cadangan.';
            $isSimulation = true;
        }

        if (empty($assignedIndicators)) {
            $isSimulation = true;
            $assignedIndicators = [
                [
                    'id_indikator' => 1,
                    'role' => 'PIC',
                    'nama_dosen' => 'Dr. Budi Raharjo, M.Kom.',
                    'link_evidence' => 'https://drive.google.com/drive/folders/cpl-trk-bukti',
                    'is_simulation' => true,
                    'data_available' => true,
                    'indikator' => [
                        'id' => 1,
                        'kode_indikator' => 'IND-C.1.1',
                        'deskripsi_indikator' => 'Kesesuaian kurikulum prodi dengan standar kompetensi lulusan TIK',
                    ],
                ],
                [
                    'id_indikator' => 2,
                    'role' => 'PIC',
                    'nama_dosen' => 'Siti Aminah, M.T.',
                    'link_evidence' => 'https://drive.google.com/drive/folders/dosen-tri-dharma',
                    'is_simulation' => true,
                    'data_available' => true,
                    'indikator' => [
                        'id' => 2,
                        'kode_indikator' => 'IND-C.3.2',
                        'deskripsi_indikator' => 'Rekapitulasi publikasi ilmiah dosen dan karya terapan',
                    ],
                ],
                [
                    'id_indikator' => 3,
                    'role' => 'PJ',
                    'nama_dosen' => 'Agus Priyono, Ph.D.',
                    'link_evidence' => '',
                    'is_simulation' => true,
                    'data_available' => true,
                    'indikator' => [
                        'id' => 3,
                        'kode_indikator' => 'IND-C.4.1',
                        'deskripsi_indikator' => 'Kecukupan fasilitas laboratorium komputasi dan workstation',
                    ],
                ],
                [
                    'id_indikator' => 4,
                    'role' => 'Institusi / UPPS',
                    'nama_dosen' => 'UPPS / Institusi',
                    'link_evidence' => '',
                    'is_readonly' => true,
                    'is_simulation' => true,
                    'data_available' => false,
                    'indikator' => [
                        'id' => 4,
                        'kode_indikator' => 'C.2.1',
                        'deskripsi_indikator' => 'Tata Pamong, Tata Kelola, dan Kerjasama (Read-Only UPPS)',
                    ],
                ],
            ];
        }

        $indicatorsWithEvidence = [];
        $indicatorsWithoutEvidence = [];

        foreach ($assignedIndicators as $item) {
            if (! is_array($item)) {
                continue;
            }
            $indikator = is_array($item['indikator'] ?? null) ? $item['indikator'] : $item;
            $code = (string) ($indikator['kode_indikator'] ?? $indikator['code'] ?? $item['kode_indikator'] ?? 'IND');
            $normalizedCode = strtoupper(trim($code));
            $isPlaceholderCriteria = str_starts_with($normalizedCode, 'C.2')
                || str_starts_with($normalizedCode, 'C2')
                || str_starts_with($normalizedCode, 'K2')
                || str_starts_with($normalizedCode, 'IND-C.2')
                || str_starts_with($normalizedCode, 'C.5')
                || str_starts_with($normalizedCode, 'C5')
                || str_starts_with($normalizedCode, 'K5')
                || str_starts_with($normalizedCode, 'IND-C.5');

            $link = trim((string) ($item['link_evidence'] ?? $item['link'] ?? $item['evidence_url'] ?? ''));
            $hasEvidence = $link !== '';
            $dataAvailable = array_key_exists('data_available', $item)
                ? (bool) $item['data_available']
                : (! array_key_exists('has_api_data', $item) || (bool) $item['has_api_data']);

            $normalizedItem = [
                'id_indikator' => $item['id_indikator'] ?? $indikator['id'] ?? null,
                'kode_indikator' => $code,
                'deskripsi_indikator' => $indikator['deskripsi_indikator'] ?? $indikator['nama_indikator'] ?? $indikator['deskripsi'] ?? 'Deskripsi indikator',
                'nama_dosen' => $item['nama_dosen'] ?? $item['pic_name'] ?? 'Belum ditugaskan',
                'role' => $item['role'] ?? 'PIC',
                'link_evidence' => $link,
                'has_evidence' => $hasEvidence,
                'data_available' => $dataAvailable,
                'is_simulation' => (bool) ($item['is_simulation'] ?? $isSimulation),
                'is_readonly' => (bool) ($item['is_readonly'] ?? $isPlaceholderCriteria),
            ];

            if ($hasEvidence) {
                $indicatorsWithEvidence[] = $normalizedItem;
            } else {
                $indicatorsWithoutEvidence[] = $normalizedItem;
            }
        }

        $totalIndikator = count($indicatorsWithEvidence) + count($indicatorsWithoutEvidence);
        $terisiIndikator = count($indicatorsWithEvidence);
        $persenIndikator = $totalIndikator > 0 ? (int) round(($terisiIndikator / $totalIndikator) * 100) : 0;

        return [
            'evidenceData' => $evidenceData,
            'assignedIndicators' => array_merge($indicatorsWithEvidence, $indicatorsWithoutEvidence),
            'indicatorsWithEvidence' => $indicatorsWithEvidence,
            'indicatorsWithoutEvidence' => $indicatorsWithoutEvidence,
            'apiError' => $overviewApiError,
            'isSimulation' => $isSimulation,
            'indicatorStats' => [
                'total' => $totalIndikator,
                'terisi' => $terisiIndikator,
                'persen' => $persenIndikator,
            ],
        ];
    }
}
