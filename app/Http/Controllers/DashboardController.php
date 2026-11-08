<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService
    ) {}

    /**
     * Display the main dashboard view.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->session()->get('user', [
            'nama' => 'Superadmin',
            'email' => '-',
            'role' => 'SUPERADMIN',
        ]);

        if (str_contains(strtolower((string) ($user['role'] ?? $user['role_name'] ?? '')), 'reviewer')) {
            return redirect()->route('reviewer.dashboard');
        }

        // Default KPI data matching Figma design
        $kpi = [
            'mahasiswa_aktif' => 425,
            'mahasiswa_baru' => 112,
            'lulus_tepat_waktu' => 87,
            'rata_rata_ipk' => 3.78,
            'prestasi_nasional' => 97,
            'prestasi_internasional' => 34,
        ];

        // Ringkasan Tri Dharma dengan default fallback
        $tridharma = [
            'dtps' => 20,
            'dosen_s3' => 8,
            'lektor_kepala' => 3,
            'penelitian' => 35,
            'publikasi_scopus' => 14,
            'hki' => 8,
            'pkm' => 22,
            'kerjasama_aktif' => 17,
        ];

        $token = (string) $request->session()->get('access_token');

        // 1. Hit API Statistik Dosen (DTPS, Dosen S3, Lektor Kepala)
        try {
            $dosenRes = $this->tridharmaService->getDosenStatistik($token);
            if ($dosenRes->successful()) {
                $dosenData = $dosenRes->json('data', []);
                if (isset($dosenData['total_aktif'])) {
                    $tridharma['dtps'] = (int) $dosenData['total_aktif'];
                }

                if (isset($dosenData['sebaran_pendidikan']) && is_array($dosenData['sebaran_pendidikan'])) {
                    $s3Count = 0;
                    foreach ($dosenData['sebaran_pendidikan'] as $pend) {
                        if (str_contains(strtoupper((string) ($pend['jenjang'] ?? '')), 'S3')) {
                            $s3Count += (int) ($pend['total_dosen'] ?? 0);
                        }
                    }
                    if ($s3Count > 0) {
                        $tridharma['dosen_s3'] = $s3Count;
                    }
                }

                if (isset($dosenData['sebaran_jabatan']) && is_array($dosenData['sebaran_jabatan'])) {
                    $lkCount = 0;
                    foreach ($dosenData['sebaran_jabatan'] as $jab) {
                        if (str_contains(strtolower((string) ($jab['nama_jabatan'] ?? '')), 'lektor kepala')) {
                            $lkCount += (int) ($jab['total_dosen'] ?? 0);
                        }
                    }
                    if ($lkCount > 0) {
                        $tridharma['lektor_kepala'] = $lkCount;
                    }
                }
            }
        } catch (\Throwable) {
        }

        // 2. Hit API Ringkasan Tri Dharma (Penelitian, PKM, Luaran/Scopus & HKI)
        try {
            $summaryRes = $this->tridharmaService->getTridharmaSummary([], $token);
            if ($summaryRes->successful()) {
                $summaryData = $summaryRes->json('data', []);
                $penelitianTotal = $summaryData['kegiatan']['penelitian']['total']
                    ?? $summaryData['penelitian']['total']
                    ?? null;
                if ($penelitianTotal !== null) {
                    $tridharma['penelitian'] = (int) $penelitianTotal;
                }

                $pkmTotal = $summaryData['kegiatan']['pkm']['total']
                    ?? $summaryData['pkm']['total']
                    ?? null;
                if ($pkmTotal !== null) {
                    $tridharma['pkm'] = (int) $pkmTotal;
                }

                $byJenis = $summaryData['luaran']['by_jenis'] ?? [];
                if (is_array($byJenis)) {
                    foreach ($byJenis as $jenis => $count) {
                        $jenisLower = strtolower((string) $jenis);
                        if (str_contains($jenisLower, 'hki') || str_contains($jenisLower, 'hak cipta')) {
                            $tridharma['hki'] = (int) $count;
                        }
                        if (str_contains($jenisLower, 'scopus') || str_contains($jenisLower, 'internasional') || str_contains($jenisLower, 'jurnal')) {
                            $tridharma['publikasi_scopus'] = (int) $count;
                        }
                    }
                }
            }
        } catch (\Throwable) {
        }

        // 3. Hit API Kerja Sama Aktif
        try {
            $kerjasamaRes = $this->tridharmaService->getKerjasamaAktif([], $token);
            if ($kerjasamaRes->successful()) {
                $totalAktif = $kerjasamaRes->json('data.total_aktif');
                if ($totalAktif !== null) {
                    $tridharma['kerjasama_aktif'] = (int) $totalAktif;
                }
            }
        } catch (\Throwable) {
        }

        // Tren data 2022 - 2026
        $tren = [
            'years' => ['2022', '2023', '2024', '2025', '2026'],
            'ipk' => [3.30, 3.40, 3.52, 3.52, 3.52],
            'kelulusan' => [73, 88, 77, 87, 85],
        ];

        // Prestasi Mahasiswa Terbaru
        $prestasiTerbaru = collect(range(1, 6))->map(fn (int $no): array => [
            'no' => $no,
            'nama' => 'Gerald Manunggal Fauzi',
            'lomba' => 'UI/UX Miaw Miaw',
            'tingkat' => 'Internasional',
            'tahun' => '2026',
        ])->all();

        return view('dashboard', [
            'user' => $user,
            'kpi' => $kpi,
            'tridharma' => $tridharma,
            'tren' => $tren,
            'prestasiTerbaru' => $prestasiTerbaru,
        ]);
    }
}
