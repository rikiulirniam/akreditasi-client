<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PrestasiMahasiswaController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 9;
        $search = trim((string) $request->query('search', ''));
        $prestasiResponse = $this->akademikService->getPrestasiMahasiswaList(['per_page' => 500]);
        $mahasiswaResponse = $this->akademikService->getMahasiswaList(['per_page' => 500]);
        $evidenceResponse = $this->akademikService->getEvidencePrestasiMahasiswaList(['per_page' => 500]);
        $jenisResponse = $this->akademikService->getJenisPrestasiList(['per_page' => 500]);
        $prodiResponse = $this->akademikService->getProdiList(['per_page' => 500]);
        $kelasResponse = $this->akademikService->getKelasList(['per_page' => 500]);
        $angkatanResponse = $this->akademikService->getAngkatanList(['per_page' => 500]);
        $angkatanKelasResponse = $this->akademikService->getAngkatanKelasList(['per_page' => 500]);
        $enrollmentKelasResponse = $this->akademikService->getEnrollmentKelasList(['per_page' => 500]);

        $responses = [
            'prestasi' => $prestasiResponse,
            'mahasiswa' => $mahasiswaResponse,
            'evidence' => $evidenceResponse,
            'jenis' => $jenisResponse,
            'program studi' => $prodiResponse,
            'kelas' => $kelasResponse,
            'angkatan' => $angkatanResponse,
            'relasi kelas' => $angkatanKelasResponse,
            'anggota kelas' => $enrollmentKelasResponse,
        ];
        foreach ($responses as $response) {
            if ($response->status() === 401) {
                return redirect()->route('login')->with('toast', [
                    'type' => 'warning',
                    'title' => 'Sesi Berakhir',
                    'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
                ]);
            }
        }

        $prestasiList = $this->responseList($prestasiResponse);
        $mahasiswaList = $this->responseList($mahasiswaResponse);
        $mahasiswaMap = collect($mahasiswaList)->keyBy(fn (array $item) => (string) ($item['id'] ?? ''));
        $evidenceByPrestasi = collect($this->responseList($evidenceResponse))
            ->groupBy(fn (array $item) => (string) ($item['id_prestasi_mahasiswa'] ?? ''));
        $jenisByPrestasi = collect($this->responseList($jenisResponse))
            ->groupBy(fn (array $item) => (string) ($item['id_prestasi_mahasiswa'] ?? ''));
        $errorMessages = [];
        foreach ($responses as $name => $response) {
            if (! $response->successful()) {
                $errorMessages[] = $this->extractErrorMessage($response, "Data {$name} gagal dimuat.");
            }
        }

        foreach ($prestasiList as &$prestasi) {
            $prestasiId = (string) ($prestasi['id'] ?? '');
            $mahasiswa = $prestasi['mahasiswa'] ?? $mahasiswaMap->get((string) ($prestasi['id_mahasiswa'] ?? ''));
            $prestasi['nama_mahasiswa'] = is_array($mahasiswa)
                ? ($mahasiswa['nama_mahasiswa'] ?? $mahasiswa['nama'] ?? '-')
                : '-';
            $prestasi['nim'] = is_array($mahasiswa)
                ? ($mahasiswa['nim_display'] ?? $mahasiswa['nim'] ?? '-')
                : '-';
            $prestasi['jenis'] = $jenisByPrestasi->get($prestasiId, collect())->first()['jenis'] ?? '-';
            $prestasi['evidence'] = $evidenceByPrestasi->get($prestasiId, collect())->first()['evidence'] ?? '';
        }
        unset($prestasi);

        if ($search !== '') {
            $keyword = mb_strtolower($search);
            $prestasiList = array_values(array_filter($prestasiList, function (array $item) use ($keyword): bool {
                $searchable = implode(' ', [
                    $item['nama_mahasiswa'] ?? '',
                    $item['nim'] ?? '',
                    $item['nama_aktivitas'] ?? '',
                    $item['jenis'] ?? '',
                    $item['tahun'] ?? '',
                ]);

                return str_contains(mb_strtolower($searchable), $keyword);
            }));
        }

        $total = count($prestasiList);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = min($page, $lastPage);
        $offset = ($currentPage - 1) * $perPage;
        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        $prodiList = $this->responseList($prodiResponse);
        $kelasMap = collect($this->responseList($kelasResponse))->keyBy(fn (array $item) => (string) ($item['id'] ?? ''));
        $angkatanMap = collect($this->responseList($angkatanResponse))->keyBy(fn (array $item) => (string) ($item['id'] ?? ''));
        $prodiMap = collect($prodiList)->keyBy(fn (array $item) => (string) ($item['id'] ?? ''));
        $angkatanKelasList = $this->responseList($angkatanKelasResponse);
        $enrollmentKelasByAngkatanKelas = collect($this->responseList($enrollmentKelasResponse))
            ->groupBy(fn (array $item) => (string) ($item['angkatan_kelas_id'] ?? ''));

        $assignmentOptions = [];
        $studentOptions = [];
        foreach ($angkatanKelasList as $assignment) {
            $assignmentId = (string) ($assignment['id'] ?? '');
            $prodiId = (string) ($assignment['prodi_id'] ?? '');
            $kelasId = (string) ($assignment['kelas_id'] ?? '');
            $angkatanId = (string) ($assignment['angkatan_id'] ?? '');
            $kelas = $kelasMap->get($kelasId);
            $angkatan = $angkatanMap->get($angkatanId);
            $prodi = $prodiMap->get($prodiId);

            if ($assignmentId === '' || ! is_array($kelas) || ! is_array($angkatan) || ! is_array($prodi)) {
                continue;
            }

            $assignmentOptions[] = [
                'id' => $assignmentId,
                'prodi_id' => $prodiId,
                'prodi' => $prodi['nama_prodi'] ?? $prodi['nama_program_studi'] ?? '-',
                'kelas_id' => $kelasId,
                'kelas' => $kelas['kelas'] ?? $kelas['nama_kelas'] ?? '-',
                'angkatan_id' => $angkatanId,
                'angkatan' => $angkatan['angkatan'] ?? $angkatan['tahun'] ?? '-',
            ];

            foreach ($enrollmentKelasByAngkatanKelas->get($assignmentId, collect()) as $enrollment) {
                $student = $mahasiswaMap->get((string) ($enrollment['id_mahasiswa'] ?? ''));
                if (! is_array($student)) {
                    continue;
                }

                $studentOptions[] = [
                    'id' => (string) ($student['id'] ?? ''),
                    'nama' => $student['nama_mahasiswa'] ?? $student['nama'] ?? '-',
                    'nim' => $student['nim_display'] ?? $student['nim'] ?? '-',
                    'label' => ($student['nim_display'] ?? $student['nim'] ?? '-').' – '.($student['nama_mahasiswa'] ?? $student['nama'] ?? 'Mahasiswa'),
                    'assignment_id' => $assignmentId,
                ];
            }
        }

        $jenisOptions = collect($this->responseList($jenisResponse))
            ->pluck('jenis')
            ->filter(fn ($jenis) => is_string($jenis) && trim($jenis) !== '')
            ->map(fn (string $jenis) => trim($jenis))
            ->unique()
            ->values()
            ->all();
        $jenisOptions = array_values(array_unique(array_merge(['Akademik', 'Non-Akademik'], $jenisOptions)));

        $formDataReady = collect([
            $mahasiswaResponse,
            $prodiResponse,
            $kelasResponse,
            $angkatanResponse,
            $angkatanKelasResponse,
            $enrollmentKelasResponse,
        ])->every(fn (Response $response) => $response->successful());

        return view('prestasi-mahasiswa.index', [
            'prestasiList' => array_slice($prestasiList, $offset, $perPage),
            'mahasiswaList' => $mahasiswaList,
            'pagination' => $pagination,
            'search' => $search,
            'errorMessages' => array_values(array_unique($errorMessages)),
            'formDataReady' => $formDataReady,
            'assignmentOptions' => $assignmentOptions,
            'studentOptions' => $studentOptions,
            'jenisOptions' => $jenisOptions,
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $prestasiResponse = $this->akademikService->getPrestasiMahasiswaById($id);

        if ($prestasiResponse->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $prestasi = $prestasiResponse->json('data');
        if (! $prestasiResponse->successful() || ! is_array($prestasi)) {
            return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                'type' => 'error',
                'title' => 'Prestasi Tidak Ditemukan',
                'message' => $this->extractErrorMessage($prestasiResponse, 'Data prestasi mahasiswa tidak ditemukan.'),
            ]);
        }

        $mahasiswaResponse = $this->akademikService->getMahasiswaById($prestasi['id_mahasiswa']);
        $mahasiswaListResponse = $this->akademikService->getMahasiswaList(['per_page' => 500]);
        $evidenceResponse = $this->akademikService->getEvidencePrestasiMahasiswaList(['per_page' => 500]);
        $jenisResponse = $this->akademikService->getJenisPrestasiList(['per_page' => 500]);

        if (in_array(401, [
            $mahasiswaResponse->status(),
            $mahasiswaListResponse->status(),
            $evidenceResponse->status(),
            $jenisResponse->status(),
        ], true)) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $mahasiswa = $mahasiswaResponse->json('data');
        $mahasiswaList = $this->responseList($mahasiswaListResponse);
        $evidenceList = array_values(array_filter(
            $this->responseList($evidenceResponse),
            fn (array $item) => (string) ($item['id_prestasi_mahasiswa'] ?? '') === (string) $id
        ));
        $jenisList = array_values(array_filter(
            $this->responseList($jenisResponse),
            fn (array $item) => (string) ($item['id_prestasi_mahasiswa'] ?? '') === (string) $id
        ));

        $relatedErrors = [];
        foreach ([
            'mahasiswa' => $mahasiswaResponse,
            'daftar mahasiswa' => $mahasiswaListResponse,
            'evidence' => $evidenceResponse,
            'jenis' => $jenisResponse,
        ] as $name => $response) {
            if (! $response->successful()) {
                $relatedErrors[] = $this->extractErrorMessage($response, "Data {$name} gagal dimuat.");
            }
        }

        return view('prestasi-mahasiswa.show', [
            'prestasi' => $prestasi,
            'mahasiswa' => is_array($mahasiswa) ? $mahasiswa : [],
            'mahasiswaList' => $mahasiswaList,
            'evidenceList' => $evidenceList,
            'jenisList' => $jenisList,
            'jenisPrestasi' => $jenisList[0]['jenis'] ?? '-',
            'relatedErrors' => $relatedErrors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_mahasiswa' => ['required', 'integer', 'min:1'],
            'angkatan_kelas_id' => ['required', 'integer', 'min:1'],
            'nama_aktivitas' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:1900', 'max:2100'],
            'certificate' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'id_mahasiswa.required' => 'Mahasiswa wajib dipilih.',
            'angkatan_kelas_id.required' => 'Program studi, kelas, dan angkatan wajib dipilih.',
            'nama_aktivitas.required' => 'Nama aktivitas wajib diisi.',
            'jenis.required' => 'Jenis prestasi wajib dipilih.',
            'tahun.required' => 'Tahun prestasi wajib diisi.',
            'tahun.min' => 'Tahun prestasi tidak valid.',
            'tahun.max' => 'Tahun prestasi tidak valid.',
            'certificate.required' => 'Sertifikat prestasi wajib diunggah.',
            'certificate.mimes' => 'Sertifikat harus berupa file PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => 'Ukuran sertifikat maksimal 10 MB.',
        ]);

        $cohortError = $this->validateStudentCohort(
            (int) $validated['id_mahasiswa'],
            (int) $validated['angkatan_kelas_id']
        );
        if ($cohortError) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Mahasiswa Tidak Sesuai',
                'message' => $cohortError,
            ]);
        }

        $response = $this->akademikService->createPrestasiMahasiswa([
            'id_mahasiswa' => (int) $validated['id_mahasiswa'],
            'nama_aktivitas' => trim($validated['nama_aktivitas']),
            'tahun' => (int) $validated['tahun'],
        ]);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $prestasiId = $body['data']['id'] ?? null;
            if (! $prestasiId) {
                return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                    'type' => 'error',
                    'title' => 'Prestasi Dibuat, Data Belum Lengkap',
                    'message' => 'Prestasi berhasil dibuat, tetapi API tidak mengembalikan ID untuk menyimpan jenis dan sertifikat.',
                ]);
            }

            $jenisResponse = $this->akademikService->createJenisPrestasi([
                'id_prestasi_mahasiswa' => (int) $prestasiId,
                'jenis' => trim($validated['jenis']),
            ]);
            if (! $jenisResponse->successful() || ! ($jenisResponse->json('success') ?? true)) {
                return redirect()->route('prestasi-mahasiswa.show', $prestasiId)->with('toast', [
                    'type' => 'error',
                    'title' => 'Prestasi Dibuat, Jenis Gagal Disimpan',
                    'message' => $this->extractErrorMessage($jenisResponse, 'Jenis prestasi gagal disimpan.'),
                ]);
            }

            $certificatePath = $request->file('certificate')->storePublicly('prestasi-mahasiswa', 'public');
            if (! $certificatePath) {
                return redirect()->route('prestasi-mahasiswa.show', $prestasiId)->with('toast', [
                    'type' => 'error',
                    'title' => 'Prestasi Dibuat, Sertifikat Gagal Disimpan',
                    'message' => 'File sertifikat tidak dapat disimpan. Silakan unggah ulang dari detail prestasi.',
                ]);
            }

            $evidenceResponse = $this->akademikService->createEvidencePrestasiMahasiswa([
                'id_prestasi_mahasiswa' => (int) $prestasiId,
                'evidence' => Storage::disk('public')->url($certificatePath),
            ]);
            if (! $evidenceResponse->successful() || ! ($evidenceResponse->json('success') ?? true)) {
                return redirect()->route('prestasi-mahasiswa.show', $prestasiId)->with('toast', [
                    'type' => 'error',
                    'title' => 'Prestasi Dibuat, Sertifikat Gagal Disimpan',
                    'message' => $this->extractErrorMessage($evidenceResponse, 'Sertifikat gagal ditautkan pada prestasi.'),
                ]);
            }

            return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => 'Prestasi, jenis, dan sertifikat mahasiswa berhasil ditambahkan.',
            ]);
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $this->extractErrorMessage($response, 'Prestasi mahasiswa gagal ditambahkan.'),
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'id_mahasiswa' => ['required', 'integer', 'min:1'],
            'nama_aktivitas' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:1900', 'max:2100'],
            'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $response = $this->akademikService->updatePrestasiMahasiswa($id, [
            'id_mahasiswa' => (int) $validated['id_mahasiswa'],
            'nama_aktivitas' => trim($validated['nama_aktivitas']),
            'tahun' => (int) $validated['tahun'],
        ]);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $jenisResponse = $this->syncAchievementRelation(
                $id,
                trim($validated['jenis']),
                $this->akademikService->getJenisPrestasiList(['per_page' => 500]),
                fn (?string $relatedId, array $payload) => $relatedId
                    ? $this->akademikService->updateJenisPrestasi($relatedId, $payload)
                    : $this->akademikService->createJenisPrestasi($payload),
                'jenis'
            );
            if ($jenisResponse) {
                return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                    'type' => 'error',
                    'title' => 'Prestasi Diperbarui, Jenis Gagal Disimpan',
                    'message' => $jenisResponse,
                ]);
            }

            if ($request->hasFile('certificate')) {
                $certificatePath = $request->file('certificate')->storePublicly('prestasi-mahasiswa', 'public');
                if (! $certificatePath) {
                    return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                        'type' => 'error',
                        'title' => 'Prestasi Diperbarui, Sertifikat Gagal Disimpan',
                        'message' => 'File sertifikat tidak dapat disimpan.',
                    ]);
                }

                $evidenceResponse = $this->syncAchievementRelation(
                    $id,
                    Storage::disk('public')->url($certificatePath),
                    $this->akademikService->getEvidencePrestasiMahasiswaList(['per_page' => 500]),
                    fn (?string $relatedId, array $payload) => $relatedId
                        ? $this->akademikService->updateEvidencePrestasiMahasiswa($relatedId, $payload)
                        : $this->akademikService->createEvidencePrestasiMahasiswa($payload),
                    'evidence'
                );
                if ($evidenceResponse) {
                    return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                        'type' => 'error',
                        'title' => 'Prestasi Diperbarui, Sertifikat Gagal Disimpan',
                        'message' => $evidenceResponse,
                    ]);
                }
            }

            return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => 'Prestasi mahasiswa berhasil diperbarui.',
            ]);
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $this->extractErrorMessage($response, 'Prestasi mahasiswa gagal diperbarui.'),
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deletePrestasiMahasiswa($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('prestasi-mahasiswa.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Prestasi mahasiswa berhasil dihapus.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $this->extractErrorMessage($response, 'Prestasi mahasiswa gagal dihapus.'),
        ]);
    }

    public function storeEvidence(Request $request, int|string $prestasiId): RedirectResponse
    {
        $validated = $request->validate([
            'evidence' => ['required', 'string', 'max:2048'],
        ], [
            'evidence.required' => 'Evidence wajib diisi.',
        ]);

        return $this->saveRelatedRecord(
            $prestasiId,
            $this->akademikService->createEvidencePrestasiMahasiswa([
                'id_prestasi_mahasiswa' => (int) $prestasiId,
                'evidence' => trim($validated['evidence']),
            ]),
            'Evidence'
        );
    }

    public function updateEvidence(Request $request, int|string $prestasiId, int|string $evidenceId): RedirectResponse
    {
        $validated = $request->validate([
            'evidence' => ['required', 'string', 'max:2048'],
        ]);

        $ownershipError = $this->validateRelatedRecordOwnership(
            $prestasiId,
            $evidenceId,
            $this->akademikService->getEvidencePrestasiMahasiswaById($evidenceId)
        );
        if ($ownershipError) {
            return $ownershipError;
        }

        $response = $this->akademikService->updateEvidencePrestasiMahasiswa($evidenceId, [
            'id_prestasi_mahasiswa' => (int) $prestasiId,
            'evidence' => trim($validated['evidence']),
        ]);

        return $this->saveRelatedRecord($prestasiId, $response, 'Evidence');
    }

    public function destroyEvidence(int|string $prestasiId, int|string $evidenceId): RedirectResponse
    {
        $ownershipError = $this->validateRelatedRecordOwnership(
            $prestasiId,
            $evidenceId,
            $this->akademikService->getEvidencePrestasiMahasiswaById($evidenceId)
        );
        if ($ownershipError) {
            return $ownershipError;
        }

        return $this->saveRelatedRecord(
            $prestasiId,
            $this->akademikService->deleteEvidencePrestasiMahasiswa($evidenceId),
            'Evidence',
            'dihapus'
        );
    }

    public function storeJenis(Request $request, int|string $prestasiId): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => ['required', 'string', 'max:255'],
        ], [
            'jenis.required' => 'Jenis prestasi wajib diisi.',
        ]);

        return $this->saveRelatedRecord(
            $prestasiId,
            $this->akademikService->createJenisPrestasi([
                'id_prestasi_mahasiswa' => (int) $prestasiId,
                'jenis' => trim($validated['jenis']),
            ]),
            'Jenis prestasi'
        );
    }

    public function updateJenis(Request $request, int|string $prestasiId, int|string $jenisId): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => ['required', 'string', 'max:255'],
        ]);

        $ownershipError = $this->validateRelatedRecordOwnership(
            $prestasiId,
            $jenisId,
            $this->akademikService->getJenisPrestasiById($jenisId)
        );
        if ($ownershipError) {
            return $ownershipError;
        }

        $response = $this->akademikService->updateJenisPrestasi($jenisId, [
            'id_prestasi_mahasiswa' => (int) $prestasiId,
            'jenis' => trim($validated['jenis']),
        ]);

        return $this->saveRelatedRecord($prestasiId, $response, 'Jenis prestasi');
    }

    private function validateStudentCohort(int $studentId, int $angkatanKelasId): ?string
    {
        $assignmentResponse = $this->akademikService->getAngkatanKelasList(['per_page' => 500]);
        $enrollmentResponse = $this->akademikService->getEnrollmentKelasList(['per_page' => 500]);

        foreach ([$assignmentResponse, $enrollmentResponse] as $response) {
            if (! $response->successful()) {
                return $this->extractErrorMessage($response, 'Data kelas dan mahasiswa gagal diverifikasi.');
            }
        }

        $assignmentExists = collect($this->responseList($assignmentResponse))
            ->contains(fn (array $assignment) => (string) ($assignment['id'] ?? '') === (string) $angkatanKelasId);
        if (! $assignmentExists) {
            return 'Kombinasi program studi, kelas, dan angkatan tidak ditemukan.';
        }

        $studentIsInAssignment = collect($this->responseList($enrollmentResponse))
            ->contains(fn (array $enrollment) => (string) ($enrollment['angkatan_kelas_id'] ?? '') === (string) $angkatanKelasId
                && (string) ($enrollment['id_mahasiswa'] ?? '') === (string) $studentId);

        return $studentIsInAssignment ? null : 'Mahasiswa tidak terdaftar pada kelas dan angkatan yang dipilih.';
    }

    private function syncAchievementRelation(
        int|string $prestasiId,
        string $value,
        Response $listResponse,
        callable $persist,
        string $field
    ): ?string {
        if (! $listResponse->successful()) {
            return $this->extractErrorMessage($listResponse, 'Data relasi prestasi gagal dimuat.');
        }

        $relatedRecord = collect($this->responseList($listResponse))
            ->first(fn (array $item) => (string) ($item['id_prestasi_mahasiswa'] ?? '') === (string) $prestasiId);
        $payload = [
            'id_prestasi_mahasiswa' => (int) $prestasiId,
            $field => $value,
        ];
        $response = $persist(
            isset($relatedRecord['id']) ? (string) $relatedRecord['id'] : null,
            $payload
        );
        $body = $response->json();

        if (! $response->successful() || ! ($body['success'] ?? true)) {
            return $this->extractErrorMessage($response, "Data {$field} gagal disimpan.");
        }

        return null;
    }

    public function destroyJenis(int|string $prestasiId, int|string $jenisId): RedirectResponse
    {
        $ownershipError = $this->validateRelatedRecordOwnership(
            $prestasiId,
            $jenisId,
            $this->akademikService->getJenisPrestasiById($jenisId)
        );
        if ($ownershipError) {
            return $ownershipError;
        }

        return $this->saveRelatedRecord(
            $prestasiId,
            $this->akademikService->deleteJenisPrestasi($jenisId),
            'Jenis prestasi',
            'dihapus'
        );
    }

    private function responseList(Response $response): array
    {
        if (! $response->successful()) {
            return [];
        }

        $items = $response->json('data') ?? [];
        if (is_array($items) && isset($items['data']) && is_array($items['data'])) {
            $items = $items['data'];
        }

        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }

    private function saveRelatedRecord(
        int|string $prestasiId,
        Response $response,
        string $label,
        string $successVerb = 'disimpan'
    ): RedirectResponse {
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            return redirect()->route('prestasi-mahasiswa.show', $prestasiId)->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? "{$label} berhasil {$successVerb}.",
            ]);
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $this->extractErrorMessage($response, "{$label} gagal {$successVerb}."),
        ]);
    }

    private function validateRelatedRecordOwnership(
        int|string $prestasiId,
        int|string $relatedId,
        Response $response
    ): ?RedirectResponse {
        $relatedRecord = $response->json('data');

        if (! $response->successful() || ! is_array($relatedRecord)) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Ditemukan',
                'message' => $this->extractErrorMessage($response, 'Data terkait tidak ditemukan.'),
            ]);
        }

        if ((string) ($relatedRecord['id_prestasi_mahasiswa'] ?? '') !== (string) $prestasiId) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Data Tidak Sesuai',
                'message' => 'Data terkait bukan milik prestasi mahasiswa ini.',
            ]);
        }

        return null;
    }

    private function extractErrorMessage(Response $response, string $fallback): string
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
