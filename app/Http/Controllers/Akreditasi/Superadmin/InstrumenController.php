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

    public function index(int|string $lembagaId): View
    {
        $lembaga = null;
        $versis = [];
        $instrumens = [];
        $versiAktif = null;

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

        // Versi Instrumen
        $versis = $lembaga['versi_instrumen'] ?? [];
        if (empty($versis)) {
            try {
                $versis = $this->akreditasiService->getVersiInstrumen(['id_lembaga' => $lembagaId])->json('data') ?? [];
            } catch (\Throwable) {
                $versis = [];
            }
        }

        if (! empty($versis)) {
            $versiAktif = collect($versis)->firstWhere('status_aktif', true) ?? ($versis[0] ?? null);
        }

        // Fetch Instrumen
        try {
            $allInstrumens = $this->akreditasiService->getInstrumens()->json('data') ?? [];
            $versiIds = collect($versis)->pluck('id')->filter()->map(fn ($id) => (string) $id)->all();

            $instrumens = array_values(array_filter($allInstrumens, function ($item) use ($versiIds, $lembagaId) {
                $itemLembagaId = (string) ($item['versi_instrumen']['id_lembaga'] ?? ($item['versi_instrumen']['lembaga']['id'] ?? ''));
                $itemVersiId = (string) ($item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? ''));

                if ($itemLembagaId !== '' && $itemLembagaId === (string) $lembagaId) {
                    return true;
                }

                return ! empty($versiIds) && in_array($itemVersiId, $versiIds, true);
            }));
        } catch (\Throwable) {
            $instrumens = [];
        }

        return view('pages.superadmin.instrumen', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'versis' => $versis,
            'versiAktif' => $versiAktif,
            'instrumens' => $instrumens,
        ]);
    }

    public function store(Request $request, int|string $lembagaId): RedirectResponse
    {
        $validated = $request->validate([
            'id_versi_instrumen' => 'required|integer',
            'id_jenjang' => 'required|integer',
            'id_jenis_instrumen' => 'required|integer',
        ]);

        try {
            $response = $this->akreditasiService->createInstrumen($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.instrumen', $lembagaId)
                    ->with('success', 'Instrumen berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan instrumen.'));
        } catch (\Throwable) {
            return back()->with('error', 'Terjadi kesalahan saat menambahkan instrumen.');
        }
    }

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
                return redirect()->route('akreditasi.superadmin.instrumen', $lembagaId)
                    ->with('success', 'Instrumen berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(int|string $lembagaId, int|string $instrumenId): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteInstrumen($instrumenId);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.instrumen', $lembagaId)
                    ->with('success', 'Instrumen berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
