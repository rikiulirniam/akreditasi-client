<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\View\View;

class NilaiMahasiswaController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    /**
     * Menampilkan daftar nilai mahasiswa beserta filter dan pencarian.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));
        $search = trim((string) $request->query('search', ''));
        $mkAkId = $request->query('mata_kuliah_angkatan_kelas_id');
        $kelasId = $request->query('kelas_id');
        $mataKuliahId = $request->query('mata_kuliah_id');
        $semester = $request->query('semester');
        $tahunAkademikId = $request->query('tahun_akademik_id');

        $queryParams = array_filter([
            'page' => $page,
            'per_page' => $request->wantsJson() ? 100 : $perPage,
            'mata_kuliah_angkatan_kelas_id' => $mkAkId,
            'mata_kuliah_id' => $mataKuliahId,
            'kelas_id' => $kelasId,
            'tahun_akademik_id' => $tahunAkademikId,
            'semester' => $semester,
            'search' => $search,
        ], fn ($v) => $v !== null && $v !== '');

        $response = $this->akademikService->getNilaiMahasiswaList($queryParams);

        if ($response->status() === 401) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $rawData = $response->json('data') ?? [];
        $apiPagination = $response->json('pagination');

        $nilaiList = [];
        if (is_array($rawData)) {
            if (isset($rawData['data']) && is_array($rawData['data'])) {
                $nilaiList = $rawData['data'];
                if (! $apiPagination) {
                    $apiPagination = [
                        'total' => $rawData['total'] ?? count($nilaiList),
                        'per_page' => $rawData['per_page'] ?? $perPage,
                        'current_page' => $rawData['current_page'] ?? $page,
                        'last_page' => $rawData['last_page'] ?? 1,
                    ];
                }
            } else {
                $nilaiList = $rawData;
            }
        }

        if ($mkAkId) {
            $nilaiList = array_values(array_filter($nilaiList, function ($item) use ($mkAkId) {
                return (string) ($item['mata_kuliah_angkatan_kelas_id'] ?? '') === (string) $mkAkId;
            }));
        }

        if ($request->wantsJson()) {
            $hasMissingMahasiswa = collect($nilaiList)->contains(fn ($it) => empty($it['mahasiswa']) && ! empty($it['mahasiswa_id']));
            if ($hasMissingMahasiswa) {
                $mahasiswaList = $this->fetchReferenceData('getMahasiswaList');
                $mhsMap = collect($mahasiswaList)->keyBy('id');
                foreach ($nilaiList as &$item) {
                    if (empty($item['mahasiswa']) && ! empty($item['mahasiswa_id'])) {
                        $item['mahasiswa'] = $mhsMap->get($item['mahasiswa_id']);
                    }
                }
                unset($item);
            }

            return response()->json([
                'success' => true,
                'data' => $nilaiList,
            ]);
        }

        // Ambil data referensi (Mata Kuliah Angkatan Kelas, Kelas, Mata Kuliah, Tahun Akademik, Mahasiswa)
        $mkAkList = $this->fetchReferenceData('getMataKuliahAngkatanKelasList');
        $kelasList = $this->fetchReferenceData('getKelasList');
        $mataKuliahList = $this->fetchReferenceData('getMataKuliahList');
        $tahunAkademikList = $this->fetchReferenceData('getTahunAkademikList');
        $mahasiswaList = $this->fetchReferenceData('getMahasiswaList');

        // Filter di sisi client jika filter lokal / pencarian spesifik diterapkan
        if ($search !== '' || $kelasId || $mataKuliahId) {
            $nilaiList = array_values(array_filter($nilaiList, function ($item) use ($search, $kelasId, $mataKuliahId) {
                $mhs = $item['mahasiswa'] ?? [];
                $mkAk = $item['mata_kuliah_angkatan_kelas'] ?? [];
                $mk = $mkAk['enrollment_mata_kuliah']['mata_kuliah'] ?? [];
                $kelas = $mkAk['angkatan_kelas']['kelas'] ?? [];

                if ($kelasId && (string) ($kelas['id'] ?? '') !== (string) $kelasId) {
                    return false;
                }

                if ($mataKuliahId && (string) ($mk['id'] ?? '') !== (string) $mataKuliahId) {
                    return false;
                }

                if ($search !== '') {
                    $keyword = strtolower($search);
                    $nim = strtolower((string) ($mhs['nim'] ?? ''));
                    $namaMhs = strtolower((string) ($mhs['nama_mahasiswa'] ?? ''));
                    $kodeMk = strtolower((string) ($mk['kode_mata_kuliah'] ?? ''));
                    $namaMk = strtolower((string) ($mk['nama_mata_kuliah'] ?? ''));
                    $namaKelas = strtolower((string) ($kelas['kelas'] ?? ($kelas['nama_kelas'] ?? '')));

                    return str_contains($nim, $keyword)
                        || str_contains($namaMhs, $keyword)
                        || str_contains($kodeMk, $keyword)
                        || str_contains($namaMk, $keyword)
                        || str_contains($namaKelas, $keyword);
                }

                return true;
            }));
        }

        // Setup Pagination
        $total = count($nilaiList);
        if ($apiPagination && is_array($apiPagination) && $search === '' && ! $kelasId && ! $mataKuliahId) {
            $pagination = [
                'total' => (int) ($apiPagination['total'] ?? $total),
                'per_page' => (int) ($apiPagination['per_page'] ?? $perPage),
                'current_page' => (int) ($apiPagination['current_page'] ?? $page),
                'last_page' => (int) ($apiPagination['last_page'] ?? 1),
                'from' => $apiPagination['from'] ?? (($page - 1) * $perPage + 1),
                'to' => $apiPagination['to'] ?? min($page * $perPage, (int) ($apiPagination['total'] ?? $total)),
            ];
            $pagedList = $nilaiList;
        } else {
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = max(1, min($page, $lastPage));
            $offset = ($currentPage - 1) * $perPage;
            $pagedList = array_slice($nilaiList, $offset, $perPage);

            $pagination = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ];
        }

        // Ringkasan Statistik
        $totalData = count($nilaiList);
        $totalLulus = collect($nilaiList)->filter(fn ($n) => ! in_array(strtoupper(trim((string) ($n['nilai_huruf'] ?? ''))), ['D', 'E', '']))->count();
        $avgBobot = $totalData > 0 ? round(collect($nilaiList)->avg('bobot') ?? 0, 2) : 0;

        return view('nilai-mahasiswa.index', [
            'nilaiList' => $pagedList,
            'pagination' => $pagination,
            'search' => $search,
            'selectedMkAkId' => $mkAkId,
            'selectedKelasId' => $kelasId,
            'selectedMataKuliahId' => $mataKuliahId,
            'selectedSemester' => $semester,
            'selectedTahunAkademikId' => $tahunAkademikId,
            'mkAkList' => $mkAkList,
            'kelasList' => $kelasList,
            'mataKuliahList' => $mataKuliahList,
            'tahunAkademikList' => $tahunAkademikList,
            'mahasiswaList' => $mahasiswaList,
            'stats' => [
                'total' => $totalData,
                'lulus' => $totalLulus,
                'avg_bobot' => $avgBobot,
            ],
        ]);
    }

    /**
     * Menyimpan data nilai mahasiswa secara manual.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mata_kuliah_angkatan_kelas_id' => ['required', 'integer'],
            'mahasiswa_id' => ['required', 'integer'],
            'tahun_akademik_id' => ['nullable', 'integer'],
            'nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_huruf' => ['nullable', 'string', 'max:5'],
            'bobot' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'link_nilai' => ['nullable', 'string', 'max:2048'],
            'link_evidence' => ['nullable', 'string', 'max:2048'],
        ], [
            'mata_kuliah_angkatan_kelas_id.required' => 'Mata kuliah & kelas wajib dipilih.',
            'mahasiswa_id.required' => 'Mahasiswa wajib dipilih.',
            'nilai_angka.numeric' => 'Nilai angka harus berupa angka.',
            'nilai_angka.min' => 'Nilai angka minimal 0.',
            'nilai_angka.max' => 'Nilai angka maksimal 100.',
        ]);

        if (isset($validated['nilai_angka']) && empty($validated['nilai_huruf']) && ! isset($validated['bobot'])) {
            $calc = self::hitungBobot((float) $validated['nilai_angka']);
            $validated['nilai_huruf'] = $calc['huruf'];
            $validated['bobot'] = $calc['bobot'];
        }

        $response = $this->akademikService->createNilaiMahasiswa($validated);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('nilai-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Disimpan',
                'message' => $body['message'] ?? 'Nilai mahasiswa berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan nilai mahasiswa.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menyimpan',
            'message' => $errorMessage,
        ]);
    }

    /**
     * Memperbarui data nilai mahasiswa.
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'mata_kuliah_angkatan_kelas_id' => ['sometimes', 'required', 'integer'],
            'mahasiswa_id' => ['sometimes', 'required', 'integer'],
            'tahun_akademik_id' => ['nullable', 'integer'],
            'nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_huruf' => ['nullable', 'string', 'max:5'],
            'bobot' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'link_nilai' => ['nullable', 'string', 'max:2048'],
            'link_evidence' => ['nullable', 'string', 'max:2048'],
        ], [
            'nilai_angka.numeric' => 'Nilai angka harus berupa angka.',
            'nilai_angka.min' => 'Nilai angka minimal 0.',
            'nilai_angka.max' => 'Nilai angka maksimal 100.',
        ]);

        if (isset($validated['nilai_angka']) && empty($validated['nilai_huruf']) && ! isset($validated['bobot'])) {
            $calc = self::hitungBobot((float) $validated['nilai_angka']);
            $validated['nilai_huruf'] = $calc['huruf'];
            $validated['bobot'] = $calc['bobot'];
        }

        $response = $this->akademikService->updateNilaiMahasiswa($id, $validated);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('nilai-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Nilai mahasiswa berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui nilai mahasiswa.');

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    /**
     * Menghapus data nilai mahasiswa.
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteNilaiMahasiswa($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('nilai-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Data nilai mahasiswa berhasil dihapus.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus data nilai mahasiswa.');

        return redirect()->route('nilai-mahasiswa.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $errorMessage,
        ]);
    }

    /**
     * Mengunduh template Excel/CSV nilai mahasiswa dari API.
     */
    public function downloadTemplate(Request $request): HttpResponse
    {
        $format = strtolower($request->query('format', 'xlsx'));
        $mode = strtolower($request->query('mode', 'single'));
        $mkAkId = $request->query('mata_kuliah_angkatan_kelas_id');
        $kodeMk = $request->query('kode_mata_kuliah');

        $query = [
            'format' => $format,
            'mode' => $mode,
        ];
        if (! empty($mkAkId)) {
            $query['mata_kuliah_angkatan_kelas_id'] = $mkAkId;
        }
        if (! empty($kodeMk)) {
            $query['kode_mata_kuliah'] = $kodeMk;
        }

        $response = $this->akademikService->downloadNilaiMahasiswaTemplate($query);

        $contentType = $response->header('Content-Type')
            ?: ($format === 'csv'
                ? 'text/csv; charset=UTF-8'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $filename = "template_nilai_{$mode}.".$format;

        return response($response->body(), $response->status(), [
            'Content-Type' => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Mengimpor nilai mahasiswa via file Excel (.xlsx) atau CSV (.csv).
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'duplicate_action' => ['nullable', 'string', 'in:update,skip,fail'],
            'mata_kuliah_angkatan_kelas_id' => ['nullable', 'integer'],
            'tahun_akademik_id' => ['nullable', 'integer'],
        ], [
            'file.required' => 'File Excel atau CSV wajib diunggah.',
            'file.file' => 'Unggahan harus berupa berkas file.',
            'file.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['xlsx', 'csv', 'tsv', 'txt'])) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Format Tidak Sesuai',
                'message' => 'Format file tidak didukung. Harap unggah file dengan format .xlsx atau .csv',
            ]);
        }

        $payload = array_filter([
            'duplicate_action' => $request->input('duplicate_action', 'update'),
            'mata_kuliah_angkatan_kelas_id' => $request->input('mata_kuliah_angkatan_kelas_id'),
            'tahun_akademik_id' => $request->input('tahun_akademik_id'),
        ], fn ($v) => $v !== null && $v !== '');

        $response = $this->akademikService->importNilaiMahasiswa($file, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $data = $body['data'] ?? [];
            $imported = $data['imported_count'] ?? 0;
            $updated = $data['updated_count'] ?? 0;
            $skipped = $data['skipped_count'] ?? 0;
            $failed = $data['failed_count'] ?? 0;

            $message = "Import berhasil diproses: {$imported} data baru ditambahkan, {$updated} diperbarui.";
            if ($skipped > 0) {
                $message .= " {$skipped} dilewati (duplikat).";
            }
            if ($failed > 0) {
                $message .= " {$failed} baris gagal.";
            }

            if (! empty($data['errors']) && is_array($data['errors'])) {
                session()->flash('import_errors', array_slice($data['errors'], 0, 15));
            }
            if (! empty($data['warnings']) && is_array($data['warnings'])) {
                session()->flash('import_warnings', array_slice($data['warnings'], 0, 15));
            }

            $toast = [
                'type' => $failed > 0 ? 'warning' : 'success',
                'title' => 'Import Selesai',
                'message' => $message,
            ];

            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))->with('toast', $toast);
            }

            if ($request->headers->has('referer')) {
                return back()->with('toast', $toast);
            }

            return redirect()->route('nilai-mahasiswa.index')->with('toast', $toast);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Terjadi kesalahan saat memproses impor berkas nilai.');

        return back()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Mengimpor',
            'message' => $errorMessage,
        ]);
    }

    /**
     * Mengambil data Kartu Hasil Studi (KHS) mahasiswa per semester (JSON untuk modal/AJAX).
     */
    public function khs(Request $request, int|string $mahasiswaId): JsonResponse
    {
        $query = array_filter([
            'semester' => $request->query('semester'),
            'tahun_akademik_id' => $request->query('tahun_akademik_id'),
        ]);

        $response = $this->akademikService->getKhsMahasiswa($mahasiswaId, $query);

        return response()->json($response->json(), $response->status());
    }

    /**
     * Mengambil data Transkrip Nilai Akademik Mahasiswa (JSON untuk modal/AJAX).
     */
    public function transkrip(Request $request, int|string $mahasiswaId): JsonResponse
    {
        $query = array_filter([
            'rule_mengulang' => $request->query('rule_mengulang', 'terbaik'),
        ]);

        $response = $this->akademikService->getTranskripMahasiswa($mahasiswaId, $query);

        return response()->json($response->json(), $response->status());
    }

    /**
     * Konversi nilai angka menjadi nilai huruf dan bobot baku.
     */
    public static function hitungBobot(float $angka): array
    {
        return match (true) {
            $angka >= 85 => ['huruf' => 'A', 'bobot' => 4.0],
            $angka >= 80 => ['huruf' => 'AB', 'bobot' => 3.5],
            $angka >= 70 => ['huruf' => 'B', 'bobot' => 3.0],
            $angka >= 65 => ['huruf' => 'BC', 'bobot' => 2.5],
            $angka >= 55 => ['huruf' => 'C', 'bobot' => 2.0],
            $angka >= 40 => ['huruf' => 'D', 'bobot' => 1.0],
            default => ['huruf' => 'E', 'bobot' => 0.0],
        };
    }

    /**
     * Helper untuk mengambil data referensi dropdown secara aman.
     */
    protected function fetchReferenceData(string $method, array $query = []): array
    {
        try {
            $response = $this->akademikService->$method($query);
            if ($response->successful()) {
                $raw = $response->json('data') ?? [];

                return isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : (is_array($raw) ? $raw : []);
            }
        } catch (\Throwable $e) {
            // Silently fallback to empty array
        }

        return [];
    }

    /**
     * Helper mengekstrak pesan error dari response API.
     */
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
}
