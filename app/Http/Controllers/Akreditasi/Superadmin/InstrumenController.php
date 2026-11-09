<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstrumenController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    /**
     * Menampilkan halaman detail versi instrumen beserta daftar instrumen di dalamnya.
     */
    public function index(Request $request, int|string $lembagaId, int|string|null $versiId = null): View|RedirectResponse
    {
        $lembaga = null;
        $versis = [];
        $versi = null;
        $instrumens = [];

        // 1. Resolve Lembaga Details
        try {
            $lembagaRes = $this->akreditasiService->getLembagaAkreditasiDetail($lembagaId);
            if ($lembagaRes->successful()) {
                $lembaga = $lembagaRes->json('data');
            }
        } catch (\Throwable) {
            // fallback
        }

        if (! $lembaga) {
            try {
                $allLembaga = $this->akreditasiService->getLembagaAkreditasi()->json('data') ?? [];
                foreach ($allLembaga as $l) {
                    if ((string) ($l['id'] ?? '') === (string) $lembagaId) {
                        $lembaga = $l;
                        break;
                    }
                }
            } catch (\Throwable) {
                // ignore
            }
        }

        if (! $lembaga) {
            $lembaga = [
                'id' => $lembagaId,
                'nama_lembaga' => 'LAM INFOKOM',
                'keterangan' => 'Lembaga Akreditasi Mandiri Informatika dan Komputer',
            ];
        }

        // 2. Resolve Versi Instrumen list for this Lembaga
        $versis = $lembaga['versi_instrumen'] ?? [];
        if (empty($versis)) {
            try {
                $versis = $this->akreditasiService->getVersiInstrumen(['id_lembaga' => $lembagaId])->json('data') ?? [];
            } catch (\Throwable) {
                $versis = [];
            }
        }

        // 3. Resolve Current Versi Instrumen
        $resolvedVersiId = $versiId ?? $request->query('id_versi_instrumen') ?? $request->query('versi_id');

        if (! $resolvedVersiId) {
            if (! empty($versis)) {
                $selected = collect($versis)->firstWhere('status_aktif', true) ?? ($versis[0] ?? null);
                if ($selected && isset($selected['id'])) {
                    return redirect()->route('akreditasi.superadmin.instrumen', [
                        'lembagaId' => $lembagaId,
                        'versiId' => $selected['id'],
                    ]);
                }
            } else {
                return redirect()->route('akreditasi.superadmin.versi_instrumen', $lembagaId)
                    ->with('error', 'Lembaga ini belum memiliki versi instrumen. Silakan tambahkan versi instrumen terlebih dahulu.');
            }
        } else {
            // Cari dari daftar versis lembaga
            $versi = collect($versis)->firstWhere('id', (int) $resolvedVersiId)
                ?? collect($versis)->firstWhere('id', (string) $resolvedVersiId);

            if (! $versi) {
                try {
                    $versiRes = $this->akreditasiService->getVersiInstrumenDetail($resolvedVersiId);
                    if ($versiRes->successful()) {
                        $versi = $versiRes->json('data');
                    }
                } catch (\Throwable) {
                    // fallback
                }
            }

            if (! $versi) {
                $versi = [
                    'id' => $resolvedVersiId,
                    'nomor_versi' => '1.0',
                    'status_aktif' => true,
                    'tanggal_berlaku' => null,
                    'id_lembaga' => $lembagaId,
                ];
            }
        }

        // 4. Fetch Instrumen for this Versi Instrumen
        try {
            $allInstrumens = $this->akreditasiService->getInstrumens()->json('data') ?? [];
            $targetVersiId = (string) ($versi['id'] ?? $resolvedVersiId);

            $instrumens = array_values(array_filter($allInstrumens, function ($item) use ($targetVersiId) {
                $itemVersiId = (string) ($item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? ''));

                return $itemVersiId === $targetVersiId;
            }));
        } catch (\Throwable) {
            $instrumens = [];
        }

        return view('pages.superadmin.instrumen', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'versiId' => $versi['id'] ?? $resolvedVersiId,
            'versi' => $versi,
            'versis' => $versis,
            'instrumens' => $instrumens,
        ]);
    }

    /**
     * Menyimpan data instrumen baru pada versi instrumen tertentu.
     */
    public function store(Request $request, int|string $lembagaId, int|string|null $versiId = null): RedirectResponse
    {
        $resolvedVersiId = $request->input('id_versi_instrumen') ?? $versiId;

        $request->merge([
            'id_versi_instrumen' => $resolvedVersiId,
        ]);

        $validated = $request->validate([
            'id_versi_instrumen' => 'required|integer',
            'id_jenjang' => 'required|integer',
            'id_jenis_instrumen' => 'required|integer',
        ]);

        try {
            $response = $this->akreditasiService->createInstrumen($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.instrumen', [
                    'lembagaId' => $lembagaId,
                    'versiId' => $validated['id_versi_instrumen'],
                ])->with('success', 'Instrumen berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan instrumen.'));
        } catch (\Throwable) {
            return back()->with('error', 'Terjadi kesalahan saat menambahkan instrumen.');
        }
    }

    /**
     * Memperbarui data instrumen.
     */
    public function update(Request $request, int|string $lembagaId, int|string $instrumenId): RedirectResponse
    {
        $validated = $request->validate([
            'id_versi_instrumen' => 'required|integer',
            'id_jenjang' => 'required|integer',
            'id_jenis_instrumen' => 'required|integer',
        ]);

        try {
            $response = $this->akreditasiService->updateInstrumen($instrumenId, $validated);
            if ($response->successful()) {
                return back()->with('success', 'Instrumen berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Menghapus data instrumen.
     */
    public function destroy(int|string $lembagaId, int|string $instrumenId): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteInstrumen($instrumenId);
            if ($response->successful()) {
                return back()->with('success', 'Instrumen berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
