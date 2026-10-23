<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanMahasiswaController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $response = $this->akademikService->getKegiatanMahasiswaList();

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $apiData = $response->json('data') ?? [];
        $kegiatanList = [];

        if (is_array($apiData) && ! empty($apiData)) {
            foreach ($apiData as $item) {
                $kegiatanList[] = $this->normalizeKegiatan($item);
            }
        }

        // Apply session modifications
        $deletedIds = session('deleted_kegiatan_mahasiswa_ids', []);
        $kegiatanList = array_values(
            array_filter($kegiatanList, fn ($k) => ! in_array((string) ($k['id'] ?? ''), array_map('strval', $deletedIds)))
        );

        $customList = session('custom_kegiatan_mahasiswa_list', []);
        if (is_array($customList)) {
            foreach ($customList as $custom) {
                $found = false;
                foreach ($kegiatanList as $idx => $k) {
                    if ((string) ($k['id'] ?? '') === (string) ($custom['id'] ?? '')) {
                        $kegiatanList[$idx] = $this->normalizeKegiatan(array_merge($kegiatanList[$idx], $custom));
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    array_unshift($kegiatanList, $this->normalizeKegiatan($custom));
                }
            }
        }

        // Fetch lookup data
        $mahasiswaList = $this->fetchMahasiswaList();
        $tahunAkademikList = $this->fetchTahunAkademikList();

        // Build lookup maps
        $mahasiswaMap = [];
        foreach ($mahasiswaList as $m) {
            $mahasiswaMap[(string) ($m['id'] ?? '')] = $m;
        }
        $tahunAkademikMap = [];
        foreach ($tahunAkademikList as $t) {
            $tahunAkademikMap[(string) ($t['id'] ?? '')] = $t;
        }

        // Enrich kegiatan with lookup info
        foreach ($kegiatanList as &$k) {
            $mId = (string) ($k['mahasiswa_id'] ?? '');
            $tId = (string) ($k['tahun_akademik_id'] ?? '');
            if (isset($mahasiswaMap[$mId])) {
                $k['nama_mahasiswa'] = $mahasiswaMap[$mId]['nama'] ?? $mahasiswaMap[$mId]['nama_mahasiswa'] ?? '-';
                $k['nim'] = $mahasiswaMap[$mId]['nim'] ?? '-';
            }
            if (isset($tahunAkademikMap[$tId])) {
                $ta = $tahunAkademikMap[$tId];
                $taYear = $ta['tahun'] ?? $ta['tahun_akademik'] ?? $ta['nama'] ?? '';
                $taSem = $ta['semester'] ?? '';
                $k['nama_tahun_akademik'] = trim($taYear.($taSem ? ' ('.$taSem.')' : '')) ?: '-';
            }
        }
        unset($k);

        // Filter
        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status_validasi', ''));
        $tahunFilter = trim((string) $request->query('tahun_akademik_id', ''));

        if ($search !== '' || $statusFilter !== '' || $tahunFilter !== '') {
            $kegiatanList = array_values(array_filter($kegiatanList, function ($k) use ($search, $statusFilter, $tahunFilter) {
                $matchSearch = $search === '' ||
                    str_contains(strtolower($k['nama_kegiatan'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($k['nama_mahasiswa'] ?? ''), strtolower($search)) ||
                    str_contains(strtolower($k['nim'] ?? ''), strtolower($search));

                $matchStatus = $statusFilter === '' || strtolower($k['status_validasi'] ?? '') === strtolower($statusFilter);
                $matchTahun = $tahunFilter === '' || (string) ($k['tahun_akademik_id'] ?? '') === $tahunFilter;

                return $matchSearch && $matchStatus && $matchTahun;
            }));
        }

        // Paginate (10 per halaman)
        $page = (int) $request->query('page', 1);
        $perPage = 10;
        $total = count($kegiatanList);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($page, $lastPage));
        $offset = ($currentPage - 1) * $perPage;
        $pagedList = array_slice($kegiatanList, $offset, $perPage);

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        return view('kegiatan-mahasiswa.index', [
            'kegiatanList' => $pagedList,
            'pagination' => $pagination,
            'tahunAkademikList' => $tahunAkademikList,
            'search' => $search,
            'selectedStatus' => $statusFilter,
            'selectedTahun' => $tahunFilter,
        ]);
    }

    public function create(): View
    {
        $mahasiswaList = $this->fetchMahasiswaList();
        $tahunAkademikList = $this->fetchTahunAkademikList();

        return view('kegiatan-mahasiswa.create', [
            'mahasiswaList' => $mahasiswaList,
            'tahunAkademikList' => $tahunAkademikList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mahasiswa_id' => ['required'],
            'tahun_akademik_id' => ['required'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'jenis_kegiatan' => ['required', 'string', 'in:Organisasi,Lomba,Seminar,Magang,UKM'],
            'peran' => ['required', 'string', 'max:100'],
            'tingkat' => ['required', 'string', 'in:Jurusan,Polines,Regional,Nasional,Internasional'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'penyelenggara' => ['required', 'string', 'max:255'],
            'status_validasi' => ['required', 'string', 'in:Pending,Disetujui,Ditolak'],
        ], [
            'mahasiswa_id.required' => 'Mahasiswa wajib dipilih.',
            'tahun_akademik_id.required' => 'Tahun Akademik wajib dipilih.',
            'nama_kegiatan.required' => 'Nama Kegiatan wajib diisi.',
            'jenis_kegiatan.required' => 'Jenis Kegiatan wajib dipilih.',
            'peran.required' => 'Peran wajib diisi.',
            'tingkat.required' => 'Tingkat wajib dipilih.',
            'tanggal_mulai.required' => 'Tanggal Mulai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal Selesai harus setelah atau sama dengan Tanggal Mulai.',
            'penyelenggara.required' => 'Penyelenggara wajib diisi.',
            'status_validasi.required' => 'Status Validasi wajib dipilih.',
        ]);

        $payload = [
            'mahasiswa_id' => (int) $validated['mahasiswa_id'],
            'tahun_akademik_id' => (int) $validated['tahun_akademik_id'],
            'nama_kegiatan' => trim($validated['nama_kegiatan']),
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'peran' => trim($validated['peran']),
            'tingkat' => $validated['tingkat'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'penyelenggara' => trim($validated['penyelenggara']),
            'status_validasi' => $validated['status_validasi'],
        ];

        $response = $this->akademikService->createKegiatanMahasiswa($payload);

        if (! $response->successful()) {
            $newId = 'temp_'.time();
            $customList = session('custom_kegiatan_mahasiswa_list', []);
            $customList[] = array_merge($payload, ['id' => $newId]);
            session(['custom_kegiatan_mahasiswa_list' => $customList]);

            return redirect()->route('kegiatan-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => 'Data Kegiatan Mahasiswa berhasil disimpan secara lokal.',
            ]);
        }

        $newId = $response->json('data.id') ?? time();
        $customList = session('custom_kegiatan_mahasiswa_list', []);
        $customList[] = array_merge($payload, ['id' => $newId]);
        session(['custom_kegiatan_mahasiswa_list' => $customList]);

        return redirect()->route('kegiatan-mahasiswa.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Ditambahkan',
            'message' => $response->json('message') ?? 'Data Kegiatan Mahasiswa berhasil ditambahkan.',
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $response = $this->akademikService->getKegiatanMahasiswaById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $kegiatan = $response->json('data');

        if (! $kegiatan) {
            $customList = session('custom_kegiatan_mahasiswa_list', []);
            foreach ($customList as $k) {
                if ((string) ($k['id'] ?? '') === (string) $id) {
                    $kegiatan = $k;
                    break;
                }
            }
        }

        if (! $kegiatan) {
            return redirect()->route('kegiatan-mahasiswa.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data kegiatan mahasiswa tidak ditemukan.',
            ]);
        }

        $kegiatan = $this->normalizeKegiatan($kegiatan);

        // Lookup mahasiswa
        $mahasiswaResponse = $this->akademikService->getMahasiswaById($kegiatan['mahasiswa_id'] ?? 0);
        $mahasiswaDetail = $mahasiswaResponse->successful() ? ($mahasiswaResponse->json('data') ?? null) : null;
        if (! $mahasiswaDetail && ! empty($kegiatan['mahasiswa_id'])) {
            $mhsList = $this->fetchMahasiswaList();
            foreach ($mhsList as $m) {
                if ((string) ($m['id'] ?? '') === (string) $kegiatan['mahasiswa_id']) {
                    $mahasiswaDetail = $m;
                    break;
                }
            }
        }

        // Lookup tahun akademik
        $tahunResponse = $this->akademikService->getTahunAkademikById($kegiatan['tahun_akademik_id'] ?? 0);
        $tahunDetail = $tahunResponse->successful() ? ($tahunResponse->json('data') ?? null) : null;
        if (! $tahunDetail && ! empty($kegiatan['tahun_akademik_id'])) {
            $taList = $this->fetchTahunAkademikList();
            foreach ($taList as $ta) {
                if ((string) ($ta['id'] ?? '') === (string) $kegiatan['tahun_akademik_id']) {
                    $tahunDetail = $ta;
                    break;
                }
            }
        }

        return view('kegiatan-mahasiswa.show', [
            'kegiatan' => $kegiatan,
            'mahasiswaDetail' => $mahasiswaDetail,
            'tahunDetail' => $tahunDetail,
        ]);
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        $response = $this->akademikService->getKegiatanMahasiswaById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $kegiatan = $response->json('data');

        if (! $kegiatan) {
            $customList = session('custom_kegiatan_mahasiswa_list', []);
            foreach ($customList as $k) {
                if ((string) ($k['id'] ?? '') === (string) $id) {
                    $kegiatan = $k;
                    break;
                }
            }
        }

        if (! $kegiatan) {
            return redirect()->route('kegiatan-mahasiswa.index')->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => 'Data kegiatan mahasiswa tidak ditemukan.',
            ]);
        }

        $kegiatan = $this->normalizeKegiatan($kegiatan);
        $mahasiswaList = $this->fetchMahasiswaList();
        $tahunAkademikList = $this->fetchTahunAkademikList();

        return view('kegiatan-mahasiswa.edit', [
            'kegiatan' => $kegiatan,
            'mahasiswaList' => $mahasiswaList,
            'tahunAkademikList' => $tahunAkademikList,
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'mahasiswa_id' => ['required'],
            'tahun_akademik_id' => ['required'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'jenis_kegiatan' => ['required', 'string', 'in:Organisasi,Lomba,Seminar,Magang,UKM'],
            'peran' => ['required', 'string', 'max:100'],
            'tingkat' => ['required', 'string', 'in:Jurusan,Polines,Regional,Nasional,Internasional'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'penyelenggara' => ['required', 'string', 'max:255'],
            'status_validasi' => ['required', 'string', 'in:Pending,Disetujui,Ditolak'],
        ], [
            'mahasiswa_id.required' => 'Mahasiswa wajib dipilih.',
            'tahun_akademik_id.required' => 'Tahun Akademik wajib dipilih.',
            'nama_kegiatan.required' => 'Nama Kegiatan wajib diisi.',
            'jenis_kegiatan.required' => 'Jenis Kegiatan wajib dipilih.',
            'peran.required' => 'Peran wajib diisi.',
            'tingkat.required' => 'Tingkat wajib dipilih.',
            'tanggal_mulai.required' => 'Tanggal Mulai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal Selesai harus setelah atau sama dengan Tanggal Mulai.',
            'penyelenggara.required' => 'Penyelenggara wajib diisi.',
            'status_validasi.required' => 'Status Validasi wajib dipilih.',
        ]);

        $payload = [
            'mahasiswa_id' => (int) $validated['mahasiswa_id'],
            'tahun_akademik_id' => (int) $validated['tahun_akademik_id'],
            'nama_kegiatan' => trim($validated['nama_kegiatan']),
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'peran' => trim($validated['peran']),
            'tingkat' => $validated['tingkat'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'penyelenggara' => trim($validated['penyelenggara']),
            'status_validasi' => $validated['status_validasi'],
        ];

        $this->akademikService->updateKegiatanMahasiswa($id, $payload);

        // Optimistic session update
        $customList = session('custom_kegiatan_mahasiswa_list', []);
        $found = false;
        foreach ($customList as &$k) {
            if ((string) ($k['id'] ?? '') === (string) $id) {
                $k = array_merge($k, $payload, ['id' => $id]);
                $found = true;
                break;
            }
        }
        unset($k);
        if (! $found) {
            $customList[] = array_merge($payload, ['id' => $id]);
        }
        session(['custom_kegiatan_mahasiswa_list' => $customList]);

        return redirect()->route('kegiatan-mahasiswa.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => 'Data Kegiatan Mahasiswa berhasil diperbarui.',
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $this->akademikService->deleteKegiatanMahasiswa($id);

        $customList = session('custom_kegiatan_mahasiswa_list', []);
        $customList = array_values(
            array_filter($customList, fn ($k) => (string) ($k['id'] ?? '') !== (string) $id)
        );
        session(['custom_kegiatan_mahasiswa_list' => $customList]);

        $deletedIds = session('deleted_kegiatan_mahasiswa_ids', []);
        $deletedIds[] = (string) $id;
        session(['deleted_kegiatan_mahasiswa_ids' => $deletedIds]);

        return redirect()->route('kegiatan-mahasiswa.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => 'Data Kegiatan Mahasiswa berhasil dihapus.',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchMahasiswaList(): array
    {
        try {
            $response = $this->akademikService->getMahasiswaList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                if (is_array($raw)) {
                    return isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
                }
            }
        } catch (\Throwable) {
            // Silently fallback
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchTahunAkademikList(): array
    {
        try {
            $response = $this->akademikService->getTahunAkademikList(['per_page' => 100]);
            if ($response->successful()) {
                $raw = $response->json('data');
                $list = [];
                if (is_array($raw)) {
                    $list = isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
                }

                // Merge custom session items if any
                $customList = session('custom_tahun_akademik_list', []);
                if (is_array($customList) && ! empty($customList)) {
                    $deletedIds = session('deleted_tahun_akademik_ids', []);
                    $list = array_values(array_filter($list, fn ($item) => ! in_array($item['id'] ?? null, $deletedIds)));
                    foreach ($customList as $customItem) {
                        $foundIdx = null;
                        foreach ($list as $idx => $existingItem) {
                            if ((string) ($existingItem['id'] ?? '') === (string) ($customItem['id'] ?? '')) {
                                $foundIdx = $idx;
                                break;
                            }
                        }
                        if ($foundIdx !== null) {
                            $list[$foundIdx] = array_merge($list[$foundIdx], $customItem);
                        } else {
                            array_unshift($list, $customItem);
                        }
                    }
                }

                return $list;
            }
        } catch (\Throwable) {
            // Silently fallback
        }

        return session('custom_tahun_akademik_list', []);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function normalizeKegiatan(array $item): array
    {
        $tahunData = $item['tahun_akademik'] ?? $item['tahunAkademik'] ?? [];
        $tahunStr = '';
        if (is_array($tahunData) && ! empty($tahunData)) {
            $tahunPart = $tahunData['tahun'] ?? $tahunData['tahun_akademik'] ?? $tahunData['nama'] ?? '';
            $semPart = $tahunData['semester'] ?? '';
            $tahunStr = trim($tahunPart.($semPart ? ' ('.$semPart.')' : ''));
        }

        return [
            'id' => $item['id'] ?? null,
            'mahasiswa_id' => $item['mahasiswa_id'] ?? null,
            'tahun_akademik_id' => $item['tahun_akademik_id'] ?? null,
            'nama_kegiatan' => $item['nama_kegiatan'] ?? '-',
            'jenis_kegiatan' => $item['jenis_kegiatan'] ?? '-',
            'peran' => $item['peran'] ?? '-',
            'tingkat' => $item['tingkat'] ?? '-',
            'tanggal_mulai' => $item['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $item['tanggal_selesai'] ?? null,
            'penyelenggara' => $item['penyelenggara'] ?? '-',
            'status_validasi' => $item['status_validasi'] ?? 'Pending',
            'nama_mahasiswa' => $item['nama_mahasiswa'] ?? ($item['mahasiswa']['nama'] ?? '-'),
            'nim' => $item['nim'] ?? ($item['mahasiswa']['nim'] ?? '-'),
            'nama_tahun_akademik' => $item['nama_tahun_akademik'] ?? ($tahunStr ?: '-'),
        ];
    }
}
