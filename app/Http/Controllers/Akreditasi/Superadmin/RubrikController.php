<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RubrikController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(Request $request): View
    {
        $indikatorId = $request->query('id_indikator');
        $rubriks = [];

        try {
            $params = $indikatorId ? ['id_indikator' => $indikatorId] : [];
            $rubriks = $this->akreditasiService->getRubriks($params)->json('data') ?? [];
        } catch (\Throwable) {
            $rubriks = [];
        }

        return view('pages.superadmin.rubrik', [
            'rubriks' => $rubriks,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_indikator' => 'required|integer',
            'skor' => 'required|integer|min:1|max:4',
            'deskripsi_kondisi' => 'required|string|max:1000',
        ]);

        try {
            $response = $this->akreditasiService->createRubrik($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.rubrik')
                    ->with('success', 'Rubrik penilaian berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan rubrik penilaian.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
