<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KriteriaController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(int|string $lembagaId, int|string $instrumenId): View
    {
        $instrumen = null;
        $lembaga = null;
        $kriterias = [];

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

        $kriterias = $instrumen['kriterias'] ?? [];
        if (empty($kriterias)) {
            try {
                $kriterias = $this->akreditasiService->getKriteria(['id_instrumen' => $instrumenId])->json('data') ?? [];
            } catch (\Throwable) {
                $kriterias = [];
            }
        }

        return view('pages.superadmin.kriteria', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'instrumenId' => $instrumenId,
            'instrumen' => $instrumen,
            'instrumenTitle' => $instrumenTitle,
            'kriterias' => $kriterias,
        ]);
    }

    public function store(Request $request, int|string $lembagaId, int|string $instrumenId): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kriteria' => 'required|string|max:50',
            'nama_kriteria' => 'required|string|max:255',
        ]);

        $payload = array_merge($validated, [
            'id_instrumen' => $instrumenId,
        ]);

        try {
            $response = $this->akreditasiService->createKriteria($payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.kriteria', [$lembagaId, $instrumenId])
                    ->with('success', 'Kriteria berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan kriteria.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function update(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kriteria' => 'required|string|max:50',
            'nama_kriteria' => 'required|string|max:255',
        ]);

        try {
            $response = $this->akreditasiService->updateKriteria($kriteriaId, $validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.kriteria', [$lembagaId, $instrumenId])
                    ->with('success', 'Kriteria berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui kriteria.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteKriteria($kriteriaId);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.kriteria', [$lembagaId, $instrumenId])
                    ->with('success', 'Kriteria berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus kriteria.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
