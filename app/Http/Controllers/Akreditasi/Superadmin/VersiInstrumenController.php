<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\JsonResponse;
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

            if (! array_key_exists('status_aktif', $v)) {
                $v['status_aktif'] = collect($v['jenjangs'] ?? [])->contains(
                    fn (array $jenjang): bool => filter_var(
                        $jenjang['pivot']['status_aktif'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    )
                );
            } else {
                $v['status_aktif'] = filter_var($v['status_aktif'], FILTER_VALIDATE_BOOLEAN);
            }
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
            'status_aktif' => 'required|boolean',
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
     * Mengaktifkan versi instrumen.
     */
    public function activate(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'id_jenjang' => ['required', 'integer'],
        ]);

        try {
            $response = $this->akreditasiService->activateVersiInstrumen(
                $id,
                $validated['id_jenjang']
            );

            $message = $response->json('message');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => $response->successful(),
                    'message' => $message ?? ($response->successful()
                        ? 'Versi instrumen berhasil diaktifkan untuk jenjang tersebut.'
                        : 'Gagal mengaktifkan versi instrumen.'),
                ], $response->status() ?: ($response->successful() ? 200 : 422));
            }

            if ($response->successful()) {
                return back()->with(
                    'success',
                    (string) (
                        $message
                        ?? 'Versi instrumen berhasil diaktifkan untuk jenjang tersebut.'
                    )
                );
            }

            return back()->with(
                'error',
                (string) (
                    $message
                    ?? 'Gagal mengaktifkan versi instrumen.'
                )
            );
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                ], 500);
            }

            return back()->with(
                'error',
                'Terjadi kesalahan saat mengaktifkan versi instrumen.'
            );
        }
    }

    /**
     * Menghapus data versi instrumen.
     */
    public function destroy(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteVersiInstrumen($id);
            $message = $response->json('message');

            if ($request->wantsJson() || $request->ajax()) {
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message ?? 'Versi instrumen berhasil dihapus!',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $message ?? 'Gagal menghapus versi instrumen.',
                ], $response->status() ?: 422);
            }

            if ($response->successful()) {
                return back()->with('success', 'Versi instrumen berhasil dihapus!');
            }

            return back()->with('error', (string) ($message ?? 'Gagal menghapus versi instrumen.'));
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
