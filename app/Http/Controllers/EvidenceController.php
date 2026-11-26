<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EvidenceController extends Controller
{
    private const JENIS_DOKUMEN_OPTIONS = [
        'Proposal',
        'Kontrak',
        'Laporan',
        'Publikasi',
        'HKI',
        'Sertifikat',
    ];

    private const VALIDATOR_ROLES = [
        'ADMINISTRATOR',
        'KAPRODI',
        'ASESOR',
    ];

    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    /**
     * Helper to get current authenticated user's role.
     */
    private function getCurrentUserRole(): string
    {
        $user = session('user');
        if (is_array($user)) {
            return strtoupper(trim((string) ($user['role'] ?? 'KAPRODI')));
        }

        return 'KAPRODI';
    }

    /**
     * Check if current user has permission to validate evidence.
     */
    private function canValidateEvidence(): bool
    {
        return in_array($this->getCurrentUserRole(), self::VALIDATOR_ROLES, true);
    }

    /**
     * Menampilkan daftar repositori Evidence.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status_validasi' => ['nullable', 'string', 'max:50'],
            'jenis_dokumen' => ['nullable', 'string', 'max:100'],
            'id_kegiatan' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = array_filter($filters, static fn ($value) => $value !== null && $value !== '');
        if (! isset($query['per_page'])) {
            $query['per_page'] = 10;
        }

        $localSearchPayload = null;

        try {
            $response = $this->tridharmaService->getEvidenceList($query);

            // Robust fallback if search causes server error on legacy backend
            if (($query['search'] ?? '') !== '' && $response->serverError()) {
                $fallbackQuery = $query;
                unset($fallbackQuery['search']);
                $fallbackQuery['page'] = 1;
                $fallbackQuery['per_page'] = 100;
                $fallbackResponse = $this->tridharmaService->getEvidenceList($fallbackQuery);

                if ($fallbackResponse->successful() && $fallbackResponse->json('success') !== false) {
                    $fallbackPayload = $this->evidenceListPayload($fallbackResponse);
                    $allEvidences = $fallbackPayload['data'];
                    $searchTerm = mb_strtolower((string) $query['search']);

                    $matchingEvidences = array_values(array_filter($allEvidences, function ($item) use ($searchTerm): bool {
                        if (! is_array($item)) {
                            return false;
                        }

                        $fileName = $this->evidenceFileName($item) ?? '';
                        $jenis = (string) ($item['jenis_dokumen'] ?? '');

                        return str_contains(mb_strtolower($fileName), $searchTerm)
                            || str_contains(mb_strtolower($jenis), $searchTerm);
                    }));

                    $perPage = (int) ($query['per_page'] ?? 10);
                    $currentPage = (int) ($query['page'] ?? 1);
                    $total = count($matchingEvidences);

                    $localSearchPayload = [
                        'data' => array_slice($matchingEvidences, ($currentPage - 1) * $perPage, $perPage),
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $currentPage,
                        'last_page' => max(1, (int) ceil($total / $perPage)),
                        'from' => $total > 0 ? (($currentPage - 1) * $perPage) + 1 : null,
                        'to' => $total > 0 ? min($currentPage * $perPage, $total) : null,
                    ];
                    $response = $fallbackResponse;
                }
            }
        } catch (ConnectionException) {
            return view('evidence.index', [
                'evidences' => [],
                'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1, 'from' => null, 'to' => null],
                'filters' => $filters,
                'apiError' => 'Tidak dapat terhubung ke server API Tridharma. Silakan coba beberapa saat lagi.',
                'jenisDokumenOptions' => self::JENIS_DOKUMEN_OPTIONS,
                'canValidate' => $this->canValidateEvidence(),
                'currentUserRole' => $this->getCurrentUserRole(),
            ]);
        }

        if ($response->status() === 401) {
            return view('evidence.index', [
                'evidences' => [],
                'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1, 'from' => null, 'to' => null],
                'filters' => $filters,
                'apiError' => 'Sesi API Tridharma belum terautentikasi atau token tidak valid. Silakan lakukan login ulang.',
                'jenisDokumenOptions' => self::JENIS_DOKUMEN_OPTIONS,
                'canValidate' => $this->canValidateEvidence(),
                'currentUserRole' => $this->getCurrentUserRole(),
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            $errorMessage = $response->json('message') ?? 'Gagal memuat data evidence dari server.';

            return view('evidence.index', [
                'evidences' => [],
                'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1, 'from' => null, 'to' => null],
                'filters' => $filters,
                'apiError' => $errorMessage,
                'jenisDokumenOptions' => self::JENIS_DOKUMEN_OPTIONS,
                'canValidate' => $this->canValidateEvidence(),
                'currentUserRole' => $this->getCurrentUserRole(),
            ]);
        }

        $payload = $localSearchPayload ?? $this->evidenceListPayload($response);
        $evidences = array_map(fn (array $item): array => $this->presentEvidence($item), $payload['data']);

        $pagination = [
            'total' => (int) ($payload['total'] ?? count($evidences)),
            'per_page' => (int) ($payload['per_page'] ?? ($query['per_page'] ?? 10)),
            'current_page' => (int) ($payload['current_page'] ?? ($query['page'] ?? 1)),
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'from' => $payload['from'] ?? ($evidences !== [] ? 1 : null),
            'to' => $payload['to'] ?? ($evidences !== [] ? count($evidences) : null),
        ];

        return view('evidence.index', [
            'evidences' => $evidences,
            'pagination' => $pagination,
            'filters' => $filters,
            'apiError' => null,
            'jenisDokumenOptions' => self::JENIS_DOKUMEN_OPTIONS,
            'canValidate' => $this->canValidateEvidence(),
            'currentUserRole' => $this->getCurrentUserRole(),
        ]);
    }

    /**
     * Simpan data metadata evidence dan tautan baru (POST /api/evidence).
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_file' => ['required', 'string', 'max:255'],
            'jenis_dokumen' => ['required', 'string', 'max:255'],
            'tanggal_upload' => ['nullable', 'date'],
            'url_drive' => ['required', 'url', 'max:2048'],
            'status_validasi' => ['nullable', 'string', Rule::in(['Valid', 'Menunggu Validasi', 'Ditolak'])],
            'konfirmasi_lanjut' => ['nullable', 'boolean'],
            'id_kegiatan' => ['nullable', 'integer'],
        ], [
            'nama_file.required' => 'Nama File / Judul Dokumen wajib diisi.',
            'url_drive.required' => 'Tautan Evidence wajib diisi.',
            'url_drive.url' => 'Format Tautan Evidence harus berupa URL yang valid (misal: https://contoh.com/dokumen).',
            'jenis_dokumen.required' => 'Jenis Dokumen wajib dipilih.',
            'status_validasi.in' => 'Pilihan Status Validasi harus Menunggu Validasi, Valid, atau Ditolak.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()
                ->withErrors($validator, 'evidenceUpload')
                ->with('openEvidenceModal', true)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Periksa Data',
                    'message' => $validator->errors()->first(),
                ]);
        }

        $validated = $validator->validated();

        $payload = [
            'nama_file' => trim($validated['nama_file']),
            'url_drive' => trim($validated['url_drive']),
            'jenis_dokumen' => trim($validated['jenis_dokumen']),
            'tanggal_upload' => ! empty($validated['tanggal_upload']) ? $validated['tanggal_upload'] : now()->toDateString(),
        ];

        if (! empty($validated['id_kegiatan'])) {
            $payload['id_kegiatan'] = (int) $validated['id_kegiatan'];
        }

        if ($request->boolean('konfirmasi_lanjut')) {
            $payload['konfirmasi_lanjut'] = true;
        }

        try {
            $response = $this->tridharmaService->createEvidence($payload);
        } catch (ConnectionException) {
            return back()->withInput()
                ->with('openEvidenceModal', true)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal Mengunggah Evidence',
                    'message' => 'Tidak dapat terhubung ke server API Tridharma. Silakan coba beberapa saat lagi.',
                ]);
        }

        // Handle 409 Conflict: Duplikasi URL
        if ($response->status() === 409) {
            return back()->withInput()
                ->with('openEvidenceModal', true)
                ->with('duplicateUrlWarning', true)
                ->with('toast', [
                    'type' => 'warning',
                    'title' => 'Tautan Duplikat Terdeteksi',
                    'message' => $response->json('message') ?? 'Tautan ini sudah pernah digunakan sebelumnya. Centang konfirmasi untuk tetap menyimpan.',
                ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            $message = $response->json('message') ?? 'Gagal menyimpan data evidence ke backend.';
            $apiErrors = $response->json('errors', []);
            $fieldErrors = [];

            if (is_array($apiErrors)) {
                foreach ($apiErrors as $field => $errors) {
                    if (is_string($field)) {
                        $fieldErrors[$field] = is_array($errors) ? array_values(array_filter($errors, 'is_string')) : (string) $errors;
                    }
                }
            }

            $redirect = back()->withInput()
                ->with('openEvidenceModal', true)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal Menambahkan Evidence',
                    'message' => is_string($message) ? $message : 'Server menolak data evidence.',
                ]);

            if ($fieldErrors !== []) {
                $redirect->withErrors($fieldErrors, 'evidenceUpload');
            }

            return $redirect;
        }

        // Jika status yang dipilih adalah 'Valid' atau 'Ditolak' dan pengguna berwenang (Kaprodi/Admin/Asesor),
        // jalankan validasi backend agar status tersimpan sesuai pilihan pengguna
        $chosenStatus = $validated['status_validasi'] ?? 'Menunggu Validasi';
        if (in_array($chosenStatus, ['Valid', 'Ditolak'], true) && $this->canValidateEvidence()) {
            $createdData = $response->json('data', []);
            $newId = $createdData['id_evidence'] ?? $createdData['id'] ?? null;
            if ($newId) {
                try {
                    $this->tridharmaService->validateEvidence($newId, [
                        'status_validasi' => $chosenStatus,
                        'catatan_validasi' => $chosenStatus === 'Ditolak' ? 'Ditolak saat pembuatan awal.' : null,
                    ]);
                } catch (\Throwable) {
                    // Abaikan jika pemanggilan validasi gagal, evidence tetap tersimpan
                }
            }
        }

        return redirect()->route('evidence.index')->with('toast', [
            'type' => 'success',
            'title' => 'Evidence Berhasil Ditambahkan',
            'message' => $response->json('message') ?? 'Data evidence berhasil disimpan.',
        ]);
    }

    /**
     * Tampilkan detail evidence (GET /api/evidence/{id}).
     */
    public function show(int|string $id): View|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getEvidenceById($id);
        } catch (ConnectionException) {
            return redirect()->route('evidence.index')->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Detail',
                'message' => 'Tidak dapat terhubung ke server API Tridharma.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            return redirect()->route('evidence.index')->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Detail',
                'message' => $response->json('message') ?? 'Data evidence tidak ditemukan.',
            ]);
        }

        $evidence = $response->json('data', []);
        if (is_array($evidence['data'] ?? null)) {
            $evidence = $evidence['data'];
        }

        return view('evidence.show', [
            'evidence' => $this->presentEvidence(is_array($evidence) ? $evidence : []),
            'canValidate' => $this->canValidateEvidence(),
            'currentUserRole' => $this->getCurrentUserRole(),
            'jenisDokumenOptions' => self::JENIS_DOKUMEN_OPTIONS,
        ]);
    }

    /**
     * Satu form edit gabungan untuk memperbarui metadata dan/atau tautan evidence.
     * Mengatur pemanggilan endpoint PUT /api/evidence/{id} dan/atau POST /api/evidence/{id}/ganti-link
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $evidenceId = (int) $id;

        $newUrl = trim((string) $request->input('url_drive', ''));
        $oldUrl = trim((string) $request->input('url_drive_lama', ''));
        $isUrlChanged = ($newUrl !== '' && $oldUrl !== '' && $newUrl !== $oldUrl);

        $rules = [
            'nama_file' => ['required', 'string', 'max:255'],
            'jenis_dokumen' => ['required', 'string', 'max:255'],
            'tanggal_upload' => ['nullable', 'date'],
            'status_validasi' => ['nullable', 'string', Rule::in(['Menunggu Validasi', 'Valid', 'Ditolak'])],
            'url_drive' => ['nullable', 'url', 'max:2048'],
            'url_drive_lama' => ['nullable', 'string'],
        ];

        if ($isUrlChanged) {
            $rules['alasan_perubahan'] = ['required', 'string', 'max:255'];
        } else {
            $rules['alasan_perubahan'] = ['nullable', 'string', 'max:255'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'nama_file.required' => 'Nama File / Judul Dokumen wajib diisi.',
            'jenis_dokumen.required' => 'Jenis Dokumen wajib dipilih.',
            'url_drive.required' => 'Tautan Evidence wajib diisi.',
            'url_drive.url' => 'Format Tautan Evidence harus berupa URL yang valid (misal: https://contoh.com/dokumen).',
            'alasan_perubahan.required' => 'Alasan perubahan tautan wajib diisi karena tautan dokumen diubah.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()
                ->withErrors($validator, 'evidenceEdit')
                ->with('editEvidenceId', (string) $id)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Periksa Formulir Perubahan',
                    'message' => $validator->errors()->first(),
                ]);
        }

        $validated = $validator->validated();
        $linkUpdated = false;
        $metadataUpdated = false;
        $errorMessages = [];

        // 1. Jika tautan diubah, panggil endpoint POST /api/evidence/{id}/ganti-link
        if ($isUrlChanged) {
            try {
                $linkResponse = $this->tridharmaService->changeEvidenceLink($evidenceId, [
                    'url_drive_baru' => $newUrl,
                    'alasan_perubahan' => trim((string) $validated['alasan_perubahan']),
                ]);

                if ($linkResponse->successful() && $linkResponse->json('success') !== false) {
                    $linkUpdated = true;
                } else {
                    $errorMessages[] = 'Gagal mengganti tautan: '.($linkResponse->json('message') ?? 'Server menolak perubahan tautan.');
                }
            } catch (ConnectionException) {
                return back()->withInput()->with('editEvidenceId', (string) $id)->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal Terhubung',
                    'message' => 'Tidak dapat terhubung ke server API saat mengganti tautan.',
                ]);
            }
        }

        // 2. Perbarui metadata menggunakan PUT /api/evidence/{id}
        $metadataPayload = [
            'nama_file' => trim($validated['nama_file']),
            'jenis_dokumen' => trim($validated['jenis_dokumen']),
        ];

        if (! empty($validated['tanggal_upload'])) {
            $metadataPayload['tanggal_upload'] = $validated['tanggal_upload'];
        }

        // Sertakan status validasi jika pengguna berwenang (Kaprodi, Administrator, Asesor)
        if (! empty($validated['status_validasi']) && $this->canValidateEvidence()) {
            $metadataPayload['status_validasi'] = $validated['status_validasi'];
        }

        try {
            $metaResponse = $this->tridharmaService->updateEvidence($evidenceId, $metadataPayload);
            if ($metaResponse->successful() && $metaResponse->json('success') !== false) {
                $metadataUpdated = true;
            } else {
                $errorMessages[] = 'Gagal memperbarui metadata: '.($metaResponse->json('message') ?? 'Server menolak pembaruan metadata.');
            }
        } catch (ConnectionException) {
            if ($linkUpdated) {
                return redirect()->route('evidence.index')->with('toast', [
                    'type' => 'warning',
                    'title' => 'Pembaruan Parsial',
                    'message' => 'Tautan berhasil diperbarui, namun metadata gagal disimpan karena masalah koneksi.',
                ]);
            }

            return back()->withInput()->with('editEvidenceId', (string) $id)->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Terhubung',
                'message' => 'Tidak dapat terhubung ke server API saat memperbarui metadata.',
            ]);
        }

        // Evaluasi hasil kombinasi
        if ($errorMessages !== []) {
            if ($linkUpdated && ! $metadataUpdated) {
                return redirect()->route('evidence.index')->with('toast', [
                    'type' => 'warning',
                    'title' => 'Pembaruan Parsial',
                    'message' => 'Tautan berhasil diganti, tetapi metadata gagal diperbarui: '.implode('; ', $errorMessages),
                ]);
            }

            if (! $linkUpdated && $metadataUpdated) {
                return redirect()->route('evidence.index')->with('toast', [
                    'type' => 'warning',
                    'title' => 'Pembaruan Parsial',
                    'message' => 'Metadata berhasil diperbarui, tetapi tautan gagal diganti: '.implode('; ', $errorMessages),
                ]);
            }

            return back()->withInput()->with('editEvidenceId', (string) $id)->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menyimpan Perubahan',
                'message' => implode('; ', $errorMessages),
            ]);
        }

        $successMessage = $isUrlChanged
            ? 'Evidence berhasil diperbarui (metadata dan tautan berhasil disimpan).'
            : 'Perubahan data berhasil disimpan.';

        return redirect()->route('evidence.index')->with('toast', [
            'type' => 'update',
            'title' => 'Evidence Berhasil Diperbarui',
            'message' => $successMessage,
        ]);
    }

    /**
     * Ganti tautan evidence langsung (POST /api/evidence/{id}/ganti-link).
     */
    public function updateLink(Request $request, int|string $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'url_drive_baru' => ['required', 'url', 'max:2048'],
            'alasan_perubahan' => ['required', 'string', 'max:255'],
        ], [
            'url_drive_baru.required' => 'Tautan Evidence baru wajib diisi.',
            'url_drive_baru.url' => 'Tautan baru harus berupa URL yang valid (misal: https://contoh.com/dokumen).',
            'alasan_perubahan.required' => 'Alasan perubahan tautan wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()
                ->withErrors($validator, 'evidenceGantiLink')
                ->with('gantiLinkEvidenceId', (string) $id)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Periksa Perubahan Tautan',
                    'message' => $validator->errors()->first(),
                ]);
        }

        $validated = $validator->validated();
        $payload = [
            'url_drive_baru' => trim($validated['url_drive_baru']),
            'alasan_perubahan' => trim($validated['alasan_perubahan']),
        ];

        try {
            $response = $this->tridharmaService->changeEvidenceLink($id, $payload);
        } catch (ConnectionException) {
            return back()->withInput()->with('gantiLinkEvidenceId', (string) $id)->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memperbarui Tautan',
                'message' => 'Tidak dapat terhubung ke server API Tridharma.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            return back()->withInput()->with('gantiLinkEvidenceId', (string) $id)->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Mengganti Tautan',
                'message' => $response->json('message') ?? 'Server menolak perubahan tautan evidence.',
            ]);
        }

        return redirect()->route('evidence.show', $id)->with('toast', [
            'type' => 'success',
            'title' => 'Tautan Evidence Diperbarui',
            'message' => $response->json('message') ?? 'Tautan evidence berhasil diganti dan diarsipkan.',
        ]);
    }

    /**
     * Proses validasi status evidence (POST /api/evidence/{id}/validasi).
     */
    public function validasi(Request $request, int|string $id): RedirectResponse
    {
        if (! $this->canValidateEvidence()) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Hanya Administrator, Kaprodi, atau Asesor yang berwenang melakukan validasi evidence.',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'status_validasi' => ['required', Rule::in(['Valid', 'Ditolak'])],
            'catatan_validasi' => ['required_if:status_validasi,Ditolak', 'nullable', 'string', 'max:1000'],
        ], [
            'status_validasi.required' => 'Pilihan status validasi wajib ditentukan.',
            'status_validasi.in' => 'Status validasi hanya boleh Valid atau Ditolak.',
            'catatan_validasi.required_if' => 'Catatan validasi wajib diisi jika status Ditolak.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()
                ->withErrors($validator, 'evidenceValidasi')
                ->with('validasiEvidenceId', (string) $id)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Periksa Formulir Validasi',
                    'message' => $validator->errors()->first(),
                ]);
        }

        $validated = $validator->validated();
        $payload = [
            'status_validasi' => $validated['status_validasi'],
            'catatan_validasi' => $validated['catatan_validasi'] ?? null,
        ];

        try {
            $response = $this->tridharmaService->validateEvidence($id, $payload);
        } catch (ConnectionException) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memvalidasi Evidence',
                'message' => 'Tidak dapat terhubung ke server API Tridharma.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memvalidasi Evidence',
                'message' => $response->json('message') ?? 'Server menolak validasi evidence.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Validasi Berhasil Disimpan',
            'message' => $response->json('message') ?? "Evidence berhasil divalidasi dengan status: {$validated['status_validasi']}.",
        ]);
    }

    /**
     * Unduh atau buka tautan dokumen evidence.
     */
    public function download(int|string $id): RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getEvidenceById($id);
            if ($response->successful() && $response->json('success') !== false) {
                $evidence = $this->presentEvidence($response->json('data', []));
                if (! empty($evidence['_drive_url'])) {
                    return redirect()->away($evidence['_drive_url']);
                }
            }
        } catch (\Throwable) {
            // fallback
        }

        return redirect()->route('evidence.show', $id)->with('toast', [
            'type' => 'error',
            'title' => 'Tautan Tidak Tersedia',
            'message' => 'Tautan berkas dokumen evidence tidak ditemukan.',
        ]);
    }

    /**
     * Hapus record evidence (DELETE /api/evidence/{id}).
     */
    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $response = $this->tridharmaService->deleteEvidence($id);
        } catch (ConnectionException) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menghapus Evidence',
                'message' => 'Tidak dapat terhubung ke server API Tridharma.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menghapus Evidence',
                'message' => $response->json('message') ?? 'Server menolak penghapusan evidence.',
            ]);
        }

        return redirect()->route('evidence.index')->with('toast', [
            'type' => 'delete',
            'title' => null,
            'message' => 'Evidence berhasil dihapus.',
        ]);
    }

    /**
     * Parse and normalize pagination payload from API response.
     *
     * @return array<string, mixed>
     */
    private function evidenceListPayload(Response $response): array
    {
        $payload = $response->json('data', []);
        if (! is_array($payload)) {
            return ['data' => []];
        }

        if (is_array($payload['data'] ?? null)) {
            return $payload;
        }

        return array_is_list($payload) ? ['data' => $payload] : ['data' => []];
    }

    /**
     * Resolve document display title.
     *
     * @param  array<string, mixed>  $evidence
     */
    private function evidenceFileName(array $evidence): ?string
    {
        foreach (['nama_file', 'judul_file', 'nama_file_display', 'file_name', 'original_filename'] as $field) {
            $value = $evidence[$field] ?? null;
            if (is_string($value) && trim($value) !== '' && filter_var(trim($value), FILTER_VALIDATE_URL) === false) {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * Resolve universal document evidence URL.
     *
     * @param  array<string, mixed>  $evidence
     */
    private function evidenceDriveUrl(array $evidence): ?string
    {
        foreach (['url_drive', 'url_evidence', 'url', 'nama_file'] as $field) {
            $value = $evidence[$field] ?? null;
            if (is_string($value) && filter_var(trim($value), FILTER_VALIDATE_URL) !== false) {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * Transform raw API evidence record to presentation array.
     *
     * @param  array<string, mixed>  $evidence
     * @return array<string, mixed>
     */
    private function presentEvidence(array $evidence): array
    {
        $evidence['id_evidence'] = $evidence['id_evidence'] ?? $evidence['id'] ?? null;
        $evidence['_display_file_name'] = $this->evidenceFileName($evidence) ?? 'Dokumen Tanpa Judul';
        $evidence['_drive_url'] = $this->evidenceDriveUrl($evidence);

        $rawDate = $evidence['tanggal_upload'] ?? $evidence['created_at'] ?? null;
        $evidence['_formatted_date'] = '-';
        $evidence['_raw_date_input'] = '';
        if ($rawDate) {
            try {
                $parsed = Carbon::parse($rawDate);
                $evidence['_formatted_date'] = $parsed->format('d/m/Y');
                $evidence['_raw_date_input'] = $parsed->format('Y-m-d');
            } catch (\Throwable) {
                $evidence['_formatted_date'] = (string) $rawDate;
            }
        }

        $evidence['status_validasi'] = trim((string) ($evidence['status_validasi'] ?? 'Menunggu Validasi'));
        if ($evidence['status_validasi'] === '') {
            $evidence['status_validasi'] = 'Menunggu Validasi';
        }

        return $evidence;
    }
}
