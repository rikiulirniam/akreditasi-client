<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SertifikasiController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'tipe_pemilik' => ['nullable', 'in:dosen,tendik'],
            'id_dosen' => ['nullable', 'integer', 'min:1'],
            'id_tendik' => ['nullable', 'integer', 'min:1'],
            'tahun_sertifikasi' => ['nullable', 'integer', 'between:1900,2100'],
            'status_sertifikasi' => ['nullable', 'string', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $idTendik = ! empty($filters['id_tendik']) ? (int) $filters['id_tendik'] : null;
        $selectedTendik = null;
        $idDosen = ! empty($filters['id_dosen']) ? (int) $filters['id_dosen'] : null;
        $selectedDosen = null;
        $dosenRecord = null;

        if ($idTendik !== null) {
            try {
                $tendikResp = $this->tridharmaService->getTendik($idTendik);
                if ($tendikResp->successful()) {
                    $tendikRecord = $tendikResp->json('data', []);
                    $selectedTendik = [
                        'id' => (int) ($tendikRecord['id_tendik'] ?? $idTendik),
                        'nama' => (string) ($tendikRecord['nama_tendik'] ?? $tendikRecord['nama'] ?? 'Tenaga Pendidikan #'.$idTendik),
                    ];
                }
            } catch (\Throwable) {
            }

            if ($selectedTendik === null) {
                $selectedTendik = [
                    'id' => $idTendik,
                    'nama' => 'Tenaga Pendidikan #'.$idTendik,
                ];
            }
        }

        if ($idDosen !== null) {
            try {
                $dosenResp = $this->tridharmaService->getDosenById($idDosen);
                if ($dosenResp->successful()) {
                    $dosenRecord = $dosenResp->json('data', []);
                    $selectedDosen = [
                        'id' => (int) ($dosenRecord['id_dosen'] ?? $dosenRecord['id'] ?? $idDosen),
                        'nama' => (string) ($dosenRecord['nama_dosen'] ?? $dosenRecord['nama'] ?? 'Dosen #'.$idDosen),
                    ];
                }
            } catch (\Throwable) {
            }

            if ($selectedDosen === null) {
                $selectedDosen = [
                    'id' => $idDosen,
                    'nama' => 'Dosen #'.$idDosen,
                ];
            }
        }

        try {
            $response = $this->tridharmaService->getSertifikasiList(array_filter($filters, static fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException) {
            return view('sertifikasi.index', [
                'certifications' => [],
                'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
                'filters' => $filters,
                'selectedTendik' => $selectedTendik,
                'selectedDosen' => $selectedDosen,
                'apiError' => 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            return view('sertifikasi.index', [
                'certifications' => [],
                'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
                'filters' => $filters,
                'selectedTendik' => $selectedTendik,
                'selectedDosen' => $selectedDosen,
                'apiError' => $this->responseMessage($response, 'Gagal memuat data sertifikasi.'),
            ]);
        }

        $data = $response->json('data', []);
        $certifications = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) ? $data : []);

        if ($idTendik !== null) {
            $certifications = $this->tridharmaService->mergeTendikSertifikasi($idTendik, $certifications);
        }

        if ($idDosen !== null && empty($certifications) && ! empty($dosenRecord['sertifikasi'])) {
            $sertifikasiUrl = is_string($dosenRecord['sertifikasi']) ? $dosenRecord['sertifikasi'] : ($dosenRecord['sertifikasi']['url'] ?? '');
            if ($sertifikasiUrl !== '') {
                $certifications = [
                    [
                        'id' => 1,
                        'id_sertifikasi' => 1,
                        'id_dosen' => $idDosen,
                        'jenis_sertifikasi' => 'Sertifikasi Dosen',
                        'tahun_sertifikasi' => (int) date('Y'),
                        'nomor_sertifikat' => '-',
                        'url_sertifikasi' => $sertifikasiUrl,
                        'status_sertifikasi' => 'Tersertifikasi',
                        'nama' => $selectedDosen['nama'],
                        'nama_dosen' => $selectedDosen['nama'],
                    ],
                ];
            }
        }

        $pagination = [
            'total' => $data['total'] ?? count($certifications),
            'per_page' => $data['per_page'] ?? 10,
            'current_page' => $data['current_page'] ?? 1,
            'last_page' => $data['last_page'] ?? 1,
            'from' => $data['from'] ?? null,
            'to' => $data['to'] ?? null,
        ];

        return view('sertifikasi.index', [
            'certifications' => $certifications,
            'pagination' => $pagination,
            'filters' => $filters,
            'selectedTendik' => $selectedTendik,
            'selectedDosen' => $selectedDosen,
            'apiError' => null,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $isDosen = $this->isAuthenticatedDosen();
        $user = session('user', []);
        $ownerType = $isDosen ? 'dosen' : $request->query('tipe_pemilik');
        $ownerId = $isDosen
            ? (is_array($user) ? ($user['id_dosen'] ?? $user['id'] ?? null) : null)
            : ($ownerType === 'dosen'
                ? $request->query('id_dosen')
                : ($ownerType === 'tendik' ? $request->query('id_tendik') : null));

        $ownerOptions = ['dosen' => [], 'tendik' => []];
        $ownerOptionsError = null;

        if (! $isDosen && ! ($ownerType && $ownerId)) {
            try {
                foreach (['dosen', 'tendik'] as $type) {
                    $options = [];
                    $lastPage = 1;

                    for ($page = 1; $page <= $lastPage; $page++) {
                        $response = $type === 'dosen'
                            ? $this->tridharmaService->getDosenList(['page' => $page, 'per_page' => 100])
                            : $this->tridharmaService->getTendikList(['page' => $page, 'per_page' => 100]);

                        if ($response->status() === 401) {
                            return $this->redirectToLogin();
                        }

                        if (! $response->successful() || $response->json('success') === false) {
                            $ownerOptionsError = $this->responseMessage(
                                $response,
                                'Gagal memuat daftar '.($type === 'dosen' ? 'dosen' : 'tenaga kependidikan').'.'
                            );
                            break 2;
                        }

                        $payload = $response->json();
                        $data = is_array($payload) ? ($payload['data'] ?? []) : [];
                        $records = is_array($data) && is_array($data['data'] ?? null)
                            ? $data['data']
                            : (is_array($data) && array_is_list($data) ? $data : []);
                        $pagination = is_array($data)
                            ? ($data['pagination'] ?? $payload['pagination'] ?? [])
                            : ($payload['pagination'] ?? []);
                        $lastPage = max(1, (int) ($data['last_page'] ?? $payload['last_page'] ?? $pagination['last_page'] ?? 1));

                        foreach ($records as $record) {
                            if (! is_array($record)) {
                                continue;
                            }

                            $id = $type === 'dosen'
                                ? ($record['id_dosen'] ?? $record['id'] ?? null)
                                : ($record['id_tendik'] ?? $record['id'] ?? null);
                            $name = $type === 'dosen'
                                ? ($record['nama_dosen'] ?? $record['nama'] ?? $record['name'] ?? null)
                                : ($record['nama_tendik'] ?? $record['nama'] ?? $record['name'] ?? null);

                            if (is_numeric($id) && is_string($name) && trim($name) !== '') {
                                $options[] = ['id' => (int) $id, 'name' => trim($name)];
                            }
                        }
                    }

                    $ownerOptions[$type] = $options;
                }
            } catch (ConnectionException) {
                $ownerOptionsError = 'Tidak dapat terhubung ke server Tridharma untuk memuat daftar pemilik sertifikasi.';
            }
        }

        return view('sertifikasi.form', [
            'certification' => [],
            'isEditing' => false,
            'ownerType' => in_array($ownerType, ['dosen', 'tendik'], true) ? $ownerType : '',
            'ownerId' => is_numeric($ownerId) ? (int) $ownerId : '',
            'isDosen' => $isDosen,
            'ownerOptions' => $ownerOptions,
            'ownerOptionsError' => $ownerOptionsError,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->isAuthenticatedDosen()) {
            $ownerId = $this->authenticatedDosenId();

            if ($ownerId === null) {
                return back()->withInput()->withErrors([
                    'owner_id' => 'ID Dosen tidak ditemukan pada sesi login. Silakan login kembali atau hubungi administrator.',
                ]);
            }

            $request->merge([
                'owner_type' => 'dosen',
                'owner_id' => $ownerId,
            ]);
        }

        $validated = $request->validate($this->certificationRules() + [
            'owner_type' => ['required', 'in:dosen,tendik'],
            'owner_id' => ['required', 'integer', 'min:1'],
        ], $this->validationMessages());

        $payload = $this->apiPayload($validated);
        $payload[$validated['owner_type'] === 'dosen' ? 'id_dosen' : 'id_tendik'] = (int) $validated['owner_id'];
        $payload[$validated['owner_type'] === 'dosen' ? 'id_tendik' : 'id_dosen'] = null;

        try {
            if ($validated['owner_type'] === 'tendik') {
                $response = $this->tridharmaService->storeTendikSertifikasi((int) $validated['owner_id'], $payload);
            } else {
                $response = $this->tridharmaService->createSertifikasi($payload);
            }
        } catch (ConnectionException) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if ($this->successfulResponse($response)) {
            $redirectUrl = $request->input('redirect_to');
            if ($redirectUrl) {
                return redirect($redirectUrl)->with('toast', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => $this->responseMessage($response, 'Sertifikasi berhasil ditambahkan.'),
                ]);
            }

            return redirect()->route('sertifikasi.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $this->responseMessage($response, 'Sertifikasi berhasil ditambahkan.'),
            ]);
        }

        return $this->mutationFailure($response, 'Gagal menambahkan sertifikasi.');
    }

    public function show(int $sertifikasi): View|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getSertifikasiById($sertifikasi);
        } catch (ConnectionException) {
            return $this->indexError('Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            return $this->indexError($this->responseMessage($response, 'Data sertifikasi tidak ditemukan.'));
        }

        return view('sertifikasi.show', [
            'certification' => $response->json('data', []),
        ]);
    }

    public function edit(int $sertifikasi): View|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getSertifikasiById($sertifikasi);
        } catch (ConnectionException) {
            return $this->indexError('Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $response->successful() || $response->json('success') === false) {
            return $this->indexError($this->responseMessage($response, 'Data sertifikasi tidak ditemukan.'));
        }

        $certData = $response->json('data', []);
        $ownerType = ! empty($certData['id_tendik']) ? 'tendik' : (! empty($certData['id_dosen']) ? 'dosen' : '');
        $ownerId = $certData['id_tendik'] ?? $certData['id_dosen'] ?? '';

        return view('sertifikasi.form', [
            'certification' => $certData,
            'isEditing' => true,
            'ownerType' => $ownerType,
            'ownerId' => $ownerId,
            'isDosen' => $this->isAuthenticatedDosen(),
        ]);
    }

    public function update(Request $request, int $sertifikasi): RedirectResponse
    {
        $validated = $request->validate($this->certificationRules(), $this->validationMessages());

        try {
            $response = $this->tridharmaService->updateSertifikasi($sertifikasi, $this->apiPayload($validated));
        } catch (ConnectionException) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if ($this->successfulResponse($response)) {
            $redirectUrl = $request->input('redirect_to');
            if ($redirectUrl) {
                return redirect($redirectUrl)->with('toast', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => $this->responseMessage($response, 'Sertifikasi berhasil diperbarui.'),
                ]);
            }

            return redirect()->route('sertifikasi.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $this->responseMessage($response, 'Sertifikasi berhasil diperbarui.'),
            ]);
        }

        return $this->mutationFailure($response, 'Gagal memperbarui sertifikasi.');
    }

    public function destroy(Request $request, int $sertifikasi): RedirectResponse
    {
        $idTendik = $request->query('id_tendik') ?? $request->input('id_tendik');
        $idTendikInt = is_numeric($idTendik) ? (int) $idTendik : null;

        try {
            if ($idTendikInt) {
                $response = $this->tridharmaService->deleteTendikSertifikasi($idTendikInt, $sertifikasi);
            } else {
                $response = $this->tridharmaService->deleteSertifikasi($sertifikasi);
            }
        } catch (ConnectionException) {
            return $this->indexRedirect()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if ($this->successfulResponse($response)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dihapus',
                ]);
            }

            return $this->indexRedirect()->with('toast', [
                'type' => 'delete',
                'title' => '',
                'message' => 'Data berhasil dihapus',
            ]);
        }

        return $this->indexRedirect()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal menghapus sertifikasi',
            'message' => $this->responseMessage($response, 'Sertifikasi gagal dihapus.'),
        ]);
    }

    private function certificationRules(): array
    {
        return [
            'jenis_sertifikasi' => ['required', 'string', 'max:255'],
            'bidang_studi' => ['required', 'string', 'max:255'],
            'nomor_registrasi_pendidik' => ['nullable', 'string', 'max:255'],
            'nomor_sk' => ['nullable', 'string', 'max:255'],
            'nomor_sertifikat' => ['nullable', 'string', 'max:255'],
            'lembaga_penerbit' => ['required', 'string', 'max:255'],
            'status_sertifikasi' => ['required', 'string', 'max:50'],
            'tahun_sertifikasi' => ['required', 'date_format:Y-m-d'],
            'tanggal_kedaluwarsa' => ['nullable', 'date'],
            'url_drive' => ['nullable', 'url', 'max:2048'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'jenis_sertifikasi.required' => 'Jenis sertifikasi wajib diisi.',
            'jenis_sertifikasi.string' => 'Jenis sertifikasi harus berupa teks.',
            'jenis_sertifikasi.max' => 'Jenis sertifikasi maksimal 255 karakter.',
            'bidang_studi.required' => 'Bidang studi wajib diisi.',
            'bidang_studi.string' => 'Bidang studi harus berupa teks.',
            'bidang_studi.max' => 'Bidang studi maksimal 255 karakter.',
            'nomor_registrasi_pendidik.string' => 'Nomor registrasi pendidik harus berupa teks.',
            'nomor_registrasi_pendidik.max' => 'Nomor registrasi pendidik maksimal 255 karakter.',
            'nomor_sk.string' => 'Nomor SK harus berupa teks.',
            'nomor_sk.max' => 'Nomor SK maksimal 255 karakter.',
            'nomor_sertifikat.string' => 'Nomor sertifikat harus berupa teks.',
            'nomor_sertifikat.max' => 'Nomor sertifikat maksimal 255 karakter.',
            'lembaga_penerbit.required' => 'Lembaga penerbit wajib diisi.',
            'lembaga_penerbit.string' => 'Lembaga penerbit harus berupa teks.',
            'lembaga_penerbit.max' => 'Lembaga penerbit maksimal 255 karakter.',
            'status_sertifikasi.required' => 'Status sertifikasi wajib dipilih.',
            'status_sertifikasi.string' => 'Status sertifikasi tidak valid.',
            'status_sertifikasi.max' => 'Status sertifikasi maksimal 50 karakter.',
            'tahun_sertifikasi.required' => 'Tahun sertifikasi wajib diisi.',
            'tahun_sertifikasi.date_format' => 'Tahun sertifikasi harus berupa tanggal yang valid.',
            'url_drive.url' => 'Tautan sertifikasi harus berupa URL yang valid.',
            'url_drive.max' => 'Tautan sertifikasi maksimal 2048 karakter.',
            'tanggal_kedaluwarsa.date' => 'Tanggal kedaluwarsa tidak valid.',
            'owner_type.required' => 'Jenis pemilik wajib dipilih.',
            'owner_type.in' => 'Jenis pemilik harus Dosen atau Tenaga Kependidikan.',
            'owner_id.required' => 'ID pemilik wajib diisi.',
            'owner_id.integer' => 'ID pemilik harus berupa angka.',
            'owner_id.min' => 'ID pemilik harus lebih besar dari 0.',
        ];
    }

    private function apiPayload(array $validated): array
    {
        return [
            'jenis_sertifikasi' => $validated['jenis_sertifikasi'],
            'bidang_studi' => $validated['bidang_studi'],
            'nomor_registrasi_pendidik' => $validated['nomor_registrasi_pendidik'] ?? null,
            'nomor_sk' => $validated['nomor_sk'] ?? null,
            'nomor_sertifikat' => $validated['nomor_sertifikat'] ?? null,
            'lembaga_penerbit' => $validated['lembaga_penerbit'],
            'status_sertifikasi' => $validated['status_sertifikasi'],
            'tahun_sertifikasi' => $validated['tahun_sertifikasi'],
            'tanggal_kedaluwarsa' => $validated['tanggal_kedaluwarsa'] ?? null,
            'url_drive' => $validated['url_drive'] ?? null,
        ];
    }

    private function isAuthenticatedDosen(): bool
    {
        $user = session('user', []);
        $roles = is_array($user) && is_array($user['roles'] ?? null) ? $user['roles'] : [];
        $primaryRole = is_array($roles[0] ?? null)
            ? ($roles[0]['name'] ?? '')
            : ($roles[0] ?? '');
        $role = is_array($user) ? ($user['role'] ?? $primaryRole) : '';

        return strtolower((string) $role) === 'dosen';
    }

    private function authenticatedDosenId(): ?int
    {
        $user = session('user', []);
        $ownerId = is_array($user) ? ($user['id_dosen'] ?? $user['id'] ?? null) : null;

        return is_numeric($ownerId) && (int) $ownerId > 0 ? (int) $ownerId : null;
    }

    private function successfulResponse(Response $response): bool
    {
        return $response->successful() && $response->json('success') !== false;
    }

    private function responseMessage(Response $response, string $fallback): string
    {
        $message = $response->json('message');

        return is_string($message) && $message !== '' ? $message : $fallback;
    }

    private function mutationFailure(Response $response, string $fallback): RedirectResponse
    {
        $message = $this->responseMessage($response, $fallback);
        $errors = $response->json('errors', $response->json('data', []));

        if (is_array($errors) && $errors !== []) {
            $fieldErrors = [];
            foreach ($errors as $field => $messages) {
                if (is_string($field) && (is_string($messages) || is_array($messages))) {
                    $fieldErrors[$field] = is_array($messages) ? $messages : [$messages];
                }
            }

            if ($fieldErrors !== []) {
                // Terjemahkan pesan error yang umum dan/atau datang dari API
                $translated = $this->translateApiFieldErrors($fieldErrors);

                return back()->withInput()->withErrors($translated)->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal',
                    'message' => $message,
                ]);
            }
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $message,
        ]);
    }

    private function indexError(string $message): RedirectResponse
    {
        return redirect()->route('sertifikasi.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal memuat sertifikasi',
            'message' => $message,
        ]);
    }

    private function indexRedirect(?int $idTendik = null): RedirectResponse
    {
        if ($idTendik) {
            return redirect()->route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => $idTendik]);
        }

        return redirect()->route('sertifikasi.index');
    }

    /**
     * Translate/normalize API field errors into localized Indonesian messages where possible.
     *
     * This helps when the backend returns English messages or uses different field keys
     * (for example "url_sertifikasi") that the frontend wants to present in Bahasa.
     *
     * @param  array<string, array<int, string>>  $fieldErrors
     * @return array<string, array<int, string>>
     */
    private function translateApiFieldErrors(array $fieldErrors): array
    {
        $mapFieldToMessage = [
            // backend may use url_sertifikasi while frontend uses url_drive
            'url_sertifikasi' => ['Tautan sertifikasi wajib diisi.'],
            'url_drive' => ['Tautan sertifikasi wajib diisi.'],
            'tahun_sertifikasi' => ['Tahun sertifikasi tidak valid atau wajib diisi.'],
            'jenis_sertifikasi' => ['Jenis sertifikasi wajib diisi.'],
            'bidang_studi' => ['Bidang studi wajib diisi.'],
        ];

        $translated = [];

        foreach ($fieldErrors as $field => $messages) {
            // prefer a deterministic translated message when we know the field
            if (array_key_exists($field, $mapFieldToMessage)) {
                $translated[$field] = $mapFieldToMessage[$field];

                continue;
            }

            // fallback: if message looks like the default Laravel english "The {attr} field is required."
            $first = is_array($messages) && count($messages) ? $messages[0] : null;
            if (is_string($first) && preg_match('/^The\s+(.+)\s+field\s+is\s+required\./i', $first)) {
                $translated[$field] = [sprintf('%s wajib diisi.', ucwords(str_replace(['_', '-'], ' ', $field)))];

                continue;
            }

            // otherwise keep original messages
            $translated[$field] = $messages;
        }

        return $translated;
    }

    private function redirectToLogin(): RedirectResponse
    {
        return redirect()->route('login')->with('toast', [
            'type' => 'warning',
            'title' => 'Sesi Berakhir',
            'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
        ]);
    }
}
