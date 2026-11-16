<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LulusanController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function dashboard(): RedirectResponse
    {
        return redirect()->route('lulusan.index');
    }

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 25));
        $search = trim((string) $request->query('search', ''));
        $idProdi = $request->query('id_prodi');
        $angkatan = $request->query('angkatan');
        $tahunLulus = $request->query('tahun_lulus');

        // Fetch Tren data (GET /api/mahasiswa/tren)
        $trenN = max(1, (int) $request->query('n', 5));
        $trenResponse = $this->akademikService->getMahasiswaTren(['n' => $trenN]);

        if ($trenResponse->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawTren = $trenResponse->json('data') ?? [];
        $tren = is_array($rawTren) ? [
            'years' => $rawTren['years'] ?? [],
            'jumlah_mahasiswa_baru' => $rawTren['jumlah_mahasiswa_baru'] ?? [],
            'rata_ipk_lulusan' => $rawTren['rata_ipk_lulusan'] ?? [],
            'rata_masa_studi_bulan' => $rawTren['rata_masa_studi_bulan'] ?? [],
            'persen_lulus_tepat_waktu' => $rawTren['persen_lulus_tepat_waktu'] ?? [],
            'rata_masa_tunggu_bulan' => $rawTren['rata_masa_tunggu_bulan'] ?? [],
        ] : [
            'years' => [],
            'jumlah_mahasiswa_baru' => [],
            'rata_ipk_lulusan' => [],
            'rata_masa_studi_bulan' => [],
            'persen_lulus_tepat_waktu' => [],
            'rata_masa_tunggu_bulan' => [],
        ];

        $apiParams = [
            'page' => $page,
            'per_page' => $perPage,
        ];
        $summaryParams = [];

        if (! empty($idProdi)) {
            $apiParams['id_prodi'] = (int) $idProdi;
            $summaryParams['id_prodi'] = (int) $idProdi;
        }
        if (! empty($angkatan)) {
            $apiParams['angkatan'] = (int) $angkatan;
            $summaryParams['angkatan'] = (int) $angkatan;
        }
        if (! empty($tahunLulus)) {
            $apiParams['tahun_lulus'] = (int) $tahunLulus;
            $summaryParams['tahun_lulus'] = (int) $tahunLulus;
        }
        if ($search !== '') {
            $apiParams['search'] = $search;
        }

        $lulusanResponse = $this->akademikService->getLulusanList($apiParams);

        if ($lulusanResponse->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $ringkasanResponse = $this->akademikService->getLulusanRingkasan($summaryParams);

        // Parse Lulusan List
        $rawLulusan = $lulusanResponse->json('data') ?? [];
        $apiPagination = $lulusanResponse->json('pagination');

        $lulusanList = [];
        if (is_array($rawLulusan)) {
            if (isset($rawLulusan['data']) && is_array($rawLulusan['data'])) {
                $lulusanList = $rawLulusan['data'];
                if (! $apiPagination) {
                    $apiPagination = [
                        'total' => $rawLulusan['total'] ?? count($lulusanList),
                        'per_page' => $rawLulusan['per_page'] ?? $perPage,
                        'current_page' => $rawLulusan['current_page'] ?? $page,
                        'last_page' => $rawLulusan['last_page'] ?? 1,
                    ];
                }
            } else {
                $lulusanList = $rawLulusan;
            }
        }

        // Apply in-memory client search if search was provided and API didn't filter
        if ($search !== '' && ! empty($lulusanList)) {
            $kw = mb_strtolower($search);
            $lulusanList = array_values(array_filter($lulusanList, function ($item) use ($kw) {
                $nama = mb_strtolower((string) ($item['nama_mahasiswa'] ?? ''));
                $nim = mb_strtolower((string) ($item['nim_display'] ?? $item['nim'] ?? ''));
                $thnMasuk = (string) ($item['tahun_masuk'] ?? '');
                $jenjang = mb_strtolower((string) ($item['jenjang_prodi'] ?? ''));

                return str_contains($nama, $kw) || str_contains($nim, $kw) || str_contains($thnMasuk, $kw) || str_contains($jenjang, $kw);
            }));
        }

        // Setup pagination data
        if ($apiPagination && is_array($apiPagination)) {
            $pagination = [
                'total' => (int) ($apiPagination['total'] ?? count($lulusanList)),
                'per_page' => (int) ($apiPagination['per_page'] ?? $perPage),
                'current_page' => (int) ($apiPagination['current_page'] ?? $page),
                'last_page' => (int) ($apiPagination['last_page'] ?? 1),
                'from' => $apiPagination['from'] ?? (($page - 1) * $perPage + 1),
                'to' => $apiPagination['to'] ?? min($page * $perPage, (int) ($apiPagination['total'] ?? count($lulusanList))),
            ];
        } else {
            $total = count($lulusanList);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = max(1, min($page, $lastPage));
            $offset = ($currentPage - 1) * $perPage;
            $pagedList = array_slice($lulusanList, $offset, $perPage);

            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ];
            $lulusanList = $pagedList;
        }

        // Parse Summary Cards data
        $rawRingkasan = $ringkasanResponse->json('data') ?? [];
        $distribusiIpk = [];
        if (! empty($rawRingkasan['distribusi_ipk']) && is_array($rawRingkasan['distribusi_ipk'])) {
            $distribusiIpk = (array) $rawRingkasan['distribusi_ipk'];
        }

        // Precompute masa tunggu average from lulusan list in case summary lacks it
        $totalMasaTunggu = 0;
        $validMasaTungguCount = 0;
        foreach ($lulusanList as $item) {
            if (isset($item['masa_tunggu_bulan']) && is_numeric($item['masa_tunggu_bulan'])) {
                $totalMasaTunggu += (float) $item['masa_tunggu_bulan'];
                $validMasaTungguCount++;
            }
        }
        $avgMasaTungguFromList = $validMasaTungguCount > 0 ? round($totalMasaTunggu / $validMasaTungguCount, 1) : 0.0;

        if (! empty($rawRingkasan) && is_array($rawRingkasan)) {
            $apiMasaTunggu = isset($rawRingkasan['rata_rata_masa_tunggu_bulan']) && is_numeric($rawRingkasan['rata_rata_masa_tunggu_bulan'])
                ? (float) $rawRingkasan['rata_rata_masa_tunggu_bulan']
                : 0.0;

            $ringkasan = [
                'total_lulusan' => (int) ($rawRingkasan['total_lulusan'] ?? count($lulusanList)),
                'rata_rata_masa_studi_bulan' => (float) ($rawRingkasan['rata_rata_masa_studi_bulan'] ?? 0),
                'rata_rata_ipk' => (float) ($rawRingkasan['rata_rata_ipk'] ?? 0),
                'persen_lulus_tepat_waktu' => (float) ($rawRingkasan['persen_lulus_tepat_waktu'] ?? 0),
                'jumlah_lulus_tepat_waktu' => (int) ($rawRingkasan['jumlah_lulus_tepat_waktu'] ?? 0),
                'distribusi_ipk' => $distribusiIpk,
                'rata_rata_masa_tunggu_bulan' => $apiMasaTunggu > 0 ? $apiMasaTunggu : $avgMasaTungguFromList,
            ];
        } else {
            // Compute fallback summary metrics from available items
            $totalItems = count($lulusanList);
            $tepatWaktuCount = 0;
            $totalIpk = 0.0;
            $validIpkCount = 0;
            $totalMasaStudi = 0;
            $fallbackDist = [
                '< 2.75' => 0,
                '2.75 - 3.00' => 0,
                '3.01 - 3.50' => 0,
                '> 3.50' => 0,
            ];

            foreach ($lulusanList as $item) {
                if (! empty($item['lulus_tepat_waktu'])) {
                    $tepatWaktuCount++;
                }
                if (isset($item['ipk_riil']) && is_numeric($item['ipk_riil'])) {
                    $ipkVal = (float) $item['ipk_riil'];
                    $totalIpk += $ipkVal;
                    $validIpkCount++;
                    if ($ipkVal < 2.75) {
                        $fallbackDist['< 2.75']++;
                    } elseif ($ipkVal <= 3.00) {
                        $fallbackDist['2.75 - 3.00']++;
                    } elseif ($ipkVal <= 3.50) {
                        $fallbackDist['3.01 - 3.50']++;
                    } else {
                        $fallbackDist['> 3.50']++;
                    }
                }
                if (isset($item['masa_studi_bulan']) && is_numeric($item['masa_studi_bulan'])) {
                    $totalMasaStudi += (int) $item['masa_studi_bulan'];
                }
            }

            $ringkasan = [
                'total_lulusan' => $totalItems,
                'rata_rata_masa_studi_bulan' => $totalItems > 0 ? round($totalMasaStudi / $totalItems, 1) : 0,
                'rata_rata_ipk' => $validIpkCount > 0 ? round($totalIpk / $validIpkCount, 2) : 0.0,
                'persen_lulus_tepat_waktu' => $totalItems > 0 ? round(($tepatWaktuCount / $totalItems) * 100, 1) : 0.0,
                'jumlah_lulus_tepat_waktu' => $tepatWaktuCount,
                'distribusi_ipk' => empty($distribusiIpk) ? $fallbackDist : $distribusiIpk,
                'rata_rata_masa_tunggu_bulan' => $avgMasaTungguFromList,
            ];
        }

        // Fallback if distribusi_ipk is still empty
        if (empty($ringkasan['distribusi_ipk']) && ! empty($lulusanList)) {
            $fallbackDist = [
                '< 2.75' => 0,
                '2.75 - 3.00' => 0,
                '3.01 - 3.50' => 0,
                '> 3.50' => 0,
            ];
            foreach ($lulusanList as $item) {
                if (isset($item['ipk_riil']) && is_numeric($item['ipk_riil'])) {
                    $ipkVal = (float) $item['ipk_riil'];
                    if ($ipkVal < 2.75) {
                        $fallbackDist['< 2.75']++;
                    } elseif ($ipkVal <= 3.00) {
                        $fallbackDist['2.75 - 3.00']++;
                    } elseif ($ipkVal <= 3.50) {
                        $fallbackDist['3.01 - 3.50']++;
                    } else {
                        $fallbackDist['> 3.50']++;
                    }
                }
            }
            $ringkasan['distribusi_ipk'] = $fallbackDist;
        }

        // Populate tren['rata_masa_tunggu_bulan'] if empty or all null from API
        $hasTrenMasaTunggu = false;
        foreach ($tren['rata_masa_tunggu_bulan'] as $val) {
            if ($val !== null && $val !== '') {
                $hasTrenMasaTunggu = true;
                break;
            }
        }

        if (! $hasTrenMasaTunggu && ! empty($tren['years']) && ! empty($lulusanList)) {
            $yearGroups = [];
            foreach ($lulusanList as $item) {
                if (! isset($item['masa_tunggu_bulan']) || ! is_numeric($item['masa_tunggu_bulan'])) {
                    continue;
                }
                $val = (float) $item['masa_tunggu_bulan'];
                $yLulus = ! empty($item['tanggal_kelulusan']) ? (int) date('Y', strtotime((string) $item['tanggal_kelulusan'])) : null;
                $yMasuk = ! empty($item['tahun_masuk']) ? (int) $item['tahun_masuk'] : null;

                if ($yLulus) {
                    $yearGroups['lulus'][$yLulus][] = $val;
                }
                if ($yMasuk) {
                    $yearGroups['masuk'][$yMasuk][] = $val;
                }
            }

            $computedTrenTunggu = [];
            $foundMatch = false;
            foreach ($tren['years'] as $yr) {
                if (! empty($yearGroups['lulus'][$yr])) {
                    $computedTrenTunggu[] = round(array_sum($yearGroups['lulus'][$yr]) / count($yearGroups['lulus'][$yr]), 1);
                    $foundMatch = true;
                } elseif (! empty($yearGroups['masuk'][$yr])) {
                    $computedTrenTunggu[] = round(array_sum($yearGroups['masuk'][$yr]) / count($yearGroups['masuk'][$yr]), 1);
                    $foundMatch = true;
                } else {
                    $computedTrenTunggu[] = null;
                }
            }

            if ($foundMatch) {
                $tren['rata_masa_tunggu_bulan'] = $computedTrenTunggu;
            }
        }

        // Fetch Program Studi options for filter
        $prodiOptions = [];
        try {
            $prodiRes = $this->akademikService->getProdiList(['per_page' => 100]);
            if ($prodiRes->successful()) {
                $rawProdi = $prodiRes->json('data') ?? [];
                if (isset($rawProdi['data']) && is_array($rawProdi['data'])) {
                    $rawProdi = $rawProdi['data'];
                }
                if (is_array($rawProdi)) {
                    foreach ($rawProdi as $p) {
                        if (! empty($p['id'])) {
                            $prodiOptions[] = [
                                'id' => $p['id'],
                                'nama' => $p['nama_prodi'] ?? $p['nama'] ?? 'Prodi #'.$p['id'],
                                'jenjang' => $p['jenjang'] ?? '',
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Silently ignore
        }

        // Fetch Angkatan options for filter
        $angkatanOptions = [];
        try {
            $angkatanRes = $this->akademikService->getAngkatanList(['per_page' => 100]);
            if ($angkatanRes->successful()) {
                $rawAngkatan = $angkatanRes->json('data') ?? [];
                if (isset($rawAngkatan['data']) && is_array($rawAngkatan['data'])) {
                    $rawAngkatan = $rawAngkatan['data'];
                }
                if (is_array($rawAngkatan)) {
                    foreach ($rawAngkatan as $akt) {
                        $th = $akt['angkatan'] ?? $akt['tahun'] ?? null;
                        if ($th) {
                            $angkatanOptions[] = (int) $th;
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Silently ignore
        }

        // Fallback angkatan years if empty
        if (empty($angkatanOptions)) {
            $currYear = (int) date('Y');
            for ($y = $currYear - 2; $y >= $currYear - 8; $y--) {
                $angkatanOptions[] = $y;
            }
        } else {
            rsort($angkatanOptions);
            $angkatanOptions = array_values(array_unique($angkatanOptions));
        }

        // Generate Tahun Kelulusan options
        $tahunLulusOptions = [];
        foreach ($lulusanList as $item) {
            if (! empty($item['tanggal_kelulusan'])) {
                $y = (int) date('Y', strtotime((string) $item['tanggal_kelulusan']));
                if ($y > 2000) {
                    $tahunLulusOptions[] = $y;
                }
            }
        }
        $currYear = (int) date('Y');
        for ($y = $currYear; $y >= $currYear - 5; $y--) {
            $tahunLulusOptions[] = $y;
        }
        rsort($tahunLulusOptions);
        $tahunLulusOptions = array_values(array_unique($tahunLulusOptions));

        return view('lulusan.index', [
            'lulusanList' => $lulusanList,
            'pagination' => $pagination,
            'ringkasan' => $ringkasan,
            'tren' => $tren,
            'prodiOptions' => $prodiOptions,
            'angkatanOptions' => $angkatanOptions,
            'tahunLulusOptions' => $tahunLulusOptions,
            'selectedProdi' => $idProdi,
            'selectedAngkatan' => $angkatan,
            'selectedTahunLulus' => $tahunLulus,
            'search' => $search,
        ]);
    }
}
