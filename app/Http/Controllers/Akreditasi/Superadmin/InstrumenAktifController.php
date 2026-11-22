<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\View\View;

class InstrumenAktifController extends Controller
{
    protected AkreditasiService $akreditasiService;

    public function __construct(AkreditasiService $akreditasiService)
    {
        $this->akreditasiService = $akreditasiService;
    }

    /**
     * Menampilkan hierarki instrumen aktif LAM-INFOKOM 2.1 TRK.
     */
    public function index(): View
    {
        $instrumen = null;
        $error = null;

        try {
            $response = $this->akreditasiService->getActiveStaticInstrumen();

            if ($response->successful()) {
                $instrumen = $response->json('data');

                if (empty($instrumen)) {
                    $error = 'Data hierarki instrumen aktif tidak ditemukan.';
                }
            } else {
                $error = $response->json('message')
                    ?? 'Gagal mengambil data instrumen aktif dari server.';
            }
        } catch (\Throwable $e) {
            $error = 'Tidak dapat terhubung ke server. Silakan coba lagi nanti.';
        }

        return view('pages.superadmin.instrumen-aktif', compact(
            'instrumen',
            'error'
        ));
    }
}
