<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubkriteriaController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId): View
    {
        $kriteria = null;
        $instrumen = null;
        $lembaga = null;
        $subkriterias = [];

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

        $subkriterias = $kriteria['subkriteria'] ?? [];
        if (empty($subkriterias)) {
            try {
                $subkriterias = $this->akreditasiService->getSubkriterias(['id_kriteria' => $kriteriaId])->json('data') ?? [];
            } catch (\Throwable) {
                $subkriterias = [];
            }
        }

        return view('pages.superadmin.subkriteria', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'instrumenId' => $instrumenId,
            'instrumen' => $instrumen,
            'instrumenTitle' => $instrumenTitle,
            'kriteriaId' => $kriteriaId,
            'kriteria' => $kriteria,
            'subkriterias' => $subkriterias,
        ]);
    }

    public function store(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId): RedirectResponse
    {
        $validated = $request->validate([
            'kode_subkriteria' => 'required|string|max:50',
            'nama_subkriteria' => 'required|string|max:255',
        ]);

        $payload = array_merge($validated, [
            'id_kriteria' => $kriteriaId,
        ]);

        try {
            $response = $this->akreditasiService->createSubkriteria($payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.subkriteria', [$lembagaId, $instrumenId, $kriteriaId])
                    ->with('success', 'Subkriteria berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan subkriteria.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function update(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId): RedirectResponse
    {
        $validated = $request->validate([
            'kode_subkriteria' => 'required|string|max:50',
            'nama_subkriteria' => 'required|string|max:255',
        ]);

        try {
            $response = $this->akreditasiService->updateSubkriteria($subkriteriaId, $validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.subkriteria', [$lembagaId, $instrumenId, $kriteriaId])
                    ->with('success', 'Subkriteria berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui subkriteria.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteSubkriteria($subkriteriaId);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.subkriteria', [$lembagaId, $instrumenId, $kriteriaId])
                    ->with('success', 'Subkriteria berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus subkriteria.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
