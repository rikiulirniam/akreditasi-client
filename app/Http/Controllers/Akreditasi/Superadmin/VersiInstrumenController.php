<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VersiInstrumenController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    /**
     * Menampilkan halaman detail lembaga akreditasi beserta daftar versi instrumen.
     */
    public function index(Request $request, int|string|null $lembagaId = null): View|RedirectResponse
    {
        $lembagaId = $lembagaId ?? $request->query('id_lembaga');

        if (! $lembagaId) {
            return redirect()->route('akreditasi.superadmin.lembaga_akreditasi');
        }

        $lembaga = null;
        $versis = [];
        $allInstrumens = [];

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
                $lembagas = $this->akreditasiService->getLembagaAkreditasi()->json('data') ?? [];
                foreach ($lembagas as $l) {
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

        try {
            $versis = $this->akreditasiService->getVersiInstrumen(['id_lembaga' => $lembagaId])->json('data') ?? [];
        } catch (\Throwable) {
            $versis = [];
        }

        if (empty($versis) && ! empty($lembaga['versi_instrumen'])) {
            $versis = $lembaga['versi_instrumen'];
        }

        // Ambil data instrumen untuk menghitung jumlah instrumen per versi
        try {
            $allInstrumens = $this->akreditasiService->getInstrumens()->json('data') ?? [];
        } catch (\Throwable) {
            $allInstrumens = [];
        }

        foreach ($versis as &$v) {
            $vId = (string) ($v['id'] ?? '');
            $matching = array_filter($allInstrumens, function ($item) use ($vId) {
                $itemVersiId = (string) ($item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? ''));

                return $itemVersiId === $vId;
            });
            $v['instrumen_count'] = count($matching);
        }
        unset($v);

        return view('pages.superadmin.versi_instrumen', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'versis' => $versis,
        ]);
    }

    /**
     * Backward compatibility untuk rute lama /superadmin/versi_instrumen
     */
    public function indexLegacy(Request $request): RedirectResponse
    {
        $idLembaga = $request->query('id_lembaga');
        if ($idLembaga) {
            return redirect()->route('akreditasi.superadmin.versi_instrumen', $idLembaga);
        }

        return redirect()->route('akreditasi.superadmin.lembaga_akreditasi');
    }

    /**
     * Menyimpan data versi instrumen baru.
     */
    public function store(Request $request, int|string|null $lembagaId = null): RedirectResponse
    {
        $resolvedLembagaId = $lembagaId ?? $request->input('id_lembaga');

        $request->merge([
            'id_lembaga' => $resolvedLembagaId,
            'status_aktif' => filter_var($request->status_aktif, FILTER_VALIDATE_BOOLEAN),
        ]);

        $validated = $request->validate([
            'id_lembaga' => 'required|integer',
            'nomor_versi' => 'required|string|max:50',
            'tanggal_berlaku' => 'nullable|date',
            'status_aktif' => 'required|boolean',
        ]);

        try {
            $response = $this->akreditasiService->createVersiInstrumen($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.versi_instrumen', $validated['id_lembaga'])
                    ->with('success', 'Versi instrumen berhasil ditambahkan!');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan versi instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Mengubah data versi instrumen.
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        if ($request->has('status_aktif')) {
            $request->merge([
                'status_aktif' => filter_var($request->status_aktif, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        $validated = $request->validate([
            'id_lembaga' => 'nullable|integer',
            'nomor_versi' => 'required|string|max:50',
            'tanggal_berlaku' => 'nullable|date',
            'status_aktif' => 'nullable|boolean',
        ]);

        try {
            $response = $this->akreditasiService->updateVersiInstrumen($id, $validated);
            if ($response->successful()) {
                return back()->with('success', 'Versi instrumen berhasil diperbarui!');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui versi instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Menghapus data versi instrumen.
     */
    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteVersiInstrumen($id);
            if ($response->successful()) {
                return back()->with('success', 'Versi instrumen berhasil dihapus!');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus versi instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
