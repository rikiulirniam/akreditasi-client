<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PenelitianPkmController extends Controller
{
    private const KONEKSI_GAGAL = 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.';

    private const JENIS = [
        'penelitian' => ['id' => 1, 'nama' => 'Penelitian'],
        'pkm' => ['id' => 2, 'nama' => 'PKM'],
    ];

    public function __construct(protected TridharmaService $tridharmaService) {}

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

        return view('penelitian-pkm.show', ['kegiatan' => $kegiatan] + $this->konteks());
    }

    public function create()
    {
        return view('penelitian-pkm.create', $this->konteks());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $payload = $this->bersihkan($validated) + [
            'id_jenis_kegiatan' => self::JENIS[$this->rute()]['id'],
            'in_kind' => '-',
            'anggota' => [],
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

        return view('penelitian-pkm.edit', ['kegiatan' => $kegiatan] + $this->konteks());
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules());
        $existing = $this->loadKegiatan($id);

        if ($existing instanceof RedirectResponse) {
            return $existing;
        }

        $dipertahankan = Arr::only($existing, [
            'id_jenis_kegiatan', 'afiliasi', 'litabmas_sebelumnya', 'jenis_skim',
            'tahun_pelaksanaan_ke', 'nomor_sk_penugasan', 'tanggal_sk_penugasan', 'in_kind',
        ]);
        $payload = $this->bersihkan($validated) + array_filter($dipertahankan, fn ($value) => $value !== null);

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
            'tahun_usulan' => ['required', 'integer', 'between:1900,2100'],
            'tahun_kegiatan' => ['required', 'integer', 'between:1900,2100'],
            'tahun_pelaksanaan' => ['required', 'integer', 'between:1900,2100'],
            'lokasi_kegiatan' => ['nullable', 'string', 'max:255'],
            'lama_kegiatan' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function bersihkan($data)
    {
        return array_filter($data, fn ($value) => $value !== null && $value !== '');
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
                return $item['dosen']['nama_dosen'] ?? $item['mahasiswa']['nama_mahasiswa'] ?? $item['nama'] ?? '-';
            }
        }

        return '-';
    }
}