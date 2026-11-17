<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LuaranController extends Controller
{
    /** @var array<string, string> */
    private const JENIS_KEGIATAN = [
        'penelitian' => 'Penelitian',
        'pkm' => 'PkM',
    ];

    /**
     * Kategori luaran ala SINTA beserta kolom yang ditampilkan.
     *
     * Setiap kolom: label, daftar key kandidat pada data API (diambil yang pertama terisi).
     * Kolom "Penulis" dan "Tahun" ditambahkan otomatis oleh controller.
     *
     * @var array<string, array{label: string, columns: array<int, array{0: string, 1: array<int, string>}>, dates: array<int, string>, link: string}>
     */
    private const KATEGORI = [
        'artikel' => [
            'label' => 'Artikel',
            'columns' => [
                ['Judul / Jurnal', ['nama_jurnal']],
                ['Indeksasi', ['indeksasi']],
                ['DOI', ['doi']],
            ],
            'dates' => ['tanggal_terbit'],
            'link' => 'tautan_jurnal',
        ],
        'buku' => [
            'label' => 'Buku',
            'columns' => [
                ['Judul Buku', ['judul_buku']],
                ['Penerbit', ['penerbit_buku']],
                ['ISBN', ['isbn']],
            ],
            'dates' => ['tanggal_terbit'],
            'link' => 'tautan_buku',
        ],
        'hki' => [
            'label' => 'HKI',
            'columns' => [
                ['Judul', ['judul_hak_cipta']],
                ['Jenis HKI', ['jenis_hki']],
                ['No. Sertifikat / Pendaftaran', ['nomor_sertifikat_hki', 'nomor_pendaftaran_hki']],
                ['Status', ['status_hki']],
            ],
            'dates' => ['tanggal_terbit_sertifikat_hki', 'tanggal_pendaftaran_hki'],
            'link' => 'tautan_hki',
        ],
        'seminar' => [
            'label' => 'Seminar',
            'columns' => [
                ['Seminar / Prosiding', ['nama_seminar', 'nama_prosiding']],
                ['Penyelenggara', ['penyelenggara']],
                ['Lokasi', ['lokasi_seminar']],
            ],
            'dates' => ['tanggal_seminar'],
            'link' => 'tautan_seminar',
        ],
    ];

    public function __construct(
        protected TridharmaService $tridharmaService,
    ) {}

    public function index(Request $request, string $jenis, string $kategori = 'artikel'): View|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);
        abort_unless(isset(self::KATEGORI[$kategori]), 404);

        $meta = self::KATEGORI[$kategori];
        $filters = $this->validatedFilters($request);

        $query = array_filter(
            $filters + ['kategori' => $kategori, 'per_page' => 10],
            static fn ($value) => $value !== null && $value !== ''
        );

        $viewData = [
            'kategori' => $kategori,
            'tabs' => array_map(static fn (array $item): string => $item['label'], self::KATEGORI),
            'kategoriLabel' => $meta['label'],
            'headers' => array_merge(
                ['Penulis'],
                array_map(static fn (array $column): string => $column[0], $meta['columns']),
                ['Tahun']
            ),
            'filters' => $filters,
            'jenis' => $jenis,
            'jenisLabel' => self::JENIS_KEGIATAN[$jenis],
        ];

        try {
            $response = $this->tridharmaService->getLuaranList(
                request()->routeIs('luaran.tridharma.index') ? $query + ['jenis' => $jenis] : $query
            );
        } catch (ConnectionException) {
            return view('luaran.index', $viewData + $this->emptyState('Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.'));
        }

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (! $response->successful() || $response->json('success') === false) {
            $message = $response->json('message');

            return view('luaran.index', $viewData + $this->emptyState(
                is_string($message) && $message !== '' ? $message : 'Gagal memuat data luaran.'
            ));
        }

        $data = $response->json('data', []);
        $items = is_array($data['data'] ?? null) ? $data['data'] : (is_array($data) ? $data : []);

        $rows = array_map(
            fn (array $item): array => $this->toRow($item, $meta),
            array_values(array_filter($items, fn (mixed $item): bool => is_array($item) && $this->hasCategoryContent($item, $kategori)))
        );

        return view('luaran.index', $viewData + [
            'rows' => $rows,
            'pagination' => [
                'total' => $data['total'] ?? count($rows),
                'per_page' => $data['per_page'] ?? 10,
                'current_page' => $data['current_page'] ?? 1,
                'last_page' => $data['last_page'] ?? 1,
                'from' => $data['from'] ?? null,
                'to' => $data['to'] ?? null,
            ],
            'apiError' => null,
        ]);
    }

    /**
     * Unduh luaran per kategori (sesuai filter aktif) sebagai Excel. File dibuat oleh backend.
     */
    public function export(Request $request, string $jenis, string $kategori): Response|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);
        abort_unless(isset(self::KATEGORI[$kategori]), 404);

        $filters = $this->validatedFilters($request);
        unset($filters['page']);

        try {
            $response = $this->tridharmaService->exportLuaran(array_filter(
                $filters + ['kategori' => $kategori] + (request()->routeIs('luaran.tridharma.export') ? ['jenis' => $jenis] : []),
                static fn ($value) => $value !== null && $value !== ''
            ));
        } catch (ConnectionException) {
            return $this->exportFailed('Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
        }

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return $this->exportFailed((string) ($response->json('message') ?: 'Gagal mengunduh data Excel.'));
        }

        return response($response->body(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="luaran-'.$kategori.'_'.now()->format('Ymd').'.xlsx"',
        ]);
    }

    public function show(string $jenis, string $kategori, int $id): View|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis], self::KATEGORI[$kategori]), 404);

        try {
            $response = $this->tridharmaService->getLuaran($id);
        } catch (ConnectionException) {
            return redirect()->route('luaran.tridharma.index', [$jenis, $kategori])->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Detail',
                'message' => 'Tidak dapat terhubung ke server Tridharma.',
            ]);
        }

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        abort_if($response->status() === 404, 404);

        if (! $response->successful() || ! is_array($response->json('data'))) {
            return redirect()->route('luaran.tridharma.index', [$jenis, $kategori])->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memuat Detail',
                'message' => (string) ($response->json('message') ?: 'Detail luaran tidak tersedia.'),
            ]);
        }

        return view('luaran.show', [
            'jenis' => $jenis,
            'jenisLabel' => self::JENIS_KEGIATAN[$jenis],
            'kategori' => $kategori,
            'kategoriLabel' => self::KATEGORI[$kategori]['label'],
            'luaran' => $response->json('data'),
        ]);
    }

    public function legacyIndex(Request $request, string $kategori = 'artikel'): View|RedirectResponse
    {
        return $this->index($request, 'penelitian', (string) $request->query('kategori', $kategori));
    }

    public function legacyExport(Request $request, string $kategori): Response|RedirectResponse
    {
        return $this->export($request, 'penelitian', $kategori);
    }

    public function create(Request $request, string $jenis): View|RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);

        try {
            $kegiatanResponse = $this->tridharmaService->getKegiatanList([
                'jenis' => $jenis,
                'per_page' => 200,
            ]);
            $jenisResponse = $this->tridharmaService->getJenisLuaranList(['per_page' => 200]);
            $dosenResponse = $this->tridharmaService->getDosenList(['per_page' => 200, 'status' => 'DTPS']);
        } catch (ConnectionException) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Tidak dapat terhubung ke server Tridharma.',
            ]);
        }

        if ($kegiatanResponse->status() === 401 || $jenisResponse->status() === 401 || $dosenResponse->status() === 401) {
            return redirect()->route('login');
        }

        return view('luaran.create', [
            'jenis' => $jenis,
            'jenisLabel' => self::JENIS_KEGIATAN[$jenis],
            'kategori' => $request->query('kategori', 'artikel'),
            'idKegiatan' => $request->query('id_kegiatan'),
            'kegiatan' => $kegiatanResponse->json('data.data', []),
            'jenisLuaran' => $jenisResponse->json('data.data', []),
            'dosen' => $dosenResponse->json('data.data', []),
        ]);
    }

    public function store(Request $request, string $jenis): RedirectResponse
    {
        abort_unless(isset(self::JENIS_KEGIATAN[$jenis]), 404);

        $validated = $request->validate([
            'id_kegiatan' => ['required', 'integer'],
            'id_dosen' => ['required', 'integer'],
            'id_jenis_luaran' => ['required', 'integer'],
            'judul_buku' => ['nullable', 'string', 'max:255'],
            'nama_jurnal' => ['nullable', 'string', 'max:255'],
            'nama_seminar' => ['nullable', 'string', 'max:255'],
            'nama_prosiding' => ['nullable', 'string', 'max:255'],
            'judul_hak_cipta' => ['nullable', 'string', 'max:255'],
            'nama_produk' => ['nullable', 'string', 'max:255'],
            'tahun_terbit' => ['nullable', 'integer', 'digits:4'],
            'doi' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'tautan_jurnal' => ['nullable', 'url', 'max:2048'],
            'tautan_buku' => ['nullable', 'url', 'max:2048'],
            'tautan_seminar' => ['nullable', 'url', 'max:2048'],
            'tautan_hki' => ['nullable', 'url', 'max:2048'],
            'tautan_produk' => ['nullable', 'url', 'max:2048'],
        ]);

        $response = $this->tridharmaService->createLuaran($validated);

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        if (! $response->successful()) {
            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menyimpan',
                'message' => (string) ($response->json('message') ?: 'Data luaran tidak berhasil disimpan.'),
            ]);
        }

        $kategori = $request->input('kategori', 'artikel');

        return redirect()->route('luaran.tridharma.index', [$jenis, $kategori])->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data luaran berhasil ditambahkan.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'tahun' => ['nullable', 'integer', 'between:1900,2100'],
            'id_kegiatan' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function exportFailed(string $message): RedirectResponse
    {
        return back()->with('toast', [
            'type' => 'error',
            'title' => 'Unduh Gagal',
            'message' => $message,
        ]);
    }

    /**
     * Judul utama sebuah luaran, apa pun jenisnya.
     *
     * @param  array<string, mixed>  $luaran
     */
    public static function judul(array $luaran): string
    {
        foreach (['judul_buku', 'nama_jurnal', 'nama_prosiding', 'nama_seminar', 'judul_hak_cipta', 'nama_produk'] as $key) {
            $value = trim((string) ($luaran[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '-';
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array{label: string, columns: array<int, array{0: string, 1: array<int, string>}>, dates: array<int, string>, link: string}  $meta
     * @return array{cells: array<int, string>, link: ?string}
     */
    private function toRow(array $item, array $meta): array
    {
        $cells = [(string) ($item['nama_penulis'] ?? $item['dosen']['nama_dosen'] ?? '-')];

        foreach ($meta['columns'] as [, $keys]) {
            $cells[] = $this->firstFilled($item, $keys);
        }

        $cells[] = $this->tahun($item, $meta['dates']);

        $link = trim((string) ($item[$meta['link']] ?? ''));

        return [
            'id' => $item['id_luaran'] ?? null,
            'cells' => $cells,
            'link' => preg_match('~^https?://~i', $link) === 1 && filter_var($link, FILTER_VALIDATE_URL) ? $link : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $keys
     */
    private function firstFilled(array $item, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($item[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '-';
    }

    /**
     * Hindari menampilkan data yang tidak memiliki field utama kategorinya.
     *
     * @param  array<string, mixed>  $item
     */
    private function hasCategoryContent(array $item, string $kategori): bool
    {
        $fields = match ($kategori) {
            'artikel' => ['nama_jurnal'],
            'buku' => ['judul_buku'],
            'hki' => ['judul_hak_cipta'],
            'seminar' => ['nama_seminar', 'nama_prosiding'],
        };

        foreach ($fields as $field) {
            if (trim((string) ($item[$field] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $dateKeys
     */
    private function tahun(array $item, array $dateKeys): string
    {
        if (! empty($item['tahun_terbit'])) {
            return (string) $item['tahun_terbit'];
        }

        foreach ($dateKeys as $key) {
            if (! empty($item[$key]) && preg_match('/^\d{4}/', (string) $item[$key], $matches)) {
                return $matches[0];
            }
        }

        return '-';
    }

    /**
     * @return array{rows: array<int, mixed>, pagination: array<string, int>, apiError: string}
     */
    private function emptyState(string $message): array
    {
        return [
            'rows' => [],
            'pagination' => ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
            'apiError' => $message,
        ];
    }
}
