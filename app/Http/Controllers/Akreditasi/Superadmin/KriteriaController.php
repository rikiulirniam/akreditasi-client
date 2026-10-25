<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KriteriaController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(Request $request): View
    {
        $versiId = $request->query('id_versi_instrumen');
        $kriterias = [];
        $versis = [];

        try {
            $params = $versiId ? ['id_versi_instrumen' => $versiId] : [];
            $kriterias = $this->akreditasiService->getKriteria($params)->json('data') ?? [];
            $versis = $this->akreditasiService->getVersiInstrumen()->json('data') ?? [];
        } catch (\Throwable) {
            $kriterias = [];
        }

        return view('pages.superadmin.kriteria', [
            'kriterias' => $kriterias,
            'versis' => $versis,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_versi_instrumen' => 'required|integer',
            'kode_kriteria' => 'required|string|max:50',
            'nama_kriteria' => 'required|string|max:255',
        ]);

        try {
            $response = $this->akreditasiService->createKriteria($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.kriteria')
                    ->with('success', 'Kriteria berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan kriteria.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
