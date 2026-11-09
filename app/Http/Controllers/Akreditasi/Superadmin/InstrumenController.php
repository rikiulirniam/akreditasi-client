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

    public function index(int|string $lembagaId, int|string|null $versiId = null): View|RedirectResponse
    {
        $lembaga = null;
        $versis = [];
        $instrumens = [];

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

        // Tentukan versi yang sedang dibuka
        $versi = null;
        if ($versiId) {
            $versi = collect($versis)->firstWhere('id', $versiId);
        }

        if (! $versi) {
            $versi = collect($versis)->firstWhere('status_aktif', true) ?? ($versis[0] ?? null);
            if ($versi) {
                $versiId = $versi['id'];
            }
        }

        if (! $versi) {
            $versi = [
                'id' => $versiId ?: 1,
                'nomor_versi' => '2.1',
                'status_aktif' => true,
            ];
            $versiId = $versi['id'];
        }

        // Fetch Instrumen untuk versi ini
        try {
            $allInstrumens = $this->akreditasiService->getInstrumens()->json('data') ?? [];

            $instrumens = array_values(array_filter($allInstrumens, function ($item) use ($versiId) {
                $itemVersiId = (string) ($item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? ''));

                return $itemVersiId === (string) $versiId;
            }));
        } catch (\Throwable) {
            $instrumens = [];
        }

        return view('pages.superadmin.instrumen', [
            'lembagaId' => $lembagaId,
            'versiId' => $versiId,
            'lembaga' => $lembaga,
            'versi' => $versi,
            'versis' => $versis,
            'instrumens' => $instrumens,
        ]);
    }

    public function store(Request $request, int|string $lembagaId, int|string|null $versiId = null): RedirectResponse
    {
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
                return redirect()->route('akreditasi.superadmin.instrumen', ['lembagaId' => $lembagaId, 'versiId' => $targetVersiId])
                    ->with('success', 'Instrumen berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan instrumen.'));
        } catch (\Throwable) {
            return back()->with('error', 'Terjadi kesalahan saat menambahkan instrumen.');
        }
    }

    public function update(Request $request, int|string $lembagaId, int|string|null $versiId = null, int|string|null $instrumenId = null): RedirectResponse
    {
        if ($instrumenId === null) {
            $instrumenId = $versiId;
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
                return redirect()->route('akreditasi.superadmin.instrumen', ['lembagaId' => $lembagaId, 'versiId' => $targetVersiId])
                    ->with('success', 'Instrumen berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(Request $request, int|string $lembagaId, int|string|null $versiId = null, int|string|null $instrumenId = null): RedirectResponse
    {
        if ($instrumenId === null) {
            $instrumenId = $versiId;
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
