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
     * Tampilkan daftar versi instrumen untuk suatu lembaga akreditasi.
     */
    public function index(int|string $lembagaId): View
    {
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

        // Ambil daftar versi instrumen untuk lembaga ini
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

        // Hitung jumlah instrumen per versi
        try {
            $allInstrumens = $this->akreditasiService->getInstrumens()->json('data') ?? [];
        } catch (\Throwable) {
            $allInstrumens = [];
        }

        foreach ($versis as &$versi) {
            $vId = (string) ($versi['id'] ?? '');
            $matching = array_filter($allInstrumens, function ($item) use ($vId) {
                $itemVersiId = (string) ($item['id_versi_instrumen'] ?? ($item['versi_instrumen']['id'] ?? ''));

                return $itemVersiId !== '' && $itemVersiId === $vId;
            });
            $versi['instrumen_count'] = count($matching);
        }
        unset($versi);

        $versiAktif = collect($versis)->firstWhere('status_aktif', true) ?? ($versis[0] ?? null);

        return view('pages.superadmin.versi-instrumen', [
            'lembagaId' => $lembagaId,
            'lembaga' => $lembaga,
            'versis' => $versis,
            'versiAktif' => $versiAktif,
        ]);
    }

    /**
     * Tambah versi instrumen baru.
     */
    public function store(Request $request, int|string $lembagaId): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_versi' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:1000',
            'status_aktif' => 'nullable',
        ]);

        $payload = [
            'id_lembaga' => (int) $lembagaId,
            'nomor_versi' => trim($validated['nomor_versi']),
            'keterangan' => $validated['keterangan'] ?? null,
            'status_aktif' => $request->has('status_aktif') ? (bool) $request->input('status_aktif') : true,
        ];

        try {
            $response = $this->akreditasiService->createVersiInstrumen($payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.versi_instrumen', $lembagaId)
                    ->with('success', 'Versi instrumen berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan versi instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Perbarui versi instrumen.
     */
    public function update(Request $request, int|string $lembagaId, int|string $versiId): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_versi' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:1000',
            'status_aktif' => 'nullable',
        ]);

        $payload = [
            'id_lembaga' => (int) $lembagaId,
            'nomor_versi' => trim($validated['nomor_versi']),
            'keterangan' => $validated['keterangan'] ?? null,
            'status_aktif' => $request->has('status_aktif') ? (bool) $request->input('status_aktif') : false,
        ];

        try {
            $response = $this->akreditasiService->updateVersiInstrumen($versiId, $payload);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.versi_instrumen', $lembagaId)
                    ->with('success', 'Versi instrumen berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui versi instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Hapus versi instrumen.
     */
    public function destroy(int|string $lembagaId, int|string $versiId): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteVersiInstrumen($versiId);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.versi_instrumen', $lembagaId)
                    ->with('success', 'Versi instrumen berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus versi instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
