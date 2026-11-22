<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UppsController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    /**
     * Master Page UPPS — Menampilkan daftar UPPS dengan relasi 1 UPPS menaungi banyak Program Studi.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $uppsResponse = $this->akademikService->getUppsList();

        if ($uppsResponse->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $prodiResponse = $this->akademikService->getProdiList();
        $prodiData = $prodiResponse->json('data') ?? [];
        $rawProdis = is_array($prodiData) ? $prodiData : [];

        // Apply session custom prodi if exists
        $sessionCustomProdi = session('custom_prodi_list', []);
        $deletedProdiIds = session('deleted_prodi_ids', []);
        $allProdis = array_values(array_filter($rawProdis, fn ($p) => ! in_array($p['id'] ?? null, $deletedProdiIds)));
        foreach ($sessionCustomProdi as $cp) {
            $fIdx = null;
            foreach ($allProdis as $idx => $ap) {
                if ((string) ($ap['id'] ?? '') === (string) ($cp['id'] ?? '')) {
                    $fIdx = $idx;
                    break;
                }
            }
            if ($fIdx !== null) {
                $allProdis[$fIdx] = array_merge($allProdis[$fIdx], $cp);
            } else {
                $allProdis[] = $cp;
            }
        }

        // Fallback prodis jika data API kosong
        if (empty($allProdis)) {
            $allProdis = [
                ['id' => 1, 'kode_prodi' => 'TL01', 'nama_prodi' => 'D3 Teknik Listrik', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 1],
                ['id' => 2, 'kode_prodi' => 'TE01', 'nama_prodi' => 'D3 Teknik Elektronika', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 1],
                ['id' => 3, 'kode_prodi' => 'TT01', 'nama_prodi' => 'D4 Teknik Telekomunikasi', 'jenjang' => 'D4', 'akreditasi' => 'Baik Sekali', 'id_upps' => 1],
                ['id' => 4, 'kode_prodi' => 'TM01', 'nama_prodi' => 'D3 Teknik Mesin', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 2],
                ['id' => 5, 'kode_prodi' => 'TR01', 'nama_prodi' => 'D4 Rekayasa Mesin', 'jenjang' => 'D4', 'akreditasi' => 'Baik Sekali', 'id_upps' => 2],
                ['id' => 6, 'kode_prodi' => 'TS01', 'nama_prodi' => 'D3 Teknik Sipil', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 3],
                ['id' => 7, 'kode_prodi' => 'AK01', 'nama_prodi' => 'D3 Akuntansi', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 4],
                ['id' => 8, 'kode_prodi' => 'AB01', 'nama_prodi' => 'D3 Administrasi Bisnis', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 5],
            ];
        }

        $uppsData = $uppsResponse->json('data') ?? [];
        $allUpps = is_array($uppsData) ? $uppsData : [];
        $deletedUppsIds = session('deleted_upps_ids', []);
        $allUpps = array_values(array_filter($allUpps, fn ($u) => ! in_array($u['id'] ?? null, $deletedUppsIds)));

        $customUpps = session('custom_upps_list', []);
        if (is_array($customUpps)) {
            foreach ($customUpps as $cu) {
                $foundIdx = null;
                foreach ($allUpps as $idx => $eu) {
                    if ((string) ($eu['id'] ?? '') === (string) ($cu['id'] ?? '')) {
                        $foundIdx = $idx;
                        break;
                    }
                }
                if ($foundIdx !== null) {
                    $allUpps[$foundIdx] = array_merge($allUpps[$foundIdx], $cu);
                } else {
                    $allUpps[] = $cu;
                }
            }
        }

        // Fallback UPPS jika data API kosong — mencerminkan relasi 1 UPPS menaungi banyak prodi
        if (empty($allUpps) && ! session()->has('custom_upps_list')) {
            $allUpps = [
                [
                    'id' => 1,
                    'kode_upps' => 'JTE-01',
                    'nama_upps' => 'Jurusan Teknik Elektro',
                    'jenis' => 'Jurusan',
                    'pimpinan_upps' => 'Dr. Garup Lambang Goro, S.T., M.T.',
                    'prodi_ids' => [1, 2, 3],
                ],
                [
                    'id' => 2,
                    'kode_upps' => 'JTM-02',
                    'nama_upps' => 'Jurusan Teknik Mesin',
                    'jenis' => 'Jurusan',
                    'pimpinan_upps' => 'Dr. Rusnaldy, S.T., M.T.',
                    'prodi_ids' => [4, 5],
                ],
                [
                    'id' => 3,
                    'kode_upps' => 'JTS-03',
                    'nama_upps' => 'Jurusan Teknik Sipil',
                    'jenis' => 'Jurusan',
                    'pimpinan_upps' => 'Drs. Supriyadi, M.T.',
                    'prodi_ids' => [6],
                ],
                [
                    'id' => 4,
                    'kode_upps' => 'JAK-04',
                    'nama_upps' => 'Jurusan Akuntansi',
                    'jenis' => 'Jurusan',
                    'pimpinan_upps' => 'Dra. Endang Purwanti, M.Si.',
                    'prodi_ids' => [7],
                ],
                [
                    'id' => 5,
                    'kode_upps' => 'JAB-05',
                    'nama_upps' => 'Jurusan Administrasi Bisnis',
                    'jenis' => 'Jurusan',
                    'pimpinan_upps' => 'Drs. Tri Wardoyo, M.Si.',
                    'prodi_ids' => [8],
                ],
            ];
        }

        // Petakan relasi 1 UPPS menaungi banyak Program Studi
        $uppsList = [];
        foreach ($allUpps as $u) {
            $uId = $u['id'] ?? null;
            $associatedProdiIds = array_map('strval', (array) ($u['prodi_ids'] ?? []));
            if (! empty($u['prodi_id'])) {
                $associatedProdiIds[] = (string) $u['prodi_id'];
            }

            // Kumpulkan prodi yang berafiliasi dengan UPPS ini
            $matchedProdis = [];
            foreach ($allProdis as $p) {
                $pId = (string) ($p['id'] ?? '');
                $pUppsId = (string) ($p['id_upps'] ?? '');
                if ($pUppsId === (string) $uId || in_array($pId, $associatedProdiIds, true)) {
                    $matchedProdis[] = [
                        'id' => $p['id'] ?? null,
                        'kode_prodi' => $p['kode_prodi'] ?? '',
                        'nama_prodi' => $p['nama_prodi'] ?? '-',
                        'jenjang' => $p['jenjang'] ?? '-',
                        'akreditasi' => $p['akreditasi'] ?? ($p['status_akreditasi'] ?? '-'),
                    ];
                }
            }

            $uppsList[] = [
                'id' => $uId,
                'kode_upps' => $u['kode_upps'] ?? ('UPPS-'.$uId),
                'nama_upps' => $u['nama_upps'] ?? ($u['nama'] ?? '-'),
                'jenis' => $u['jenis'] ?? 'Jurusan',
                'pimpinan_upps' => $u['pimpinan_upps'] ?? ($u['pimpinan'] ?? '-'),
                'prodis' => $matchedProdis,
                'prodi_count' => count($matchedProdis),
                'prodi_ids' => array_column($matchedProdis, 'id'),
            ];
        }

        return view('upps.index', compact('uppsList', 'allProdis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_upps' => ['required', 'string', 'max:255'],
            'pimpinan_upps' => ['nullable', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:100'],
            'prodi_ids' => ['nullable', 'array'],
            'prodi_id' => ['nullable'],
            'id_pt' => ['nullable'],
            'kode_upps' => ['nullable', 'string', 'max:50'],
        ], [
            'nama_upps.required' => 'Nama UPPS wajib diisi.',
            'jenis.required' => 'Jenis UPPS wajib dipilih.',
        ]);

        $kodeUpps = trim($validated['kode_upps'] ?? '');
        if (empty($kodeUpps)) {
            $words = preg_split('/\s+/', trim($validated['nama_upps']));
            $acronym = '';
            foreach ($words as $w) {
                $acronym .= strtoupper(substr($w, 0, 1));
            }
            $acronym = substr(preg_replace('/[^A-Z0-9]/', '', $acronym), 0, 4);
            if (strlen($acronym) < 2) {
                $acronym = 'UPPS';
            }
            $kodeUpps = $acronym.'-'.strtoupper(Str::random(3));
        }

        $prodiIds = array_values(array_map('intval', (array) ($validated['prodi_ids'] ?? [])));
        if (empty($prodiIds) && ! empty($validated['prodi_id'])) {
            $prodiIds = [(int) $validated['prodi_id']];
        }

        $payload = [
            'id_pt' => (int) ($validated['id_pt'] ?? 1),
            'kode_upps' => $kodeUpps,
            'nama_upps' => trim($validated['nama_upps']),
            'pimpinan_upps' => trim($validated['pimpinan_upps'] ?? ''),
            'jenis' => trim($validated['jenis']),
            'prodi_id' => ! empty($prodiIds) ? $prodiIds[0] : 1,
            'prodi_ids' => $prodiIds,
        ];

        $response = $this->akademikService->createUpps($payload);

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal menambahkan data UPPS ke server.');

            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menambahkan',
                'message' => $message,
            ]);
        }

        $newId = $response->json('data.id') ?? time();
        $customUpps = session('custom_upps_list', []);
        $customUpps[] = array_merge($payload, ['id' => $newId]);
        session(['custom_upps_list' => $customUpps]);

        // Perbarui asosiasi pada session custom_prodi_list
        $this->syncProdisToUpps($newId, $prodiIds, trim($validated['nama_upps']));

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Ditambahkan',
            'message' => $response->json('message') ?? 'Data UPPS berhasil ditambahkan.',
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_upps' => ['required', 'string', 'max:255'],
            'pimpinan_upps' => ['nullable', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:100'],
            'prodi_ids' => ['nullable', 'array'],
            'prodi_id' => ['nullable'],
            'id_pt' => ['nullable'],
            'kode_upps' => ['nullable', 'string', 'max:50'],
        ], [
            'nama_upps.required' => 'Nama UPPS wajib diisi.',
            'jenis.required' => 'Jenis UPPS wajib dipilih.',
        ]);

        $kodeUpps = trim($validated['kode_upps'] ?? '');
        if (empty($kodeUpps)) {
            $kodeUpps = 'UPPS-'.$id;
        }

        $prodiIds = array_values(array_map('intval', (array) ($validated['prodi_ids'] ?? [])));
        if (empty($prodiIds) && ! empty($validated['prodi_id'])) {
            $prodiIds = [(int) $validated['prodi_id']];
        }

        $payload = [
            'id_pt' => (int) ($validated['id_pt'] ?? 1),
            'kode_upps' => $kodeUpps,
            'nama_upps' => trim($validated['nama_upps']),
            'pimpinan_upps' => trim($validated['pimpinan_upps'] ?? ''),
            'jenis' => trim($validated['jenis']),
            'prodi_id' => ! empty($prodiIds) ? $prodiIds[0] : 1,
            'prodi_ids' => $prodiIds,
        ];

        $response = $this->akademikService->updateUpps($id, $payload);

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal memperbarui data UPPS di server.');

            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memperbarui',
                'message' => $message,
            ]);
        }

        $customUpps = session('custom_upps_list', []);
        $found = false;
        foreach ($customUpps as &$u) {
            if ((string) $u['id'] === (string) $id) {
                $u = array_merge($u, $payload);
                $found = true;
                break;
            }
        }
        if (! $found) {
            $customUpps[] = array_merge($payload, ['id' => $id]);
        }
        session(['custom_upps_list' => $customUpps]);

        // Perbarui relasi pada session prodi
        $this->syncProdisToUpps($id, $prodiIds, trim($validated['nama_upps']));

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => $response->json('message') ?? 'Data UPPS berhasil diperbarui.',
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteUpps($id);

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal menghapus data UPPS dari server.');

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menghapus',
                'message' => $message,
            ]);
        }

        $customUpps = session('custom_upps_list', []);
        $customUpps = array_values(array_filter($customUpps, fn ($u) => (string) $u['id'] !== (string) $id));
        session(['custom_upps_list' => $customUpps]);

        $deletedIds = session('deleted_upps_ids', []);
        $deletedIds[] = $id;
        session(['deleted_upps_ids' => $deletedIds]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => $response->json('message') ?? 'Data UPPS berhasil dihapus.',
        ]);
    }

    /**
     * Tampilan Anggota UPPS — Mencerminkan Hirarki Jabatan Akademik UPPS.
     */
    public function anggota(int|string $id): View
    {
        $responseUpps = $this->akademikService->getUppsById($id);
        $upps = $responseUpps->successful() ? ($responseUpps->json('data') ?? []) : [];

        // Fallback jika detail UPPS diambil dari session atau daftar standar
        if (empty($upps)) {
            $customUpps = session('custom_upps_list', []);
            foreach ($customUpps as $cu) {
                if ((string) ($cu['id'] ?? '') === (string) $id) {
                    $upps = $cu;
                    break;
                }
            }
        }
        if (empty($upps)) {
            $upps = [
                'id' => $id,
                'kode_upps' => 'UPPS-'.$id,
                'nama' => 'Jurusan Teknik Elektro',
                'nama_upps' => 'Jurusan Teknik Elektro',
                'jenis' => 'Jurusan',
                'pimpinan_upps' => 'Dr. Garup Lambang Goro, S.T., M.T.',
            ];
        }

        // Ambil data anggota dari API
        $responseAnggota = $this->akademikService->getAnggotaUppsList(['id_upps' => $id]);
        $rawAnggota = $responseAnggota->successful() ? ($responseAnggota->json('data') ?? []) : ($upps['anggota'] ?? []);

        // Sinkronisasi dengan session
        $sessionKey = 'custom_anggota_upps_'.$id;
        $deletedKey = 'deleted_anggota_upps_'.$id;
        $deletedIds = session($deletedKey, []);
        $anggotaList = array_values(array_filter($rawAnggota, fn ($a) => ! in_array($a['id'] ?? null, $deletedIds)));

        $customAnggota = session($sessionKey, []);
        if (is_array($customAnggota)) {
            foreach ($customAnggota as $ca) {
                $foundIdx = null;
                foreach ($anggotaList as $idx => $ea) {
                    if ((string) ($ea['id'] ?? '') === (string) ($ca['id'] ?? '')) {
                        $foundIdx = $idx;
                        break;
                    }
                }
                if ($foundIdx !== null) {
                    $anggotaList[$foundIdx] = array_merge($anggotaList[$foundIdx], $ca);
                } else {
                    $anggotaList[] = $ca;
                }
            }
        }

        // Fallback data anggota terstruktur jika kosong — menampilkan struktur hirarki jabatan riil
        if (empty($anggotaList) && ! session()->has($sessionKey)) {
            $anggotaList = [
                [
                    'id' => 1,
                    'nama' => $upps['pimpinan_upps'] ?? 'Dr. Garup Lambang Goro, S.T., M.T.',
                    'jabatan' => 'Ketua Jurusan',
                    'level_jabatan' => 1,
                ],
                [
                    'id' => 2,
                    'nama' => 'Ir. Budi Santoso, M.T.',
                    'jabatan' => 'Sekretaris Jurusan',
                    'level_jabatan' => 2,
                ],
                [
                    'id' => 3,
                    'nama' => 'Dr. Amin Suharjono, S.T., M.T.',
                    'jabatan' => 'Koordinator Program Studi D3 Teknik Listrik',
                    'level_jabatan' => 3,
                ],
                [
                    'id' => 4,
                    'nama' => 'Slamet Riyadi, S.T., M.Eng.',
                    'jabatan' => 'Kepala Laboratorium Sistem Tenaga Listrik',
                    'level_jabatan' => 4,
                ],
                [
                    'id' => 5,
                    'nama' => 'Siti Rahmawati, S.Kom.',
                    'jabatan' => 'Staf Administrasi Akademik UPPS',
                    'level_jabatan' => 5,
                ],
            ];
        }

        // Hitung level hirarki dan label badge untuk setiap anggota
        foreach ($anggotaList as &$item) {
            $info = self::getHierarchyInfo($item['jabatan'] ?? '', isset($item['level_jabatan']) ? (int) $item['level_jabatan'] : null);
            $item['level_jabatan'] = $info['level'];
            $item['level_label'] = $info['label'];
            $item['badge_class'] = $info['badge_class'];
        }
        unset($item);

        // Urutkan anggota berdasarkan Hirarki Jabatan (Level 1 teratas, kemudian 2, 3, 4, 5)
        usort($anggotaList, function ($a, $b) {
            $lvlA = $a['level_jabatan'] ?? 5;
            $lvlB = $b['level_jabatan'] ?? 5;
            if ($lvlA === $lvlB) {
                return strcmp($a['nama'] ?? '', $b['nama'] ?? '');
            }

            return $lvlA <=> $lvlB;
        });

        return view('upps.anggota', compact('upps', 'anggotaList', 'id'));
    }

    public function storeAnggota(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'level_jabatan' => ['nullable', 'integer', 'between:1,5'],
        ], [
            'nama.required' => 'Nama Anggota wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
        ]);

        $level = isset($validated['level_jabatan']) && (int) $validated['level_jabatan'] > 0
            ? (int) $validated['level_jabatan']
            : self::getHierarchyInfo($validated['jabatan'])['level'];

        $payload = [
            'id_upps' => (int) $id,
            'nama' => trim($validated['nama']),
            'jabatan' => trim($validated['jabatan']),
            'level_jabatan' => $level,
        ];

        $response = $this->akademikService->createAnggotaUpps($payload);

        $newId = $response->json('data.id') ?? time();
        $sessionKey = 'custom_anggota_upps_'.$id;
        $customAnggota = session($sessionKey, []);
        $customAnggota[] = array_merge($payload, ['id' => $newId]);
        session([$sessionKey => $customAnggota]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Anggota UPPS berhasil ditambahkan.',
        ]);
    }

    public function updateAnggota(Request $request, int|string $id, int|string $anggotaId): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'level_jabatan' => ['nullable', 'integer', 'between:1,5'],
        ], [
            'nama.required' => 'Nama Anggota wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
        ]);

        $level = isset($validated['level_jabatan']) && (int) $validated['level_jabatan'] > 0
            ? (int) $validated['level_jabatan']
            : self::getHierarchyInfo($validated['jabatan'])['level'];

        $payload = [
            'id_upps' => (int) $id,
            'nama' => trim($validated['nama']),
            'jabatan' => trim($validated['jabatan']),
            'level_jabatan' => $level,
        ];

        $response = $this->akademikService->updateAnggotaUpps($anggotaId, $payload);

        $sessionKey = 'custom_anggota_upps_'.$id;
        $customAnggota = session($sessionKey, []);
        $found = false;
        foreach ($customAnggota as &$a) {
            if ((string) ($a['id'] ?? '') === (string) $anggotaId) {
                $a = array_merge($a, $payload);
                $found = true;
                break;
            }
        }
        if (! $found) {
            $customAnggota[] = array_merge($payload, ['id' => $anggotaId]);
        }
        session([$sessionKey => $customAnggota]);

        return redirect()->route('upps.anggota', $id)->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => 'Data anggota UPPS berhasil diperbarui.',
        ]);
    }

    public function destroyAnggota(int|string $id, int|string $anggotaId): RedirectResponse
    {
        $response = $this->akademikService->deleteAnggotaUpps($anggotaId);

        $sessionKey = 'custom_anggota_upps_'.$id;
        $customAnggota = session($sessionKey, []);
        $customAnggota = array_values(array_filter($customAnggota, fn ($a) => (string) ($a['id'] ?? '') !== (string) $anggotaId));
        session([$sessionKey => $customAnggota]);

        $deletedKey = 'deleted_anggota_upps_'.$id;
        $deletedIds = session($deletedKey, []);
        $deletedIds[] = $anggotaId;
        session([$deletedKey => $deletedIds]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Anggota berhasil dihapus.',
        ]);
    }

    /**
     * Helper untuk menentukan Level Hirarki Jabatan dan Badge Styling.
     */
    public static function getHierarchyInfo(string $jabatan, ?int $level = null): array
    {
        if ($level !== null && $level >= 1 && $level <= 5) {
            $lvl = $level;
        } else {
            $j = strtolower($jabatan);
            if (str_contains($j, 'ketua') || str_contains($j, 'dekan') || str_contains($j, 'direktur') || str_contains($j, 'pimpinan') || str_contains($j, 'kajur')) {
                $lvl = 1;
            } elseif (str_contains($j, 'sekretaris') || str_contains($j, 'sekjur') || str_contains($j, 'wakil')) {
                $lvl = 2;
            } elseif (str_contains($j, 'koordinator') || str_contains($j, 'kaprodi') || str_contains($j, 'kbk') || str_contains($j, 'mutu')) {
                $lvl = 3;
            } elseif (str_contains($j, 'kepala lab') || str_contains($j, 'kalab') || str_contains($j, 'bengkel') || str_contains($j, 'studio')) {
                $lvl = 4;
            } else {
                $lvl = 5;
            }
        }

        return match ($lvl) {
            1 => [
                'level' => 1,
                'label' => 'Pimpinan UPPS',
                'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],
            2 => [
                'level' => 2,
                'label' => 'Sekretaris UPPS',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            3 => [
                'level' => 3,
                'label' => 'Koordinator / Kaprodi',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            4 => [
                'level' => 4,
                'label' => 'Kepala Lab / Bengkel',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            default => [
                'level' => 5,
                'label' => 'Staf / Anggota',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],
        };
    }

    /**
     * Helper privat untuk menyinkronkan prodi yang dinaungi ke session prodi.
     */
    protected function syncProdisToUpps(int|string $uppsId, array $prodiIds, string $namaUpps): void
    {
        $customProdi = session('custom_prodi_list', []);
        foreach ($customProdi as &$p) {
            if (in_array((int) ($p['id'] ?? 0), $prodiIds, true)) {
                $p['id_upps'] = $uppsId;
                $p['nama_upps'] = $namaUpps;
            } elseif (($p['id_upps'] ?? null) == $uppsId) {
                // Lepas prodi yang tidak lagi dipilih
                $p['id_upps'] = null;
                $p['nama_upps'] = '-';
            }
        }
        session(['custom_prodi_list' => $customProdi]);
    }

    protected function extractErrorMessage($response, string $fallback): string
    {
        $errors = $response->json('errors');
        if (is_array($errors) && ! empty($errors)) {
            $firstField = reset($errors);
            if (is_array($firstField) && ! empty($firstField)) {
                return (string) $firstField[0];
            }
            if (is_string($firstField)) {
                return $firstField;
            }
        }

        return (string) ($response->json('message') ?? $fallback);
    }
}
