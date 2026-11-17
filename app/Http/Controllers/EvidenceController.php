<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EvidenceController extends Controller
{
    private const JENIS_DOKUMEN_OPTIONS = ['Proposal', 'Kontrak', 'Laporan', 'Publikasi', 'HKI'];

    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_file_display' => ['required', 'string', 'max:255'],
            'tanggal_upload' => ['required', 'date'],
            'nama_file' => [
                'required',
                'string',
                'url',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $host = is_string($value) ? parse_url($value, PHP_URL_HOST) : null;

                    if (! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
                        $fail('Tautan harus berasal dari Google Drive.');
                    }
                },
            ],
            'jenis_dokumen' => ['required', 'string', Rule::in(self::JENIS_DOKUMEN_OPTIONS)],
            'status_validasi' => ['required', 'string', Rule::in(['Valid', 'Menunggu Validasi', 'Ditolak'])],
        ]);

        if ($validator->fails()) {
            return back()->withInput()
                ->withErrors($validator, 'evidenceUpload')
                ->with('openEvidenceModal', true)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Periksa data',
                    'message' => $validator->errors()->first(),
                ]);
        }

        $validated = $validator->validated();
        $payload = [
            'nama_file' => $validated['nama_file'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'status_validasi' => $validated['status_validasi'],
        ];

        try {
            $response = $this->tridharmaService->createEvidence($payload);
        } catch (ConnectionException) {
            return back()->withInput()
                ->with('openEvidenceModal', true)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal mengunggah evidence',
                    'message' => 'Tidak dapat terhubung ke server API Tridharma. Silakan coba beberapa saat lagi.',
                ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            $message = $response->json('message');
            if ($response->serverError() && is_string($message) && str_contains($message, 'SQLSTATE')) {
                if (preg_match("/Unknown column '([^']+)'/i", $message, $matches) === 1) {
                    $message = sprintf(
                        'Server API gagal menyimpan evidence: query database merujuk kolom "%s" yang tidak tersedia. Periksa mapping/query endpoint POST /api/evidence di backend; frontend tidak mengirim kolom tersebut.',
                        $matches[1],
                    );
                } else {
                    $message = 'Server API gagal menyimpan evidence karena kesalahan database. Periksa query dan mapping endpoint POST /api/evidence di backend.';
                }
            }
            $fieldErrors = [];
            $apiErrors = $response->json('errors', []);

            if (is_array($apiErrors)) {
                foreach ($apiErrors as $field => $errors) {
                    if (! is_string($field)) {
                        continue;
                    }

                    if (is_string($errors)) {
                        $fieldErrors[$field] = $errors;
                    } elseif (is_array($errors)) {
                        $messages = array_values(array_filter($errors, 'is_string'));
                        if ($messages !== []) {
                            $fieldErrors[$field] = $messages;
                        }
                    }
                }
            }

            $redirect = back()->withInput()
                ->with('openEvidenceModal', true)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal mengunggah evidence',
                    'message' => is_string($message) && $message !== '' ? $message : 'Server menolak unggahan evidence.',
                ]);

            if ($fieldErrors !== []) {
                $redirect->withErrors($fieldErrors, 'evidenceUpload');
            }

            return $redirect;
        }

        return redirect()->route('evidence.index')->with('toast', [
            'type' => 'success',
            'title' => 'Evidence berhasil ditambahkan',
            'message' => 'Tautan, jenis dokumen, dan status diteruskan ke API. Kontrak API saat ini tidak mencantumkan Nama File tampilan atau Tanggal Upload sebagai field yang dapat disimpan.',
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getEvidenceById($id);
        } catch (ConnectionException) {
            return redirect()->route('evidence.index')->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memuat detail evidence',
                'message' => 'Tidak dapat terhubung ke server API Tridharma.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            return redirect()->route('evidence.index')->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memuat detail evidence',
                'message' => $response->json('message') ?? 'Data evidence tidak ditemukan.',
            ]);
        }

        $evidence = $response->json('data', []);
        if (is_array($evidence['data'] ?? null)) {
            $evidence = $evidence['data'];
        }

        return view('evidence.show', [
            'evidence' => $this->presentEvidence(is_array($evidence) ? $evidence : []),
        ]);
    }

    public function updateLink(Request $request, int|string $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'url_drive_baru' => [
                'required',
                'url',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $host = is_string($value) ? parse_url($value, PHP_URL_HOST) : null;

                    if (! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
                        $fail('Tautan harus berasal dari Google Drive.');
                    }
                },
            ],
            'alasan_perubahan' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withInput()
                ->withErrors($validator, 'evidenceEdit')
                ->with('editEvidenceId', (string) $id)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Periksa perubahan tautan',
                    'message' => $validator->errors()->first(),
                ]);
        }

        try {
            $validated = $validator->validated();
            $payload = ['url_drive_baru' => $validated['url_drive_baru']];
            if (is_string($validated['alasan_perubahan'] ?? null) && $validated['alasan_perubahan'] !== '') {
                $payload['alasan_perubahan'] = $validated['alasan_perubahan'];
            }

            $response = $this->tridharmaService->changeEvidenceLink($id, $payload);
        } catch (ConnectionException) {
            return back()->withInput()->with('editEvidenceId', (string) $id)->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memperbarui tautan',
                'message' => 'Tidak dapat terhubung ke server API Tridharma.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            return back()->withInput()->with('editEvidenceId', (string) $id)->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memperbarui tautan',
                'message' => $response->json('message') ?? 'Server menolak perubahan tautan evidence.',
            ]);
        }

        return redirect()->route('evidence.index')->with('toast', [
            'type' => 'success',
            'title' => 'Tautan evidence diperbarui',
            'message' => $response->json('message') ?? 'Tautan evidence berhasil diperbarui.',
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $response = $this->tridharmaService->deleteEvidence($id);
        } catch (ConnectionException) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal menghapus evidence',
                'message' => 'Tidak dapat terhubung ke server API Tridharma.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal menghapus evidence',
                'message' => $response->json('message') ?? 'Server menolak penghapusan evidence.',
            ]);
        }

        return redirect()->route('evidence.index')->with('toast', [
            'type' => 'success',
            'title' => 'Evidence berhasil dihapus',
            'message' => $response->json('message') ?? 'Data evidence berhasil dihapus.',
        ]);
    }

    /**
     * Menampilkan daftar repositori Evidence.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Validasi parameter filter yang terbukti didukung oleh API backend
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

            if (($query['search'] ?? '') !== '' && $response->serverError()) {
                $fallbackQuery = $query;
                unset($fallbackQuery['search']);
                $fallbackQuery['page'] = 1;
                $fallbackQuery['per_page'] = 100;
                $fallbackResponse = $this->tridharmaService->getEvidenceList($fallbackQuery);

                if ($fallbackResponse->successful() && $fallbackResponse->json('success') !== false) {
                    $fallbackPayload = $this->evidenceListPayload($fallbackResponse);
                    $allEvidences = $fallbackPayload['data'];
                    $lastApiPage = (int) ($fallbackPayload['last_page'] ?? 1);
                    $allPagesLoaded = true;

                    for ($apiPage = 2; $apiPage <= $lastApiPage; $apiPage++) {
                        $fallbackQuery['page'] = $apiPage;
                        $pageResponse = $this->tridharmaService->getEvidenceList($fallbackQuery);
                        if (! $pageResponse->successful() || $pageResponse->json('success') === false) {
                            $response = $pageResponse;
                            $allPagesLoaded = false;
                            break;
                        }

                        $allEvidences = array_merge($allEvidences, $this->evidenceListPayload($pageResponse)['data']);
                    }

                    if ($allPagesLoaded) {
                        $searchTerm = mb_strtolower((string) $query['search']);
                        $matchingEvidences = array_values(array_filter($allEvidences, function ($item) use ($searchTerm): bool {
                            if (! is_array($item)) {
                                return false;
                            }

                            $fileName = $this->evidenceFileName($item);

                            return $fileName !== null && str_contains(mb_strtolower($fileName), $searchTerm);
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
                } else {
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
            ]);
        }

        if ($response->status() === 401) {
            return view('evidence.index', [
                'evidences' => [],
                'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1, 'from' => null, 'to' => null],
                'filters' => $filters,
                'apiError' => 'Sesi API Tridharma belum terautentikasi atau token tidak valid. Silakan lakukan login ulang.',
                'jenisDokumenOptions' => self::JENIS_DOKUMEN_OPTIONS,
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
        ]);
    }

    /** @return array<string, mixed> */
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

    /** @param array<string, mixed> $evidence */
    private function evidenceFileName(array $evidence): ?string
    {
        foreach (['nama_file_display', 'nama_file_asli', 'nama_file_original', 'original_filename', 'file_name', 'original_name', 'file', 'nama_file', 'path_file'] as $field) {
            $value = $evidence[$field] ?? null;
            if (is_string($value) && trim($value) !== '' && filter_var(trim($value), FILTER_VALIDATE_URL) === false) {
                $fileName = trim($value);

                return in_array($field, ['file', 'path_file'], true)
                    ? basename(str_replace('\\', '/', $fileName))
                    : $fileName;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $evidence */
    private function evidenceDriveUrl(array $evidence): ?string
    {
        foreach (['url_drive', 'url_evidence', 'nama_file'] as $field) {
            $value = $evidence[$field] ?? null;
            if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $evidence
     * @return array<string, mixed>
     */
    private function presentEvidence(array $evidence): array
    {
        $evidence['_display_file_name'] = $this->evidenceFileName($evidence) ?? 'Nama file tidak tersedia';
        $evidence['_drive_url'] = $this->evidenceDriveUrl($evidence);

        return $evidence;
    }
}
