<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenagaKependidikanController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $search = trim((string) $request->query('cari', $request->query('search', '')));
        $selectedJabatan = trim((string) $request->query('jabatan', ''));

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

            $pendidikan = collect($rawPendidikan)
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
                ->unique(fn (array $item) => Str::lower($item['jenjang']))
                ->values()
                ->all();

            if (empty($pendidikan)) {
                $pendidikan = [
                    ['id' => 4, 'id_pendidikan' => 4, 'jenjang' => 'D4/S1'],
                    ['id' => 1, 'id_pendidikan' => 1, 'jenjang' => 'S2'],
                    ['id' => 3, 'id_pendidikan' => 3, 'jenjang' => 'S3'],
                ];
            }

            $pendidikanById = collect($pendidikan)->mapWithKeys(fn (array $item) => [
                (string) ($item['id'] ?? '') => (string) ($item['jenjang'] ?? ''),
            ])->all();

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
                ->values();

            $jabatans = collect($this->listData($tendikResponse))
                ->map(fn (array $item) => trim((string) ($item['jabatan_tendik'] ?? '')))
                ->filter()
                ->unique()
                ->sort()
                ->values();

            $perPage = 9;
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
                'kualifikasis' => $this->qualificationCards($record, $tendik),
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
                asMultipart: true,
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

        return [
            'id' => $id,
            'nama' => (string) ($record['nama_tendik'] ?? $record['nama'] ?? ''),
            'nip' => (string) ($record['nip'] ?? ''),
            'jabatan' => (string) ($record['jabatan_tendik'] ?? $record['jabatan'] ?? ''),
            'bidang_keahlian' => (string) ($record['bidang_keahlian'] ?? ''),
            'email' => (string) ($record['email'] ?? ''),
            'status' => (string) ($record['status'] ?? ''),
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
                'institusi' => (string) ($item['institusi'] ?? ''),
                'program_studi' => (string) ($item['program_studi'] ?? ''),
                'tahun_lulus' => (string) ($item['tahun_lulus'] ?? ''),
            ], array_filter($qualifications, 'is_array'));
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
        return $response->successful() && $response->json('success') === true;
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
