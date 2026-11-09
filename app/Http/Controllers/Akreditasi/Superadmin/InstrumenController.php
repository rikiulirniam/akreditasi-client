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
                $versiRes = $this->akreditasiService->getVersiInstrumen(['id_lembaga' => $lembagaId]);
                if ($versiRes->successful()) {
                    $versis = $versiRes->json('data') ?? [];
                }
            } catch (\Throwable) {
                $versis = [];
            }
        }

        if (empty($versis)) {
            try {
                $allVersi = $this->akreditasiService->getVersiInstrumen()->json('data') ?? [];
                $versis = array_values(array_filter($allVersi, function ($v) use ($lembagaId) {
                    $itemLembagaId = (string) ($v['id_lembaga'] ?? ($v['lembaga']['id'] ?? ''));

                    return $itemLembagaId === '' || $itemLembagaId === (string) $lembagaId;
                }));
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
                    'nomor_versi' => '2.1',
                    'status_aktif' => true,
                    'tanggal_berlaku' => null,
                    'id_lembaga' => $lembagaId,
                ];
            }
        }

        $activeVersiId = $versi['id'] ?? $resolvedVersiId;

        // 4. Fetch Instrumen for this Versi Instrumen
        try {
            $allInstrumens = $this->akreditasiService->getInstrumens()->json('data') ?? [];
            $targetVersiId = (string) $activeVersiId;

            $instrumens = array_values(array_filter($allInstrumens, function ($item) use ($targetVersiId) {
                $itemVersiId = (string) ($item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? ''));

                return $itemVersiId === $targetVersiId;
            }));
        } catch (\Throwable) {
            $instrumens = [];
        }

        return view('pages.superadmin.instrumen', [
            'lembagaId' => $lembagaId,
            'versiId' => $activeVersiId,
            'lembaga' => $lembaga,
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
            'id_versi_instrumen' => 'nullable|integer',
            'id_jenjang' => 'required|integer',
            'id_jenis_instrumen' => 'required|integer',
        ]);

        $targetVersiId = $validated['id_versi_instrumen'] ?? ($versiId ?: 1);

        $payload = [
            'id_versi_instrumen' => (int) $targetVersiId,
            'id_jenjang' => (int) $validated['id_jenjang'],
            'id_jenis_instrumen' => (int) $validated['id_jenis_instrumen'],
        ];

        try {
            $response = $this->akreditasiService->createInstrumen($payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.instrumen', [
                    'lembagaId' => $lembagaId,
                    'versiId' => $targetVersiId,
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
    public function update(Request $request, int|string $lembagaId, int|string $param2, int|string|null $param3 = null): RedirectResponse
    {
        if ($param3 !== null) {
            $versiId = $param2;
            $instrumenId = $param3;
        } else {
            $instrumenId = $param2;
            $versiId = $request->input('id_versi_instrumen');
        }

        $validated = $request->validate([
            'id_versi_instrumen' => 'nullable|integer',
            'id_jenjang' => 'required|integer',
            'id_jenis_instrumen' => 'required|integer',
        ]);

        $targetVersiId = $validated['id_versi_instrumen'] ?? ($versiId ?: 1);

        $payload = [
            'id_versi_instrumen' => (int) $targetVersiId,
            'id_jenjang' => (int) $validated['id_jenjang'],
            'id_jenis_instrumen' => (int) $validated['id_jenis_instrumen'],
        ];

        try {
            $response = $this->akreditasiService->updateInstrumen($instrumenId, $payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.instrumen', [
                    'lembagaId' => $lembagaId,
                    'versiId' => $targetVersiId,
                ])->with('success', 'Instrumen berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Menghapus data instrumen.
     */
    public function destroy(Request $request, int|string $lembagaId, int|string $param2, int|string|null $param3 = null): RedirectResponse
    {
        if ($param3 !== null) {
            $versiId = $param2;
            $instrumenId = $param3;
        } else {
            $instrumenId = $param2;
            $versiId = $request->input('versiId');
        }

        try {
            $response = $this->akreditasiService->deleteInstrumen($instrumenId);
            if ($response->successful()) {
                $params = ['lembagaId' => $lembagaId];
                if ($versiId) {
                    $params['versiId'] = $versiId;
                }

                return redirect()->route('akreditasi.superadmin.instrumen', $params)
                    ->with('success', 'Instrumen berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
