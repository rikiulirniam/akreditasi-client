<?php

namespace App\Http\Controllers;

use App\Services\TenagaKependidikanService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenagaKependidikanController extends Controller
{
    public function __construct(
        protected TenagaKependidikanService $tridharmaService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $search = trim((string) $request->query('cari', $request->query('search', '')));
        $selectedJabatan = trim((string) $request->query('jabatan', ''));
        $selectedKualifikasi = trim((string) $request->query('kualifikasi', ''));

        try {
            $tendikResponse = $this->tridharmaService->getTendikList(array_filter([
                'search' => $search !== '' ? $search : null,
                'per_page' => 100,
            ], static fn ($value) => $value !== null));

            if ($tendikResponse->status() === 401) {
                return $this->expiredSession();
            }

            if (! $this->apiSucceeded($tendikResponse)) {
                return $this->renderIndexError(
                    $request,
                    $search,
                    $selectedJabatan,
                    $this->apiErrorMessage($tendikResponse, 'Data Tendik tidak dapat dimuat.'),
                );
            }

            $pendidikanResponse = $this->tridharmaService->getPendidikanList();
            if ($pendidikanResponse->status() === 401) {
                return $this->expiredSession();
            }

            $rawPendidikan = $this->apiSucceeded($pendidikanResponse)
                ? $this->listData($pendidikanResponse)
                : [];

            $apiPendidikan = collect($rawPendidikan)
                ->map(function (array $item) {
                    $id = $item['id_pendidikan'] ?? $item['id'] ?? null;
                    $jenjang = $item['jenjang'] ?? $item['nama_pendidikan'] ?? null;

                    return [
                        'id' => $id !== null && $id !== '' ? (int) $id : null,
                        'id_pendidikan' => $id !== null && $id !== '' ? (int) $id : null,
                        'jenjang' => trim((string) ($jenjang ?? '')),
                    ];
                })
                ->filter(fn (array $item) => ! empty($item['id']) && $item['jenjang'] !== '')
                ->all();

            $standardOptions = [
                ['id' => 4, 'id_pendidikan' => 4, 'jenjang' => 'D4/S1'],
                ['id' => 1, 'id_pendidikan' => 1, 'jenjang' => 'S2'],
                ['id' => 3, 'id_pendidikan' => 3, 'jenjang' => 'S3'],
            ];

            // Merge API items with standard options so D4/S1, S2, S3 are always present, ordered D4/S1 to S3
            $pendidikan = collect($apiPendidikan)
                ->concat($standardOptions)
                ->unique(fn (array $item) => Str::lower($item['jenjang']))
                ->sortBy(function (array $item) {
                    $val = Str::lower($item['jenjang']);
                    if ($val === 'd4/s1' || $val === 's1' || $val === 'd4' || Str::startsWith($val, 's1') || Str::startsWith($val, 'd4')) {
                        return 1;
                    }
                    if ($val === 's2' || Str::startsWith($val, 's2')) {
                        return 2;
                    }
                    if ($val === 's3' || Str::startsWith($val, 's3')) {
                        return 3;
                    }

                    return 10;
                })
                ->values()
                ->all();

            $apiPendidikanById = collect($apiPendidikan)->mapWithKeys(fn (array $item) => [
                (string) ($item['id'] ?? '') => (string) ($item['jenjang'] ?? ''),
            ])->all();

            $standardPendidikanById = collect($standardOptions)->mapWithKeys(fn (array $item) => [
                (string) ($item['id'] ?? '') => (string) ($item['jenjang'] ?? ''),
            ])->all();

            $pendidikanById = $apiPendidikanById + $standardPendidikanById;

            $items = collect($this->listData($tendikResponse))
                ->map(fn (array $item) => $this->normalizeTendik($item, $pendidikanById))
                ->filter(fn (array $item) => $search === '' || Str::contains(
                    Str::lower($item['nama'].' '.$item['nip']),
                    Str::lower($search),
                ))
                ->when($selectedJabatan !== '', function ($collection) use ($selectedJabatan) {
                    $needle = Str::lower($selectedJabatan);
                    if ($needle === 'all data' || $needle === 'all') {
                        return $collection;
                    }
                    if ($needle === 'staff' || $needle === 'staf') {
                        return $collection->filter(fn ($item) => Str::contains(Str::lower($item['jabatan']), 'staff') ||
                            Str::contains(Str::lower($item['jabatan']), 'staf')
                        );
                    }

                    return $collection->filter(fn ($item) => Str::contains(Str::lower($item['jabatan']), $needle)
                    );
                })
                ->when($selectedKualifikasi !== '', function ($collection) use ($selectedKualifikasi) {
                    $needle = Str::lower($selectedKualifikasi);
                    if ($needle === 'all data' || $needle === 'all' || $needle === 'semua kualifikasi') {
                        return $collection;
                    }

                    return $collection->filter(fn ($item) => Str::contains(Str::lower($item['kualifikasi']), $needle));
                })
                ->values();

            $jabatans = collect($this->listData($tendikResponse))
                ->map(fn (array $item) => trim((string) ($item['jabatan_tendik'] ?? '')))
                ->filter()
                ->unique()
                ->sort()
                ->values();

            $perPage = 8;
            $currentPage = max(1, (int) $request->query('page', 1));
            $totalData = $items->count();

            $tenagaKependidikan = new LengthAwarePaginator(
                $items->forPage($currentPage, $perPage)->values(),
                $totalData,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ],
            );

            if ($currentPage > $tenagaKependidikan->lastPage() && $totalData > 0) {
                return redirect()->to($tenagaKependidikan->url($tenagaKependidikan->lastPage()));
            }

            return view('tenaga-kependidikan.index', [
                'tenagaKependidikan' => $tenagaKependidikan,
                'totalData' => $totalData,
                'search' => $search,
                'selectedJabatan' => $selectedJabatan,
                'selectedKualifikasi' => $selectedKualifikasi,
                'jabatans' => $jabatans,
                'pendidikan' => $pendidikan,
                'apiError' => null,
            ]);
        } catch (ConnectionException) {
            return $this->renderIndexError(
                $request,
                $search,
                $selectedJabatan,
                'Tidak dapat terhubung ke layanan Tendik. Periksa koneksi lalu coba lagi.',
            );
        }
    }

    public function storePendidikan(Request $request, int|string $tendikId): RedirectResponse
    {
        $validated = $request->validate([
            'gelar' => ['nullable', 'string', 'max:100'],
            'jenjang' => ['required', 'string', 'max:50'],
            'institusi' => ['nullable', 'string', 'max:255'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'tahun_lulus' => ['nullable', 'string', 'max:10'],
        ], [
            'jenjang.required' => 'Jenjang pendidikan wajib dipilih.',
        ]);

        $this->tridharmaService->storeTendikPendidikan((int) $tendikId, [
            'jenjang' => $validated['jenjang'],
            'gelar' => $validated['gelar'] ?? null,
            'universitas' => $validated['institusi'] ?? null,
            'institusi' => $validated['institusi'] ?? null,
            'program_studi' => $validated['program_studi'] ?? null,
            'tahun_lulus' => $validated['tahun_lulus'] ?? null,
        ]);

        return redirect()->route('tenaga-kependidikan.show', $tendikId)->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Pendidikan berhasil ditambahkan.',
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('tenaga-kependidikan.index', ['tambah' => 1]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50'],
            'jabatan' => ['required', 'string', 'max:255'],
            'id_pendidikan' => ['required', 'integer', 'min:1'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'id_pendidikan.required' => 'Jenjang pendidikan wajib dipilih.',
        ]);

        try {
            $response = $this->tridharmaService->createTendik($this->apiPayload($validated));

            return $this->handleMutationResponse($response, 'Data Tendik berhasil ditambahkan.');
        } catch (ConnectionException) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Koneksi Terputus',
                'message' => 'Tidak dapat terhubung ke layanan Tendik. Data belum tersimpan.',
            ]);
        }
    }

    public function show(Request $request, int|string $id): JsonResponse|RedirectResponse|View
    {
        try {
            $response = $this->tridharmaService->getTendik($id);

            if ($response->status() === 401) {
                return $this->expiredSession();
            }

            if (! $this->apiSucceeded($response)) {
                return response()->json([
                    'success' => false,
                    'message' => $this->apiErrorMessage($response, 'Detail Tendik tidak dapat dimuat.'),
                ], $response->status());
            }

            $record = $response->json('data', []);
            if (! is_array($record)) {
                $record = [];
            }

            $education = $record['pendidikan']['jenjang'] ?? $record['pendidikan']['nama_pendidikan'] ?? null;
            $tendikPendidikanId = $record['id_pendidikan'] ?? ($record['pendidikan']['id_pendidikan'] ?? $record['pendidikan']['id'] ?? null);
            if (! $education && ! empty($tendikPendidikanId)) {
                $educationResponse = $this->tridharmaService->getPendidikanList();
                if ($educationResponse->status() === 401) {
                    return $this->expiredSession();
                }

                if ($this->apiSucceeded($educationResponse)) {
                    foreach ($this->listData($educationResponse) as $educationRecord) {
                        $edId = $educationRecord['id_pendidikan'] ?? $educationRecord['id'] ?? null;
                        if ((string) $edId === (string) $tendikPendidikanId) {
                            $education = $educationRecord['jenjang'] ?? $educationRecord['nama_pendidikan'] ?? null;
                            break;
                        }
                    }
                }
            }

            $tendik = $this->normalizeTendik($record, [
                (string) ($tendikPendidikanId ?? '') => (string) ($education ?? '-'),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'data' => $tendik]);
            }

            $sertifikasiResponse = $this->tridharmaService->getSertifikasiList([
                'tipe_pemilik' => 'tendik',
                'id_tendik' => (int) $id,
                'per_page' => 100,
                'page' => 1,
            ]);

            if ($sertifikasiResponse->status() === 401) {
                return $this->expiredSession();
            }

            return view('tenaga-kependidikan.show', [
                'tenagaKependidikan' => $tendik,
                'kualifikasis' => $this->mergeKualifikasi($record, $tendik, (int) $id),
                'sertifikasis' => $this->apiSucceeded($sertifikasiResponse)
                    ? $this->listData($sertifikasiResponse)
                    : [],
                'detailApiError' => $this->apiSucceeded($sertifikasiResponse)
                    ? null
                    : $this->apiErrorMessage($sertifikasiResponse, 'Sertifikasi tidak dapat dimuat.'),
            ]);
        } catch (ConnectionException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat terhubung ke layanan Tendik.',
                ], 503);
            }

            return redirect()->route('tenaga-kependidikan.index')->with('toast', [
                'type' => 'error',
                'title' => 'Koneksi Terputus',
                'message' => 'Tidak dapat memuat detail Tendik dari layanan API.',
            ]);
        }
    }

    public function storeSertifikasi(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_sertifikasi' => ['required', 'string', 'max:255'],
            'tahun_sertifikasi' => ['required', 'max:20'],
            'bidang_studi' => ['nullable', 'string', 'max:255'],
            'nomor_registrasi_pendidik' => ['nullable', 'string', 'max:255'],
            'nomor_sk' => ['nullable', 'string', 'max:255'],
            'nomor_sertifikat' => ['nullable', 'string', 'max:255'],
            'lembaga_penerbit' => ['nullable', 'string', 'max:255'],
            'tanggal_kedaluwarsa' => ['nullable', 'date'],
            'status_sertifikasi' => ['nullable', 'string', 'max:100'],
            'url_drive' => ['nullable', 'url', 'max:2048'],
            'url_sertifikasi' => ['nullable', 'url', 'max:2048'],
            'evidence_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'jenis_sertifikasi.required' => 'Nama sertifikasi wajib diisi.',
            'tahun_sertifikasi.required' => 'Tahun sertifikasi wajib diisi.',
            'evidence_file.max' => 'Berkas evidence maksimal 10 MB.',
        ]);

        $payload = Arr::only($validated, [
            'jenis_sertifikasi',
            'tahun_sertifikasi',
            'bidang_studi',
            'nomor_registrasi_pendidik',
            'nomor_sk',
            'nomor_sertifikat',
            'lembaga_penerbit',
            'tanggal_kedaluwarsa',
            'status_sertifikasi',
            'url_drive',
        ]);
        $payload['id_tendik'] = (int) $id;
        // url_sertifikasi is required by the API; fall back to url_drive if not separately provided
        $payload['url_sertifikasi'] = $validated['url_sertifikasi']
            ?? $validated['url_drive']
            ?? 'https://drive.google.com/';

        try {
            $response = $this->tridharmaService->createSertifikasi(
                $payload,
                $request->file('evidence_file'),
            );

            if ($response->status() === 401) {
                return $this->expiredSession();
            }

            if ($this->apiSucceeded($response)) {
                return redirect()->route('tenaga-pendidikan.show', $id)->with('toast', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => $response->json('message') ?: 'Sertifikasi berhasil ditambahkan.',
                ]);
            }

            return back()->withInput()->withErrors($this->validationErrors($response))->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menambahkan Sertifikasi',
                'message' => $this->apiErrorMessage($response, 'Sertifikasi belum tersimpan.'),
            ]);
        } catch (ConnectionException) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Koneksi Terputus',
                'message' => 'Tidak dapat terhubung ke layanan sertifikasi. Data belum tersimpan.',
            ]);
        }
    }

    public function updateSertifikasi(Request $request, int|string $tendikId, int|string $sertifikasiId): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_sertifikasi' => ['required', 'string', 'max:255'],
            'tahun_sertifikasi' => ['required', 'max:20'],
            'bidang_studi' => ['nullable', 'string', 'max:255'],
            'nomor_registrasi_pendidik' => ['nullable', 'string', 'max:255'],
            'nomor_sk' => ['nullable', 'string', 'max:255'],
            'nomor_sertifikat' => ['nullable', 'string', 'max:255'],
            'lembaga_penerbit' => ['nullable', 'string', 'max:255'],
            'tanggal_kedaluwarsa' => ['nullable', 'date'],
            'status_sertifikasi' => ['nullable', 'string', 'max:100'],
            'url_sertifikasi' => ['nullable', 'url', 'max:2048'],
            'url_drive' => ['nullable', 'url', 'max:2048'],
        ], [
            'jenis_sertifikasi.required' => 'Nama sertifikasi wajib diisi.',
            'tahun_sertifikasi.required' => 'Tahun sertifikasi wajib diisi.',
        ]);

        $payload = Arr::only($validated, [
            'jenis_sertifikasi',
            'tahun_sertifikasi',
            'bidang_studi',
            'nomor_registrasi_pendidik',
            'nomor_sk',
            'nomor_sertifikat',
            'lembaga_penerbit',
            'tanggal_kedaluwarsa',
            'status_sertifikasi',
            'url_drive',
        ]);
        $payload['url_sertifikasi'] = $validated['url_sertifikasi'] ?? $validated['url_drive'] ?? 'https://drive.google.com/';

        try {
            $response = $this->tridharmaService->updateSertifikasi($sertifikasiId, $payload);

            if ($response->status() === 401) {
                return $this->expiredSession();
            }

            if ($this->apiSucceeded($response)) {
                return redirect()->route('tenaga-pendidikan.show', $tendikId)->with('toast', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => $response->json('message') ?: 'Sertifikasi berhasil diperbarui.',
                ]);
            }

            return back()->withInput()->withErrors($this->validationErrors($response))->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memperbarui Sertifikasi',
                'message' => $this->apiErrorMessage($response, 'Sertifikasi belum tersimpan.'),
            ]);
        } catch (ConnectionException) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Koneksi Terputus',
                'message' => 'Tidak dapat terhubung ke layanan sertifikasi. Perubahan belum tersimpan.',
            ]);
        }
    }

    public function destroySertifikasi(int|string $tendikId, int|string $sertifikasiId): RedirectResponse
    {
        try {
            $response = $this->tridharmaService->deleteSertifikasi($sertifikasiId);

            if ($response->status() === 401) {
                return $this->expiredSession();
            }

            if ($this->apiSucceeded($response)) {
                return redirect()->route('tenaga-pendidikan.show', $tendikId)->with('toast', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => $response->json('message') ?: 'Sertifikasi berhasil dihapus.',
                ]);
            }

            return redirect()->route('tenaga-pendidikan.show', $tendikId)->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menghapus Sertifikasi',
                'message' => $this->apiErrorMessage($response, 'Sertifikasi tidak dapat dihapus.'),
            ]);
        } catch (ConnectionException) {
            return redirect()->route('tenaga-pendidikan.show', $tendikId)->with('toast', [
                'type' => 'error',
                'title' => 'Koneksi Terputus',
                'message' => 'Tidak dapat terhubung ke layanan sertifikasi. Data belum dihapus.',
            ]);
        }
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('tenaga-kependidikan.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50'],
            'jabatan' => ['required', 'string', 'max:255'],
            'id_pendidikan' => ['required', 'integer', 'min:1'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'id_pendidikan.required' => 'Jenjang pendidikan wajib dipilih.',
        ]);

        try {
            $response = $this->tridharmaService->updateTendik($id, $this->apiPayload($validated));

            return $this->handleMutationResponse($response, 'Data Tendik berhasil diperbarui.', $id);
        } catch (ConnectionException) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Koneksi Terputus',
                'message' => 'Tidak dapat terhubung ke layanan Tendik. Perubahan belum tersimpan.',
            ]);
        }
    }

    public function destroy(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->deleteTendik($id);

            return $this->handleMutationResponse($response, 'Data Tendik berhasil dihapus.');
        } catch (ConnectionException) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat terhubung ke layanan Tendik.',
                ], 503);
            }

            return redirect()->route('tenaga-kependidikan.index')->with('toast', [
                'type' => 'error',
                'title' => 'Koneksi Terputus',
                'message' => 'Tidak dapat terhubung ke layanan Tendik. Data belum dihapus.',
            ]);
        }
    }

    /** @param array<string, mixed> $validated
     * @return array{id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string}
     */
    private function apiPayload(array $validated): array
    {
        return [
            'id_pendidikan' => (int) $validated['id_pendidikan'],
            'nama_tendik' => trim($validated['nama']),
            'jabatan_tendik' => trim($validated['jabatan']),
            'nip' => trim($validated['nip']),
        ];
    }

    /** @param array<string, string> $pendidikanById
     * @return array<string, mixed>
     */
    private function normalizeTendik(array $record, array $pendidikanById): array
    {
        $pendidikan = $record['pendidikan'] ?? [];
        $pendidikanId = (string) ($record['id_pendidikan'] ?? (is_array($pendidikan) ? ($pendidikan['id_pendidikan'] ?? $pendidikan['id'] ?? '') : ''));
        $kualifikasi = is_array($pendidikan)
            ? ($pendidikan['jenjang'] ?? $pendidikan['nama_pendidikan'] ?? null)
            : (is_string($pendidikan) ? $pendidikan : null);

        $id = $record['id'] ?? $record['id_tendik'] ?? '';

        $bidangKeahlian = (string) ($record['bidang_keahlian'] ?? (is_array($pendidikan) ? ($pendidikan['program_studi'] ?? '') : ''));
        $email = (string) ($record['email'] ?? '');
        $status = (string) ($record['status'] ?? ($record['status_kepegawaian'] ?? ''));

        return [
            'id' => $id,
            'nama' => (string) ($record['nama_tendik'] ?? $record['nama'] ?? ''),
            'nip' => (string) ($record['nip'] ?? ''),
            'jabatan' => (string) ($record['jabatan_tendik'] ?? $record['jabatan'] ?? ''),
            'bidang_keahlian' => $bidangKeahlian,
            'email' => $email,
            'status' => $status,
            'id_pendidikan' => $pendidikanId,
            'kualifikasi' => (string) ($kualifikasi ?: ($pendidikanById[$pendidikanId] ?? '-')),
        ];
    }

    /** @param array<string, mixed> $record
     * @param  array<string, mixed>  $tendik
     * @return array<int, array<string, string>>
     */
    private function qualificationCards(array $record, array $tendik): array
    {
        $qualifications = $record['kualifikasi'] ?? $record['kualifikasis'] ?? [];
        if (! is_array($qualifications)) {
            $qualifications = [];
        }

        if (array_is_list($qualifications) && $qualifications !== []) {
            return array_map(fn (array $item) => [
                'gelar' => (string) ($item['gelar'] ?? $item['jenjang'] ?? $tendik['kualifikasi']),
                'jenjang' => (string) ($item['jenjang'] ?? ''),
                'institusi' => (string) ($item['institusi'] ?? $item['universitas'] ?? ''),
                'program_studi' => (string) ($item['program_studi'] ?? ''),
                'tahun_lulus' => (string) ($item['tahun_lulus'] ?? ''),
            ], array_filter($qualifications, 'is_array'));
        }

        if (isset($record['pendidikan']) && is_array($record['pendidikan']) && (! empty($record['pendidikan']['jenjang']) || ! empty($record['pendidikan']['universitas']))) {
            $p = $record['pendidikan'];

            return [[
                'gelar' => (string) ($p['gelar'] ?? $p['jenjang'] ?? $tendik['kualifikasi']),
                'jenjang' => (string) ($p['jenjang'] ?? ''),
                'institusi' => (string) ($p['universitas'] ?? $p['institusi'] ?? ''),
                'program_studi' => (string) ($p['program_studi'] ?? ''),
                'tahun_lulus' => (string) ($p['tahun_lulus'] ?? ''),
            ]];
        }

        return $tendik['kualifikasi'] !== '-'
            ? [[
                'gelar' => $tendik['kualifikasi'],
                'jenjang' => '',
                'institusi' => '',
                'program_studi' => '',
                'tahun_lulus' => '',
            ]]
            : [];
    }

    /**
     * Merge kualifikasi from API record with all cached extra pendidikan IDs (stored via cache).
     *
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $tendik
     * @return array<int, array<string, string>>
     */
    private function mergeKualifikasi(array $record, array $tendik, int $tendikApiId): array
    {
        return $this->tridharmaService->getTendikPendidikanList($tendikApiId, $record);
    }

    /** @return array<int, array<string, mixed>> */
    private function listData(Response $response): array
    {
        $data = $response->json('data', []);
        if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
    }

    private function apiSucceeded(Response $response): bool
    {
        if (! $response->successful()) {
            return false;
        }

        $success = $response->json('success');
        if ($success !== null) {
            return (bool) $success;
        }

        return $response->status() >= 200 && $response->status() < 300;
    }

    private function handleMutationResponse(Response $response, string $successFallback, int|string|null $editingId = null): JsonResponse|RedirectResponse
    {
        if ($response->status() === 401) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi API telah berakhir. Silakan login kembali.',
                ], 401);
            }

            return $this->expiredSession();
        }

        if (request()->expectsJson() || request()->ajax()) {
            if ($this->apiSucceeded($response)) {
                return response()->json([
                    'success' => true,
                    'message' => $response->json('message') ?: $successFallback,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $this->apiErrorMessage($response, 'Operasi gagal.'),
                'errors' => $this->validationErrors($response),
            ], $response->status() >= 400 ? $response->status() : 400);
        }

        if ($this->apiSucceeded($response)) {
            return redirect()->route('tenaga-kependidikan.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $response->json('message') ?: $successFallback,
            ]);
        }

        $errors = $this->validationErrors($response);
        if ($response->status() === 422 && $errors !== []) {
            $redirect = back()->withInput()->withErrors($errors)->with('toast', [
                'type' => 'error',
                'title' => 'Periksa Kembali Data',
                'message' => $this->apiErrorMessage($response, 'Data belum dapat disimpan.'),
            ]);

            return $editingId !== null
                ? $redirect->with('editing_tendik_id', (string) $editingId)
                : $redirect;
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => $response->status() === 403 ? 'Akses Ditolak' : 'Permintaan Gagal',
            'message' => $this->apiErrorMessage($response, 'Layanan Tendik belum mengonfirmasi perubahan.'),
        ]);
    }

    /** @return array<string, string> */
    private function validationErrors(Response $response): array
    {
        $errors = $response->json('errors', []);
        if (! is_array($errors)) {
            return [];
        }

        $fieldMap = [
            'nama_tendik' => 'nama',
            'jabatan_tendik' => 'jabatan',
        ];

        return collect($errors)->mapWithKeys(function ($messages, $field) use ($fieldMap) {
            $message = is_array($messages) ? (string) (reset($messages) ?: '') : (string) $messages;
            $formField = $fieldMap[$field] ?? $field;

            return $message !== '' ? [$formField => $message] : [];
        })->all();
    }

    private function apiErrorMessage(Response $response, string $fallback): string
    {
        $statusMessages = [
            403 => 'Akun Anda tidak memiliki izin untuk melakukan tindakan ini.',
            404 => 'Data Tendik tidak ditemukan.',
            429 => 'Terlalu banyak permintaan. Silakan tunggu sebentar lalu coba lagi.',
        ];

        if ($response->serverError()) {
            return 'Layanan Tendik sedang bermasalah. Silakan coba lagi nanti.';
        }

        $message = $response->json('message');

        return $statusMessages[$response->status()]
            ?? (is_string($message) && $message !== '' ? $message : $fallback);
    }

    private function expiredSession(): RedirectResponse
    {
        session()->forget(['access_token', 'token_type', 'user', 'permissions']);

        return redirect()->route('login')->with('toast', [
            'type' => 'warning',
            'title' => 'Sesi Berakhir',
            'message' => 'Sesi API telah berakhir. Silakan login kembali.',
        ]);
    }

    private function renderIndexError(
        Request $request,
        string $search,
        string $selectedJabatan,
        string $message,
    ): View {
        $paginator = new LengthAwarePaginator([], 0, 9, max(1, (int) $request->query('page', 1)), [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('tenaga-kependidikan.index', [
            'tenagaKependidikan' => $paginator,
            'totalData' => 0,
            'search' => $search,
            'selectedJabatan' => $selectedJabatan,
            'jabatans' => collect(),
            'pendidikan' => [
                ['id' => 4, 'id_pendidikan' => 4, 'jenjang' => 'D4/S1'],
                ['id' => 1, 'id_pendidikan' => 1, 'jenjang' => 'S2'],
                ['id' => 3, 'id_pendidikan' => 3, 'jenjang' => 'S3'],
            ],
            'apiError' => $message,
        ]);
    }
}
