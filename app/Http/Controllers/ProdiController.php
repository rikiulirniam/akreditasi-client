<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProdiController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $response = $this->akademikService->getProdiList();

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $uppsResponse = $this->akademikService->getUppsList();
        $uppsData = $uppsResponse->json('data') ?? [];

        // Collect UPPS list & merge session custom UPPS
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

        // Map UPPS by ID and prodi_id (mendukung relasi 1 UPPS menaungi banyak prodi)
        $allUppsById = [];
        $uppsByProdiId = [];
        foreach ($allUpps as $u) {
            $uId = $u['id'] ?? null;
            if (! empty($uId)) {
                $allUppsById[$uId] = $u;
            }
            if (! empty($u['prodi_id'])) {
                $uppsByProdiId[$u['prodi_id']] = $u;
            }
            if (! empty($u['prodi_ids']) && is_array($u['prodi_ids'])) {
                foreach ($u['prodi_ids'] as $pid) {
                    $uppsByProdiId[$pid] = $u;
                }
            }
        }

        $apiData = $response->json('data') ?? [];
        $prodiList = [];

        if (is_array($apiData) && ! empty($apiData)) {
            foreach ($apiData as $item) {
                $pId = $item['id'] ?? null;
                $pUppsId = $item['id_upps'] ?? null;
                $matchedUpps = ($pUppsId && isset($allUppsById[$pUppsId]))
                    ? $allUppsById[$pUppsId]
                    : ($uppsByProdiId[$pId] ?? ($item['upps'][0] ?? null));

                $prodiList[] = [
                    'id' => $pId,
                    'kode_prodi' => $item['kode_prodi'] ?? '',
                    'nama_prodi' => $item['nama_prodi'] ?? '-',
                    'akreditasi' => $item['akreditasi'] ?? '-',
                    'id_upps' => $matchedUpps['id'] ?? $pUppsId,
                    'nama_upps' => $matchedUpps['nama_upps'] ?? ($matchedUpps['nama'] ?? '-'),
                    'upps' => $matchedUpps,
                ];
            }
        }

        // Apply session modifications for Prodi
        $sessionCustom = session('custom_prodi_list');
        if (is_array($sessionCustom)) {
            $deletedIds = session('deleted_prodi_ids', []);
            $prodiList = array_values(array_filter($prodiList, fn ($p) => ! in_array($p['id'], $deletedIds)));

            foreach ($sessionCustom as $customItem) {
                $existingIndex = null;
                foreach ($prodiList as $idx => $p) {
                    if ((string) $p['id'] === (string) $customItem['id']) {
                        $existingIndex = $idx;
                        break;
                    }
                }
                $pId = $customItem['id'] ?? null;
                $pUppsId = $customItem['id_upps'] ?? null;
                $matchedUpps = ($pUppsId && isset($allUppsById[$pUppsId]))
                    ? $allUppsById[$pUppsId]
                    : ($uppsByProdiId[$pId] ?? ($customItem['upps'] ?? null));

                $customItem['upps'] = $matchedUpps;
                $customItem['nama_upps'] = $matchedUpps['nama_upps'] ?? ($matchedUpps['nama'] ?? ($customItem['nama_upps'] ?? '-'));
                $customItem['id_upps'] = $matchedUpps['id'] ?? $pUppsId;

                if ($existingIndex !== null) {
                    $prodiList[$existingIndex] = array_merge($prodiList[$existingIndex], $customItem);
                } else {
                    array_unshift($prodiList, $customItem);
                }
            }
        }

        // Fallback matching reference: 1 UPPS menaungi beberapa prodi
        if (empty($prodiList) && ! session()->has('custom_prodi_list')) {
            $sharedUppsTeknikElektro = [
                'id' => 1,
                'id_pt' => 1,
                'kode_upps' => 'JTE-01',
                'nama_upps' => 'Jurusan Teknik Elektro',
                'pimpinan_upps' => 'Dr. Garup Lambang Goro, S.T., M.T.',
                'jenis' => 'Jurusan',
                'prodi_ids' => [1, 2, 3, 4],
            ];

            $dummyNames = [
                1 => ['kode' => 'TL01', 'nama' => 'Teknik Listrik'],
                2 => ['kode' => 'TE02', 'nama' => 'Teknik Elektronika'],
                3 => ['kode' => 'TT03', 'nama' => 'Teknik Telekomunikasi'],
                4 => ['kode' => 'TI04', 'nama' => 'Teknik Informatika'],
                5 => ['kode' => 'TM05', 'nama' => 'Teknik Mesin'],
                6 => ['kode' => 'TS06', 'nama' => 'Teknik Sipil'],
                7 => ['kode' => 'AK07', 'nama' => 'Akuntansi'],
                8 => ['kode' => 'AB08', 'nama' => 'Administrasi Bisnis'],
            ];

            for ($i = 1; $i <= 8; $i++) {
                $hasUpps = ($i <= 4); // 4 Prodi pertama dinaungi oleh Jurusan Teknik Elektro (1 UPPS menaungi banyak prodi)
                $uppsObj = $hasUpps ? $sharedUppsTeknikElektro : null;

                $prodiList[] = [
                    'id' => $i,
                    'kode_prodi' => $dummyNames[$i]['kode'] ?? ('TL0'.$i),
                    'nama_prodi' => $dummyNames[$i]['nama'] ?? 'Teknik Listrik',
                    'akreditasi' => 'Unggul',
                    'id_upps' => $uppsObj ? $uppsObj['id'] : null,
                    'nama_upps' => $uppsObj ? $uppsObj['nama_upps'] : '-',
                    'upps' => $uppsObj,
                ];
            }
        }

        // Make sure all UPPS relations are updated from $allUppsById or $uppsByProdiId
        foreach ($prodiList as &$p) {
            $pUppsId = $p['id_upps'] ?? null;
            if ($pUppsId && isset($allUppsById[$pUppsId])) {
                $p['upps'] = $allUppsById[$pUppsId];
                $p['nama_upps'] = $allUppsById[$pUppsId]['nama_upps'] ?? ($allUppsById[$pUppsId]['nama'] ?? '-');
            } elseif (isset($uppsByProdiId[$p['id']])) {
                $p['upps'] = $uppsByProdiId[$p['id']];
                $p['nama_upps'] = $uppsByProdiId[$p['id']]['nama_upps'] ?? ($uppsByProdiId[$p['id']]['nama'] ?? '-');
            } elseif (in_array($p['upps']['id'] ?? null, $deletedUppsIds)) {
                $p['upps'] = null;
                $p['nama_upps'] = '-';
            }
        }
        unset($p);

        $allProdis = $prodiList;

        $page = (int) $request->query('page', 1);
        $perPage = 10;
        $total = count($prodiList);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($page, $lastPage));
        $offset = ($currentPage - 1) * $perPage;
        $pagedProdiList = array_slice($prodiList, $offset, $perPage);

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        return view('prodi.index', [
            'prodiList' => $pagedProdiList,
            'allProdis' => $allProdis,
            'allUpps' => $allUpps,
            'pagination' => $pagination,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_prodi' => ['required', 'string', 'max:50'],
            'nama_prodi' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'in:D1,D2,D3,D4,S1,S2,S3'],
            'sk_pendirian' => ['nullable', 'string', 'max:255'],
            'tanggal_sk_pendirian' => ['nullable', 'date'],
            'status_akreditasi' => ['required', 'in:Terakreditasi,Terakreditasi Unggul,Tidak Terakreditasi'],
            'masa_berlaku_akreditasi' => ['nullable', 'date'],
            'id_upps' => ['nullable'],
        ], [
            'nama_prodi.required' => 'Nama Program Studi wajib diisi.',
            'status_akreditasi.required' => 'Status Akreditasi wajib dipilih.',
        ]);

        $namaProdi = trim($validated['nama_prodi']);
        $kodeProdi = trim($validated['kode_prodi'] ?? '');

        if (empty($kodeProdi)) {
            $words = preg_split('/\s+/', $namaProdi);
            $acronym = '';
            foreach ($words as $w) {
                $acronym .= strtoupper(substr($w, 0, 1));
            }
            $acronym = substr(preg_replace('/[^A-Z0-9]/', '', $acronym), 0, 4);
            if (strlen($acronym) < 2) {
                $acronym = 'PR';
            }
            $kodeProdi = $acronym.'-'.strtoupper(Str::random(3));
        }

        $payload = [
            'kode_prodi' => $kodeProdi,
            'nama_prodi' => $namaProdi,
            'nama' => $namaProdi,
            'jenjang' => $validated['jenjang'],
            'sk_pendirian' => $validated['sk_pendirian'] ?? null,
            'tanggal_sk_pendirian' => $validated['tanggal_sk_pendirian'] ?? null,
            'status_akreditasi' => $validated['status_akreditasi'],
            'akreditasi' => $validated['status_akreditasi'],
            'masa_berlaku_akreditasi' => $validated['masa_berlaku_akreditasi'] ?? null,
            'id_upps' => $validated['id_upps'] ?? null,
        ];

        $response = $this->akademikService->createProdi($payload);

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal menambahkan Program Studi ke server.');

            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menambahkan',
                'message' => $message,
            ]);
        }

        $newId = $response->json('data.id') ?? time();
        $customProdi = session('custom_prodi_list', []);
        $customProdi[] = [
            'id' => $newId,
            'kode_prodi' => $kodeProdi,
            'nama_prodi' => $namaProdi,
            'jenjang' => $validated['jenjang'],
            'sk_pendirian' => $validated['sk_pendirian'] ?? null,
            'tanggal_sk_pendirian' => $validated['tanggal_sk_pendirian'] ?? null,
            'status_akreditasi' => $validated['status_akreditasi'],
            'masa_berlaku_akreditasi' => $validated['masa_berlaku_akreditasi'] ?? null,
            'id_upps' => $validated['id_upps'] ?? null,
            'nama_upps' => '-',
            'upps' => null,
        ];
        session(['custom_prodi_list' => $customProdi]);

        return redirect()->route('prodi.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Ditambahkan',
            'message' => $response->json('message') ?? 'Data Program Studi berhasil ditambahkan.',
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'kode_prodi' => ['nullable', 'string', 'max:50'],
            'nama_prodi' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'in:D1,D2,D3,D4,S1,S2,S3'],
            'sk_pendirian' => ['nullable', 'string', 'max:255'],
            'tanggal_sk_pendirian' => ['nullable', 'date'],
            'status_akreditasi' => ['required', 'in:Terakreditasi,Terakreditasi Unggul,Tidak Terakreditasi'],
            'masa_berlaku_akreditasi' => ['nullable', 'date'],
            'id_upps' => ['nullable'],
        ], [
            'nama_prodi.required' => 'Nama Program Studi wajib diisi.',
            'status_akreditasi.required' => 'Status Akreditasi wajib dipilih.',
        ]);

        $namaProdi = trim($validated['nama_prodi']);
        $kodeProdi = trim($validated['kode_prodi'] ?? '');

        if (empty($kodeProdi)) {
            $kodeProdi = 'PRD-'.$id;
        }

        $payload = [
            'kode_prodi' => $kodeProdi,
            'nama_prodi' => $namaProdi,
            'nama' => $namaProdi,
            'jenjang' => $validated['jenjang'],
            'sk_pendirian' => $validated['sk_pendirian'] ?? null,
            'tanggal_sk_pendirian' => $validated['tanggal_sk_pendirian'] ?? null,
            'status_akreditasi' => $validated['status_akreditasi'],
            'akreditasi' => $validated['status_akreditasi'],
            'masa_berlaku_akreditasi' => $validated['masa_berlaku_akreditasi'] ?? null,
            'id_upps' => $validated['id_upps'] ?? null,
        ];

        $response = $this->akademikService->updateProdi($id, $payload);

        $customProdi = session('custom_prodi_list', []);
        $found = false;
        foreach ($customProdi as &$cp) {
            if ((string) $cp['id'] === (string) $id) {
                $cp = array_merge($cp, $payload);
                $found = true;
                break;
            }
        }
        if (! $found) {
            $customProdi[] = array_merge(
                ['id' => $id, 'nama_upps' => '-', 'upps' => null],
                $payload
            );
        }
        session(['custom_prodi_list' => $customProdi]);

        return redirect()->route('prodi.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => $response->json('message') ?? 'Data Program Studi berhasil diperbarui.',
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteProdi($id);

        if ($response->status() === 400) {
            return redirect()->route('prodi.index')->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menghapus',
                'message' => $response->json('message') ?? 'Program Studi masih digunakan dan tidak dapat dihapus.',
            ]);
        }

        $customProdi = session('custom_prodi_list', []);
        $customProdi = array_values(array_filter($customProdi, fn ($p) => (string) $p['id'] !== (string) $id));
        session(['custom_prodi_list' => $customProdi]);

        $deletedIds = session('deleted_prodi_ids', []);
        $deletedIds[] = $id;
        session(['deleted_prodi_ids' => $deletedIds]);

        return redirect()->route('prodi.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => $response->json('message') ?? 'Data Program Studi berhasil dihapus.',
        ]);
    }

    /* Helper privat untuk mengekstrak pesan error dari response API service. */
    private function extractErrorMessage($response, string $defaultMessage): string
    {
        if (! $response) {
            return $defaultMessage;
        }

        $json = $response->json();

        return $json['message']
            ?? $json['error']
            ?? (is_string($json['errors'] ?? null) ? $json['errors'] : null)
            ?? $defaultMessage;
    }
}
