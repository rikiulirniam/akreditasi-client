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
        ]);
    }

    public function show($id)
    {
        $kegiatan = $this->loadKegiatan($id);

        if ($kegiatan instanceof RedirectResponse) {
            return $kegiatan;
        }

        return view('penelitian-pkm.show', compact('kegiatan'));
    }

    public function create()
    {
        return view('penelitian-pkm.create', ['jenisKegiatan' => $this->jenisKegiatan()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $payload = $this->bersihkan($validated) + ['in_kind' => '-', 'anggota' => [], 'pendanaan' => []];

        return $this->simpan(fn () => $this->tridharmaService->createKegiatan($payload), 'Kegiatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kegiatan = $this->loadKegiatan($id);

        if ($kegiatan instanceof RedirectResponse) {
            return $kegiatan;
        }

        return view('penelitian-pkm.edit', ['kegiatan' => $kegiatan, 'jenisKegiatan' => $this->jenisKegiatan()]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules());
        $existing = $this->loadKegiatan($id);

        if ($existing instanceof RedirectResponse) {
            return $existing;
        }

        $dipertahankan = Arr::only($existing, [
            'afiliasi', 'litabmas_sebelumnya', 'jenis_skim', 'tahun_pelaksanaan_ke',
            'nomor_sk_penugasan', 'tanggal_sk_penugasan', 'in_kind',
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

        return redirect()->route('penelitian-pkm.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    private function rules()
    {
        return [
            'id_jenis_kegiatan' => ['required', 'integer'],
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

        return redirect()->route('penelitian-pkm.index')->with('success', $pesanBerhasil);
    }

    private function loadKegiatan($id)
    {
        try {
            $response = $this->tridharmaService->getKegiatan($id);
        } catch (ConnectionException) {
            return redirect()->route('penelitian-pkm.index')->with('error', self::KONEKSI_GAGAL);
        }

        if ($response->status() === 401) {
            return redirect()->route('login');
        }

        if ($response->status() === 404) {
            abort(404);
        }

        if (! $response->successful()) {
            return redirect()->route('penelitian-pkm.index')->with('error', $response->json('message') ?: 'Gagal memuat data kegiatan.');
        }

        return $response->json('data', []);
    }

    private function canManage()
    {
        $user = session('user', []);
        $role = strtolower((string) ($user['role'] ?? $user['role_name'] ?? $user['roles'][0]['name'] ?? ''));

        return in_array($role, ['administrator', 'admin', 'superadmin'], true);
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