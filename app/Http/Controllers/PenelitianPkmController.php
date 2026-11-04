<?php

namespace App\Http\Controllers;

use App\Services\TridharmaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class PenelitianPkmController extends Controller
{
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
            $response = $this->tridharmaService->getKegiatanList(array_filter($filters));

            if ($response->status() === 401) {
                return redirect()->route('login')->with('toast', [
                    'type' => 'warning',
                    'title' => 'Sesi Berakhir',
                    'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
                ]);
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
            logger()->error($e->getMessage());
            $apiError = 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.';
        }

        return view('penelitian-pkm.index', compact('kegiatan', 'pagination', 'filters', 'apiError'));
    }

        public function create()
    {
        return view('penelitian-pkm.create', ['jenisKegiatan' => $this->jenisKegiatan()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_jenis_kegiatan' => ['required', 'integer'],
            'judul_kegiatan' => ['required', 'string', 'max:255'],
            'status_kegiatan' => ['required', 'string', 'max:50'],
            'tahun_usulan' => ['required', 'integer', 'between:1900,2100'],
            'tahun_kegiatan' => ['required', 'integer', 'between:1900,2100'],
            'tahun_pelaksanaan' => ['required', 'integer', 'between:1900,2100'],
            'lokasi_kegiatan' => ['nullable', 'string', 'max:255'],
            'lama_kegiatan' => ['nullable', 'string', 'max:50'],
        ]);

        $payload = array_filter($validated, fn ($value) => $value !== null && $value !== '');
        $payload += ['in_kind' => '-', 'anggota' => [], 'pendanaan' => []];

        try {
            $response = $this->tridharmaService->createKegiatan($payload);
        } catch (ConnectionException) {
            return back()->withInput()->with('error', 'Tidak dapat terhubung ke server Tridharma. Silakan coba lagi nanti.');
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

        return redirect()->route('penelitian-pkm.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

       private function jenisKegiatan()
    {
        return [
            ['id_jenis_kegiatan' => 1, 'nama_jenis_kegiatan' => 'Penelitian'],
            ['id_jenis_kegiatan' => 2, 'nama_jenis_kegiatan' => 'PkM'],
        ];
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