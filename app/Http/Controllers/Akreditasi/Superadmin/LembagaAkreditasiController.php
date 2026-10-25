<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LembagaAkreditasiController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(): View
    {
        $lembagas = [];
        try {
            $lembagas = $this->akreditasiService->getLembagaAkreditasi()->json('data') ?? [];
        } catch (\Throwable) {
            $lembagas = [];
        }

        return view('pages.superadmin.lembaga-akreditasi', [
            'lembagas' => $lembagas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lembaga' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $response = $this->akreditasiService->createLembagaAkreditasi($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.lembaga_akreditasi')
                    ->with('success', 'Lembaga akreditasi berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan lembaga akreditasi.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lembaga' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $response = $this->akreditasiService->updateLembagaAkreditasi($id, $validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.lembaga_akreditasi')
                    ->with('success', 'Lembaga akreditasi berhasil diperbarui.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal memperbarui lembaga akreditasi.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteLembagaAkreditasi($id);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.lembaga_akreditasi')
                    ->with('success', 'Lembaga akreditasi berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus lembaga akreditasi.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
