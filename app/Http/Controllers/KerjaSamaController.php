<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KerjaSamaController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $this->filters($request);

        return $this->renderIndex($request, $filters, null, $request->boolean('create'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $payload = $this->apiPayload($validated);
        $payload['jenis_dokumen'] = $validated['jenis_dokumen'];
        $payload['bukti_dokumen'] = $validated['bukti_dokumen'] ?? null;

        try {
            $response = $this->tridharmaService->createKerjaSama($payload);
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
            return redirect()->route('kerja-sama.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $this->responseMessage($response, 'Kerja sama berhasil ditambahkan.'),
            ]);
        }

        return $this->mutationFailure($response, 'Gagal menambahkan kerja sama.');
    }

    public function edit(Request $request, int $id): View|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getKerjaSamaById($id);
        } catch (ConnectionException) {
            return $this->indexRedirect()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memuat kerja sama',
                'message' => 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $this->successfulResponse($response)) {
            return $this->indexRedirect()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memuat kerja sama',
                'message' => $this->responseMessage($response, 'Data kerja sama tidak ditemukan.'),
            ]);
        }

        return $this->renderIndex(
            $request,
            $this->filters($request),
            $this->extractRecord($response->json('data', [])),
            true,
        );
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        try {
            $response = $this->tridharmaService->updateKerjaSama($id, $this->apiPayload($validated));
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
            return redirect()->route('kerja-sama.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $this->responseMessage($response, 'Kerja sama berhasil diperbarui.'),
            ]);
        }

        return $this->mutationFailure($response, 'Gagal memperbarui kerja sama.');
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $response = $this->tridharmaService->deleteKerjaSama($id);
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
                'message' => $this->responseMessage($response, 'Kerja sama berhasil dihapus.'),
            ]);
        }

        return $this->indexRedirect()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal menghapus kerja sama',
            'message' => $this->responseMessage($response, 'Kerja sama gagal dihapus.'),
        ]);
    }

    public function show(int $id): View|RedirectResponse
    {
        try {
            $response = $this->tridharmaService->getKerjaSamaById($id);
        } catch (ConnectionException) {
            return $this->indexRedirect()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memuat kerja sama',
                'message' => 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.',
            ]);
        }

        if ($response->status() === 401) {
            return $this->redirectToLogin();
        }

        if (! $this->successfulResponse($response)) {
            return $this->indexRedirect()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal memuat kerja sama',
                'message' => $this->responseMessage($response, 'Data kerja sama tidak ditemukan.'),
            ]);
        }

        return view('kerja-sama.show', [
            'cooperation' => $this->extractRecord($response->json('data', [])),
        ]);
    }

    private function renderIndex(Request $request, array $filters, ?array $editing, bool $openModal): View|RedirectResponse
    {
        $cooperations = [];
        $pagination = ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1];
        $apiError = null;

        try {
            $response = $this->tridharmaService->getKerjaSamaList(array_filter(
                $filters + ['per_page' => 10],
                static fn ($value) => $value !== null && $value !== '',
            ));

            if ($response->status() === 401) {
                return $this->redirectToLogin();
            }

            if (! $this->successfulResponse($response)) {
                $apiError = $this->responseMessage($response, 'Gagal memuat data kerja sama.');
            } else {
                $data = $response->json('data', []);
                $data = is_array($data) ? $data : [];
                $cooperations = $this->extractRecords($data);
                $pagination = [
                    'total' => $data['total'] ?? count($cooperations),
                    'per_page' => $data['per_page'] ?? 10,
                    'current_page' => $data['current_page'] ?? 1,
                    'last_page' => $data['last_page'] ?? 1,
                    'from' => $data['from'] ?? null,
                    'to' => $data['to'] ?? null,
                ];
            }
        } catch (ConnectionException) {
            $apiError = 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.';
        }

        $options = ['partners' => [], 'lecturers' => [], 'types' => []];
        $optionsError = null;

        if ($openModal || $editing !== null) {
            try {
                $optionsResult = $this->loadFormOptions();
                if ($optionsResult instanceof RedirectResponse) {
                    return $optionsResult;
                }

                $options = $optionsResult['options'];
                $optionsError = $optionsResult['error'];
            } catch (ConnectionException) {
                $optionsError = 'Tidak dapat terhubung ke server Tridharma untuk memuat pilihan formulir.';
            }
        }

        return view('kerja-sama.index', [
            'cooperations' => $cooperations,
            'pagination' => $pagination,
            'filters' => $filters,
            'apiError' => $apiError,
            'options' => $options,
            'optionsError' => $optionsError,
            'editing' => $editing ?? [],
            'openModal' => $openModal || $editing !== null,
        ]);
    }

    /**
     * @return array{search?: string|null, status?: string|null, tingkat?: string|null, page?: int|null}
     */
    private function filters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'tingkat' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    /**
     * @return array{options: array{partners: array, lecturers: array, types: array}, error: ?string}|RedirectResponse
     */
    private function loadFormOptions(): array|RedirectResponse
    {
        $partners = $this->fetchOptions(
            fn (int $page): Response => $this->tridharmaService->getMitraList(['page' => $page]),
            ['id_mitra', 'id'],
            ['nama_instansi', 'nama_mitra', 'nama'],
        );

        if ($partners instanceof RedirectResponse) {
            return $partners;
        }

        if ($partners['error'] !== null) {
            return ['options' => ['partners' => [], 'lecturers' => [], 'types' => []], 'error' => $partners['error']];
        }

        $lecturers = $this->fetchOptions(
            fn (int $page): Response => $this->tridharmaService->getDosenList(['page' => $page, 'per_page' => 100]),
            ['id_dosen', 'id'],
            ['nama_dosen', 'nama'],
        );

        if ($lecturers instanceof RedirectResponse) {
            return $lecturers;
        }

        if ($lecturers['error'] !== null) {
            return ['options' => ['partners' => $partners['options'], 'lecturers' => [], 'types' => []], 'error' => $lecturers['error']];
        }

        $types = $this->fetchOptions(
            fn (int $page): Response => $this->tridharmaService->getJenisKerjaSamaList(),
            ['id_jenis_kerjasama', 'id'],
            ['nama_jenis_kerjasama', 'nama'],
            false,
        );

        if ($types instanceof RedirectResponse) {
            return $types;
        }

        return [
            'options' => [
                'partners' => $partners['options'],
                'lecturers' => $lecturers['options'],
                'types' => $types['options'],
            ],
            'error' => $types['error'],
        ];
    }

    /**
     * @param  callable(int): Response  $request
     * @param  list<string>  $idKeys
     * @param  list<string>  $nameKeys
     * @return array{options: list<array{id: int, name: string}>, error: ?string}|RedirectResponse
     */
    private function fetchOptions(callable $request, array $idKeys, array $nameKeys, bool $paginate = true): array|RedirectResponse
    {
        $options = [];
        $page = 1;
        $lastPage = 1;

        do {
            $response = $request($page);

            if ($response->status() === 401) {
                return $this->redirectToLogin();
            }

            if (! $this->successfulResponse($response)) {
                return [
                    'options' => [],
                    'error' => $this->responseMessage($response, 'Gagal memuat pilihan formulir kerja sama.'),
                ];
            }

            $data = $response->json('data', []);
            $records = $this->extractRecords($data);
            foreach ($records as $record) {
                $id = null;
                foreach ($idKeys as $key) {
                    if (isset($record[$key]) && is_numeric($record[$key])) {
                        $id = (int) $record[$key];
                        break;
                    }
                }

                $name = null;
                foreach ($nameKeys as $key) {
                    if (is_string($record[$key] ?? null) && trim($record[$key]) !== '') {
                        $name = trim($record[$key]);
                        break;
                    }
                }

                if ($id !== null && $name !== null) {
                    $options[] = ['id' => $id, 'name' => $name];
                }
            }

            $lastPage = $paginate && is_array($data)
                ? max(1, (int) ($data['last_page'] ?? 1))
                : 1;
            $page++;
        } while ($page <= $lastPage);

        return ['options' => $options, 'error' => null];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function apiPayload(array $validated): array
    {
        return [
            'id_mitra' => (int) $validated['id_mitra'],
            'id_jenis_kerjasama' => (int) $validated['id_jenis_kerjasama'],
            'id_dosen' => (int) $validated['id_dosen'],
            'judul_kerja_sama' => $validated['judul_kerja_sama'],
            'tingkat' => $validated['tingkat'] ?? null,
            'bentuk_kegiatan' => $validated['bentuk_kegiatan'] ?? null,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'nomor_dokumen' => $validated['nomor_dokumen'] ?? null,
            'status' => $validated['status'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'judul_kerja_sama' => ['required', 'string', 'max:255'],
            'id_mitra' => ['required', 'integer', 'min:1'],
            'id_jenis_kerjasama' => ['required', 'integer', 'min:1'],
            'id_dosen' => ['required', 'integer', 'min:1'],
            'jenis_dokumen' => ['required', 'string', 'max:255'],
            'tingkat' => ['nullable', 'string', 'max:100'],
            'bentuk_kegiatan' => ['nullable', 'string', 'max:2000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'bukti_dokumen' => ['nullable', 'url', 'max:2048'],
            'nomor_dokumen' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Aktif,Non-aktif,Sedang proses'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractRecords(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        if (is_array($data['data'] ?? null)) {
            return array_values(array_filter($data['data'], 'is_array'));
        }

        return array_is_list($data)
            ? array_values(array_filter($data, 'is_array'))
            : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractRecord(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        if (is_array($data['data'] ?? null) && ! array_is_list($data['data'])) {
            return $data['data'];
        }

        return $data;
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
        $fieldErrors = [];

        if (is_array($errors)) {
            foreach ($errors as $field => $messages) {
                if (is_string($field) && (is_string($messages) || is_array($messages))) {
                    $fieldErrors[$field] = is_array($messages) ? $messages : [$messages];
                }
            }
        }

        $redirect = back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $message,
        ]);

        return $fieldErrors === [] ? $redirect : $redirect->withErrors($fieldErrors);
    }

    private function indexRedirect(): RedirectResponse
    {
        return redirect()->route('kerja-sama.index');
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
