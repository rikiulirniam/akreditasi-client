<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DosenController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = (int) $request->query('page', 1);
        $search = $request->query('search');

        // API hanya mengenal DTPS/DTT/NONAKTIF, jadi label UI dipetakan ke kode tersebut.
        $statusLabels = [
            'DTPS' => 'DTPS',
            'DTT' => 'Tidak tetap',
            'TIDAK TETAP' => 'Tidak tetap',
        ];
        $status = strtoupper(trim((string) $request->query('status')));
        $status = array_key_exists($status, $statusLabels) ? $status : '';
        if ($status === 'TIDAK TETAP') {
            $status = 'DTT';
        }

        $params = [
            'page' => $page,
            'per_page' => 10,
        ];

        if (! empty($search)) {
            $params['search'] = trim($search);
        }

        if (! empty($status)) {
            $params['status'] = $status;
        }

        $response = $this->tridharmaService->getDosenList($params);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawResponse = $response->json();
        $responseData = $rawResponse['data'] ?? [];

        $perPage = 10;
        // Support nested paginator (data.data), API with pagination key, and flat array (data)
        if (isset($responseData['data']) && is_array($responseData['data'])) {
            $dosenList = $responseData['data'];
            $total = (int) ($responseData['total'] ?? count($dosenList));
            $perPage = (int) ($responseData['per_page'] ?? $perPage);
            $lastPage = (int) ($responseData['last_page'] ?? max(1, (int) ceil($total / $perPage)));
            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => (int) ($responseData['current_page'] ?? $page),
                'last_page' => $lastPage,
            ];
        } elseif (is_array($responseData)) {
            $rawPagination = $rawResponse['pagination'] ?? [];
            if (! empty($rawPagination) && is_array($rawPagination)) {
                $dosenList = $responseData;
                $total = (int) ($rawPagination['total'] ?? count($dosenList));
                $perPage = (int) ($rawPagination['per_page'] ?? $perPage);
                $lastPage = (int) ($rawPagination['last_page'] ?? max(1, (int) ceil($total / $perPage)));
                $pagination = [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => (int) ($rawPagination['current_page'] ?? $page),
                    'last_page' => $lastPage,
                ];
            } else {
                $total = count($responseData);
                $lastPage = max(1, (int) ceil($total / $perPage));
                $currentPage = max(1, min($page, $lastPage));
                $offset = ($currentPage - 1) * $perPage;
                $dosenList = array_slice($responseData, $offset, $perPage);
                $pagination = [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                ];
            }
        } else {
            $dosenList = [];
            $pagination = [
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => 1,
                'last_page' => 1,
            ];
        }

        // Fetch master data for dropdowns/selects (extract .data if paginated)
        $jabatanResponse = $this->tridharmaService->getJabatanAkademikList(['per_page' => 100]);
        $rawJabatan = $jabatanResponse->json('data') ?? [];
        $jabatanList = isset($rawJabatan['data']) && is_array($rawJabatan['data']) ? $rawJabatan['data'] : (is_array($rawJabatan) ? $rawJabatan : []);

        $pendidikanResponse = $this->tridharmaService->getPendidikanList(['per_page' => 100]);
        $rawPendidikan = $pendidikanResponse->json('data') ?? [];
        $pendidikanList = isset($rawPendidikan['data']) && is_array($rawPendidikan['data']) ? $rawPendidikan['data'] : (is_array($rawPendidikan) ? $rawPendidikan : []);

        $bidangKeahlianResponse = $this->tridharmaService->getBidangKeahlianList(['per_page' => 100]);
        $rawBidang = $bidangKeahlianResponse->json('data') ?? [];
        $bidangKeahlianList = isset($rawBidang['data']) && is_array($rawBidang['data']) ? $rawBidang['data'] : (is_array($rawBidang) ? $rawBidang : []);

        return view('dosen.index', [
            'dosenList' => $dosenList,
            'pagination' => $pagination,
            'jabatanList' => $jabatanList,
            'pendidikanList' => $pendidikanList,
            'bidangKeahlianList' => $bidangKeahlianList,
            'search' => $search ?? '',
            'selectedStatus' => $status,
            'selectedStatusLabel' => $statusLabels[$status] ?? 'Status',
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('dosen.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_dosen' => ['required', 'string', 'max:255'],
            'nidn' => ['required', 'string', 'max:50'],
            'status_dosen' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'sertifikasi' => ['nullable', 'string', 'max:500'],
            'id_jabatan' => ['nullable', 'integer'],
            'id_pendidikan' => ['nullable', 'integer'],
        ], [
            'nama_dosen.required' => 'Nama dosen wajib diisi.',
            'nidn.required' => 'NIDN wajib diisi.',
            'status_dosen.required' => 'Status dosen wajib dipilih.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $payload = [
            'nama_dosen' => trim($validated['nama_dosen']),
            'nidn' => trim($validated['nidn']),
            'status_dosen' => trim($validated['status_dosen']),
            'email' => strtolower(trim($validated['email'])),
            'sertifikasi' => ! empty($validated['sertifikasi']) ? trim($validated['sertifikasi']) : null,
            'id_jabatan' => isset($validated['id_jabatan']) && $validated['id_jabatan'] !== null ? (int) $validated['id_jabatan'] : null,
            'id_pendidikan' => isset($validated['id_pendidikan']) && $validated['id_pendidikan'] !== null ? (int) $validated['id_pendidikan'] : null,
        ];

        $response = $this->tridharmaService->createDosen($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('dosen.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Data dosen berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $body['message'] ?? 'Gagal menambahkan data dosen.';
        if (! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                $errorMessage = $firstError;
            }
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $response = $this->tridharmaService->getDosenById($id);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawResponse = $response->json();
        $dosenData = $rawResponse['data'] ?? [];

        // Normalize lecturer profile
        $rawJabatan = $dosenData['jabatan_akademik'] ?? ($dosenData['jabatan'] ?? ($dosenData['nama_jabatan'] ?? null));
        $rawJurusan = $dosenData['jurusan'] ?? ($dosenData['bidang_keahlian'] ?? ($dosenData['bidang'] ?? null));
        $rawProdi = $dosenData['prodi'] ?? ($dosenData['program_studi'] ?? ($dosenData['nama_prodi'] ?? null));
        $rawSertifikasi = $dosenData['sertifikasi'] ?? null;
        $rawNama = $dosenData['nama_dosen'] ?? ($dosenData['nama'] ?? ($dosenData['name'] ?? null));

        $dosen = [
            'id' => is_scalar($dosenData['id_dosen'] ?? ($dosenData['id'] ?? null)) ? ($dosenData['id_dosen'] ?? $dosenData['id']) : $id,
            'nama_dosen' => $this->extractString($rawNama, '-'),
            'nidn' => $this->extractString($dosenData['nidn'] ?? null, '-'),
            'jabatan' => $this->extractString($rawJabatan, '-'),
            'jurusan' => ! empty($rawJurusan) ? $this->extractString($rawJurusan, '-') : '-',
            'prodi' => $this->extractString($rawProdi, '-'),
            'email' => $this->extractString($dosenData['email'] ?? null, '-'),
            'status_dosen' => $this->extractString($dosenData['status_dosen'] ?? ($dosenData['status'] ?? null), '-'),
            'sertifikasi' => is_string($rawSertifikasi) && trim($rawSertifikasi) !== '' ? trim($rawSertifikasi) : (is_array($rawSertifikasi) ? $this->extractString($rawSertifikasi, '') : null),
        ];

        // Penelitian list — API key: penelitian
        $rawPenelitian = isset($dosenData['penelitian']) && is_array($dosenData['penelitian'])
            ? $dosenData['penelitian']
            : [];

        $penelitianList = array_map(function ($item) {
            if (! is_array($item)) {
                return ['judul' => (string) $item, 'tahun_usulan' => '-', 'tahun_kegiatan' => '-', 'tahun_pelaksanaan' => '-', 'bidang' => '-', 'instansi' => '-'];
            }

            return [
                'judul' => $this->extractString($item['judul_kegiatan'] ?? ($item['judul'] ?? ($item['title'] ?? ($item['nama'] ?? null))), '-'),
                'tahun_usulan' => $this->extractString($item['tahun_usulan'] ?? ($item['usulan'] ?? null), '-'),
                'tahun_kegiatan' => $this->extractString($item['tahun_kegiatan'] ?? ($item['kegiatan'] ?? null), '-'),
                'tahun_pelaksanaan' => $this->extractString($item['tahun_pelaksanaan'] ?? ($item['pelaksanaan'] ?? null), '-'),
                'bidang' => $this->extractString($item['bidang'] ?? ($item['jurusan'] ?? null), '-'),
                'instansi' => $this->extractString($item['instansi'] ?? null, '-'),
            ];
        }, $rawPenelitian);

        // Pengabdian list — API key: pkm
        $rawPkm = isset($dosenData['pkm']) && is_array($dosenData['pkm'])
            ? $dosenData['pkm']
            : [];

        $pengabdianMasyarakat = array_map(function ($item) {
            if (! is_array($item)) {
                return ['judul' => (string) $item, 'tahun' => '-', 'lokasi' => '-'];
            }

            return [
                'judul' => $this->extractString($item['judul_kegiatan'] ?? ($item['judul'] ?? ($item['title'] ?? null)), '-'),
                'tahun' => $this->extractString($item['tahun_pelaksanaan'] ?? ($item['tahun_kegiatan'] ?? ($item['tahun'] ?? null)), '-'),
                'lokasi' => $this->extractString($item['lokasi_kegiatan'] ?? ($item['lokasi'] ?? ($item['tempat'] ?? null)), '-'),
            ];
        }, $rawPkm);

        // Riwayat Pendidikan — API returns a single object under key: pendidikan
        $rawPendidikan = $dosenData['pendidikan'] ?? null;
        $pendidikanItems = [];
        if (is_array($rawPendidikan) && ! empty($rawPendidikan)) {
            $pendidikanItems = array_is_list($rawPendidikan) ? $rawPendidikan : [$rawPendidikan];
        }

        $riwayatPendidikan = array_map(function ($item) {
            if (! is_array($item)) {
                return ['gelar' => '-', 'jenjang' => '-', 'instansi' => (string) $item, 'jurusan' => '-', 'tahun_lulus' => '-'];
            }

            return [
                'gelar' => $this->extractString($item['gelar'] ?? null, '-'),
                'jenjang' => $this->extractString($item['jenjang'] ?? ($item['tingkat'] ?? null), '-'),
                'instansi' => $this->extractString($item['instansi'] ?? ($item['universitas'] ?? ($item['kampus'] ?? null)), '-'),
                'jurusan' => $this->extractString($item['jurusan'] ?? ($item['program_studi'] ?? ($item['prodi'] ?? null)), '-'),
                'tahun_lulus' => $this->extractString($item['tahun_lulus'] ?? ($item['tahun'] ?? null), '-'),
            ];
        }, $pendidikanItems);

        // Luaran — combines hki, luaran, buku from API
        $hkiItems = is_array($dosenData['hki'] ?? null) ? $dosenData['hki'] : [];
        $luaranItems = is_array($dosenData['luaran'] ?? null) ? $dosenData['luaran'] : [];
        $bukuItems = is_array($dosenData['buku'] ?? null) ? $dosenData['buku'] : [];

        $hasLuaranKeys = array_key_exists('hki', $dosenData) || array_key_exists('luaran', $dosenData) || array_key_exists('buku', $dosenData);

        $combinedLuaran = array_merge(
            array_map(function ($item) {
                $rawJenis = is_array($item) ? $this->extractString($item['jenis_luaran']['nama_jenis_luaran'] ?? ($item['jenis'] ?? null), 'HKI') : 'HKI';
                $jenis = (str_contains(strtolower($rawJenis), 'hak cipta') || str_contains(strtolower($rawJenis), 'hki')) ? 'HKI' : $rawJenis;

                return [
                    'judul' => is_array($item) ? $this->extractString($item['judul_hak_cipta'] ?? ($item['judul'] ?? ($item['title'] ?? null)), '-') : (string) $item,
                    'nomor' => is_array($item) ? $this->extractString($item['nomor_sertifikat_hki'] ?? ($item['nomor_pendaftaran_hki'] ?? ($item['nomor'] ?? ($item['no'] ?? null))), '-') : '-',
                    'tahun' => is_array($item) ? $this->extractString($item['tahun'] ?? (! empty($item['created_at']) ? substr((string) $item['created_at'], 0, 4) : null), '-') : '-',
                    'jenis' => $jenis,
                    'status' => is_array($item) ? $this->extractString($item['status_hki'] ?? ($item['status'] ?? null), '-') : '-',
                ];
            }, $hkiItems),
            array_map(function ($item) {
                $rawJenis = is_array($item) ? $this->extractString($item['jenis_luaran']['nama_jenis_luaran'] ?? ($item['jenis'] ?? null), 'Luaran') : 'Luaran';
                $jenis = (str_contains(strtolower($rawJenis), 'hak cipta') || str_contains(strtolower($rawJenis), 'hki')) ? 'HKI' : $rawJenis;

                return [
                    'judul' => is_array($item) ? $this->extractString($item['judul_hak_cipta'] ?? ($item['judul_buku'] ?? ($item['nama_jurnal'] ?? ($item['nama_prosiding'] ?? ($item['nama_produk'] ?? ($item['judul'] ?? ($item['title'] ?? null)))))), '-') : (string) $item,
                    'nomor' => is_array($item) ? $this->extractString($item['nomor_sertifikat_hki'] ?? ($item['isbn'] ?? ($item['nomor_pendaftaran_hki'] ?? ($item['issn'] ?? ($item['isbn_prosiding'] ?? ($item['nomor'] ?? null))))), '-') : '-',
                    'tahun' => is_array($item) ? $this->extractString($item['tahun_terbit'] ?? ($item['tahun'] ?? (! empty($item['created_at']) ? substr((string) $item['created_at'], 0, 4) : null)), '-') : '-',
                    'jenis' => $jenis,
                    'status' => is_array($item) ? $this->extractString($item['status_hki'] ?? ($item['status_pengembangan'] ?? ($item['status'] ?? null)), '-') : '-',
                ];
            }, $luaranItems),
            array_map(function ($item) {
                $rawJenis = is_array($item) ? $this->extractString($item['jenis_luaran']['nama_jenis_luaran'] ?? ($item['jenis'] ?? null), 'Buku') : 'Buku';

                return [
                    'judul' => is_array($item) ? $this->extractString($item['judul_buku'] ?? ($item['judul'] ?? ($item['title'] ?? ($item['nama'] ?? null))), '-') : (string) $item,
                    'nomor' => is_array($item) ? $this->extractString($item['isbn'] ?? ($item['nomor'] ?? null), '-') : '-',
                    'tahun' => is_array($item) ? $this->extractString($item['tahun_terbit'] ?? ($item['tahun'] ?? null), '-') : '-',
                    'jenis' => $rawJenis,
                    'status' => '-',
                ];
            }, $bukuItems),
        );

        $luaranList = $combinedLuaran;

        return view('dosen.show', [
            'dosen' => $dosen,
            'penelitianList' => $penelitianList,
            'pengabdianMasyarakat' => $pengabdianMasyarakat,
            'riwayatPendidikan' => $riwayatPendidikan,
            'luaranList' => $luaranList,
        ]);
    }

    protected function extractString(mixed $value, string $default = '-'): string
    {
        if (is_null($value)) {
            return $default;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : $default;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $keys = [
                'nama_jabatan', 'nama_bidang', 'nama_jurusan', 'nama_prodi', 'program_studi',
                'nama', 'name', 'label', 'title', 'judul', 'status', 'email', 'link', 'url',
            ];
            foreach ($keys as $key) {
                if (! empty($value[$key]) && is_string($value[$key])) {
                    return trim($value[$key]);
                }
            }

            $first = reset($value);
            if (is_string($first)) {
                return trim($first);
            }
            if (is_array($first)) {
                foreach ($keys as $key) {
                    if (! empty($first[$key]) && is_string($first[$key])) {
                        return trim($first[$key]);
                    }
                }
            }
        }

        return $default;
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('dosen.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_dosen' => ['required', 'string', 'max:255'],
            'nidn' => ['required', 'string', 'max:50'],
            'status_dosen' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'sertifikasi' => ['nullable', 'string', 'max:500'],
            'id_jabatan' => ['nullable', 'integer'],
            'id_pendidikan' => ['nullable', 'integer'],
        ], [
            'nama_dosen.required' => 'Nama dosen wajib diisi.',
            'nidn.required' => 'NIDN wajib diisi.',
            'status_dosen.required' => 'Status dosen wajib dipilih.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $payload = [
            'nama_dosen' => trim($validated['nama_dosen']),
            'nidn' => trim($validated['nidn']),
            'status_dosen' => trim($validated['status_dosen']),
            'email' => strtolower(trim($validated['email'])),
            'sertifikasi' => ! empty($validated['sertifikasi']) ? trim($validated['sertifikasi']) : null,
            'id_jabatan' => isset($validated['id_jabatan']) && $validated['id_jabatan'] !== null ? (int) $validated['id_jabatan'] : null,
            'id_pendidikan' => isset($validated['id_pendidikan']) && $validated['id_pendidikan'] !== null ? (int) $validated['id_pendidikan'] : null,
        ];

        $response = $this->tridharmaService->updateDosen($id, $payload);
        $body = $response->json();

        Log::debug('Tridharma dosen update', [
            'url' => rtrim((string) config('services.tridharma.base_url'), '/').'/dosen/'.$id,
            'requested_status' => $payload['status_dosen'],
            'http_status' => $response->status(),
            'api_success' => $body['success'] ?? null,
            'api_message' => $body['message'] ?? null,
            'api_error_fields' => array_keys($body['errors'] ?? []),
            'response_status' => data_get($body, 'data.status_dosen'),
        ]);

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('dosen.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Data dosen berhasil diperbarui.',
            ]);
        }

        $errorMessage = $body['message'] ?? 'Gagal memperbarui data dosen.';
        if (! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                $errorMessage = $firstError;
            }
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->tridharmaService->deleteDosen($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('dosen.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Data dosen berhasil dihapus.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $body['message'] ?? 'Gagal menghapus data dosen.',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Berkas Excel wajib dipilih.',
            'file.mimes' => 'Format berkas harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran berkas maksimal 10 MB.',
        ]);

        $response = $this->tridharmaService->importDosenExcel($request->file('file'));
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('dosen.index')->with('toast', [
                'type' => 'success',
                'title' => 'Import Berhasil',
                'message' => $body['message'] ?? 'Data dosen berhasil diimpor.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'error',
            'title' => 'Import Gagal',
            'message' => $body['message'] ?? 'Gagal mengimpor data dosen dari berkas Excel.',
        ]);
    }

    public function template(): BinaryFileResponse
    {
        return response()->download(
            public_path('templates/template_data_dosen.xlsx'),
            'template_data_dosen.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}
