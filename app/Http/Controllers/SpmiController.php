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
     * Display the Evidence SPMI page.
     */
    public function evidenceSpmi(Request $request): View
    {
        $selectedYear = (int) $request->query('tahun', date('Y'));

        // Daftar standar mutu yang akan ditampilkan evidence-nya
        $standarList = $this->loadStandarMutuFromApi();

        $defaultStandarId = $standarList[0]['id'] ?? 1;
        $selectedStandarId = (int) $request->query('standar', $defaultStandarId);

        // Load evidence yang sudah disubmit per standar mutu (dengan penyimpanan persisten)
        $evidenceData = $this->loadEvidence($selectedYear);

        // Hitung summary statistics
        $summary = $this->calculateEvidenceSummary($standarList, $evidenceData);

        // Daftar kategori evidence
        $kategoriEvidence = $this->getKategoriEvidence();

        return view('pages.kaprodi.evidence-spmi', [
            'selectedYear' => $selectedYear,
            'selectedStandarId' => $selectedStandarId,
            'standarList' => $standarList,
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
        $standarId = (int) $request->input('standar_id', 1);
        $tahun = (int) $request->input('tahun', date('Y'));
        $status = 'terverifikasi';

        $evidences = $request->input('evidences');

        // Muat data penyimpanan saat ini
        $stored = $this->loadEvidence($tahun);

        if (is_array($evidences)) {
            foreach ($evidences as $kategori => $item) {
                $deskripsi = trim((string) ($item['deskripsi'] ?? ''));
                $linkDokumen = trim((string) ($item['link_dokumen'] ?? ''));
                $id = ! empty($item['id']) ? (int) $item['id'] : null;

                // Proses jika terdapat isian pada deskripsi atau tautan dokumen
                if ($deskripsi !== '' || $linkDokumen !== '') {
                    $evidenceId = $id ?: (int) ($standarId * 100 + (abs(crc32($kategori)) % 90) + 10);

                    // Simpan langsung ke persistent storage lokal
                    $stored[$standarId][$kategori] = [
                        'id' => $evidenceId,
                        'deskripsi' => $deskripsi,
                        'link_dokumen' => $linkDokumen,
                        'status' => $status,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    // Upayakan sinkronisasi ke backend API
                    try {
                        if ($id) {
                            $this->akreditasiService->updateEvidenceSpmi($id, [
                                'deskripsi' => $deskripsi,
                                'link_dokumen' => $linkDokumen,
                                'status' => $status,
                            ]);
                        } else {
                            $res = $this->akreditasiService->createEvidenceSpmi([
                                'id_standar_mutu' => $standarId,
                                'kategori' => $kategori,
                                'deskripsi' => $deskripsi,
                                'link_dokumen' => $linkDokumen,
                                'tahun' => $tahun,
                                'status' => $status,
                            ]);

                            if ($res->successful() && ! empty($res->json('data.id'))) {
                                $stored[$standarId][$kategori]['id'] = $res->json('data.id');
                            }
                        }
                    } catch (\Throwable $e) {
                        // Abaikan kegagalan koneksi ke remote API (data tersimpan di lokal)
                    }
                }
            }

            Cache::forever("spmi_evidence_{$tahun}", $stored);
        } else {
            // Fallback untuk single evidence submission
            $kategori = (string) $request->input('kategori');
            $deskripsi = trim((string) $request->input('deskripsi'));
            $linkDokumen = trim((string) $request->input('link_dokumen'));
            $id = $request->input('id') ? (int) $request->input('id') : (int) ($standarId * 100 + (abs(crc32($kategori)) % 90) + 10);

            if ($deskripsi !== '' || $linkDokumen !== '') {
                $stored[$standarId][$kategori] = [
                    'id' => $id,
                    'deskripsi' => $deskripsi,
                    'link_dokumen' => $linkDokumen,
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                Cache::forever("spmi_evidence_{$tahun}", $stored);

                try {
                    $this->akreditasiService->createEvidenceSpmi([
                        'id_standar_mutu' => $standarId,
                        'kategori' => $kategori,
                        'deskripsi' => $deskripsi,
                        'link_dokumen' => $linkDokumen,
                        'tahun' => $tahun,
                        'status' => $status,
                    ]);
                } catch (\Throwable $e) {
                    // Fallback
                }
            }
        }

        return redirect()->route('spmi.evidence', [
            'tahun' => $tahun,
            'standar' => $standarId,
        ]);
    }

    /**
     * Update existing evidence SPMI.
     */
    public function updateEvidence(Request $request, int|string $id): RedirectResponse
    {
        $deskripsi = trim((string) $request->input('deskripsi'));
        $linkDokumen = trim((string) $request->input('link_dokumen'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $standarId = (int) $request->input('standar_id', 1);
        $kategori = (string) $request->input('kategori', 'bukti_evaluasi');
        $status = 'terverifikasi';

        $stored = $this->loadEvidence($tahun);
        $stored[$standarId][$kategori] = [
            'id' => (int) $id,
            'deskripsi' => $deskripsi,
            'link_dokumen' => $linkDokumen,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        Cache::forever("spmi_evidence_{$tahun}", $stored);

        try {
            $this->akreditasiService->updateEvidenceSpmi($id, [
                'deskripsi' => $deskripsi,
                'link_dokumen' => $linkDokumen,
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            // Fallback jika API belum tersedia
        }

        return redirect()->route('spmi.evidence', [
            'tahun' => $tahun,
            'standar' => $standarId,
        ]);
    }

    /**
     * Memuat daftar standar mutu dari API atau fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadStandarMutuFromApi(): array
    {
        try {
            $response = $this->akreditasiService->getStandarMutu();
            if ($response->successful()) {
                $apiData = $response->json('data') ?? [];
                if (is_array($apiData) && ! empty($apiData)) {
                    $mapped = [];
                    foreach ($apiData as $item) {
                        $mapped[] = [
                            'id' => $item['id'],
                            'kode' => $item['kode_standar'] ?? ('STD-'.str_pad((string) $item['id'], 2, '0', STR_PAD_LEFT)),
                            'nama' => $item['nama_standar'] ?? 'Standar Mutu',
                        ];
                    }

                    return $mapped;
                }
            }
        } catch (\Throwable $e) {
            // Fallback ke data default
        }

        return $this->getDefaultStandarMutu();
    }

    /**
     * Memuat evidence yang sudah ada dari Cache lokal & disinkronkan dengan API.
     *
     * @return array<int|string, array<string, array<string, mixed>>>
     */
    protected function loadEvidence(int $year): array
    {
        $cached = Cache::get("spmi_evidence_{$year}");

        if ($cached === null) {
            $cached = $this->getDefaultEvidenceData();
            Cache::forever("spmi_evidence_{$year}", $cached);
        }

        try {
            $response = $this->akreditasiService->getEvidenceSpmi(['tahun' => $year]);
            if ($response->successful()) {
                $apiData = $response->json('data') ?? [];
                if (is_array($apiData) && ! empty($apiData)) {
                    foreach ($apiData as $item) {
                        $standarId = $item['id_standar_mutu'] ?? 0;
                        $kategori = $item['kategori'] ?? 'bukti_evaluasi';

                        $cached[$standarId][$kategori] = [
                            'id' => $item['id'],
                            'deskripsi' => $item['deskripsi'] ?? '',
                            'link_dokumen' => $item['link_dokumen'] ?? '',
                            'status' => 'terverifikasi',
                            'updated_at' => $item['updated_at'] ?? null,
                        ];
                    }
                    Cache::forever("spmi_evidence_{$year}", $cached);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan kesalahan koneksi
        }

        return $cached;
    }

    /**
     * Menghitung ringkasan statistik evidence.
     *
     * @param  array<int, array<string, mixed>>  $standarList
     * @param  array<int|string, array<string, array<string, mixed>>>  $evidenceData
     * @return array<string, int>
     */
    protected function calculateEvidenceSummary(array $standarList, array $evidenceData): array
    {
        $totalSlots = count($standarList) * 4; // 4 kategori per standar
        $terisi = 0;

        foreach ($standarList as $standar) {
            $standarId = $standar['id'];
            $categories = $evidenceData[$standarId] ?? [];
            foreach ($categories as $kategori => $evidence) {
                $hasContent = ! empty($evidence['deskripsi']) || ! empty($evidence['link_dokumen']);
                if ($hasContent) {
                    $terisi++;
                }
            }
        }

        $belumTerisi = max(0, $totalSlots - $terisi);

        return [
            'total_evidence' => $totalSlots,
            'terisi' => $terisi,
            'terverifikasi' => $terisi,
            'draft' => 0,
            'belum_terisi' => $belumTerisi,
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
                'deskripsi' => 'Dokumen evaluasi capaian standar mutu (hasil audit, analisis gap, evaluasi diri).',
                'icon' => 'clipboard-check',
            ],
            [
                'key' => 'bukti_pengendalian',
                'label' => 'Bukti Pengendalian & Peningkatan',
                'deskripsi' => 'Dokumen tindakan korektif, preventif, dan peningkatan berkelanjutan.',
                'icon' => 'adjustments',
            ],
            [
                'key' => 'bukti_dampak_tridharma',
                'label' => 'Bukti Dampak Tridharma',
                'deskripsi' => 'Bukti dampak pendidikan, penelitian, dan pengabdian masyarakat.',
                'icon' => 'academic-cap',
            ],
            [
                'key' => 'dokumen_implementasi',
                'label' => 'Dokumen Implementasi Harian',
                'deskripsi' => 'Dokumen operasional harian prodi (SOP, form, logbook, notulen).',
                'icon' => 'document-text',
            ],
        ];
    }

    /**
     * Default standar mutu untuk fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getDefaultStandarMutu(): array
    {
        return [
            ['id' => 1, 'kode' => 'STD-INFOKOM-01', 'nama' => 'Standar Kompetensi Lulusan'],
            ['id' => 2, 'kode' => 'STD-INFOKOM-02', 'nama' => 'Standar Kerja Sama'],
            ['id' => 3, 'kode' => 'STD-INFOKOM-03', 'nama' => 'Standar Pendidikan'],
            ['id' => 4, 'kode' => 'STD-INFOKOM-04', 'nama' => 'Standar Isi Pembelajaran'],
            ['id' => 5, 'kode' => 'STD-INFOKOM-05', 'nama' => 'Standar Proses Pembelajaran'],
            ['id' => 6, 'kode' => 'STD-INFOKOM-06', 'nama' => 'Standar Sarana Laboratorium Komputasi dan Jaringan'],
            ['id' => 7, 'kode' => 'STD-INFOKOM-07', 'nama' => 'Standar Dosen dan Tenaga Kependidikan'],
            ['id' => 8, 'kode' => 'STD-INFOKOM-08', 'nama' => 'Standar Pembiayaan'],
            ['id' => 9, 'kode' => 'STD-INFOKOM-09', 'nama' => 'Standar Penelitian'],
            ['id' => 10, 'kode' => 'STD-INFOKOM-10', 'nama' => 'Standar Pengabdian kepada Masyarakat'],
        ];
    }

    /**
     * Default evidence data untuk fallback/demo.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    protected function getDefaultEvidenceData(): array
    {
        return [
            1 => [
                'bukti_evaluasi' => [
                    'id' => 101,
                    'deskripsi' => 'Hasil evaluasi ketercapaian CPL melalui asesmen komprehensif semester genap 2025/2026.',
                    'link_dokumen' => 'https://drive.google.com/file/d/evaluasi-cpl-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-09-15',
                ],
                'bukti_pengendalian' => [
                    'id' => 102,
                    'deskripsi' => 'Rencana tindak lanjut revisi matriks CPL-CPMK berdasarkan hasil evaluasi.',
                    'link_dokumen' => 'https://drive.google.com/file/d/rtl-cpl-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-09-20',
                ],
            ],
            4 => [
                'bukti_evaluasi' => [
                    'id' => 103,
                    'deskripsi' => 'Laporan evaluasi kesesuaian RPS dengan profil lulusan dan kebutuhan industri.',
                    'link_dokumen' => 'https://drive.google.com/file/d/eval-rps-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-10-01',
                ],
            ],
            6 => [
                'bukti_dampak_tridharma' => [
                    'id' => 104,
                    'deskripsi' => 'Dokumentasi pemanfaatan laboratorium untuk riset mahasiswa dan dosen.',
                    'link_dokumen' => 'https://drive.google.com/file/d/dampak-lab-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-10-10',
                ],
                'dokumen_implementasi' => [
                    'id' => 105,
                    'deskripsi' => 'Logbook penggunaan laboratorium dan jadwal maintenance peralatan.',
                    'link_dokumen' => 'https://drive.google.com/file/d/logbook-lab-2026',
                    'status' => 'terverifikasi',
                    'updated_at' => '2026-10-12',
                ],
            ],
        ];
    }
}
