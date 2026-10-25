<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\View\View;

class BobotController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(): View
    {
        $bobots = [];
        try {
            $indikators = $this->akreditasiService->getIndikators()->json('data') ?? [];
            foreach ($indikators as $ind) {
                if (! empty($ind['bobots'])) {
                    foreach ($ind['bobots'] as $b) {
                        $b['indikator_kode'] = $ind['kode_indikator'] ?? 'IND';
                        $b['indikator_nama'] = $ind['deskripsi_indikator'] ?? $ind['nama_indikator'] ?? '-';
                        $b['jenis_nama'] = 'Akreditasi Program Studi (APS)';
                        $b['jenjang_nama'] = 'Sarjana Terapan';
                        $bobots[] = $b;
                    }
                }
            }
        } catch (\Throwable) {
            $bobots = [];
        }

        return view('pages.superadmin.bobot', [
            'bobots' => $bobots,
        ]);
    }

    public function detail(): View
    {
        $indikator = null;
        try {
            $indikators = $this->akreditasiService->getIndikators()->json('data') ?? [];
            if (! empty($indikators)) {
                $indikator = $indikators[0];
            }
        } catch (\Throwable) {
            $indikator = null;
        }

        return view('pages.superadmin.detail-bobot', [
            'indikator' => $indikator,
        ]);
    }
}
