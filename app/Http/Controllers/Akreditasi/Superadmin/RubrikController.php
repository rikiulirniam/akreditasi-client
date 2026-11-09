<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RubrikController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId, int|string $indikatorId): View
    {
        $indikator = null;
        $subkriteria = null;
        $kriteria = null;
        $instrumen = null;
        $lembaga = null;
        $rubriks = [];

        try {
            $indikatorRes = $this->akreditasiService->getIndikator($indikatorId);
            if ($indikatorRes->successful()) {
                $indikator = $indikatorRes->json('data');
            }
        } catch (\Throwable) {
            // fallback
        }

        try {
            $subkriteriaRes = $this->akreditasiService->getSubkriteria($subkriteriaId);
            if ($subkriteriaRes->successful()) {
                $subkriteria = $subkriteriaRes->json('data');
            }
        } catch (\Throwable) {
            // fallback
        }

        try {
            $kriteriaRes = $this->akreditasiService->getKriteriaDetail($kriteriaId);
            if ($kriteriaRes->successful()) {
                $kriteria = $kriteriaRes->json('data');
            }
        } catch (\Throwable) {
            // fallback
        }

        try {
            $instrumenRes = $this->akreditasiService->getInstrumen($instrumenId);
            if ($instrumenRes->successful()) {
                $instrumen = $instrumenRes->json('data');
            }
        } catch (\Throwable) {
            // fallback
        }

        try {
            $lembagaRes = $this->akreditasiService->getLembagaAkreditasiDetail($lembagaId);
            if ($lembagaRes->successful()) {
                $lembaga = $lembagaRes->json('data');
            }
        } catch (\Throwable) {
            // fallback
        }

        if (! $lembaga) {
            $lembaga = $instrumen['versi_instrumen']['lembaga'] ?? [
                'id' => $lembagaId,
                'nama_lembaga' => 'LAM INFOKOM',
            ];
        }

        if (! $instrumen) {
            $instrumen = [
                'id' => $instrumenId,
                'jenis_instrumen' => ['nama_jenis' => 'Instrumen Untuk Unggul'],
                'jenjang' => ['nama_jenjang' => 'Sarjana'],
            ];
        }

        $jenis = $instrumen['jenis_instrumen']['nama_jenis'] ?? ($instrumen['jenis_akreditasi']['nama_jenis'] ?? ($instrumen['nama_instrumen'] ?? 'Instrumen'));
        $jenjang = $instrumen['jenjang']['nama_jenjang'] ?? '';
        $instrumenTitle = $jenjang ? "{$jenis} ({$jenjang})" : $jenis;

        if (! $kriteria) {
            $kriteria = [
                'id' => $kriteriaId,
                'kode_kriteria' => 'C1',
                'nama_kriteria' => 'Visi, Misi, Tujuan, dan Strategi',
            ];
        }

        if (! $subkriteria) {
            $subkriteria = [
                'id' => $subkriteriaId,
                'kode_subkriteria' => 'C1.1',
                'nama_subkriteria' => 'Kejelasan & kerealistisan VMTS',
            ];
        }

        if (! $indikator) {
            $indikator = [
                'id' => $indikatorId,
                'kode_indikator' => 'C1.1.1',
                'deskripsi_indikator' => 'Ketersediaan dokumen formal terkait kejelasan visi dan misi',
            ];
        }

        $rubriks = $indikator['rubriks'] ?? [];
        if (empty($rubriks)) {
            try {
                $rubriks = $this->akreditasiService->getRubriks(['id_indikator' => $indikatorId])->json('data') ?? [];
            } catch (\Throwable) {
                $rubriks = [];
            }
        }

        return view('pages.superadmin.rubrik', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'instrumenId' => $instrumenId,
            'instrumen' => $instrumen,
            'instrumenTitle' => $instrumenTitle,
            'kriteriaId' => $kriteriaId,
            'kriteria' => $kriteria,
            'subkriteriaId' => $subkriteriaId,
            'subkriteria' => $subkriteria,
            'indikatorId' => $indikatorId,
            'indikator' => $indikator,
            'rubriks' => $rubriks,
        ]);
    }

    public function store(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId, int|string $indikatorId): RedirectResponse
    {
        $validated = $request->validate([
            'skor' => 'required|integer|min:1|max:4',
            'deskripsi_rubrik' => 'nullable|string|max:1000',
            'deskripsi_kondisi' => 'nullable|string|max:1000',
        ]);

        $deskripsi = $validated['deskripsi_rubrik'] ?? ($validated['deskripsi_kondisi'] ?? '');

        if (empty($deskripsi)) {
            return back()->with('error', 'Deskripsi rubrik penilaian wajib diisi.');
        }

        $payload = [
            'id_indikator' => $indikatorId,
            'skor' => (int) $validated['skor'],
            'deskripsi_rubrik' => $deskripsi,
        ];

        try {
            $response = $this->akreditasiService->createRubrik($payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.rubrik', [$lembagaId, $instrumenId, $kriteriaId, $subkriteriaId, $indikatorId])
                    ->with('success', 'Rubrik penilaian berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan rubrik penilaian.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function update(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId, int|string $indikatorId, int|string $rubrikId): RedirectResponse
    {
        $validated = $request->validate([
            'skor' => 'required|integer|min:1|max:4',
            'deskripsi_rubrik' => 'nullable|string|max:1000',
            'deskripsi_kondisi' => 'nullable|string|max:1000',
        ]);

        $deskripsi = $validated['deskripsi_rubrik'] ?? ($validated['deskripsi_kondisi'] ?? '');

        if (empty($deskripsi)) {
            return back()->with('error', 'Deskripsi rubrik penilaian wajib diisi.');
        }

        $payload = [
            'id_indikator' => $indikatorId,
            'skor' => (int) $validated['skor'],
            'deskripsi_rubrik' => $deskripsi,
        ];

        try {
            $response = $this->akreditasiService->updateRubrik($rubrikId, $payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.rubrik', [$lembagaId, $instrumenId, $kriteriaId, $subkriteriaId, $indikatorId])
                    ->with('success', 'Rubrik penilaian berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui rubrik penilaian.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId, int|string $indikatorId, int|string $rubrikId): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteRubrik($rubrikId);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.rubrik', [$lembagaId, $instrumenId, $kriteriaId, $subkriteriaId, $indikatorId])
                    ->with('success', 'Rubrik penilaian berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus rubrik penilaian.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
