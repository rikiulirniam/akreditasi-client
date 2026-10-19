<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DosenController extends Controller
{
    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = (int) $request->query('page', 1);
        $search = $request->query('search');
        $status = $request->query('status');

        $params = [
            'page' => $page,
            'per_page' => 10,
        ];

        if (! empty($search)) {
            $params['search'] = trim($search);
        }

        if (! empty($status)) {
            $params['status'] = trim($status);
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
            'selectedStatus' => $status ?? '',
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
            'id_jabatan' => 1,
            'id_pendidikan' => 1,
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

        // Fallback to mockup data if API returned empty / not found
        if (! $response->successful() && empty($dosenData)) {
            $dosenData = [
                'id' => $id,
                'nama_dosen' => 'Dr. Budi Santoso, M. Kom.',
                'nidn' => '0015088501',
                'status_dosen' => 'Aktif',
                'email' => 'Budisantoso@gmail.com',
                'sertifikasi' => 'https://drive.google.com',
                'nama_jabatan' => 'Lektor Kepala',
                'jurusan' => 'Teknik Elektro',
                'prodi' => 'D4 - Teknologi Rekayasa Komputer',
            ];
        }

        // Normalize lecturer profile
        $rawJabatan = $dosenData['jabatan'] ?? ($dosenData['nama_jabatan'] ?? null);
        $rawJurusan = $dosenData['jurusan'] ?? ($dosenData['bidang_keahlian'] ?? ($dosenData['bidang'] ?? null));
        $rawProdi = $dosenData['prodi'] ?? ($dosenData['program_studi'] ?? null);
        $rawEmail = $dosenData['email'] ?? null;
        $rawStatus = $dosenData['status_dosen'] ?? ($dosenData['status'] ?? null);
        $rawSertifikasi = $dosenData['sertifikasi'] ?? null;
        $rawNama = $dosenData['nama_dosen'] ?? ($dosenData['nama'] ?? ($dosenData['name'] ?? null));
        $rawNidn = $dosenData['nidn'] ?? null;

        $dosen = [
            'id' => is_scalar($dosenData['id'] ?? null) ? $dosenData['id'] : $id,
            'nama_dosen' => $this->extractString($rawNama, 'Dr. Budi Santoso, M. Kom.'),
            'nidn' => $this->extractString($rawNidn, '0015088501'),
            'jabatan' => $this->extractString($rawJabatan, 'Lektor Kepala'),
            'jurusan' => $this->extractString($rawJurusan, 'Teknik Elektro'),
            'prodi' => $this->extractString($rawProdi, 'D4 - Teknologi Rekayasa Komputer'),
            'email' => $this->extractString($rawEmail, 'Budisantoso@gmail.com'),
            'status_dosen' => $this->extractString($rawStatus, 'Aktif'),
            'sertifikasi' => is_string($rawSertifikasi) && trim($rawSertifikasi) !== '' ? trim($rawSertifikasi) : (is_array($rawSertifikasi) ? $this->extractString($rawSertifikasi, '') : null),
        ];

        // Penelitian (Research) list
        $rawPenelitian = ! empty($dosenData['penelitian']) && is_array($dosenData['penelitian'])
            ? $dosenData['penelitian']
            : [
                [
                    'judul' => 'Sistem Prediksi Harga Komoditas Pangan Jawa Tengah dengan LSTM',
                    'tahun_usulan' => '2025',
                    'tahun_kegiatan' => '2026',
                    'tahun_pelaksanaan' => '2026',
                    'bidang' => 'Teknik Elektro',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
                [
                    'judul' => 'Rancang Bangun Sistem Monitoring Kualitas Udara Berbasis IoT',
                    'tahun_usulan' => '2024',
                    'tahun_kegiatan' => '2025',
                    'tahun_pelaksanaan' => '2025',
                    'bidang' => 'Teknik Elektro',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
                [
                    'judul' => 'Optimasi Proses Pengolahan Limbah Cair Industri Tekstil',
                    'tahun_usulan' => '2024',
                    'tahun_kegiatan' => '2025',
                    'tahun_pelaksanaan' => '2025',
                    'bidang' => 'Teknik Elektro',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
                [
                    'judul' => 'Pengembangan Aplikasi Presensi Mahasiswa dengan Pengenalan Wajah',
                    'tahun_usulan' => '2024',
                    'tahun_kegiatan' => '2025',
                    'tahun_pelaksanaan' => '2025',
                    'bidang' => 'Teknik Elektro',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
                [
                    'judul' => 'Analisis Kekuatan Beton Ringan dengan Campuran Abu Sekam Padi',
                    'tahun_usulan' => '2024',
                    'tahun_kegiatan' => '2025',
                    'tahun_pelaksanaan' => '2025',
                    'bidang' => 'Teknik Elektro',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
            ];

        $penelitianList = array_map(function ($item) {
            if (! is_array($item)) {
                return [
                    'judul' => (string) $item,
                    'tahun_usulan' => '2025',
                    'tahun_kegiatan' => '2026',
                    'tahun_pelaksanaan' => '2026',
                    'bidang' => 'Teknik Elektro',
                    'instansi' => 'Politeknik Negeri Semarang',
                ];
            }

            return [
                'judul' => $this->extractString($item['judul'] ?? ($item['title'] ?? ($item['nama'] ?? null)), 'Judul Penelitian'),
                'tahun_usulan' => $this->extractString($item['tahun_usulan'] ?? ($item['usulan'] ?? '2025'), '2025'),
                'tahun_kegiatan' => $this->extractString($item['tahun_kegiatan'] ?? ($item['kegiatan'] ?? '2026'), '2026'),
                'tahun_pelaksanaan' => $this->extractString($item['tahun_pelaksanaan'] ?? ($item['pelaksanaan'] ?? '2026'), '2026'),
                'bidang' => $this->extractString($item['bidang'] ?? ($item['jurusan'] ?? 'Teknik Elektro'), 'Teknik Elektro'),
                'instansi' => $this->extractString($item['instansi'] ?? 'Politeknik Negeri Semarang', 'Politeknik Negeri Semarang'),
            ];
        }, $rawPenelitian);

        // HaKI (Intellectual Property) list
        $rawHaki = ! empty($dosenData['haki']) && is_array($dosenData['haki'])
            ? $dosenData['haki']
            : [
                [
                    'judul' => 'Sistem Deteksi Anomali Jaringan Komputer Menggunakan Deep Autoencoder',
                    'nomor' => 'EC00202518920',
                    'tahun' => '2025',
                    'jenis' => 'Hak Cipta',
                    'status' => 'Tercatat',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
                [
                    'judul' => 'Modul IoT Smart Agriculture Terintegrasi Sensor Suhu dan Kelembapan Tanah',
                    'nomor' => 'P00202405671',
                    'tahun' => '2024',
                    'jenis' => 'Paten Sederhana',
                    'status' => 'Granted',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
                [
                    'judul' => 'Perangkat Lunak Simulasi Protokol Routing Ad-Hoc On-Demand Distance Vector',
                    'nomor' => 'EC00202434190',
                    'tahun' => '2024',
                    'jenis' => 'Hak Cipta',
                    'status' => 'Tercatat',
                    'instansi' => 'Politeknik Negeri Semarang',
                ],
            ];

        $hakiList = array_map(function ($item) {
            if (! is_array($item)) {
                return [
                    'judul' => (string) $item,
                    'nomor' => 'EC00202518920',
                    'tahun' => '2025',
                    'jenis' => 'Hak Cipta',
                    'status' => 'Tercatat',
                    'instansi' => 'Politeknik Negeri Semarang',
                ];
            }

            return [
                'judul' => $this->extractString($item['judul'] ?? ($item['title'] ?? null), 'Judul HaKI'),
                'nomor' => $this->extractString($item['nomor'] ?? ($item['no'] ?? 'EC00202518920'), 'EC00202518920'),
                'tahun' => $this->extractString($item['tahun'] ?? '2025', '2025'),
                'jenis' => $this->extractString($item['jenis'] ?? 'Hak Cipta', 'Hak Cipta'),
                'status' => $this->extractString($item['status'] ?? 'Tercatat', 'Tercatat'),
                'instansi' => $this->extractString($item['instansi'] ?? 'Politeknik Negeri Semarang', 'Politeknik Negeri Semarang'),
            ];
        }, $rawHaki);

        // Riwayat Pendidikan list
        $rawPendidikan = ! empty($dosenData['riwayat_pendidikan']) && is_array($dosenData['riwayat_pendidikan'])
            ? $dosenData['riwayat_pendidikan']
            : (
                ! empty($dosenData['pendidikan']) && is_array($dosenData['pendidikan'])
                    ? $dosenData['pendidikan']
                    : [
                        [
                            'gelar' => 'Dr',
                            'jenjang' => 'S3',
                            'instansi' => 'Politeknik Negeri Semarang',
                            'jurusan' => 'Teknik Informatika',
                            'tahun_lulus' => '2026',
                        ],
                        [
                            'gelar' => 'Dr',
                            'jenjang' => 'S3',
                            'instansi' => 'Politeknik Negeri Semarang',
                            'jurusan' => 'Teknik Informatika',
                            'tahun_lulus' => '2026',
                        ],
                        [
                            'gelar' => 'Dr',
                            'jenjang' => 'S3',
                            'instansi' => 'Politeknik Negeri Semarang',
                            'jurusan' => 'Teknik Informatika',
                            'tahun_lulus' => '2026',
                        ],
                    ]
            );

        $riwayatPendidikan = array_map(function ($item) {
            if (! is_array($item)) {
                return [
                    'gelar' => 'Dr',
                    'jenjang' => 'S3',
                    'instansi' => (string) $item,
                    'jurusan' => 'Teknik Informatika',
                    'tahun_lulus' => '2026',
                ];
            }

            return [
                'gelar' => $this->extractString($item['gelar'] ?? 'Dr', 'Dr'),
                'jenjang' => $this->extractString($item['jenjang'] ?? ($item['tingkat'] ?? 'S3'), 'S3'),
                'instansi' => $this->extractString($item['instansi'] ?? ($item['universitas'] ?? ($item['kampus'] ?? 'Politeknik Negeri Semarang')), 'Politeknik Negeri Semarang'),
                'jurusan' => $this->extractString($item['jurusan'] ?? ($item['program_studi'] ?? ($item['prodi'] ?? 'Teknik Informatika')), 'Teknik Informatika'),
                'tahun_lulus' => $this->extractString($item['tahun_lulus'] ?? ($item['tahun'] ?? '2026'), '2026'),
            ];
        }, $rawPendidikan);

        // Pengabdian Kepada Masyarakat list
        $rawPkm = ! empty($dosenData['pengabdian_masyarakat']) && is_array($dosenData['pengabdian_masyarakat'])
            ? $dosenData['pengabdian_masyarakat']
            : (
                ! empty($dosenData['pkm']) && is_array($dosenData['pkm'])
                    ? $dosenData['pkm']
                    : [
                        [
                            'judul' => 'SISTEM KEAMANAN LINGKUNGAN BERBASIS INTERNET OF THING (IOT) DI WILAYAH TLOGO RT 06, GEBLAKAN, TAMANTIRTO, KASIHAN, BANTUL',
                            'tahun' => '2026',
                            'lokasi' => 'TLOGO RT 06, GEBLAKAN, TAMANTIRTO, KASIHAN, BANTUL',
                        ],
                        [
                            'judul' => 'PELATIHAN PEMANFAATAN PANEL SURYA SEBAGAI SUMBER ENERGI ALTERNATIF BAGI MASYARAKAT DESA',
                            'tahun' => '2026',
                            'lokasi' => 'DESA KALISIDI, UNGARAN BARAT, KABUPATEN SEMARANG',
                        ],
                        [
                            'judul' => 'PEMANFAATAN INTERNET OF THINGS UNTUK MONITORING SUHU DAN KELEMBAPAN PADA BUDIDAYA TANAMAN',
                            'tahun' => '2026',
                            'lokasi' => 'DESA LEREP, UNGARAN BARAT, SEMARANG',
                        ],
                    ]
            );

        $pengabdianMasyarakat = array_map(function ($item) {
            if (! is_array($item)) {
                return [
                    'judul' => (string) $item,
                    'tahun' => '2026',
                    'lokasi' => 'Semarang',
                ];
            }

            return [
                'judul' => $this->extractString($item['judul'] ?? ($item['title'] ?? ($item[0] ?? 'Kegiatan Pengabdian Masyarakat')), 'Kegiatan Pengabdian Masyarakat'),
                'tahun' => $this->extractString($item['tahun'] ?? '2026', '2026'),
                'lokasi' => $this->extractString($item['lokasi'] ?? ($item['tempat'] ?? 'Semarang'), 'Semarang'),
            ];
        }, $rawPkm);

        return view('dosen.show', [
            'dosen' => $dosen,
            'penelitianList' => $penelitianList,
            'hakiList' => $hakiList,
            'riwayatPendidikan' => $riwayatPendidikan,
            'pengabdianMasyarakat' => $pengabdianMasyarakat,
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
            'id_jabatan' => 1,
            'id_pendidikan' => 1,
        ];

        $response = $this->tridharmaService->updateDosen($id, $payload);
        $body = $response->json();

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

    public function template(): BinaryFileResponse|StreamedResponse
    {
        $templatePath = public_path('templates/template_data_dosen.xlsx');

        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'template_data_dosen.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $staticCsv = public_path('templates/template_data_dosen.csv');
        if (file_exists($staticCsv)) {
            return response()->download($staticCsv, 'template_data_dosen.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // Fallback: Generate a CSV formatted spreadsheet template on the fly
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_data_dosen.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'nidn',
                'nuptk',
                'nama_dosen',
                'email',
                'status_dosen',
                'id_jabatan',
                'id_pendidikan',
                'sertifikasi',
            ]);
            fputcsv($handle, [
                '0015088501',
                '1234567890',
                'Dr. Ir. Ahmad Dahlan, M.T.',
                'ahmad.dahlan@polines.ac.id',
                'DTPS',
                '1',
                '1',
                'Pendidik Profesional',
            ]);
            fclose($handle);
        }, 200, $headers);
    }
}
