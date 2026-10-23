<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));

        $response = $this->akademikService->getMahasiswaList([
            'page' => $page,
            'per_page' => $perPage,
        ]);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawData = $response->json('data') ?? [];
        $apiPagination = $response->json('pagination');

        // Extract list of mahasiswa
        $mahasiswaList = [];
        if (is_array($rawData)) {
            if (isset($rawData['data']) && is_array($rawData['data'])) {
                $mahasiswaList = $rawData['data'];
                if (! $apiPagination) {
                    $apiPagination = [
                        'total' => $rawData['total'] ?? count($mahasiswaList),
                        'per_page' => $rawData['per_page'] ?? $perPage,
                        'current_page' => $rawData['current_page'] ?? $page,
                        'last_page' => $rawData['last_page'] ?? 1,
                    ];
                }
            } else {
                $mahasiswaList = $rawData;
            }
        }

        // Merge custom session items if any (for smooth resilience)
        $customMahasiswa = session('custom_mahasiswa_list', []);
        if (is_array($customMahasiswa) && ! empty($customMahasiswa)) {
            $deletedIds = session('deleted_mahasiswa_ids', []);
            $mahasiswaList = array_values(array_filter($mahasiswaList, fn ($item) => ! in_array($item['id'] ?? null, $deletedIds)));

            foreach ($customMahasiswa as $customItem) {
                $foundIdx = null;
                foreach ($mahasiswaList as $idx => $existingItem) {
                    if ((string) ($existingItem['id'] ?? '') === (string) ($customItem['id'] ?? '')) {
                        $foundIdx = $idx;
                        break;
                    }
                }
                if ($foundIdx !== null) {
                    $mahasiswaList[$foundIdx] = array_merge($mahasiswaList[$foundIdx], $customItem);
                } else {
                    array_unshift($mahasiswaList, $customItem);
                }
            }
        }

        // Setup pagination data
        if ($apiPagination && is_array($apiPagination)) {
            $pagination = [
                'total' => (int) ($apiPagination['total'] ?? count($mahasiswaList)),
                'per_page' => (int) ($apiPagination['per_page'] ?? $perPage),
                'current_page' => (int) ($apiPagination['current_page'] ?? $page),
                'last_page' => (int) ($apiPagination['last_page'] ?? 1),
                'from' => $apiPagination['from'] ?? (($page - 1) * $perPage + 1),
                'to' => $apiPagination['to'] ?? min($page * $perPage, (int) ($apiPagination['total'] ?? count($mahasiswaList))),
            ];
        } else {
            $total = count($mahasiswaList);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = max(1, min($page, $lastPage));
            $offset = ($currentPage - 1) * $perPage;
            $pagedList = array_slice($mahasiswaList, $offset, $perPage);

            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ];
            $mahasiswaList = $pagedList;
        }

        // Fetch Angkatan list for Tahun Masuk dropdown options
        $tahunOptions = [];
        try {
            $angkatanRes = $this->akademikService->getAngkatanList(['per_page' => 100]);
            if ($angkatanRes->successful()) {
                $angkatanData = $angkatanRes->json('data') ?? [];
                if (isset($angkatanData['data']) && is_array($angkatanData['data'])) {
                    $angkatanData = $angkatanData['data'];
                }
                if (is_array($angkatanData)) {
                    foreach ($angkatanData as $akt) {
                        $th = $akt['angkatan'] ?? $akt['tahun'] ?? null;
                        if ($th) {
                            $tahunOptions[] = (int) $th;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fall through to default years
        }

        // Ensure default years if empty
        if (empty($tahunOptions)) {
            $currentYear = (int) date('Y');
            for ($y = $currentYear + 1; $y >= $currentYear - 6; $y--) {
                $tahunOptions[] = $y;
            }
        } else {
            rsort($tahunOptions);
            $tahunOptions = array_values(array_unique($tahunOptions));
        }

        $jenisPendaftaranOptions = [
            'SNBP',
            'SNBT',
            'Mandiri',
            'Reguler',
            'Transfer',
            'Kerjasama',
        ];

        return view('mahasiswa.index', [
            'mahasiswaList' => $mahasiswaList,
            'pagination' => $pagination,
            'tahunOptions' => $tahunOptions,
            'jenisPendaftaranOptions' => $jenisPendaftaranOptions,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('mahasiswa.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_mahasiswa' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50'],
            'tahun_masuk' => ['required', 'integer'],
            'jenis_pendaftaran' => ['required', 'string', 'max:100'],
            'tanggal_kelulusan' => ['nullable', 'date'],
            'masa_studi_bulan' => ['nullable', 'integer', 'min:1', 'max:120'],
        ], [
            'nama_mahasiswa.required' => 'Nama mahasiswa wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'tahun_masuk.required' => 'Tahun masuk wajib dipilih.',
            'jenis_pendaftaran.required' => 'Jenis pendaftaran wajib dipilih.',
            'tanggal_kelulusan.date' => 'Format tanggal kelulusan tidak valid.',
        ]);

        $rawNim = (string) $validated['nim'];
        $numericNim = (int) preg_replace('/\D/', '', $rawNim);
        if ($numericNim <= 0) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Input Tidak Valid',
                'message' => 'NIM harus memuat angka yang valid.',
            ]);
        }

        $payload = [
            'nama_mahasiswa' => trim($validated['nama_mahasiswa']),
            'nim' => $numericNim,
            'tahun_masuk' => (int) $validated['tahun_masuk'],
            'jenis_pendaftaran' => trim($validated['jenis_pendaftaran']),
            'tanggal_kelulusan' => ! empty($validated['tanggal_kelulusan']) ? $validated['tanggal_kelulusan'] : null,
            'masa_studi_bulan' => ! empty($validated['masa_studi_bulan']) ? (int) $validated['masa_studi_bulan'] : 48,
        ];

        $response = $this->akademikService->createMahasiswa($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $createdId = $body['data']['id'] ?? time();
            $custom = session('custom_mahasiswa_list', []);
            $custom[] = array_merge($payload, [
                'id' => $createdId,
                'nim_display' => $rawNim,
            ]);
            session(['custom_mahasiswa_list' => $custom]);

            return redirect()->route('mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => $body['message'] ?? 'Data mahasiswa berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan data mahasiswa.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $errorMessage,
        ]);
    }

    public function show(int|string $id): RedirectResponse
    {
        return redirect()->route('mahasiswa.index');
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('mahasiswa.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_mahasiswa' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50'],
            'tahun_masuk' => ['required', 'integer'],
            'jenis_pendaftaran' => ['required', 'string', 'max:100'],
            'tanggal_kelulusan' => ['nullable', 'date'],
            'masa_studi_bulan' => ['nullable', 'integer', 'min:1', 'max:120'],
        ], [
            'nama_mahasiswa.required' => 'Nama mahasiswa wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'tahun_masuk.required' => 'Tahun masuk wajib dipilih.',
            'jenis_pendaftaran.required' => 'Jenis pendaftaran wajib dipilih.',
            'tanggal_kelulusan.date' => 'Format tanggal kelulusan tidak valid.',
        ]);

        $rawNim = (string) $validated['nim'];
        $numericNim = (int) preg_replace('/\D/', '', $rawNim);
        if ($numericNim <= 0) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Input Tidak Valid',
                'message' => 'NIM harus memuat angka yang valid.',
            ]);
        }

        $payload = [
            'nama_mahasiswa' => trim($validated['nama_mahasiswa']),
            'nim' => $numericNim,
            'tahun_masuk' => (int) $validated['tahun_masuk'],
            'jenis_pendaftaran' => trim($validated['jenis_pendaftaran']),
            'tanggal_kelulusan' => ! empty($validated['tanggal_kelulusan']) ? $validated['tanggal_kelulusan'] : null,
            'masa_studi_bulan' => ! empty($validated['masa_studi_bulan']) ? (int) $validated['masa_studi_bulan'] : 48,
        ];

        $response = $this->akademikService->updateMahasiswa($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_mahasiswa_list', []);
            $found = false;
            foreach ($custom as &$c) {
                if ((string) $c['id'] === (string) $id) {
                    $c = array_merge($c, $payload, ['nim_display' => $rawNim]);
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $custom[] = array_merge($payload, [
                    'id' => $id,
                    'nim_display' => $rawNim,
                ]);
            }
            session(['custom_mahasiswa_list' => $custom]);

            return redirect()->route('mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Data mahasiswa berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui data mahasiswa.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteMahasiswa($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_mahasiswa_list', []);
            $custom = array_values(array_filter($custom, fn ($item) => (string) ($item['id'] ?? '') !== (string) $id));
            session(['custom_mahasiswa_list' => $custom]);

            $deleted = session('deleted_mahasiswa_ids', []);
            $deleted[] = $id;
            session(['deleted_mahasiswa_ids' => $deleted]);

            return redirect()->route('mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Data mahasiswa berhasil dihapus.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus data mahasiswa.');

        return redirect()->route('mahasiswa.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $errorMessage,
        ]);
    }

    protected function extractErrorMessage($response, string $fallback): string
    {
        $body = $response->json();
        if (is_array($body) && ! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                return (string) $firstError;
            }
        }

        return (string) ($body['message'] ?? $fallback);
    }

    public function jenisPrestasi()
    {
        $response = $this->akademikService->getJenisPrestasiList();
        $jenisPrestasiList = $response->successful() ? ($response->json('data') ?? []) : [];

        return view('mahasiswa.jenis-prestasi', compact('jenisPrestasiList'));
    }

    public function storeJenisPrestasi(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:100'],
        ]);

        $response = $this->akademikService->createJenisPrestasi($validated);

        if (!$response->successful()) {
            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => $response->json('message') ?? 'Gagal menyimpan jenis prestasi.'
            ]);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Jenis prestasi berhasil ditambahkan.'
        ]);
    }

    public function updateJenisPrestasi(Request $request, int|string $id)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:100'],
        ]);

        $response = $this->akademikService->updateJenisPrestasi($id, $validated);

        if (!$response->successful()) {
            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => $response->json('message') ?? 'Gagal memperbarui jenis prestasi.'
            ]);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Jenis prestasi berhasil diperbarui.'
        ]);
    }

    public function destroyJenisPrestasi(int|string $id)
    {
        $response = $this->akademikService->deleteJenisPrestasi($id);

        if (!$response->successful()) {
            return redirect()->back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => $response->json('message') ?? 'Gagal menghapus jenis prestasi.'
            ]);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Jenis prestasi berhasil dihapus.'
        ]);
    }
}
