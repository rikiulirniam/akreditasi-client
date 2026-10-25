<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndikatorController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(Request $request): View
    {
        $subkriteriaId = $request->query('id_subkriteria');
        $indikators = [];

        try {
            $params = $subkriteriaId ? ['id_subkriteria' => $subkriteriaId] : [];
            $indikators = $this->akreditasiService->getIndikators($params)->json('data') ?? [];
        } catch (\Throwable) {
            $indikators = [];
        }

        return view('pages.superadmin.indikator', [
            'indikators' => $indikators,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_subkriteria' => 'required|integer',
            'kode_indikator' => 'required|string|max:50',
            'deskripsi_indikator' => 'required|string|max:1000',
            'parameter_penilaian' => 'nullable|string|max:1000',
        ]);

        try {
            $response = $this->akreditasiService->createIndikator($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.indikator')
                    ->with('success', 'Indikator berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan indikator.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
