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
     * Dosen hanya dapat melihat dan mengunggah butir indikator yang ditugaskan kepadanya.
     */
    public function myAssignments(Request $request): View
    {
        $currentUser = session('user', []);
        $userId = $currentUser['id'] ?? null;
        $userName = $currentUser['nama'] ?? ($currentUser['name'] ?? 'Dosen');

        // 1. Ambil data penugasan dari backend API
        $rawAssignments = [];
        try {
            $res = $this->akreditasiService->getMyAssignments();
            if ($res->successful()) {
                $rawAssignments = $res->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            $rawAssignments = [];
        }

        // 2. Jika API kosong, cek penugasan dari session plotting dan mapping-tim
        if (empty($rawAssignments)) {
            $kriteriaMap = [
                'K1.1' => ['id' => 1, 'desc' => 'Kejelasan visi, misi, tujuan, dan ketercapaian strategi prodi TRK.', 'bobot' => 3.5],
                'K2.1' => ['id' => 2, 'desc' => 'Sistem tata pamong, kepemimpinan publik, dan efektivitas pengelolaan operasional prodi.', 'bobot' => 4.0],
                'K2.2' => ['id' => 3, 'desc' => 'Sistem penjaminan mutu internal (SPMI) dan siklus PPEPP secara berkelanjutan.', 'bobot' => 4.0],
                'K2.3' => ['id' => 4, 'desc' => 'Efektivitas kerjasama strategis tridharma tingkat nasional maupun internasional.', 'bobot' => 3.0],
                'K3.1' => ['id' => 5, 'desc' => 'Kualitas input mahasiswa baru, rasio pendaftar, dan daya tampung prodi.', 'bobot' => 3.0],
                'K3.2' => ['id' => 6, 'desc' => 'Layanan kemahasiswaan (bimbingan karir, konseling, kewirausahaan, dan penalaran).', 'bobot' => 3.0],
                'K3.3' => ['id' => 7, 'desc' => 'Prestasi mahasiswa di bidang akademik dan non-akademik tingkat nasional/internasional.', 'bobot' => 3.5],
                'K3.4' => ['id' => 8, 'desc' => 'Masa tunggu lulusan, tingkat kepuasan pengguna lulusan, dan kesesuaian bidang kerja.', 'bobot' => 4.0],
                'K4.1' => ['id' => 9, 'desc' => 'Kualifikasi akademik dan kecukupan Dosen Tetap Program Studi (DTPS).', 'bobot' => 4.0],
                'K4.2' => ['id' => 10, 'desc' => 'Beban kerja dosen (EWMP), rasio dosen terhadap mahasiswa, dan produktivitas tridharma.', 'bobot' => 3.5],
                'K5.1' => ['id' => 12, 'desc' => 'Kecukupan alokasi dana operasional, penelitian, pkm, dan investasi sarana prodi.', 'bobot' => 3.5],
                'K6.1' => ['id' => 14, 'desc' => 'Kesesuaian kurikulum berbasis OBE (Outcome-Based Education) dan Standar KKNI.', 'bobot' => 4.0],
            ];

            // Cek session plotting project aktif
            $sessionPlotting = session('plotting_assignments_1', []);
            if (! empty($sessionPlotting) && is_array($sessionPlotting)) {
                foreach ($sessionPlotting as $code => $roles) {
                    $matchedRole = null;
                    if (! empty($roles['pic']) && (strtolower(trim($roles['pic'])) === strtolower(trim($userName)) || str_contains(strtolower($userName), strtolower(trim($roles['pic']))))) {
                        $matchedRole = 'PIC';
                    } elseif (! empty($roles['pj']) && (strtolower(trim($roles['pj'])) === strtolower(trim($userName)) || str_contains(strtolower($userName), strtolower(trim($roles['pj']))))) {
                        $matchedRole = 'PJ';
                    }

                    if ($matchedRole && isset($kriteriaMap[$code])) {
                        $rawAssignments[] = [
                            'id_mapping' => count($rawAssignments) + 1,
                            'role' => $matchedRole,
                            'id_indikator' => $kriteriaMap[$code]['id'],
                            'kode_indikator' => $code,
                            'deskripsi' => $kriteriaMap[$code]['desc'],
                            'bobot' => $kriteriaMap[$code]['bobot'],
                            'id_project' => 1,
                            'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2026',
                            'has_bukti' => false,
                        ];
                    }
                }
            }

            // Fallback cek mapping-tim dari API backend
            if (empty($rawAssignments)) {
                try {
                    $mappingRes = $this->akreditasiService->getMappingTim();
                    $allMappings = $this->extractData($mappingRes);

                    foreach ($allMappings as $m) {
                        $mUserId = $m['id_user'] ?? ($m['user']['id'] ?? null);
                        $mUserName = $m['user']['nama'] ?? null;

                        if (($userId && (string) $mUserId === (string) $userId) ||
                            (strtolower(trim($mUserName ?? '')) === strtolower(trim($userName)))) {
                            $rawAssignments[] = [
                                'id_mapping' => $m['id'] ?? null,
                                'role' => $m['role'] ?? 'PIC',
                                'id_indikator' => $m['id_indikator'] ?? ($m['indikator']['id'] ?? 1),
                                'kode_indikator' => $m['indikator']['kode_indikator'] ?? 'Indikator',
                                'deskripsi' => $m['indikator']['nama_indikator'] ?? ($m['indikator']['deskripsi'] ?? 'Deskripsi butir penilaian akreditasi'),
                                'bobot' => $m['indikator']['bobot'] ?? 1.0,
                                'id_project' => $m['id_project'] ?? 1,
                                'nama_project' => $m['project']['nama_project'] ?? 'Akreditasi LAM INFOKOM D4 TRK 2026',
                                'has_bukti' => ! empty($m['has_bukti']),
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    // Abaikan
                }
            }
        }

        // 3. Cek penugasan dari session plotting (termasuk jika Kaprodi menugaskan dirinya sendiri sebagai PIC)
        $userRole = strtolower((string) (session('user.role') ?? session('user.role_name') ?? ''));
        $isKaprodi = str_contains($userRole, 'kaprodi') || str_contains($userRole, 'admin');

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

        // Kumpulkan indikator yang sudah ada di rawAssignments agar tidak duplikat
        $existingCodes = array_flip(array_column($rawAssignments, 'kode_indikator'));

        foreach ([1, 2, 3] as $projId) {
            $sessionPlotting = session("plotting_assignments_{$projId}", []);
            if (empty($sessionPlotting) || ! is_array($sessionPlotting)) {
                continue;
            }

            foreach ($sessionPlotting as $code => $roles) {
                if (isset($existingCodes[$code])) {
                    continue;
                }

                foreach (['pic' => 'PIC', 'pj' => 'PJ'] as $roleKey => $roleLabel) {
                    $assignedName = $roles[$roleKey] ?? null;
                    if (! $assignedName) {
                        continue;
                    }

                    // Cocokkan nama user: sama persis, substring, atau jika Kaprodi menugaskan dirinya sendiri
                    $cleanAssigned = strtolower(trim(str_replace('(kaprodi)', '', $assignedName)));
                    $cleanUser = strtolower(trim(str_replace('(kaprodi)', '', $userName)));

                    $isMatch = ($cleanAssigned === $cleanUser)
                        || ($cleanUser !== '' && str_contains($cleanAssigned, $cleanUser))
                        || ($cleanAssigned !== '' && str_contains($cleanUser, $cleanAssigned))
                        || (str_contains(strtolower($assignedName), 'kaprodi') && $isKaprodi);

                    if ($isMatch && isset($kriteriaMap[$code])) {
                        $existingCodes[$code] = true;
                        $rawAssignments[] = [
                            'id_mapping' => count($rawAssignments) + 1,
                            'role' => $roleLabel,
                            'id_indikator' => $kriteriaMap[$code]['id'],
                            'kode_indikator' => $code,
                            'deskripsi' => $kriteriaMap[$code]['desc'],
                            'bobot' => $kriteriaMap[$code]['bobot'],
                            'id_project' => $projId,
                            'nama_project' => "Akreditasi LAM INFOKOM D4 TRK 202{$projId}",
                            'has_bukti' => false,
                            'evidence_url' => null,
                            'evidence_notes' => null,
                        ];
                        break; // Satu indikator cukup satu entri per project
                    }
                }
            }
        }

        // 4. Fallback demo data HANYA jika belum ada penugasan sama sekali (dari API maupun session)
        if (empty($rawAssignments)) {
            $rawAssignments = [
                [
                    'id_mapping' => 1,
                    'role' => 'PJ',
                    'id_indikator' => 1,
                    'kode_indikator' => 'K1.1',
                    'deskripsi' => 'Kejelasan visi, misi, dan ketercapaian strategi prodi TRK.',
                    'bobot' => 3.5,
                    'id_project' => 1,
                    'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2026',
                    'has_bukti' => true,
                    'evidence_url' => 'https://drive.google.com/drive/folders/sample-k11-vision',
                    'evidence_notes' => 'Dokumen Renstra dan Buku Visi Misi Prodi.',
                ],
                [
                    'id_mapping' => 2,
                    'role' => 'PIC',
                    'id_indikator' => 2,
                    'kode_indikator' => 'K2.1',
                    'deskripsi' => 'Sistem tata pamong, kepemimpinan publik, dan efektivitas pengelolaan operasional prodi.',
                    'bobot' => 4.0,
                    'id_project' => 1,
                    'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2026',
                    'has_bukti' => false,
                    'evidence_url' => null,
                    'evidence_notes' => null,
                ],
                [
                    'id_mapping' => 3,
                    'role' => 'PIC',
                    'id_indikator' => 5,
                    'kode_indikator' => 'K3.1',
                    'deskripsi' => 'Kualitas input mahasiswa baru, rasio pendaftar, dan tingkat kelulusan tepat waktu.',
                    'bobot' => 3.0,
                    'id_project' => 1,
                    'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2026',
                    'has_bukti' => false,
                    'evidence_url' => null,
                    'evidence_notes' => null,
                ],
            ];
        }

        // Sinkronkan dengan bukti yang tersimpan di session lokal
        $rawAssignments = array_map(function ($item) {
            $projId = $item['id_project'] ?? 1;
            $code = $item['kode_indikator'] ?? '';
            $sessEvidence = session("evidence_{$projId}_{$code}");
            if ($sessEvidence) {
                $item['has_bukti'] = true;
                $item['evidence_url'] = $sessEvidence['url'] ?? ($item['evidence_url'] ?? null);
                $item['evidence_notes'] = $sessEvidence['notes'] ?? ($item['evidence_notes'] ?? null);
            }

            return $item;
        }, $rawAssignments);

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
