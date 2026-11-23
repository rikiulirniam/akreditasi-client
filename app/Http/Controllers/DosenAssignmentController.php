<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use App\Services\AkreditasiService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenAssignmentController extends Controller
{
    protected AkreditasiService $akreditasiService;

    protected AkademikService $akademikService;

    public function __construct(AkreditasiService $akreditasiService, AkademikService $akademikService)
    {
        $this->akreditasiService = $akreditasiService;
        $this->akademikService = $akademikService;
    }

    /**
     * Helper untuk mengekstrak array data dari response API atau default.
     */
    protected function extractData(mixed $response, mixed $default = []): mixed
    {
        if ($response instanceof Response) {
            return $response->successful() ? ($response->json('data') ?? $default) : $default;
        }

        if (is_array($response)) {
            return $response;
        }

        return $default;
    }

    /**
     * Tampilkan halaman Tugas Saya (Penugasan Dosen Tim Akreditasi).
     * Dosen dan Kaprodi melihat butir indikator yang ditugaskan kepadanya di halaman Plotting Tim Akreditasi.
     */
    public function myAssignments(Request $request): View
    {
        $currentUser = session('user', []);
        $userId = $currentUser['id'] ?? null;
        $userName = $currentUser['nama'] ?? ($currentUser['name'] ?? 'Dosen');
        $userRole = strtolower((string) ($currentUser['role'] ?? ($currentUser['role_name'] ?? '')));
        $isKaprodi = str_contains($userRole, 'kaprodi') || str_contains($userRole, 'admin');

        // Peta butir indikator LAM-INFOKOM 2.1 TRK
        $kriteriaMap = [
            'K1.1' => ['id' => 1, 'desc' => 'Kejelasan visi, misi, tujuan, dan ketercapaian strategi prodi TRK.', 'bobot' => 3.5],
            'K2.1' => ['id' => 2, 'desc' => 'Sistem tata pamong, kepemimpinan publik, dan efektivitas pengelolaan operasional prodi.', 'bobot' => 4.0],
            'K2.2' => ['id' => 3, 'desc' => 'Sistem penjaminan mutu internal (SPMI) dan siklus PPEPP secara berkelanjutan.', 'bobot' => 4.0],
            'K2.3' => ['id' => 4, 'desc' => 'Efektivitas kerjasama strategis tridharma tingkat nasional maupun internasional.', 'bobot' => 3.0],
            'K3.1' => ['id' => 5, 'desc' => 'Kualitas input mahasiswa baru, rasio pendaftar, dan daya tampung prodi.', 'bobot' => 3.0],
            'K3.2' => ['id' => 6, 'desc' => 'Layanan kemahasiswaan (bimbingan, konseling, kewirausahaan, dan penalaran).', 'bobot' => 3.0],
            'K3.3' => ['id' => 7, 'desc' => 'Prestasi mahasiswa di bidang akademik dan non-akademik tingkat nasional/internasional.', 'bobot' => 3.5],
            'K3.4' => ['id' => 8, 'desc' => 'Masa tunggu lulusan, tingkat kepuasan pengguna lulusan, dan kesesuaian bidang kerja.', 'bobot' => 4.0],
            'K4.1' => ['id' => 9, 'desc' => 'Kualifikasi akademik dan kecukupan Dosen Tetap Program Studi (DTPS).', 'bobot' => 4.0],
            'K4.2' => ['id' => 10, 'desc' => 'Beban kerja dosen (EWMP), rasio dosen terhadap mahasiswa, dan produktivitas tridharma.', 'bobot' => 3.5],
            'K4.3' => ['id' => 11, 'desc' => 'Kualifikasi dan kecukupan tenaga kependidikan (laboran, teknisi, dan pustakawan).', 'bobot' => 3.0],
            'K5.1' => ['id' => 12, 'desc' => 'Kecukupan alokasi dana operasional, penelitian, pkm, dan investasi sarana prodi.', 'bobot' => 3.5],
            'K5.2' => ['id' => 13, 'desc' => 'Kelayakan dan kecukupan laboratorium komputer, perangkat keras, dan sistem informasi.', 'bobot' => 4.0],
            'K6.1' => ['id' => 14, 'desc' => 'Kesesuaian kurikulum berbasis OBE (Outcome-Based Education) dan Standar KKNI.', 'bobot' => 4.0],
            'K6.2' => ['id' => 15, 'desc' => 'Rencana pembelajaran semester (RPS), metode pembelajaran partisipatif dan proyek.', 'bobot' => 3.5],
            'K6.3' => ['id' => 16, 'desc' => 'Sistem monitoring dan evaluasi proses pembelajaran serta penilaian mahasiswa.', 'bobot' => 3.5],
            'K6.4' => ['id' => 17, 'desc' => 'Integrasi hasil penelitian dan pengabdian dosen ke dalam materi perkuliahan.', 'bobot' => 3.0],
            'K7.1' => ['id' => 18, 'desc' => 'Relevansi peta jalan (roadmap) penelitian dosen dan mahasiswa dengan prodi TRK.', 'bobot' => 3.5],
            'K7.2' => ['id' => 19, 'desc' => 'Produktivitas publikasi penelitian dosen pada jurnal terakreditasi dan bereputasi.', 'bobot' => 4.0],
            'K8.1' => ['id' => 20, 'desc' => 'Peta jalan dan implementasi pengabdian kepada masyarakat (PkM) yang terarah.', 'bobot' => 3.0],
            'K8.2' => ['id' => 21, 'desc' => 'Penerapan teknologi tepat guna dan tingkat kepuasan mitra masyarakat.', 'bobot' => 3.5],
            'K9.1' => ['id' => 22, 'desc' => 'IPK rata-rata lulusan, masa studi mahasiswa, dan persentase kelulusan tepat waktu.', 'bobot' => 4.0],
            'K9.2' => ['id' => 23, 'desc' => 'Paten, hak cipta (HKI), buku ajar, dan produk teknologi yang dihasilkan prodi.', 'bobot' => 4.0],
            'K9.3' => ['id' => 24, 'desc' => 'Capaian pembelajaran lulusan (CPL) dan akreditasi sertifikasi kompetensi mahasiswa.', 'bobot' => 4.0],
        ];

        // 1. Ambil data penugasan langsung dari endpoint REST API backend jika tersedia
        $rawAssignments = [];
        try {
            $res = $this->akreditasiService->getMyAssignments();
            if ($res->successful()) {
                $rawAssignments = $res->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            $rawAssignments = [];
        }

        // Format data dari API jika ada
        if (! empty($rawAssignments) && is_array($rawAssignments)) {
            $rawAssignments = array_map(function ($item) {
                return [
                    'id_mapping' => $item['id_mapping'] ?? ($item['id'] ?? null),
                    'role' => strtoupper($item['role'] ?? 'PIC'),
                    'id_indikator' => $item['id_indikator'] ?? ($item['indikator']['id'] ?? 1),
                    'kode_indikator' => $item['kode_indikator'] ?? ($item['indikator']['kode_indikator'] ?? 'Indikator'),
                    'deskripsi' => $item['deskripsi'] ?? ($item['indikator']['nama_indikator'] ?? ($item['indikator']['deskripsi'] ?? '')),
                    'bobot' => $item['bobot'] ?? ($item['indikator']['bobot'] ?? 1.0),
                    'id_project' => $item['id_project'] ?? 1,
                    'nama_project' => $item['nama_project'] ?? ($item['project']['nama_project'] ?? 'Akreditasi LAM INFOKOM D4 TRK 2026'),
                    'has_bukti' => ! empty($item['has_bukti']) || ! empty($item['evidence_url']),
                    'evidence_url' => $item['evidence_url'] ?? null,
                    'evidence_notes' => $item['evidence_notes'] ?? null,
                ];
            }, $rawAssignments);
        }

        // 2. Hubungkan dengan Plotting Tim Akreditasi (baseline & session Kaprodi)
        $projects = [
            1 => 'Akreditasi LAM INFOKOM D4 TRK 2026',
            2 => 'Akreditasi LAM INFOKOM D4 TRK 2027',
        ];

        $defaultBaseline = [
            'K1.1' => ['pj' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.', 'pic' => 'KUWAT SANTOSO M.Kom.'],
            'K2.1' => ['pj' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.', 'pic' => 'Dr. LILIEK TRIYONO S.T., M.Kom.'],
            'K2.2' => ['pj' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.', 'pic' => null],
            'K2.3' => ['pj' => null, 'pic' => 'MARDIYONO S.Kom., M.Sc.'],
            'K3.1' => ['pj' => 'KUWAT SANTOSO M.Kom.', 'pic' => 'SARNO S.T., M.T.'],
            'K3.2' => ['pj' => 'KUWAT SANTOSO M.Kom.', 'pic' => 'KUWAT SANTOSO M.Kom.'],
            'K3.3' => ['pj' => null, 'pic' => null],
            'K3.4' => ['pj' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.', 'pic' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.'],
            'K4.1' => ['pj' => 'Dr. LILIEK TRIYONO S.T., M.Kom.', 'pic' => 'MARDIYONO S.Kom., M.Sc.'],
            'K4.2' => ['pj' => 'Dr. LILIEK TRIYONO S.T., M.Kom.', 'pic' => 'SLAMET HANDOKO S.T., M.Kom.'],
            'K5.1' => ['pj' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.', 'pic' => 'TRISNO CAHYO S.Pd., M.Cs.'],
            'K6.1' => ['pj' => 'MARDIYONO S.Kom., M.Sc.', 'pic' => 'SARNO S.T., M.T.'],
            'K7.1' => ['pj' => 'Dr. LILIEK TRIYONO S.T., M.Kom.', 'pic' => 'SLAMET HANDOKO S.T., M.Kom.'],
            'K8.1' => ['pj' => 'KUWAT SANTOSO M.Kom.', 'pic' => 'TRISNO CAHYO S.Pd., M.Cs.'],
            'K9.1' => ['pj' => 'Prof. Dr. Ir. KURNIANINGSIH S.T., M.T.', 'pic' => 'SUROSO S.Kom., M.Kom.'],
        ];

        $existingKeys = [];
        foreach ($rawAssignments as $ra) {
            $existingKeys[($ra['id_project'] ?? 1) . '_' . ($ra['kode_indikator'] ?? '') . '_' . ($ra['role'] ?? '')] = true;
        }

        foreach ($projects as $projId => $projName) {
            $activeAssignments = ($projId === 1) ? $defaultBaseline : [];

            // Sinkronkan pemetaan dari backend jika ada
            try {
                $backendMapping = $this->extractData($this->akreditasiService->getMappingTim(['project_id' => $projId]));
                if (is_array($backendMapping)) {
                    foreach ($backendMapping as $bm) {
                        $code = $bm['indikator']['kode_indikator'] ?? null;
                        $role = strtolower((string) ($bm['role'] ?? ''));
                        $name = $bm['user']['nama'] ?? null;
                        if ($code && in_array($role, ['pj', 'pic'], true) && $name) {
                            $activeAssignments[$code] ??= ['pj' => null, 'pic' => null];
                            $activeAssignments[$code][$role] = $name;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Gunakan default & session
            }

            // Timpa dengan data plotting session yang disimpan Kaprodi saat menekan Tetapkan PIC/PJ
            $sessionPlotting = session("plotting_assignments_{$projId}", []);
            if (is_array($sessionPlotting)) {
                foreach ($sessionPlotting as $code => $roles) {
                    if (! isset($activeAssignments[$code])) {
                        $activeAssignments[$code] = ['pj' => null, 'pic' => null];
                    }
                    if (array_key_exists('pj', $roles)) {
                        $activeAssignments[$code]['pj'] = $roles['pj'];
                    }
                    if (array_key_exists('pic', $roles)) {
                        $activeAssignments[$code]['pic'] = $roles['pic'];
                    }
                }
            }

            // Cocokkan indikator yang di-assign ke user login saat ini
            foreach ($activeAssignments as $code => $roles) {
                if (! isset($kriteriaMap[$code])) {
                    continue;
                }

                foreach (['pic' => 'PIC', 'pj' => 'PJ'] as $roleKey => $roleLabel) {
                    $assignedName = $roles[$roleKey] ?? null;
                    if (! $assignedName) {
                        continue;
                    }

                    if ($this->isUserMatch($assignedName, $userName, $isKaprodi)) {
                        $dedupKey = "{$projId}_{$code}_{$roleLabel}";
                        if (isset($existingKeys[$dedupKey])) {
                            continue;
                        }
                        $existingKeys[$dedupKey] = true;

                        $rawAssignments[] = [
                            'id_mapping' => count($rawAssignments) + 1,
                            'role' => $roleLabel,
                            'id_indikator' => $kriteriaMap[$code]['id'],
                            'kode_indikator' => $code,
                            'deskripsi' => $kriteriaMap[$code]['desc'],
                            'bobot' => $kriteriaMap[$code]['bobot'],
                            'id_project' => $projId,
                            'nama_project' => $projName,
                            'has_bukti' => false,
                            'evidence_url' => null,
                            'evidence_notes' => null,
                        ];
                    }
                }
            }
        }

        // 3. Sinkronkan dengan bukti yang diunggah dosen ke Google Drive (dari session lokal)
        $rawAssignments = array_map(function ($item) {
            $projId = $item['id_project'] ?? 1;
            $code = $item['kode_indikator'] ?? '';
            $sessEvidence = session("evidence_{$projId}_{$code}");
            if ($sessEvidence && ! empty($sessEvidence['url'])) {
                $item['has_bukti'] = true;
                $item['evidence_url'] = $sessEvidence['url'];
                $item['evidence_notes'] = $sessEvidence['notes'] ?? ($item['evidence_notes'] ?? null);
            }

            return $item;
        }, $rawAssignments);

        // Urutkan berdasarkan kode indikator (K1.1, K2.1, dst.)
        usort($rawAssignments, function ($a, $b) {
            return strcmp((string) ($a['kode_indikator'] ?? ''), (string) ($b['kode_indikator'] ?? ''));
        });

        // Hitung metrik ringkas
        $totalTugas = count($rawAssignments);
        $totalPj = count(array_filter($rawAssignments, fn ($a) => strtoupper($a['role'] ?? '') === 'PJ'));
        $totalPic = count(array_filter($rawAssignments, fn ($a) => strtoupper($a['role'] ?? '') === 'PIC'));
        $totalBuktiTerisi = count(array_filter($rawAssignments, fn ($a) => ! empty($a['has_bukti'])));

        return view('pages.dosen.my_assignments', [
            'assignments' => $rawAssignments,
            'stats' => [
                'total' => $totalTugas,
                'pj' => $totalPj,
                'pic' => $totalPic,
                'terisi' => $totalBuktiTerisi,
                'persen' => $totalTugas > 0 ? round(($totalBuktiTerisi / $totalTugas) * 100) : 0,
            ],
            'userName' => $userName,
        ]);
    }

    /**
     * Helper untuk mencocokkan nama penugasan plotting dengan akun dosen/kaprodi yang sedang login.
     */
    protected function isUserMatch(?string $assignedName, string $userName, bool $isKaprodi): bool
    {
        if (empty($assignedName)) {
            return false;
        }

        $assignedLower = strtolower(trim($assignedName));
        $userLower = strtolower(trim($userName));

        // 1. Jika sama persis
        if ($assignedLower === $userLower) {
            return true;
        }

        // 2. Pembersihan gelar akademik dan simbol
        $clean = function (string $str): string {
            $cleaned = preg_replace('/(prof\.|dr\.|ir\.|s\.t\.|m\.t\.|m\.kom\.|m\.sc\.|s\.pd\.|m\.cs\.|s\.kom\.|,|&|\.)/i', '', $str);
            $cleaned = str_replace(['(kaprodi)', 'kaprodi', 'ketua program studi'], '', $cleaned ?? '');

            return trim(preg_replace('/\s+/', ' ', $cleaned));
        };

        $cleanAssigned = $clean($assignedLower);
        $cleanUser = $clean($userLower);

        // 3. Khusus Kaprodi: jika butir ditugaskan ke label Kaprodi (misal: 'Kuwat (Kaprodi)' atau 'Kaprodi')
        if ($isKaprodi && str_contains($assignedLower, 'kaprodi')) {
            return true;
        }

        // 4. Pencocokan nama bersih
        if ($cleanAssigned !== '' && $cleanUser !== '') {
            if ($cleanAssigned === $cleanUser
                || str_contains($cleanAssigned, $cleanUser)
                || str_contains($cleanUser, $cleanAssigned)) {
                return true;
            }
        }

        // 5. Pencocokan per kata nama utama (token minimal 4 karakter)
        $assignedTokens = array_filter(explode(' ', $cleanAssigned), fn ($t) => strlen($t) >= 4);
        $userTokens = array_filter(explode(' ', $cleanUser), fn ($t) => strlen($t) >= 4);

        foreach ($assignedTokens as $at) {
            foreach ($userTokens as $ut) {
                if ($at === $ut) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Simpan tautan Google Drive bukti dukung indikator oleh dosen PIC.
     */
    public function storeEvidence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'indicator_id' => 'nullable',
            'project_id' => 'nullable',
            'code' => 'required|string',
            'evidence_url' => 'required|url|max:1000',
            'evidence_notes' => 'nullable|string|max:1000',
        ]);

        $projectId = (string) ($validated['project_id'] ?? '1');
        $indicatorId = (string) ($validated['indicator_id'] ?? '1');
        $code = (string) $validated['code'];
        $url = (string) $validated['evidence_url'];
        $notes = (string) ($validated['evidence_notes'] ?? '');

        // 1. Simpan ke session lokal agar langsung muncul di antarmuka
        session([
            "evidence_{$projectId}_{$code}" => [
                'url' => $url,
                'notes' => $notes,
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);

        // 2. Kirim ke REST API backend
        try {
            $payload = [
                'link_evidence' => $url,
                'file_url' => $url,
                'nama_bukti' => "Bukti Google Drive {$code}",
                'catatan' => $notes,
            ];

            // Coba endpoint PUT /api/v1/projects/{id}/indicators/{indicatorId}/evidence
            $response = $this->akreditasiService->updateIndicatorEvidence($projectId, $indicatorId, $payload);

            // Jika route tidak ada (404), simpan via self-assessment atau bukti
            if (! $response->successful()) {
                $this->akreditasiService->post('/self-assessment', [
                    'id_project' => (int) $projectId,
                    'id_indikator' => (int) $indicatorId,
                    'catatan_penjelasan' => "Tautan Bukti: {$url}\nCatatan: {$notes}",
                ]);
            }
        } catch (\Throwable $e) {
            // Silently fallback ke session
        }

        return response()->json([
            'success' => true,
            'message' => 'Tautan Google Drive bukti dukung berhasil disimpan.',
            'data' => [
                'code' => $code,
                'evidence_url' => $url,
                'evidence_notes' => $notes,
            ],
        ]);
    }
}
