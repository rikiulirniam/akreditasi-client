<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PenelitianPkmController extends Controller
{
    private const KONEKSI_GAGAL = 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.';

    private const JENIS = [
        'penelitian' => ['id' => 1, 'nama' => 'Penelitian', 'peran' => 'Peneliti'],
        'pkm' => ['id' => 2, 'nama' => 'PKM', 'peran' => 'Pelaksana'],
    ];

    public function __construct(
        protected TridharmaService $tridharmaService,
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status_kegiatan' => ['nullable', 'string', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $kegiatan = [];
        $apiError = null;
        $pagination = ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1, 'from' => null, 'to' => null];

        try {
            $response = $this->tridharmaService->getKegiatanList(
                array_filter($filters) + ['id_jenis_kegiatan' => self::JENIS[$this->rute()]['id']]
            );

            if ($response->status() === 401) {
                return redirect()->route('login');
            }

            if ($response->successful()) {
                $data = $response->json('data', []);
                $kegiatan = array_map(
                    fn ($item) => $item + ['ketua' => $this->namaKetua($item['anggota'] ?? [])],
                    $data['data'] ?? []
                );
                $pagination = [
                    'total' => $data['total'] ?? 0,
                    'per_page' => $data['per_page'] ?? 10,
                    'current_page' => $data['current_page'] ?? 1,
                    'last_page' => $data['last_page'] ?? 1,
                    'from' => $data['from'] ?? null,
                    'to' => $data['to'] ?? null,
                ];
            } else {
                $apiError = $response->json('message') ?: 'Gagal memuat data kegiatan.';
            }
        } catch (ConnectionException) {
            $apiError = self::KONEKSI_GAGAL;
        }

        return view('penelitian-pkm.index', [
            'kegiatan' => $kegiatan,
            'pagination' => $pagination,
            'filters' => $filters,
            'apiError' => $apiError,
            'canManage' => $this->canManage(),
        ] + $this->konteks());
    }

    public function show($id)
    {
        $kegiatan = $this->loadKegiatan($id);

        if ($kegiatan instanceof RedirectResponse) {
            return $kegiatan;
        }

        if ($pindah = $this->halamanBenar($kegiatan, 'show', $id)) {
            return $pindah;
        }
        $kegiatan['anggota'] = $this->lengkapiMahasiswa($kegiatan['anggota'] ?? []);
        return view('penelitian-pkm.show', ['kegiatan' => $kegiatan] + $this->konteks());
    }

    public function create()
    {
        return view('penelitian-pkm.create', $this->konteks() + [
            'dosenList' => $this->daftarDosen(),
            'anggota' => ['ketua' => null, 'dosen' => [], 'mahasiswa' => [], 'lain' => []],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->pesan());
        $payload = $this->dataKegiatan($validated) + [
            'id_jenis_kegiatan' => self::JENIS[$this->rute()]['id'],
            'in_kind' => '-',
            'anggota' => $this->bangunAnggota($validated),
            'pendanaan' => [],
        ];

        return $this->simpan(fn () => $this->tridharmaService->createKegiatan($payload), 'Kegiatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kegiatan = $this->loadKegiatan($id);

        if ($kegiatan instanceof RedirectResponse) {
            return $kegiatan;
        }

        if ($pindah = $this->halamanBenar($kegiatan, 'edit', $id)) {
            return $pindah;
        }

        return view('penelitian-pkm.edit', $this->konteks() + [
            'kegiatan' => $kegiatan,
            'dosenList' => $this->daftarDosen(),
            'anggota' => $this->pecahAnggota($this->lengkapiMahasiswa($kegiatan['anggota'] ?? [])),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules(), $this->pesan());
        $existing = $this->loadKegiatan($id);

        if ($existing instanceof RedirectResponse) {
            return $existing;
        }

        $dipertahankan = Arr::only($existing, [
            'id_jenis_kegiatan', 'afiliasi', 'litabmas_sebelumnya', 'jenis_skim',
            'tahun_pelaksanaan_ke', 'nomor_sk_penugasan', 'tanggal_sk_penugasan', 'in_kind',
        ]);
        $payload = $this->dataKegiatan($validated)
            + ['anggota' => $this->bangunAnggota($validated)]
            + array_filter($dipertahankan, fn ($value) => $value !== null);

        return $this->simpan(fn () => $this->tridharmaService->updateKegiatan($id, $payload), 'Kegiatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $response = $this->tridharmaService->deleteKegiatan($id);
        } catch (ConnectionException) {
            return back()->with('error', self::KONEKSI_GAGAL);
        }

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        if (! $response->successful()) {
            return back()->with('error', $response->json('message') ?: 'Gagal menghapus kegiatan.');
        }

        return redirect()->route($this->rute() . '.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status_kegiatan' => ['nullable', 'string', 'max:50'],
        ]);
        $jenis = self::JENIS[$this->rute()];
        $rows = [];
        $page = 1;

        try {
            do {
                $response = $this->tridharmaService->getKegiatanList(
                    array_filter($filters) + ['id_jenis_kegiatan' => $jenis['id'], 'per_page' => 100, 'page' => $page]
                );

                if ($response->status() === 401) {
                    return redirect()->route('login');
                }

                if (! $response->successful()) {
                    return back()->with('error', $response->json('message') ?: 'Gagal mengunduh data.');
                }

                $data = $response->json('data', []);
                $rows = array_merge($rows, $data['data'] ?? []);
                $lastPage = min($data['last_page'] ?? 1, 50);
            } while ($page++ < $lastPage);
        } catch (ConnectionException) {
            return back()->with('error', self::KONEKSI_GAGAL);
        }

        $filename = strtolower($jenis['nama']) . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No', 'Judul', 'Jenis', 'Ketua', 'Skim', 'Afiliasi', 'Lokasi', 'Status', 'Tahun Usulan', 'Tahun Kegiatan', 'Tahun Pelaksanaan', 'Lama Kegiatan'], ';', '"', '');

            foreach ($rows as $index => $item) {
                fputcsv($out, array_map([$this, 'amankanSel'], [
                    $index + 1,
                    $item['judul_kegiatan'] ?? '',
                    $item['jenis_kegiatan']['nama_jenis_kegiatan'] ?? '',
                    $this->namaKetua($item['anggota'] ?? []),
                    $item['jenis_skim'] ?? '',
                    $item['afiliasi'] ?? '',
                    $item['lokasi_kegiatan'] ?? '',
                    $item['status_kegiatan'] ?? '',
                    $item['tahun_usulan'] ?? '',
                    $item['tahun_kegiatan'] ?? '',
                    $item['tahun_pelaksanaan'] ?? '',
                    $item['lama_kegiatan'] ?? '',
                ]), ';', '"', '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function amankanSel($nilai)
    {
        return is_string($nilai) && preg_match('/^[=+\-@\t\r]/', $nilai) ? "'" . $nilai : $nilai;
    }

    private function rules()
    {
        return [
            'judul_kegiatan' => ['required', 'string', 'max:255'],
            'status_kegiatan' => ['required', 'string', 'max:50'],
            'tahun_usulan' => ['required', 'integer', 'digits:4'],
            'tahun_kegiatan' => ['required', 'integer', 'digits:4'],
            'tahun_pelaksanaan' => ['required', 'integer', 'digits:4'],
            'lokasi_kegiatan' => ['nullable', 'string', 'max:255'],
            'lama_kegiatan' => ['nullable', 'string', 'max:50'],
            'ketua' => ['required', 'integer', 'min:1'],
            'anggota_dosen' => ['nullable', 'array'],
            'anggota_dosen.*' => ['nullable', 'integer', 'min:1'],
            'anggota_mahasiswa' => ['nullable', 'array'],
            'anggota_mahasiswa.*' => ['nullable', 'string', 'max:255'],
            'anggota_lain' => ['nullable', 'string'],
        ];
    }

    private function pesan()
    {
        return ['ketua.required' => 'Ketua wajib dipilih.'];
    }

    private function dataKegiatan($validated)
    {
        $data = Arr::except($validated, ['ketua', 'anggota_dosen', 'anggota_mahasiswa', 'anggota_lain']);

        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

        private function bangunAnggota($validated)
    {
        $peran = self::JENIS[$this->rute()]['peran'];
        $ketua = (int) $validated['ketua'];
        $anggota = [['jenis_anggota' => 'Dosen', 'id_dosen' => $ketua, 'peran' => "Ketua {$peran}"]];

        $dosenLain = array_diff(array_unique(array_map('intval', $validated['anggota_dosen'] ?? [])), [$ketua, 0]);

        foreach ($dosenLain as $idDosen) {
            $anggota[] = ['jenis_anggota' => 'Dosen', 'id_dosen' => $idDosen, 'peran' => "Anggota {$peran}"];
        }

        $mahasiswa = array_unique(array_filter(array_map('intval', array_keys($validated['anggota_mahasiswa'] ?? []))));

        foreach ($mahasiswa as $idMahasiswa) {
            $anggota[] = ['jenis_anggota' => 'Mahasiswa', 'id_mahasiswa' => $idMahasiswa, 'peran' => "Anggota {$peran}"];
        }

        $lain = json_decode($validated['anggota_lain'] ?? '[]', true);

        foreach (is_array($lain) ? $lain : [] as $item) {
            if (is_array($item)) {
                $anggota[] = Arr::only($item, ['jenis_anggota', 'id_dosen', 'id_mahasiswa', 'peran']);
            }
        }

        return $anggota;
    }

    private function pecahAnggota($anggota)
    {
        $ketua = null;
        $dosen = [];
        $mahasiswa = [];
        $lain = [];

        foreach ($anggota as $item) {
            $jenis = strtolower($item['jenis_anggota'] ?? '');

            if (! empty($item['id_dosen']) && in_array($jenis, ['', 'dosen'], true)) {
                if ($ketua === null && str_contains(strtolower($item['peran'] ?? ''), 'ketua')) {
                    $ketua = (int) $item['id_dosen'];
                } else {
                    $dosen[] = (int) $item['id_dosen'];
                }
            } elseif (! empty($item['id_mahasiswa']) && in_array($jenis, ['', 'mahasiswa'], true)) {
                $mahasiswa[(int) $item['id_mahasiswa']] = $this->labelMahasiswa($item);
            } else {
                $lain[] = Arr::only($item, ['jenis_anggota', 'id_dosen', 'id_mahasiswa', 'peran']);
            }
        }

        return compact('ketua', 'dosen', 'mahasiswa', 'lain');
    }

    private function labelMahasiswa($item)
    {
        $data = $item['mahasiswa'] ?? [];
        $nama = $data['nama_mahasiswa'] ?? $data['nama'] ?? $item['nama_mahasiswa'] ?? null;
        $nim = $data['nim'] ?? $item['nim'] ?? null;

        if (! $nama) {
            return 'Mahasiswa #' . $item['id_mahasiswa'];
        }

        return $nim ? "{$nama} ({$nim})" : $nama;
    }
        private function lengkapiMahasiswa($anggota)
    {
        foreach ($anggota as $index => $item) {
            $id = $item['id_mahasiswa'] ?? null;

            if (! $id || $this->labelMahasiswa($item) !== 'Mahasiswa #' . $id) {
                continue;
            }

            try {
                $response = $this->akademikService->getMahasiswaById($id);
            } catch (ConnectionException) {
                continue;
            }

            if ($response->successful()) {
                $data = $response->json('data', []);
                $anggota[$index]['nama_mahasiswa'] = $data['nama_mahasiswa'] ?? $data['nama'] ?? null;
                $anggota[$index]['nim'] = $data['nim'] ?? null;
            }
        }

        return $anggota;
    }

    public function cariMahasiswa(Request $request)
    {
        $kata = trim((string) $request->query('q', ''));

        if (mb_strlen($kata) < 2) {
            return response()->json([]);
        }

        try {
            $response = $this->akademikService->getMahasiswaList(['search' => $kata, 'per_page' => 500]);
        } catch (ConnectionException) {
            return response()->json(['message' => self::KONEKSI_GAGAL], 503);
        }

        if (! $response->successful()) {
            return response()->json(['message' => 'Gagal memuat data mahasiswa.'], $response->status() === 401 ? 401 : 502);
        }

        $raw = $response->json('data', []);
        $kunci = mb_strtolower($kata);

        $hasil = collect($raw['data'] ?? $raw)
            ->map(fn ($item) => [
                'id' => $item['id'] ?? null,
                'nama' => $item['nama_mahasiswa'] ?? $item['nama'] ?? '-',
                'nim' => $item['nim'] ?? '',
            ])
            ->filter(fn ($item) => $item['id'] && str_contains(mb_strtolower($item['nama'] . ' ' . $item['nim']), $kunci))
            ->take(15)
            ->values();

        return response()->json($hasil);
    }

    private function daftarDosen()
    {
        $dosen = [];
        $page = 1;

        try {
            do {
                $response = $this->tridharmaService->getDosenList(['per_page' => 100, 'page' => $page]);

                if (! $response->successful()) {
                    break;
                }

                $data = $response->json('data', []);

                foreach ($data['data'] ?? $data as $item) {
                    if (isset($item['id_dosen'])) {
                        $dosen[$item['id_dosen']] = $item['nama_dosen'] ?? '-';
                    }
                }

                $lastPage = min($data['last_page'] ?? 1, 20);
            } while ($page++ < $lastPage);
        } catch (ConnectionException) {
        }

        asort($dosen);

        return $dosen;
    }

    private function simpan($request, $pesanBerhasil)
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            return back()->withInput()->with('error', self::KONEKSI_GAGAL);
        }

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        if (! $response->successful()) {
            return back()
                ->withInput()
                ->withErrors($response->json('errors') ?? [])
                ->with('error', $response->json('message') ?: 'Gagal menyimpan kegiatan.');
        }

        return redirect()->route($this->rute() . '.index')->with('success', $pesanBerhasil);
    }

    private function loadKegiatan($id)
    {
        try {
            $response = $this->tridharmaService->getKegiatan($id);
        } catch (ConnectionException) {
            return redirect()->route($this->rute() . '.index')->with('error', self::KONEKSI_GAGAL);
        }

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        if ($response->status() === 404) {
            abort(404);
        }

        if (! $response->successful()) {
            return redirect()->route($this->rute() . '.index')->with('error', $response->json('message') ?: 'Gagal memuat data kegiatan.');
        }

        return $response->json('data', []);
    }

    private function halamanBenar($kegiatan, $aksi, $id)
    {
        $slug = collect(self::JENIS)->search(fn ($jenis) => $jenis['id'] === ($kegiatan['id_jenis_kegiatan'] ?? null));

        return $slug && $slug !== $this->rute() ? redirect()->route("{$slug}.{$aksi}", $id) : null;
    }

    private function rute()
    {
        $segmen = request()->segment(1);

        return array_key_exists($segmen, self::JENIS) ? $segmen : 'penelitian';
    }

    private function konteks()
    {
        $rute = $this->rute();

        return ['rute' => $rute, 'halaman' => self::JENIS[$rute]['nama']];
    }

    private function canManage()
    {
        $user = session('user', []);
        $role = strtolower((string) ($user['role'] ?? $user['role_name'] ?? $user['roles'][0]['name'] ?? ''));

        return in_array($role, ['administrator', 'admin', 'superadmin'], true);
    }

    private function namaKetua($anggota)
    {
        foreach ($anggota as $item) {
            if (str_contains(strtolower($item['peran'] ?? ''), 'ketua')) {
                return $item['dosen']['nama_dosen'] ?? $item['mahasiswa']['nama_mahasiswa'] ?? $item['nama_dosen'] ?? $item['nama'] ?? '-';
            }
        }

        return '-';
    }
}