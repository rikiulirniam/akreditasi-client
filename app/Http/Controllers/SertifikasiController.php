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

        try {
            $response = $this->tridharmaService->getSertifikasiList(array_filter($filters, static fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException) {
            return view('sertifikasi.index', [
                'certifications' => [],
                'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
                'filters' => $filters,
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
                'apiError' => $this->responseMessage($response, 'Gagal memuat data sertifikasi.'),
            ]);
        }

        $data = $response->json('data', []);
        $certifications = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) ? $data : []);
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
            'apiError' => null,
        ]);
    }

    public function create(Request $request): View
    {
        $ownerType = $request->query('tipe_pemilik');
        $ownerId = $ownerType === 'dosen'
            ? $request->query('id_dosen')
            : ($ownerType === 'tendik' ? $request->query('id_tendik') : null);

        return view('sertifikasi.form', [
            'certification' => [],
            'isEditing' => false,
            'ownerType' => in_array($ownerType, ['dosen', 'tendik'], true) ? $ownerType : '',
            'ownerId' => is_numeric($ownerId) ? (int) $ownerId : '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->certificationRules() + [
            'owner_type' => ['required', 'in:dosen,tendik'],
            'owner_id' => ['required', 'integer', 'min:1'],
        ], $this->validationMessages());

        $payload = $this->apiPayload($validated);
        $payload[$validated['owner_type'] === 'dosen' ? 'id_dosen' : 'id_tendik'] = (int) $validated['owner_id'];
        $payload[$validated['owner_type'] === 'dosen' ? 'id_tendik' : 'id_dosen'] = null;

        try {
            $response = $this->tridharmaService->createSertifikasi($payload);
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
            return redirect()->route('sertifikasi.index', $this->ownerQuery($validated['owner_type'], (int) $validated['owner_id']))
                ->with('toast', [
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

        return view('sertifikasi.form', [
            'certification' => $response->json('data', []),
            'isEditing' => true,
            'ownerType' => '',
            'ownerId' => '',
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
            return redirect()->route('sertifikasi.show', $sertifikasi)->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $this->responseMessage($response, 'Sertifikasi berhasil diperbarui.'),
            ]);
        }

        return $this->mutationFailure($response, 'Gagal memperbarui sertifikasi.');
    }

    public function destroy(int $sertifikasi): RedirectResponse
    {
        try {
            $response = $this->tridharmaService->deleteSertifikasi($sertifikasi);
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
            return $this->indexRedirect()->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $this->responseMessage($response, 'Sertifikasi berhasil dihapus.'),
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
            'tahun_sertifikasi' => ['required', 'integer', 'between:1900,2100'],
            'tanggal_kedaluwarsa' => ['nullable', 'date'],
            'url_drive' => ['nullable', 'url', 'max:2048'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'jenis_sertifikasi.required' => 'Jenis sertifikasi wajib diisi.',
            'bidang_studi.required' => 'Bidang studi wajib diisi.',
            'lembaga_penerbit.required' => 'Lembaga penerbit wajib diisi.',
            'status_sertifikasi.required' => 'Status sertifikasi wajib dipilih.',
            'tahun_sertifikasi.required' => 'Tahun sertifikasi wajib diisi.',
            'tahun_sertifikasi.integer' => 'Tahun sertifikasi harus berupa angka.',
            'tanggal_kedaluwarsa.date' => 'Tanggal kedaluwarsa tidak valid.',
            'url_drive.url' => 'Tautan sertifikasi harus berupa URL yang valid.',
            'owner_type.required' => 'Jenis pemilik wajib dipilih.',
            'owner_id.required' => 'ID pemilik wajib diisi.',
            'owner_id.integer' => 'ID pemilik harus berupa angka.',
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
            'tahun_sertifikasi' => (int) $validated['tahun_sertifikasi'],
            'tanggal_kedaluwarsa' => $validated['tanggal_kedaluwarsa'] ?? null,
            'url_drive' => $validated['url_drive'] ?? null,
        ];
    }

    private function ownerQuery(string $ownerType, int $ownerId): array
    {
        $ownerKey = $ownerType === 'dosen' ? 'id_dosen' : 'id_tendik';

        return [
            'tipe_pemilik' => $ownerType,
            $ownerKey => $ownerId,
        ];
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
                return back()->withInput()->withErrors($fieldErrors)->with('toast', [
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

    private function indexRedirect(): RedirectResponse
    {
        return redirect()->route('sertifikasi.index', request()->only([
            'search',
            'tipe_pemilik',
            'id_dosen',
            'id_tendik',
            'tahun_sertifikasi',
            'status_sertifikasi',
        ]));
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
