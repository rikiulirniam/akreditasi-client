<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndikatorController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId): View
    {
        $subkriteria = null;
        $kriteria = null;
        $instrumen = null;
        $lembaga = null;
        $indikators = [];

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

        $indikators = $subkriteria['indikator'] ?? [];
        if (empty($indikators)) {
            try {
                $indikators = $this->akreditasiService->getIndikators(['id_subkriteria' => $subkriteriaId])->json('data') ?? [];
            } catch (\Throwable) {
                $indikators = [];
            }
        }

        return view('pages.superadmin.indikator', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'instrumenId' => $instrumenId,
            'instrumen' => $instrumen,
            'instrumenTitle' => $instrumenTitle,
            'kriteriaId' => $kriteriaId,
            'kriteria' => $kriteria,
            'subkriteriaId' => $subkriteriaId,
            'subkriteria' => $subkriteria,
            'indikators' => $indikators,
        ]);
    }

    public function store(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId): RedirectResponse
    {
        $validated = $request->validate([
            'kode_indikator' => 'required|string|max:50',
            'deskripsi_indikator' => 'required|string|max:1000',
            'parameter_penilaian' => 'nullable|string|max:1000',
            'bobot' => 'required|numeric|between:0,999999.99',
        ]);

        $payload = array_merge($validated, [
            'id_subkriteria' => $subkriteriaId,
            'bobot' => $validated['bobot'] ?? 1,
        ]);

        try {
            $response = $this->akreditasiService->createIndikator($payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.indikator', [$lembagaId, $instrumenId, $kriteriaId, $subkriteriaId])
                    ->with('success', 'Indikator berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan indikator.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function update(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId, int|string $indikatorId): RedirectResponse
    {
        $validated = $request->validate([
            'kode_indikator' => 'required|string|max:50',
            'deskripsi_indikator' => 'required|string|max:1000',
            'parameter_penilaian' => 'nullable|string|max:1000',
            'bobot' => 'required|numeric|between:0,999999.99',
        ]);

        try {
            $response = $this->akreditasiService->updateIndikator($indikatorId, $validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.indikator', [$lembagaId, $instrumenId, $kriteriaId, $subkriteriaId])
                    ->with('success', 'Indikator berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui indikator.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(Request $request, int|string $lembagaId, int|string $instrumenId, int|string $kriteriaId, int|string $subkriteriaId, int|string $indikatorId): JsonResponse|RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteIndikator($indikatorId);
            $message = $response->json('message');

            if ($request->wantsJson() || $request->ajax()) {
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message ?? 'Indikator berhasil dihapus.',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $message ?? 'Gagal menghapus indikator.',
                ], $response->status() ?: 422);
            }

            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.indikator', [$lembagaId, $instrumenId, $kriteriaId, $subkriteriaId])
                    ->with('success', 'Indikator berhasil dihapus.');
            }

            return back()->with('error', (string) ($message ?? 'Gagal menghapus indikator.'));
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
