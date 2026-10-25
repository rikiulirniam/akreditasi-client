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

    public function index(): View
    {
        $versis = [];
        $instrumens = [];
        $versiAktif = null;

        try {
            $versis = $this->akreditasiService->getVersiInstrumen()->json('data') ?? [];
            $instrumens = $this->akreditasiService->getInstrumens()->json('data') ?? [];
        } catch (\Throwable) {
            // Abaikan kesalahan koneksi
        }

        if (! empty($versis)) {
            $versiAktif = $versis[0];
            foreach ($versis as $v) {
                if (! empty($v['status_aktif'])) {
                    $versiAktif = $v;
                    break;
                }
            }
        }

        return view('pages.superadmin.instrumen', [
            'versis' => $versis,
            'versiAktif' => $versiAktif,
            'instrumens' => $instrumens,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_versi_instrumen' => 'required|integer',
            'id_jenjang' => 'required|integer',
            'id_jenis_instrumen' => 'required|integer',
        ]);

        try {
            $response = $this->akreditasiService->createInstrumen($validated);
            if ($response->successful()) {
                return redirect()->route('akreditasi.superadmin.instrumen')
                    ->with('success', 'Instrumen berhasil ditambahkan.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menambahkan instrumen.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
