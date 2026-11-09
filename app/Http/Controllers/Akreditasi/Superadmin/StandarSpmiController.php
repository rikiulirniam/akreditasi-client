<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StandarSpmiController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(): View
    {
        $standars = [];
        try {
            $standars = $this->akreditasiService->getStandarMutu()->json('data') ?? [];
        } catch (\Throwable) {
            $standars = [];
        }

        return view('pages.superadmin.standar-spmi', [
            'standars' => $standars,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_standar' => 'required|string|max:50',
            'nama_standar' => 'required|string|max:255',
            'pernyataan_standar' => 'nullable|string|max:2000',
        ]);

        try {
            $response = $this->akreditasiService->createStandarMutu($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.standar_spmi')
                    ->with('success', 'Standar mutu SPMI berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan standar mutu.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
